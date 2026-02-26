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
    $db->query("SELECT COUNT(*) as total FROM fases_proyecto WHERE nombre_fase LIKE :busqueda OR descripcion LIKE :busqueda");
    $db->bind(':busqueda', $busquedaLike);
} else {
    $db->query("SELECT COUNT(*) as total FROM fases_proyecto");
}

$total_resultado = $db->single();
$total_registros = $total_resultado['total'] ?? 0;
$total_paginas = ceil($total_registros / $registros_por_pagina);

// Listado de fases
if (!empty($busqueda)) {
    $db->query("
        SELECT f.*, p.nombre as proyecto, pe.nombre as periodo
        FROM fases_proyecto f
        LEFT JOIN proyectos p ON f.id_proyecto = p.id_proyecto
        LEFT JOIN periodo_academico pe ON f.id_periodo = pe.id_periodo
        WHERE f.nombre_fase LIKE :busqueda OR f.descripcion LIKE :busqueda
        ORDER BY f.fecha_creacion DESC
        LIMIT :offset, :limit
    ");
    $db->bind(':busqueda', $busquedaLike);
} else {
    $db->query("
        SELECT f.*, p.nombre as proyecto, pe.nombre as periodo
        FROM fases_proyecto f
        LEFT JOIN proyectos p ON f.id_proyecto = p.id_proyecto
        LEFT JOIN periodo_academico pe ON f.id_periodo = pe.id_periodo
        ORDER BY f.fecha_creacion DESC
        LIMIT :offset, :limit
    ");
}

$db->bind(':offset', $offset, PDO::PARAM_INT);
$db->bind(':limit', $registros_por_pagina, PDO::PARAM_INT);

$fases = $db->resultSet();
if (!$fases) $fases = [];

include '../header_admin.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Gestión de Fases</h1>
        <a href="crear.php" class="btn btn-primary"><i class="fas fa-plus-circle me-2"></i> Nueva Fase</a>
    </div>

    <!-- Búsqueda -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="" method="get" class="row g-3">
                <div class="col-md-8">
                    <div class="input-group">
                        <input type="text" name="busqueda" class="form-control" placeholder="Buscar fases..." value="<?php echo $busqueda; ?>">
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
            <h5 class="card-title mb-0">Listado de Fases</h5>
        </div>
        <div class="card-body">
            <?php if (count($fases) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Fase</th>
                                <th>Proyecto</th>
                                <th>Periodo</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($fases as $fase): ?>
                                <tr>
                                    <td><?php echo $fase['id_fase']; ?></td>
                                    <td><?php echo htmlspecialchars($fase['nombre_fase']); ?></td>
                                    <td><?php echo htmlspecialchars($fase['proyecto'] ?: '-'); ?></td>
                                    <td><?php echo htmlspecialchars($fase['periodo'] ?: '-'); ?></td>
                                    <td><?php echo $fase['estado']; ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="ver.php?id=<?php echo $fase['id_fase']; ?>" class="btn btn-info"><i class="fas fa-eye"></i></a>
                                            <a href="editar.php?id=<?php echo $fase['id_fase']; ?>" class="btn btn-primary"><i class="fas fa-edit"></i></a>
                                            <a href="eliminar.php?id=<?php echo $fase['id_fase']; ?>" class="btn btn-danger" onclick="return confirm('¿Estás seguro de eliminar esta fase?');"><i class="fas fa-trash"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <?php if ($total_paginas > 1): ?>
                    <nav aria-label="Paginación de fases">
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
                <p class="text-center">No se encontraron fases.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../footer_admin.php'; ?>
