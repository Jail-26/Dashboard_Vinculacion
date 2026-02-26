<?php
require_once '../../../includes/config.php';
require_once '../../../includes/db.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/functions.php';

if (!estaLogueado()) {
    header('Location: ../login.php');
    exit;
}

$db = new Database();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$db->query("SELECT f.*, p.nombre as nombre_proyecto, pa.nombre as nombre_periodo FROM fases f 
            LEFT JOIN proyectos p ON f.id_proyecto = p.id_proyecto 
            LEFT JOIN periodos_academicos pa ON f.id_periodo = pa.id_periodo 
            WHERE f.id_fase = :id");
$db->bind(':id', $id);
$fase = $db->single();

if (!$fase) {
    header('Location: index.php?mensaje=error');
    exit;
}

// Obtener docentes asignados
$db->query("SELECT df.*, d.nombres, d.apellidos FROM docentes_fases df 
            LEFT JOIN docentes d ON df.id_docente = d.id_docente 
            WHERE df.id_fase = :id ORDER BY d.apellidos");
$db->bind(':id', $id);
$docentes_asignados = $db->resultSet();

// Obtener estudiantes participando
$db->query("SELECT pe.*, e.nombres, e.apellidos FROM participaciones_estudiantes pe 
            LEFT JOIN estudiantes e ON pe.id_estudiante = e.id_estudiante 
            WHERE pe.id_fase = :id ORDER BY e.apellidos");
$db->bind(':id', $id);
$estudiantes_participando = $db->resultSet();

// Obtener documentos de la fase
$db->query("SELECT * FROM documentos_fases WHERE id_fase = :id ORDER BY fecha_subida DESC");
$db->bind(':id', $id);
$documentos = $db->resultSet();

// Contar métricas
$total_docentes = count($docentes_asignados);
$total_estudiantes = count($estudiantes_participando);
$total_horas_asignadas = 0;
$total_horas_cumplidas = 0;
$promedio_calificacion = 0;

foreach ($estudiantes_participando as $est) {
    $total_horas_asignadas += (int)$est['horas_asignadas'];
    $total_horas_cumplidas += (int)$est['horas_cumplidas'];
    if ($est['calificacion']) {
        $promedio_calificacion += (float)$est['calificacion'];
    }
}

if (count($estudiantes_participando) > 0) {
    $promedio_calificacion = $promedio_calificacion / count($estudiantes_participando);
}

include '../header_admin.php';
?>

<div class="container-fluid py-4">
    <a href="index.php" class="btn btn-secondary mb-3">
        <i class="fas fa-arrow-left me-2"></i>Volver
    </a>
    
    <h1 class="mb-4"><?php echo htmlspecialchars($fase['nombre']); ?></h1>
    
    <!-- Banner de la fase -->
    <?php if ($fase['banner']): ?>
        <div class="mb-4">
            <img src="<?php echo UPLOAD_URL . 'fases/' . htmlspecialchars($fase['banner']); ?>" alt="Banner" class="img-fluid rounded" style="max-width: 100%; max-height: 300px; object-fit: cover;">
        </div>
    <?php endif; ?>
    
    <!-- Información de la fase -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Información de la Fase</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="25%">Nombre:</th>
                            <td><?php echo htmlspecialchars($fase['nombre']); ?></td>
                        </tr>
                        <tr>
                            <th>Descripción:</th>
                            <td><?php echo html_entity_decode($fase['descripcion']); ?></td>
                        </tr>
                        <tr>
                            <th>Proyecto:</th>
                            <td><?php echo $fase['nombre_proyecto'] ? htmlspecialchars($fase['nombre_proyecto']) : 'Sin asignar'; ?></td>
                        </tr>
                        <tr>
                            <th>Período:</th>
                            <td><?php echo htmlspecialchars($fase['nombre_periodo']); ?></td>
                        </tr>
                        <tr>
                            <th>Fecha Inicio:</th>
                            <td><?php echo $fase['fecha_inicio'] ? formatearFecha($fase['fecha_inicio']) : '-'; ?></td>
                        </tr>
                        <tr>
                            <th>Fecha Fin:</th>
                            <td><?php echo $fase['fecha_fin'] ? formatearFecha($fase['fecha_fin']) : '-'; ?></td>
                        </tr>
                        <tr>
                            <th>Estado:</th>
                            <td>
                                <span class="badge bg-<?php echo ($fase['estado'] == 'Pendiente') ? 'secondary' : (($fase['estado'] == 'En ejecución') ? 'primary' : 'success'); ?>">
                                    <?php echo htmlspecialchars($fase['estado']); ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Publicado:</th>
                            <td>
                                <span class="badge bg-<?php echo $fase['publicado'] ? 'success' : 'danger'; ?>">
                                    <?php echo $fase['publicado'] ? 'Sí' : 'No'; ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Beneficiados:</th>
                            <td><?php echo (int)$fase['cantidad_beneficiados']; ?></td>
                        </tr>
                    </table>
                    
                    <?php if ($_SESSION['rol'] != 'visualizador'): ?>
                    <div class="mt-3">
                        <a href="editar.php?id=<?php echo $fase['id_fase']; ?>" class="btn btn-primary">
                            <i class="fas fa-edit me-2"></i>Editar Fase
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Métricas</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3 text-center pb-3 border-bottom">
                        <h3 class="text-primary"><?php echo $total_docentes; ?></h3>
                        <p class="text-muted">Docentes</p>
                    </div>
                    <div class="mb-3 text-center pb-3 border-bottom">
                        <h3 class="text-info"><?php echo $total_estudiantes; ?></h3>
                        <p class="text-muted">Estudiantes</p>
                    </div>
                    <div class="mb-3 text-center pb-3 border-bottom">
                        <h3 class="text-success"><?php echo $total_horas_cumplidas . '/' . $total_horas_asignadas; ?></h3>
                        <p class="text-muted">Horas</p>
                    </div>
                    <div class="text-center">
                        <h3 class="text-warning"><?php echo number_format($promedio_calificacion, 2); ?></h3>
                        <p class="text-muted">Promedio Calificación</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Sección de Docentes -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Docentes Asignados</h5>
                    <?php if ($_SESSION['rol'] != 'visualizador'): ?>
                    <a href="../docentes_fases/crear.php?fase=<?php echo $id; ?>" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus me-1"></i>Asignar Docente
                    </a>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (count($docentes_asignados) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Docente</th>
                                        <th>Rol</th>
                                        <th>Fecha Asignación</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($docentes_asignados as $doc): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($doc['nombres'] . ' ' . $doc['apellidos']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo ($doc['rol'] == 'Responsable') ? 'success' : 'info'; ?>">
                                                    <?php echo htmlspecialchars($doc['rol']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo formatearFecha($doc['fecha_asignacion'], 'd/m/Y'); ?></td>
                                            <td>
                                                <?php if ($_SESSION['rol'] != 'visualizador'): ?>
                                                <a href="../docentes_fases/editar.php?id=<?php echo $doc['id_docente_fase']; ?>" class="btn btn-sm btn-primary" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="../docentes_fases/eliminar.php?id=<?php echo $doc['id_docente_fase']; ?>" class="btn btn-sm btn-danger" title="Eliminar" onclick="return confirm('¿Estás seguro?');">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No hay docentes asignados a esta fase.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Sección de Estudiantes -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Participaciones de Estudiantes</h5>
                    <?php if ($_SESSION['rol'] != 'visualizador'): ?>
                    <a href="../participaciones_estudiantes/crear.php?fase=<?php echo $id; ?>" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus me-1"></i>Agregar Estudiante
                    </a>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (count($estudiantes_participando) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Estudiante</th>
                                        <th>Calificación</th>
                                        <th>Horas Asignadas</th>
                                        <th>Horas Cumplidas</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($estudiantes_participando as $est): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($est['nombres'] . ' ' . $est['apellidos']); ?></td>
                                            <td><?php echo $est['calificacion'] ? number_format($est['calificacion'], 2) : '-'; ?></td>
                                            <td><?php echo (int)$est['horas_asignadas']; ?></td>
                                            <td><?php echo (int)$est['horas_cumplidas']; ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo ($est['estado'] == 'Activo') ? 'success' : 'secondary'; ?>">
                                                    <?php echo htmlspecialchars($est['estado']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($_SESSION['rol'] != 'visualizador'): ?>
                                                <a href="../participaciones_estudiantes/editar.php?id=<?php echo $est['id_participacion']; ?>" class="btn btn-sm btn-primary" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="../participaciones_estudiantes/eliminar.php?id=<?php echo $est['id_participacion']; ?>" class="btn btn-sm btn-danger" title="Eliminar" onclick="return confirm('¿Estás seguro?');">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No hay estudiantes participando en esta fase.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Sección de Documentos -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Documentos de la Fase</h5>
                    <?php if ($_SESSION['rol'] != 'visualizador'): ?>
                    <a href="../documentos_fases/crear.php?fase=<?php echo $id; ?>" class="btn btn-sm btn-primary">
                        <i class="fas fa-upload me-1"></i>Subir Documento
                    </a>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (count($documentos) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Tipo</th>
                                        <th>Fecha Subida</th>
                                        <th>Descargar</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($documentos as $doc): ?>
                                        <tr>
                                            <td><?php echo truncarTexto(htmlspecialchars($doc['nombre']), 40); ?></td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    <?php echo htmlspecialchars($doc['tipo']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo formatearFecha($doc['fecha_subida'], 'd/m/Y H:i'); ?></td>
                                            <td>
                                                <?php if ($doc['archivo_url']): ?>
                                                    <a href="<?php echo  $doc['archivo_url']; ?>" target="_blank" class="btn btn-sm btn-success" title="Descargar">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($_SESSION['rol'] != 'visualizador'): ?>
                                                <a href="../documentos_fases/editar.php?id=<?php echo $doc['id_documento']; ?>" class="btn btn-sm btn-primary" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="../documentos_fases/eliminar.php?id=<?php echo $doc['id_documento']; ?>" class="btn btn-sm btn-danger" title="Eliminar" onclick="return confirm('¿Estás seguro?');">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No hay documentos subidos para esta fase.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../footer_admin.php'; ?>
