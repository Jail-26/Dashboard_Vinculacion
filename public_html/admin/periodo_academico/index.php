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

// Total registros
if (!empty($busqueda)) {
    $db->query("SELECT COUNT(*) as total FROM periodo_academico WHERE nombre LIKE :busqueda");
    $db->bind(':busqueda', $busquedaLike);
} else {
    $db->query("SELECT COUNT(*) as total FROM periodo_academico");
}

$total_resultado = $db->single();
$total_registros = $total_resultado['total'] ?? 0;
$total_paginas = ceil($total_registros / $registros_por_pagina);

// Listado de periodos
if (!empty($busqueda)) {
    $db->query("SELECT * FROM periodo_academico WHERE nombre LIKE :busqueda ORDER BY id_periodo DESC LIMIT :offset, :limit");
    $db->bind(':busqueda', $busquedaLike);
} else {
    $db->query("SELECT * FROM periodo_academico ORDER BY id_periodo DESC LIMIT :offset, :limit");
}

$db->bind(':offset', $offset, PDO::PARAM_INT);
$db->bind(':limit', $registros_por_pagina, PDO::PARAM_INT);

$periodos = $db->resultSet();
if (!$periodos) $periodos = [];

include '../header_admin.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Gestión de Periodos Académicos</h1>
        <a href="crear.php" class="btn btn-primary"><i class="fas fa-plus-circle me-2"></i> Nuevo Periodo</a>
    </div>

    <!-- Búsqueda -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="" method="get" class="row g-3">
                <div class="col-md-8">
                    <div class="input-group">
                        <input type="text" name="busqueda" class="form-control" placeholder="Buscar periodos..." value="<?php echo $busqueda; ?>">
                        <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <?php if (!empty($busqueda)): ?>
                        <a href="index.php" class="btn btn-outline-danger"><i class="fas fa-times me-2"></i> Limpiar filtros</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Listado -->
    <div class="card">
        <div class="card-header bg-white">
            <h5 class="card-title mb-0">Listado de Periodos Académicos</h5>
        </div>
        <div class="card-body">
            <?php if (count($periodos) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Fecha Inicio</th>
                                <th>Fecha Fin</th>
                                <th>Activo</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($periodos as $periodo): ?>
                                <tr>
                                    <td><?php echo $periodo['id_periodo']; ?></td>
                                    <td><?php echo htmlspecialchars($periodo['nombre']); ?></td>
                                    <td><?php echo formatearFecha($periodo['fecha_inicio'], 'd/m/Y'); ?></td>
                                    <td><?php echo formatearFecha($periodo['fecha_fin'], 'd/m/Y'); ?></td>
                                    <td><?php echo $periodo['activo'] ? 'Sí' : 'No'; ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="ver.php?id=<?php echo $periodo['id_periodo']; ?>" class="btn btn-info"><i class="fas fa-eye"></i></a>
                                            <a href="editar.php?id=<?php echo $periodo['id_periodo']; ?>" class="btn btn-primary"><i class="fas fa-edit"></i></a>
                                            <a href="eliminar.php?id=<?php echo $periodo['id_periodo']; ?>" class="btn btn-danger" onclick="return confirm('¿Estás seguro de eliminar este periodo?');"><i class="fas fa-trash"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <?php if ($total_paginas > 1): ?>
                    <nav aria-label="Paginación de periodos">
                        <ul class="pagination justify-content-center mt-4">
                            <li class="page-item <?php echo ($pagina_actual <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?pagina=<?php echo $pagina_actual - 1; ?><?php echo !empty($busqueda) ? '&busqueda=' . $busqueda : ''; ?>">&laquo;</a>
                            </li>
                            <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                                <li class="page-item <?php echo ($pagina_actual == $i) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?pagina=<?php echo $i; ?><?php echo !empty($busqueda) ? '&busqueda=' . $busqueda : ''; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?php echo ($pagina_actual >= $total_paginas) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?pagina=<?php echo $pagina_actual + 1; ?><?php echo !empty($busqueda) ? '&busqueda=' . $busqueda : ''; ?>">&raquo;</a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php else: ?>
                <p class="text-center">No se encontraron periodos.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../footer_admin.php'; ?>