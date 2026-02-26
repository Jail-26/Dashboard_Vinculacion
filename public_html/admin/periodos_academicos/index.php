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
$registros_por_pagina = 10;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

$busqueda = isset($_GET['busqueda']) ? limpiarDatos($_GET['busqueda']) : '';
$busquedaLike = '%' . $busqueda . '%';

if (!empty($busqueda)) {
    $db->query("SELECT COUNT(*) as total FROM periodos_academicos WHERE nombre LIKE :busqueda OR descripcion LIKE :busqueda1");
    $db->bind(':busqueda', $busquedaLike);
    $db->bind(':busqueda1', $busquedaLike);

} else {
    $db->query("SELECT COUNT(*) as total FROM periodos_academicos");
}
$total_resultado = $db->single();
$total_registros = $total_resultado['total'] ?? 0;
$total_paginas = ceil($total_registros / $registros_por_pagina);

if (!empty($busqueda)) {
    $db->query("SELECT * FROM periodos_academicos WHERE nombre LIKE :busqueda OR descripcion LIKE :busqueda1 ORDER BY fecha_creacion DESC LIMIT :limit OFFSET :offset");
    $db->bind(':busqueda', $busquedaLike);
    $db->bind(':busqueda1', $busquedaLike);

    $db->bind(':limit', $registros_por_pagina, PDO::PARAM_INT);
    $db->bind(':offset', $offset, PDO::PARAM_INT);
} else {
    $db->query("SELECT * FROM periodos_academicos ORDER BY fecha_creacion DESC LIMIT :limit OFFSET :offset");
    $db->bind(':limit', $registros_por_pagina, PDO::PARAM_INT);
    $db->bind(':offset', $offset, PDO::PARAM_INT);
}
$periodos = $db->resultSet();
if (!$periodos) {
    $periodos = [];
}

include '../header_admin.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Gestión de Períodos Académicos</h1>
        <?php if ($_SESSION['rol'] != 'visualizador'): ?>
        <a href="crear.php" class="btn btn-primary">
            <i class="fas fa-plus-circle me-2"></i>Nuevo Período
        </a>
        <?php endif; ?>
    </div>
    
    <?php if (isset($_GET['mensaje'])): ?>
        <?php
        $tipo_alerta = 'success';
        $mensaje = '';
        
        switch ($_GET['mensaje']) {
            case 'creado':
                $mensaje = 'El período ha sido creado exitosamente.';
                break;
            case 'actualizado':
                $mensaje = 'El período ha sido actualizado exitosamente.';
                break;
            case 'eliminado':
                $mensaje = 'El período ha sido eliminado exitosamente.';
                break;
            case 'error':
                $tipo_alerta = 'danger';
                $mensaje = 'Ha ocurrido un error. Inténtalo de nuevo.';
                break;
        }
        ?>
        <div class="alert alert-<?php echo $tipo_alerta; ?> alert-dismissible fade show" role="alert">
            <?php echo $mensaje; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="card mb-4">
        <div class="card-body">
            <form action="" method="get" class="row g-3">
                <div class="col-md-8">
                    <div class="input-group">
                        <input type="text" name="busqueda" class="form-control" placeholder="Buscar períodos..." value="<?php echo $busqueda; ?>">
                        <button class="btn btn-outline-secondary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <?php if (!empty($busqueda)): ?>
                        <a href="index.php" class="btn btn-outline-danger">
                            <i class="fas fa-times me-2"></i>Limpiar filtros
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header bg-white">
            <h5 class="card-title mb-0">Listado de Períodos Académicos</h5>
        </div>
        <div class="card-body">
            <?php if (count($periodos) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Fecha Inicio</th>
                                <th>Fecha Fin</th>
                                <th>Estado</th>
                                <th>Fases</th>
                                <th>Fecha Creación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($periodos as $periodo): 
                                $db->query("SELECT COUNT(*) as total FROM fases WHERE id_periodo = :id_periodo");
                                $db->bind(':id_periodo', $periodo['id_periodo']);
                                $total_fases = $db->single()['total'];
                            ?>
                                <tr>
                                    <td><?php echo $periodo['nombre']; ?></td>
                                    <td><?php echo $periodo['fecha_inicio'] ? formatearFecha($periodo['fecha_inicio']) : '-'; ?></td>
                                    <td><?php echo $periodo['fecha_fin'] ? formatearFecha($periodo['fecha_fin']) : '-'; ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo ($periodo['estado'] == 'Activo') ? 'success' : (($periodo['estado'] == 'Cerrado') ? 'danger' : 'secondary'); ?>">
                                            <?php echo ucfirst($periodo['estado']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="../fases/index.php?id_periodo=<?php echo $periodo['id_periodo']; ?>" class="badge bg-info text-decoration-none">
                                            <?php echo $total_fases; ?>
                                        </a>
                                    </td>
                                    <td><?php echo formatearFecha($periodo['fecha_creacion'], 'd/m/Y'); ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="ver.php?id=<?php echo $periodo['id_periodo']; ?>" class="btn btn-info" title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if ($_SESSION['rol'] != 'visualizador'): ?>
                                            <a href="editar.php?id=<?php echo $periodo['id_periodo']; ?>" class="btn btn-primary" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="eliminar.php?id=<?php echo $periodo['id_periodo']; ?>" class="btn btn-danger" title="Eliminar" onclick="return confirm('¿Estás seguro?');">
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
                                <a class="page-link" href="?pagina=<?php echo $pagina_actual - 1; ?><?php echo !empty($busqueda) ? '&busqueda=' . $busqueda : ''; ?>" aria-label="Anterior">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                            
                            <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                                <li class="page-item <?php echo ($pagina_actual == $i) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?pagina=<?php echo $i; ?><?php echo !empty($busqueda) ? '&busqueda=' . $busqueda : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <li class="page-item <?php echo ($pagina_actual >= $total_paginas) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?pagina=<?php echo $pagina_actual + 1; ?><?php echo !empty($busqueda) ? '&busqueda=' . $busqueda : ''; ?>" aria-label="Siguiente">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php else: ?>
                <p class="text-center">No se encontraron períodos académicos.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../footer_admin.php'; ?>
