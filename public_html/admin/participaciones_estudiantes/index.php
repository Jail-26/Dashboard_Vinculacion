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

$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$registros_por_pagina = 15;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

$busqueda = isset($_GET['busqueda']) ? limpiarDatos($_GET['busqueda']) : '';

if (!empty($busqueda)) {
    $db->query("SELECT COUNT(*) as total FROM participaciones_estudiantes pe 
                LEFT JOIN estudiantes e ON pe.id_estudiante = e.id_estudiante 
                LEFT JOIN fases f ON pe.id_fase = f.id_fase 
                WHERE CONCAT(e.nombres, ' ', e.apellidos) LIKE :busqueda OR f.nombre LIKE :busqueda1");
    $db->bind(':busqueda', "%$busqueda%", PDO::PARAM_STR);
    $db->bind(':busqueda1', "%$busqueda%", PDO::PARAM_STR);

} else {
    $db->query("SELECT COUNT(*) as total FROM participaciones_estudiantes");
}
$total_resultado = $db->single();
$total_registros = $total_resultado['total'] ?? 0;
$total_paginas = ceil($total_registros / $registros_por_pagina);

if (!empty($busqueda)) {
    $db->query("SELECT pe.*, e.nombres, e.apellidos, f.nombre as nombre_fase FROM participaciones_estudiantes pe 
                LEFT JOIN estudiantes e ON pe.id_estudiante = e.id_estudiante 
                LEFT JOIN fases f ON pe.id_fase = f.id_fase 
                WHERE CONCAT(e.nombres, ' ', e.apellidos) LIKE :busqueda OR f.nombre LIKE :busqueda1
                ORDER BY pe.fecha_registro DESC LIMIT :limit OFFSET :offset");
    $db->bind(':busqueda', "%$busqueda%", PDO::PARAM_STR);
    $db->bind(':busqueda1', "%$busqueda%", PDO::PARAM_STR);

} else {
    $db->query("SELECT pe.*, e.nombres, e.apellidos, f.nombre as nombre_fase FROM participaciones_estudiantes pe 
                LEFT JOIN estudiantes e ON pe.id_estudiante = e.id_estudiante 
                LEFT JOIN fases f ON pe.id_fase = f.id_fase 
                ORDER BY pe.fecha_registro DESC LIMIT :limit OFFSET :offset");
}
$db->bind(':limit', $registros_por_pagina, PDO::PARAM_INT);
$db->bind(':offset', $offset, PDO::PARAM_INT);
$participaciones = $db->resultSet();
if (!$participaciones) {
    $participaciones = [];
}

include '../header_admin.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Participaciones de Estudiantes</h1>
        <?php if ($_SESSION['rol'] != 'visualizador'): ?>
        <a href="crear.php" class="btn btn-primary">
            <i class="fas fa-plus-circle me-2"></i>Nueva Participación
        </a>
        <?php endif; ?>
    </div>
    
    <?php if (isset($_GET['mensaje'])): ?>
        <?php
        $tipo_alerta = 'success';
        $mensaje = isset($_GET['mensaje']) ? ($_GET['mensaje'] == 'creado' ? 'Participación creada exitosamente.' : ($_GET['mensaje'] == 'actualizado' ? 'Participación actualizada.' : ($_GET['mensaje'] == 'eliminado' ? 'Participación eliminada.' : 'Ha ocurrido un error.'))) : '';
        $tipo_alerta = isset($_GET['mensaje']) && $_GET['mensaje'] == 'error' ? 'danger' : 'success';
        ?>
        <div class="alert alert-<?php echo $tipo_alerta; ?> alert-dismissible fade show" role="alert">
            <?php echo $mensaje; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header bg-white">
            <h5 class="card-title mb-0">Listado de Participaciones</h5>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <form method="GET" class="row g-3">
                    <div class="col-md-9">
                        <input type="text" name="busqueda" class="form-control" placeholder="Buscar por estudiante o fase..." value="<?php echo $busqueda; ?>">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-2"></i>Buscar
                        </button>
                    </div>
                </form>
            </div>
            
            <?php if (!empty($busqueda)): ?>
                <p class="text-muted mb-3">
                    Resultados para: <strong><?php echo htmlspecialchars($busqueda); ?></strong>
                    <a href="index.php" class="ms-2">Limpiar búsqueda</a>
                </p>
            <?php endif; ?>
            
            <?php if (count($participaciones) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Estudiante</th>
                                <th>Fase</th>
                                <th>Horas Asignadas</th>
                                <th>Horas Cumplidas</th>
                                <th>Calificación</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($participaciones as $part): ?>
                                <tr>
                                    <td><?php echo $part['nombres'] . ' ' . $part['apellidos']; ?></td>
                                    <td><?php echo $part['nombre_fase']; ?></td>
                                    <td><?php echo $part['horas_asignadas']; ?> hrs</td>
                                    <td><?php echo $part['horas_cumplidas']; ?> hrs</td>
                                    <td><?php echo $part['calificacion'] ? number_format($part['calificacion'], 2) : '-'; ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo ($part['estado'] == 'Activo') ? 'success' : 'secondary'; ?>">
                                            <?php echo ucfirst($part['estado']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="editar.php?id=<?php echo $part['id_participacion']; ?>" class="btn btn-primary" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($_SESSION['rol'] != 'visualizador'): ?>
                                            <a href="eliminar.php?id=<?php echo $part['id_participacion']; ?>" class="btn btn-danger" title="Eliminar" onclick="return confirm('¿Seguro?');">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if ($total_paginas > 1): ?>
                    <nav aria-label="Paginación">
                        <ul class="pagination justify-content-center mt-4">
                            <li class="page-item <?php echo ($pagina_actual <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?pagina=<?php echo $pagina_actual - 1; ?><?php echo !empty($busqueda) ? '&busqueda=' . urlencode($busqueda) : ''; ?>" aria-label="Anterior">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                            <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                                <li class="page-item <?php echo ($pagina_actual == $i) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?pagina=<?php echo $i; ?><?php echo !empty($busqueda) ? '&busqueda=' . urlencode($busqueda) : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?php echo ($pagina_actual >= $total_paginas) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?pagina=<?php echo $pagina_actual + 1; ?><?php echo !empty($busqueda) ? '&busqueda=' . urlencode($busqueda) : ''; ?>" aria-label="Siguiente">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php else: ?>
                <p class="text-center">No hay participaciones registradas.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../footer_admin.php'; ?>
