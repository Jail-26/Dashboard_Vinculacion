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
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = limpiarDatos($_POST['nombre']);
    $apellido = limpiarDatos($_POST['apellido']);
    $correo = limpiarDatos($_POST['correo']);
    $rol = $_POST['rol'];
    $contrasena = $_POST['contrasena'];

    if (empty($nombre) || empty($apellido) || empty($correo) || empty($rol) || empty($contrasena)) {
        $error = 'Todos los campos son obligatorios.';
    } elseif (!validarEmail($correo)) {
        $error = 'El correo electrónico no es válido.';
    } else {
        $hash_contrasena = hashContrasena($contrasena);

        $db->query("INSERT INTO usuarios (nombre, apellido, correo, contrasena, rol, fecha_creacion) VALUES (:nombre, :apellido, :correo, :contrasena, :rol, NOW())");
        $db->bind(':nombre', $nombre);
        $db->bind(':apellido', $apellido);
        $db->bind(':correo', $correo);
        $db->bind(':contrasena', $hash_contrasena);
        $db->bind(':rol', $rol);

        if ($db->execute()) {
            header('Location: index.php?mensaje=creado');
            exit;
        } else {
            $error = 'Error al crear el usuario.';
        }
    }
}

include '../header_admin.php';
?>

<div class="container py-4">
    <h1>Crear Nuevo Usuario</h1>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <form method="post">
        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre</label>
            <input type="text" class="form-control" id="nombre" name="nombre" required>
        </div>
        <div class="mb-3">
            <label for="apellido" class="form-label">Apellido</label>
            <input type="text" class="form-control" id="apellido" name="apellido" required>
        </div>
        <div class="mb-3">
            <label for="correo" class="form-label">Correo Electrónico</label>
            <input type="email" class="form-control" id="correo" name="correo" required>
        </div>
        <div class="mb-3">
            <label for="rol" class="form-label">Rol</label>
            <select class="form-select" id="rol" name="rol" required>
                <option value="">Seleccione un rol</option>
                <option value="administrador">Administrador</option>
                <option value="visualizador">Visualizador</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="contrasena" class="form-label">Contraseña</label>
            <input type="password" class="form-control" id="contrasena" name="contrasena" required>
        </div>
        <button type="submit" class="btn btn-primary">Crear Usuario</button>
        <a href="index.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

<?php include '../footer_admin.php'; ?>