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

// Obtener periodos para el filtro
$db->query("SELECT id_periodo, nombre FROM periodos_academicos ORDER BY nombre DESC");
$periodos = $db->resultSet();

$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$registros_por_pagina = 10;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

$busqueda = isset($_GET['busqueda']) ? limpiarDatos($_GET['busqueda']) : '';
$id_proyecto = isset($_GET['id_proyecto']) ? (int)$_GET['id_proyecto'] : 0;
$id_periodo = isset($_GET['id_periodo']) ? (int)$_GET['id_periodo'] : 0;
$busquedaLike = '%' . $busqueda . '%';

$where = "WHERE 1=1";
if (!empty($busqueda)) {
    $where .= " AND f.nombre LIKE :busqueda";
}
if ($id_proyecto > 0) {
    $where .= " AND f.id_proyecto = :id_proyecto";
}
if ($id_periodo > 0) {
    $where .= " AND f.id_periodo = :id_periodo";
}

$db->query("SELECT COUNT(*) as total FROM fases f " . $where);
if (!empty($busqueda)) {
    $db->bind(':busqueda', $busquedaLike);
}
if ($id_proyecto > 0) {
    $db->bind(':id_proyecto', $id_proyecto);
}
if ($id_periodo > 0) {
    $db->bind(':id_periodo', $id_periodo);
}
$total_resultado = $db->single();
$total_registros = $total_resultado['total'] ?? 0;
$total_paginas = ceil($total_registros / $registros_por_pagina);

$db->query("SELECT f.*, p.nombre as nombre_proyecto, pa.nombre as nombre_periodo FROM fases f 
            LEFT JOIN proyectos p ON f.id_proyecto = p.id_proyecto 
            LEFT JOIN periodos_academicos pa ON f.id_periodo = pa.id_periodo " . $where . " 
            ORDER BY f.fecha_creacion DESC LIMIT :offset, :limit");
if (!empty($busqueda)) {
    $db->bind(':busqueda', $busquedaLike);
}
if ($id_proyecto > 0) {
    $db->bind(':id_proyecto', $id_proyecto);
}
if ($id_periodo > 0) {
    $db->bind(':id_periodo', $id_periodo);
}
$db->bind(':offset', $offset, PDO::PARAM_INT);
$db->bind(':limit', $registros_por_pagina, PDO::PARAM_INT);
$fases = $db->resultSet();
if (!$fases) {
    $fases = [];
}

// Construir query string base para paginación y enlaces (preservar filtros)
$qs = '';
if (!empty($busqueda)) { $qs .= '&busqueda=' . urlencode($busqueda); }
if ($id_proyecto > 0) { $qs .= '&id_proyecto=' . $id_proyecto; }
if ($id_periodo > 0) { $qs .= '&id_periodo=' . $id_periodo; }

include '../header_admin.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Gestión de Fases</h1>
        <?php if ($_SESSION['rol'] != 'visualizador'): ?>
        <a href="crear.php" class="btn btn-primary">
            <i class="fas fa-plus-circle me-2"></i>Nueva Fase
        </a>
        <?php endif; ?>
    </div>
    
    <?php if (isset($_GET['mensaje'])): ?>
        <?php
        $tipo_alerta = 'success';
        $mensaje = '';
        switch ($_GET['mensaje']) {
            case 'creado': $mensaje = 'La fase ha sido creada exitosamente.'; break;
            case 'actualizado': $mensaje = 'La fase ha sido actualizada exitosamente.'; break;
            case 'eliminado': $mensaje = 'La fase ha sido eliminada exitosamente.'; break;
            case 'error': $tipo_alerta = 'danger'; $mensaje = 'Ha ocurrido un error. Inténtalo de nuevo.'; break;
        }
        ?>
        <div class="alert alert-<?php echo $tipo_alerta; ?> alert-dismissible fade show" role="alert">
            <?php echo $mensaje; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="card mb-4">
        <div class="card-body">
            <form action="" method="get" class="row g-3 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <input type="text" name="busqueda" class="form-control" placeholder="Buscar fases..." value="<?php echo $busqueda; ?>">
                        <button class="btn btn-outline-secondary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="id_periodo" class="form-select">
                        <option value="">Filtrar por período académico</option>
                        <?php foreach ($periodos as $periodo): ?>
                            <option value="<?php echo $periodo['id_periodo']; ?>" <?php echo ($id_periodo == $periodo['id_periodo']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($periodo['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 text-end">
                    <?php if (!empty($busqueda) || $id_proyecto > 0 || $id_periodo > 0): ?>
                        <a href="index.php" class="btn btn-outline-danger">
                            <i class="fas fa-times me-2"></i>Limpiar
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header bg-white">
            <h5 class="card-title mb-0">Listado de Fases</h5>
        </div>
        <div class="card-body">
            <?php if (count($fases) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Proyecto</th>
                                <th>Período</th>
                                <th>Fecha Inicio</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($fases as $fase): ?>
                                <tr>
                                    <td><?php echo $fase['nombre']; ?></td>
                                    <td><?php echo $fase['nombre_proyecto'] ? $fase['nombre_proyecto'] : '-'; ?></td>
                                    <td><?php echo $fase['nombre_periodo']; ?></td>
                                    <td><?php echo $fase['fecha_inicio'] ? formatearFecha($fase['fecha_inicio']) : '-'; ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo ($fase['estado'] == 'Pendiente') ? 'secondary' : 
                                                 (($fase['estado'] == 'En ejecución') ? 'primary' : 'success'); 
                                        ?>">
                                            <?php echo ucfirst($fase['estado']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="ver.php?id=<?php echo $fase['id_fase']; ?>" class="btn btn-info" title="Ver">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if ($_SESSION['rol'] != 'visualizador'): ?>
                                            <a href="editar.php?id=<?php echo $fase['id_fase']; ?>" class="btn btn-primary" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="eliminar.php?id=<?php echo $fase['id_fase']; ?>" class="btn btn-danger" title="Eliminar" onclick="return confirm('¿Seguro?');">
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
                                <a class="page-link" href="?pagina=<?php echo $pagina_actual - 1; ?><?php echo $qs; ?>" aria-label="Anterior">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                            
                            <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                                <li class="page-item <?php echo ($pagina_actual == $i) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?pagina=<?php echo $i; ?><?php echo $qs; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <li class="page-item <?php echo ($pagina_actual >= $total_paginas) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?pagina=<?php echo $pagina_actual + 1; ?><?php echo $qs; ?>" aria-label="Siguiente">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php else: ?>
                <p class="text-center">No se encontraron fases.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../footer_admin.php'; ?>
