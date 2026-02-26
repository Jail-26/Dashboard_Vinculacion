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
$id_usuario = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Obtener datos del usuario
$db->query("SELECT * FROM usuarios WHERE id_usuario = :id");
$db->bind(':id', $id_usuario);
$usuario = $db->single();

if (!$usuario) {
    header('Location: index.php?mensaje=error');
    exit;
}

include '../header_admin.php';
?>

<div class="container py-4">
    <h1>Detalles del Usuario</h1>
    <div class="card mb-4">
        <div class="card-header bg-white">
            <h5 class="card-title mb-0">Información del Usuario</h5>
        </div>
        <div class="card-body">
            <p><strong>Nombre:</strong> <?php echo $usuario['nombre']; ?></p>
            <p><strong>Apellido:</strong> <?php echo $usuario['apellido']; ?></p>
            <p><strong>Correo Electrónico:</strong> <?php echo $usuario['correo']; ?></p>
            <p><strong>Rol:</strong> <?php echo ucfirst($usuario['rol']); ?></p>
            <p><strong>Estado:</strong> <?php echo ($usuario['estado'] == 1) ? 'Activo' : 'Inactivo'; ?></p>
            <p><strong>Fecha de Creación:</strong> <?php echo formatearFecha($usuario['fecha_creacion'], 'd/m/Y H:i'); ?></p>
        </div>
        <div class="card-footer bg-white">
            <a href="index.php" class="btn btn-secondary">Volver</a>
            <a href="editar.php?id=<?php echo $usuario['id_usuario']; ?>" class="btn btn-primary">Editar</a>
            <a href="eliminar.php?id=<?php echo $usuario['id_usuario']; ?>" class="btn btn-danger" onclick="return confirm('¿Estás seguro de eliminar este usuario?');">Eliminar</a>
        </div>
    </div>
</div>

<?php include '../footer_admin.php'; ?>