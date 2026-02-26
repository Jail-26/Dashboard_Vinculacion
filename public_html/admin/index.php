<?php
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

// Verificar si el usuario está logueado
if (!estaLogueado()) {
    header('Location: login.php');
    exit;
}

// Conexión a la base de datos
$db = new Database();

// Obtener estadísticas
// Total de programas
$db->query("SELECT COUNT(*) as total FROM programas");
$total_programas = $db->single()['total'];

// Total de proyectos
$db->query("SELECT COUNT(*) as total FROM proyectos");
$total_proyectos = $db->single()['total'];

// Total de carreras
$db->query("SELECT COUNT(*) as total FROM carreras WHERE estado = 'Activa'");
$total_carreras = $db->single()['total'];

// Total de docentes
$db->query("SELECT COUNT(*) as total FROM docentes WHERE estado = 'Activo'");
$total_docentes = $db->single()['total'];

// Total de estudiantes
$db->query("SELECT COUNT(*) as total FROM estudiantes WHERE estado = 'Activo'");
$total_estudiantes = $db->single()['total'];

// Total de fases
$db->query("SELECT COUNT(*) as total FROM fases WHERE estado IN ('Pendiente', 'En ejecución')");
$total_fases_activas = $db->single()['total'];

// Proyectos por estado
$db->query("SELECT estado, COUNT(*) as total FROM proyectos GROUP BY estado");
$proyectos_por_estado = $db->resultSet();

