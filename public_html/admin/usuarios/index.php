<?php
require_once '../../../includes/config.php';
require_once '../../../includes/db.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/functions.php';

if (!estaLogueado() || $_SESSION['rol'] != 'administrador') {
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
    $db->query("SELECT COUNT(*) as total FROM usuarios WHERE nombre LIKE :busqueda OR correo LIKE :busqueda1");
    $db->bind(':busqueda', $busquedaLike);
    $db->bind(':busqueda1', $busquedaLike);
} else {
    $db->query("SELECT COUNT(*) as total FROM usuarios");
}
$total_resultado = $db->single();
$total_registros = $total_resultado['total'] ?? 0;
$total_paginas = ceil($total_registros / $registros_por_pagina);

// Obtener listado de usuarios
if (!empty($busqueda)) {
    $db->query("SELECT * FROM usuarios WHERE nombre LIKE :busqueda OR correo LIKE :busqueda1 LIMIT :limit OFFSET :offset");
    $db->bind(':busqueda', $busquedaLike);
    $db->bind(':busqueda1', $busquedaLike);
    $db->bind(':limit', $registros_por_pagina, PDO::PARAM_INT);
    $db->bind(':offset', $offset, PDO::PARAM_INT);
} else {
    $db->query("SELECT * FROM usuarios LIMIT :limit OFFSET :offset");
    $db->bind(':limit', $registros_por_pagina, PDO::PARAM_INT);
    $db->bind(':offset', $offset, PDO::PARAM_INT);
}
$usuarios = $db->resultSet();

include '../header_admin.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Gestión de Usuarios</h1>
        <?php if ($_SESSION['rol'] != 'visualizador'): ?>
        <a href="crear.php" class="btn btn-primary">
            <i class="fas fa-plus-circle me-2"></i>Nuevo Usuario
        </a>
        <?php endif; ?>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form action="" method="get" class="row g-3">
                <div class="col-md-8">
                    <div class="input-group">
                        <input type="text" name="busqueda" class="form-control" placeholder="Buscar usuarios..." value="<?php echo $busqueda; ?>">
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
            <h5 class="card-title mb-0">Listado de Usuarios</h5>
        </div>
        <div class="card-body">
            <?php if (count($usuarios) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Correo</th>
                                <th>Rol</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $usuario): ?>
                                <tr>
                                    <td><?php echo $usuario['id_usuario']; ?></td>
                                    <td><?php echo $usuario['nombre'] . ' ' . $usuario['apellido']; ?></td>
                                    <td><?php echo $usuario['correo']; ?></td>
                                    <td><?php echo ($usuario['rol'] == 'administrador' || $usuario['rol'] == 'visualizador') ? ucfirst($usuario['rol']) : 'Desconocido'; ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="ver.php?id=<?php echo $usuario['id_usuario']; ?>" class="btn btn-info" title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if ($_SESSION['rol'] != 'visualizador'): ?>
                                            <a href="editar.php?id=<?php echo $usuario['id_usuario']; ?>" class="btn btn-primary" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="eliminar.php?id=<?php echo $usuario['id_usuario']; ?>" class="btn btn-danger" title="Eliminar" onclick="return confirm('¿Estás seguro de eliminar este usuario?');">
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
                    <nav aria-label="Paginación de usuarios">
                        <ul class="pagination justify-content-center mt-4">
                            <li class="page-item <?php echo ($pagina_actual <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?pagina=<?php echo $pagina_actual - 1; ?>" aria-label="Anterior">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                            <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                                <li class="page-item <?php echo ($pagina_actual == $i) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?pagina=<?php echo $i; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?php echo ($pagina_actual >= $total_paginas) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?pagina=<?php echo $pagina_actual + 1; ?>" aria-label="Siguiente">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php else: ?>
                <p class="text-center">No se encontraron usuarios.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../footer_admin.php'; ?>