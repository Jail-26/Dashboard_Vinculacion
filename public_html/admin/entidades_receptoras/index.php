<?php
require_once '../../../includes/config.php';
require_once '../../../includes/db.php';
require_once '../../../includes/auth.php';

if (!estaLogueado()) {
    header('Location: ../login.php');
    exit;
}

$db = new Database();

// Búsqueda
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
$busquedaLike = '%' . $busqueda . '%';

// Obtener entidades receptoras filtradas
if (!empty($busqueda)) {
    $db->query("
        SELECT * FROM entidades_receptoras
        WHERE nombre LIKE :nombre
           OR representante_legal LIKE :representante
           OR direccion LIKE :direccion
        ORDER BY fecha_creacion DESC
    ");
    $db->bind(':nombre', $busquedaLike, PDO::PARAM_STR);
    $db->bind(':representante', $busquedaLike, PDO::PARAM_STR);
    $db->bind(':direccion', $busquedaLike, PDO::PARAM_STR);
} else {
    $db->query("SELECT * FROM entidades_receptoras ORDER BY fecha_creacion DESC");
}
$entidades = $db->resultSet();

include '../header_admin.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Entidades Receptoras</h1>
        <?php if ($_SESSION['rol'] != 'visualizador'): ?>
        <a href="crear.php" class="btn btn-primary">
            <i class="fas fa-plus-circle me-2"></i>Nueva Entidad
        </a>
        <?php endif; ?>
    </div>

    <!-- Filtros y búsqueda -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="" method="get" class="row g-3">
                <div class="col-md-8">
                    <div class="input-group">
                        <input type="text" name="busqueda" class="form-control" placeholder="Buscar entidades..." value="<?php echo htmlspecialchars($busqueda); ?>">
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
            <h5 class="card-title mb-0">Listado de Entidades</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Representante Legal</th>
                            <th>Dirección</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($entidades as $entidad): ?>
                            <tr>
                                <td><?php echo $entidad['id_entidad']; ?></td>
                                <td><?php echo $entidad['nombre']; ?></td>
                                <td><?php echo $entidad['representante_legal']; ?></td>
                                <td><?php echo $entidad['direccion']; ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="ver.php?id=<?php echo $entidad['id_entidad']; ?>" class="btn btn-info" title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if ($_SESSION['rol'] != 'visualizador'): ?>
                                        <a href="editar.php?id=<?php echo $entidad['id_entidad']; ?>" class="btn btn-primary" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="eliminar.php?id=<?php echo $entidad['id_entidad']; ?>" class="btn btn-danger" title="Eliminar" onclick="return confirm('¿Estás seguro de eliminar esta entidad?');">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($entidades)): ?>
                            <tr>
                                <td colspan="5" class="text-center">No se encontraron entidades.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../footer_admin.php'; ?>