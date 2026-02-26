<?php
require_once '../../../includes/config.php';
require_once '../../../includes/db.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/functions.php';

if (!estaLogueado()) {
    header('Location: ../login.php');
    exit;
}

$id_proyecto = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$db = new Database();

// Obtener el proyecto y sus relaciones
$db->query("
    SELECT 
        p.*, 
        pr.nombre AS programa_nombre, 
        e.nombre AS entidad_nombre
    FROM proyectos p
    LEFT JOIN programas pr ON p.id_programa = pr.id_programa
    LEFT JOIN entidades_receptoras e ON p.id_entidad = e.id_entidad
    WHERE p.id_proyecto = :id_proyecto
");
$db->bind(':id_proyecto', $id_proyecto);
$proyecto = $db->single();

if (!$proyecto) {
    header('Location: index.php?mensaje=no_encontrado');
    exit;
}

// Obtener fases asociadas
$db->query("SELECT COUNT(*) as total FROM fases WHERE id_proyecto = :id");
$db->bind(':id', $id_proyecto);
$total_fases = $db->single()['total'];

// Obtener docentes únicos en fases del proyecto
$db->query("SELECT COUNT(DISTINCT df.id_docente) as total FROM docentes_fases df 
            INNER JOIN fases f ON df.id_fase = f.id_fase 
            WHERE f.id_proyecto = :id");
$db->bind(':id', $id_proyecto);
$total_docentes = $db->single()['total'];

// Obtener estudiantes únicos en participaciones de fases del proyecto
$db->query("SELECT COUNT(DISTINCT pe.id_estudiante) as total FROM participaciones_estudiantes pe 
            INNER JOIN fases f ON pe.id_fase = f.id_fase 
            WHERE f.id_proyecto = :id");
$db->bind(':id', $id_proyecto);
$total_estudiantes = $db->single()['total'];

// Obtener total de beneficiados (suma de cantidad_beneficiados en fases)
$db->query("SELECT SUM(f.cantidad_beneficiados) as total FROM fases f WHERE f.id_proyecto = :id");
$db->bind(':id', $id_proyecto);
$resultado = $db->single();
$total_beneficiados = $resultado['total'] ?? 0;

// Obtener horas totales asignadas y cumplidas
$db->query("SELECT SUM(pe.horas_asignadas) as asignadas, SUM(pe.horas_cumplidas) as cumplidas 
            FROM participaciones_estudiantes pe 
            INNER JOIN fases f ON pe.id_fase = f.id_fase 
            WHERE f.id_proyecto = :id");
$db->bind(':id', $id_proyecto);
$horas = $db->single();
$horas_asignadas = $horas['asignadas'] ?? 0;
$horas_cumplidas = $horas['cumplidas'] ?? 0;

include '../header_admin.php';
?>

<div class="container py-4">
    <a href="index.php" class="btn btn-secondary mb-3">
        <i class="fas fa-arrow-left me-2"></i>Volver
    </a>
    
    <h1 class="mb-4"><?php echo htmlspecialchars($proyecto['nombre']); ?></h1>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Información del Proyecto</h5>
                </div>
                <div class="card-body">
                    <?php if ($proyecto['banner']): ?>
                        <img src="<?php echo UPLOAD_URL . 'proyectos/' . htmlspecialchars($proyecto['banner']); ?>" alt="Banner" class="img-fluid mb-3 rounded" style="max-width: 400px;">
                    <?php endif; ?>
                    
                    <table class="table table-borderless">
                        <tr>
                            <th width="30%">Programa:</th>
                            <td><?php echo htmlspecialchars($proyecto['programa_nombre']); ?></td>
                        </tr>
                        <tr>
                            <th>Entidad Receptora:</th>
                            <td><?php echo htmlspecialchars($proyecto['entidad_nombre'] ?? 'Sin asignar'); ?></td>
                        </tr>
                        <tr>
                            <th>Descripción Corta:</th>
                            <td><?php echo htmlspecialchars($proyecto['descripcion_corta']); ?></td>
                        </tr>
                        <tr>
                            <th>Comunidad Beneficiaria:</th>
                            <td><?php echo htmlspecialchars($proyecto['comunidad_beneficiaria']); ?></td>
                        </tr>
                        <tr>
                            <th>Objetivos:</th>
                            <td><?php echo nl2br(htmlspecialchars($proyecto['objetivos'])); ?></td>
                        </tr>
                        <tr>
                            <th>Resultados Esperados:</th>
                            <td><?php echo nl2br(htmlspecialchars($proyecto['resultados_esperados'])); ?></td>
                        </tr>
                        <tr>
                            <th>Fecha de Inicio:</th>
                            <td><?php echo formatearFecha($proyecto['fecha_inicio'], 'd/m/Y'); ?></td>
                        </tr>
                        <tr>
                            <th>Fecha de Fin:</th>
                            <td><?php echo formatearFecha($proyecto['fecha_fin'], 'd/m/Y'); ?></td>
                        </tr>
                        <tr>
                            <th>Estado:</th>
                            <td>
                                <span class="badge bg-<?php echo ($proyecto['estado'] == 'En ejecución') ? 'primary' : (($proyecto['estado'] == 'Finalizado') ? 'success' : 'warning'); ?>">
                                    <?php echo htmlspecialchars($proyecto['estado']); ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Publicado:</th>
                            <td>
                                <span class="badge bg-<?php echo $proyecto['publicado'] ? 'success' : 'danger'; ?>">
                                    <?php echo $proyecto['publicado'] ? 'Sí' : 'No'; ?>
                                </span>
                            </td>
                        </tr>
                    </table>
                    
                    <?php if ($_SESSION['rol'] != 'visualizador'): ?>
                    <div class="mt-3">
                        <a href="editar.php?id=<?php echo $proyecto['id_proyecto']; ?>" class="btn btn-primary">
                            <i class="fas fa-edit me-2"></i>Editar
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Métricas de Proyecto</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3 text-center pb-3 border-bottom">
                        <h3 class="text-primary"><?php echo $total_fases; ?></h3>
                        <p class="text-muted">Fases</p>
                    </div>
                    
                    <div class="mb-3 text-center pb-3 border-bottom">
                        <h3 class="text-success"><?php echo $total_docentes; ?></h3>
                        <p class="text-muted">Docentes Asignados</p>
                    </div>
                    
                    <div class="mb-3 text-center pb-3 border-bottom">
                        <h3 class="text-info"><?php echo $total_estudiantes; ?></h3>
                        <p class="text-muted">Estudiantes Participantes</p>
                    </div>
                    
                    <div class="mb-3 text-center pb-3 border-bottom">
                        <h3 class="text-warning"><?php echo $total_beneficiados; ?></h3>
                        <p class="text-muted">Beneficiados Totales</p>
                    </div>
                    
                    <div class="text-center">
                        <small class="text-muted">
                            <strong>Horas:</strong><br>
                            Asignadas: <?php echo $horas_asignadas; ?><br>
                            Cumplidas: <?php echo $horas_cumplidas; ?>
                        </small>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Acciones</h5>
                </div>
                <div class="card-body">
                    <a href="../fases/index.php?proyecto=<?php echo $id_proyecto; ?>" class="btn btn-sm btn-outline-primary w-100 mb-2">
                        <i class="fas fa-sitemap me-1"></i>Ver Fases
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../footer_admin.php'; ?>