<?php
require_once '../../../includes/config.php';
require_once '../../../includes/db.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/functions.php';

if (!estaLogueado() || $_SESSION['rol'] != 'administrador') {
    header('Location: ../login.php');
    exit;
}

if ($_SESSION['rol'] == 'visualizador') {
    header('Location: index.php?mensaje=sin_permisos');
    exit;
}

$db = new Database();
$error = '';
$id_usuario = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Obtener datos del usuario
$db->query("SELECT * FROM usuarios WHERE id_usuario = :id");
$db->bind(':id', $id_usuario);
$usuario = $db->single();

if (!$usuario) {
    header('Location: index.php?mensaje=error');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = limpiarDatos($_POST['nombre']);
    $apellido = limpiarDatos($_POST['apellido']);
    $correo = limpiarDatos($_POST['correo']);
    $rol = $_POST['rol'];

    if (empty($nombre) || empty($apellido) || empty($correo) || empty($rol)) {
        $error = 'Todos los campos son obligatorios.';
    } elseif (!validarEmail($correo)) {
        $error = 'El correo electrónico no es válido.';
    } else {
        $db->query("UPDATE usuarios SET nombre = :nombre, apellido = :apellido, correo = :correo, rol = :rol WHERE id_usuario = :id");
        $db->bind(':nombre', $nombre);
        $db->bind(':apellido', $apellido);
        $db->bind(':correo', $correo);
        $db->bind(':rol', $rol);
        $db->bind(':id', $id_usuario);

        if ($db->execute()) {
            header('Location: index.php?mensaje=actualizado');
            exit;
        } else {
            $error = 'Error al actualizar el usuario.';
        }
    }
}

include '../header_admin.php';
?>

<div class="container py-4">
    <h1>Editar Usuario</h1>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <form method="post">
        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre</label>
            <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo $usuario['nombre']; ?>" required>
        </div>
        <div class="mb-3">
            <label for="apellido" class="form-label">Apellido</label>
            <input type="text" class="form-control" id="apellido" name="apellido" value="<?php echo $usuario['apellido']; ?>" required>
        </div>
        <div class="mb-3">
            <label for="correo" class="form-label">Correo Electrónico</label>
            <input type="email" class="form-control" id="correo" name="correo" value="<?php echo $usuario['correo']; ?>" required>
        </div>
        <div class="mb-3">
            <label for="rol" class="form-label">Rol</label>
            <select class="form-select" id="rol" name="rol" required>
                <option value="administrador" <?php echo ($usuario['rol'] == 'administrador') ? 'selected' : ''; ?>>Administrador</option>
                <option value="visualizador" <?php echo ($usuario['rol'] == 'visualizador') ? 'selected' : ''; ?>>Visualizador</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
        <a href="index.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

<?php include '../footer_admin.php'; ?>