// Últimos proyectos agregados
$db->query("SELECT p.*, pr.nombre as nombre_programa 
            FROM proyectos p 
            LEFT JOIN programas pr ON p.id_programa = pr.id_programa 
            ORDER BY p.fecha_creacion DESC LIMIT 5");
$ultimos_proyectos = $db->resultSet();

// Últimos cambios en el historial
$db->query("SELECT h.*, u.nombre, u.apellido 
            FROM historial_cambios h 
            LEFT JOIN usuarios u ON h.id_usuario = u.id_usuario 
            ORDER BY h.fecha_cambio DESC LIMIT 10");
$ultimos_cambios = $db->resultSet();

// Incluir el header
include 'header_admin.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="mb-4">Panel de Administración</h1>
            
            <?php if (isset($_GET['error']) && $_GET['error'] == 'permisos'): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                No tienes permisos para acceder a esta sección.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>
            
            <!-- Tarjetas de estadísticas -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title">Total Programas</h5>
                                    <h2 class="mb-0"><?php echo $total_programas; ?></h2>
                                </div>
                                <div>
                                    <i class="fas fa-folder-open fa-3x"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-0 text-white">
                            <a href="programas/index.php" class="text-white">Ver todos <i class="fas fa-arrow-right ms-1"></i></a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title">Total Proyectos</h5>
                                    <h2 class="mb-0"><?php echo $total_proyectos; ?></h2>
                                </div>
                                <div>
                                    <i class="fas fa-tasks fa-3x"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-0 text-white">
                            <a href="proyectos/index.php" class="text-white">Ver todos <i class="fas fa-arrow-right ms-1"></i></a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title">Proyectos Completados</h5>
                                    <?php 
                                    $completados = 0;
                                    foreach ($proyectos_por_estado as $estado) {
                                        if ($estado['estado'] == 'Finalizado') {
                                            $completados = $estado['total'];
                                            break;
                                        }
                                    }
                                    ?>
                                    <h2 class="mb-0"><?php echo $completados; ?></h2>
                                </div>
                                <div>
                                    <i class="fas fa-check-circle fa-3x"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-0 text-white">
                            <a href="proyectos/index.php?estado=completado" class="text-white">Ver detalles <i class="fas fa-arrow-right ms-1"></i></a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Segunda fila de estadísticas -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title">Carreras Activas</h5>
                                    <h2 class="mb-0"><?php echo $total_carreras; ?></h2>
                                </div>
                                <div>
                                    <i class="fas fa-graduation-cap fa-3x"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-0 text-white">
                            <a href="carreras/index.php" class="text-white">Ver todos <i class="fas fa-arrow-right ms-1"></i></a>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title">Docentes Activos</h5>
                                    <h2 class="mb-0"><?php echo $total_docentes; ?></h2>
                                </div>
                                <div>
                                    <i class="fas fa-chalkboard-user fa-3x"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-0 text-white">
                            <a href="docentes/index.php" class="text-white">Ver todos <i class="fas fa-arrow-right ms-1"></i></a>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card bg-secondary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title">Estudiantes Activos</h5>
                                    <h2 class="mb-0"><?php echo $total_estudiantes; ?></h2>
                                </div>
                                <div>
                                    <i class="fas fa-user-graduate fa-3x"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-0 text-white">
                            <a href="estudiantes/index.php" class="text-white">Ver todos <i class="fas fa-arrow-right ms-1"></i></a>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card bg-dark text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title">Fases Activas</h5>
                                    <h2 class="mb-0"><?php echo $total_fases_activas; ?></h2>
                                </div>
                                <div>
                                    <i class="fas fa-sitemap fa-3x"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-0 text-white">
                            <a href="fases/index.php" class="text-white">Ver todos <i class="fas fa-arrow-right ms-1"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <!-- Últimos proyectos -->
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0">Últimos Proyectos Agregados</h5>
                        </div>
                        <div class="card-body">
                            <?php if (count($ultimos_proyectos) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Nombre</th>
                                                <th>Programa</th>
                                                <th>Estado</th>
                                                <th>Fecha</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($ultimos_proyectos as $proyecto): ?>
                                                <tr>
                                                    <td><a href="proyectos/ver.php?id=<?php echo $proyecto['id_proyecto']; ?>"><?php echo $proyecto['nombre']; ?></a></td>
                                                    <td><?php echo $proyecto['nombre_programa']; ?></td>
                                                    <td>
                                                        <?php 
                                                        $estado_class = '';
                                                        switch ($proyecto['estado']) {
                                                            case 'planificación':
                                                                $estado_class = 'text-secondary';
                                                                break;
                                                            case 'ejecución':
                                                                $estado_class = 'text-primary';
                                                                break;
                                                            case 'completado':
                                                                $estado_class = 'text-success';
                                                                break;
                                                            case 'cancelado':
                                                                $estado_class = 'text-danger';
                                                                break;
                                                        }
                                                        ?>
                                                        <span class="<?php echo $estado_class; ?>"><?php echo ucfirst($proyecto['estado']); ?></span>
                                                    </td>
                                                    <td><?php echo formatearFecha($proyecto['fecha_creacion']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">No hay proyectos registrados.</p>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer bg-white">
                            <a href="proyectos/index.php" class="btn btn-sm btn-outline-primary">Ver todos los proyectos</a>
                        </div>
                    </div>
                </div>
                
                <!-- Historial de cambios recientes -->
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0">Historial de Cambios Recientes</h5>
                        </div>
                        <div class="card-body">
                            <?php if (count($ultimos_cambios) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Tabla</th>
                                                <th>Acción</th>
                                                <th>Usuario</th>
                                                <th>Fecha</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($ultimos_cambios as $cambio): ?>
                                                <tr>
                                                    <td><?php echo ucfirst($cambio['tabla_afectada']); ?></td>
                                                    <td>
                                                        <?php 
                                                        $accion_class = '';
                                                        switch ($cambio['tipo_cambio']) {
                                                            case 'creación':
                                                                $accion_class = 'text-success';
                                                                break;
                                                            case 'modificación':
                                                                $accion_class = 'text-primary';
                                                                break;
                                                            case 'eliminación':
                                                                $accion_class = 'text-danger';
                                                                break;
                                                        }
                                                        ?>
                                                        <span class="<?php echo $accion_class; ?>"><?php echo ucfirst($cambio['tipo_cambio']); ?></span>
                                                    </td>
                                                    <td><?php echo $cambio['nombre'] . ' ' . $cambio['apellido']; ?></td>
                                                    <td><?php echo formatearFecha($cambio['fecha_cambio'], 'd/m/Y H:i'); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">No hay cambios registrados.</p>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer bg-white">
                            <a href="historial/index.php" class="btn btn-sm btn-outline-primary">Ver historial completo</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer_admin.php'; ?>