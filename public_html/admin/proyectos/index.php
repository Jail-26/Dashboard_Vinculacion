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

// Configuración de paginación
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$registros_por_pagina = 10;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

// Búsqueda
$busqueda = isset($_GET['busqueda']) ? limpiarDatos($_GET['busqueda']) : '';
$busquedaLike = '%' . $busqueda . '%';

// Obtener total de registros
if (!empty($busqueda)) {
    $db->query("
        SELECT COUNT(*) as total 
        FROM proyectos 
        WHERE nombre LIKE :nombre 
           OR descripcion_corta LIKE :descripcion_corta 
           OR descripcion_extendida LIKE :descripcion_extendida
    ");
    $db->bind(':nombre', $busquedaLike, PDO::PARAM_STR);
    $db->bind(':descripcion_corta', $busquedaLike, PDO::PARAM_STR);
    $db->bind(':descripcion_extendida', $busquedaLike, PDO::PARAM_STR);
} else {
    $db->query("SELECT COUNT(*) as total FROM proyectos");
}

$total_resultado = $db->single();
$total_registros = $total_resultado['total'] ?? 0;
$total_paginas = ceil($total_registros / $registros_por_pagina);

// Obtener listado de proyectos
if (!empty($busqueda)) {
    $db->query("
        SELECT p.*, pr.nombre as nombre_programa 
        FROM proyectos p
        LEFT JOIN programas pr ON p.id_programa = pr.id_programa
        WHERE p.nombre LIKE :nombre 
           OR p.descripcion_corta LIKE :descripcion_corta 
           OR p.descripcion_extendida LIKE :descripcion_extendida
        ORDER BY p.fecha_creacion DESC 
        LIMIT :offset, :limit
    ");
    $db->bind(':nombre', $busquedaLike, PDO::PARAM_STR);
    $db->bind(':descripcion_corta', $busquedaLike, PDO::PARAM_STR);
    $db->bind(':descripcion_extendida', $busquedaLike, PDO::PARAM_STR);
    $db->bind(':offset', $offset, PDO::PARAM_INT);
    $db->bind(':limit', $registros_por_pagina, PDO::PARAM_INT);
} else {
    $db->query("
        SELECT p.*, pr.nombre as nombre_programa 
        FROM proyectos p
        LEFT JOIN programas pr ON p.id_programa = pr.id_programa
        ORDER BY p.fecha_creacion DESC 
        LIMIT :offset, :limit
    ");
    $db->bind(':offset', $offset, PDO::PARAM_INT);
    $db->bind(':limit', $registros_por_pagina, PDO::PARAM_INT);
}

$proyectos = $db->resultSet();
if (!$proyectos) {
    $proyectos = [];
}

include '../header_admin.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Gestión de Proyectos</h1>
        <?php if ($_SESSION['rol'] != 'visualizador'): ?>
            <a href="crear.php" class="btn btn-primary">
                <i class="fas fa-plus-circle me-2"></i>Nuevo Proyecto
            </a>
        <?php endif; ?>
    </div>

    <!-- Filtros y búsqueda -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="" method="get" class="row g-3">
                <div class="col-md-8">
                    <div class="input-group">
                        <input type="text" name="busqueda" class="form-control" placeholder="Buscar proyectos..." value="<?php echo $busqueda; ?>">
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

    <!-- Listado de proyectos -->
    <div class="card">
        <div class="card-header bg-white">
            <h5 class="card-title mb-0">Listado de Proyectos</h5>
        </div>
        <div class="card-body">
            <?php if (count($proyectos) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Programa</th>
                                <th>Estado</th>
                                <th>Fecha Creación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($proyectos as $proyecto): ?>
                                <tr>
                                    <td><?php echo $proyecto['id_proyecto']; ?></td>
                                    <td><?php echo $proyecto['nombre']; ?></td>
                                    <td><?php echo $proyecto['nombre_programa'] ?: '-'; ?></td>
                                    <td><?php echo ucfirst($proyecto['estado']); ?></td>
                                    <td><?php echo formatearFecha($proyecto['fecha_creacion'], 'd/m/Y H:i'); ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="ver.php?id=<?php echo $proyecto['id_proyecto']; ?>" class="btn btn-info" title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if ($_SESSION['rol'] != 'visualizador'): ?>
                                                <a href="editar.php?id=<?php echo $proyecto['id_proyecto']; ?>" class="btn btn-primary" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="eliminar.php?id=<?php echo $proyecto['id_proyecto']; ?>" class="btn btn-danger" title="Eliminar" onclick="return confirm('¿Estás seguro de eliminar este proyecto?');">
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

                <!-- Paginación -->
                <?php if ($total_paginas > 1): ?>
                    <nav aria-label="Paginación de proyectos">
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
                <p class="text-center">No se encontraron proyectos.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../footer_admin.php'; ?>
