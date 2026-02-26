<?php
session_start();
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth.php';

if (
    !isset($_SESSION['recuperar_correo']) ||
    !isset($_SESSION['codigo_verificado']) ||
    $_SESSION['codigo_verificado'] !== true
) {
    header('Location: recuperar.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $contrasena = limpiarDatos($_POST['contrasena']);
    $contrasena2 = limpiarDatos($_POST['contrasena2']);

    if (empty($contrasena) || empty($contrasena2)) {
        $error = 'Debes ingresar y confirmar la nueva contraseña.';
    } elseif ($contrasena !== $contrasena2) {
        $error = 'Las contraseñas no coinciden.';
    } elseif (strlen($contrasena) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres.';
    } else {
        $db = new Database();
        $hash = hashContrasena($contrasena);
        $db->query("UPDATE usuarios SET contrasena = :contrasena WHERE correo = :correo");
        $db->bind(':contrasena', $hash);
        $db->bind(':correo', $_SESSION['recuperar_correo']);
        if ($db->execute()) {
            // Limpiar sesión de recuperación
            unset($_SESSION['recuperar_correo']);
            unset($_SESSION['codigo_verificacion']);
            unset($_SESSION['codigo_expiracion']);
            unset($_SESSION['codigo_verificado']);
            $success = 'Contraseña actualizada correctamente. Ahora puedes iniciar sesión.';
        } else {
            $error = 'Ocurrió un error al actualizar la contraseña.';
        }
    }
}
?>
<?php include 'header_auth.php'; ?>
<div class="container py-4">
    <h1 class="mb-4">Nueva Contraseña</h1>
    <div class="form-container">
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php elseif ($success): ?>
            <div class="alert alert-success" style="height: 60px;"><?php echo $success; ?></div>
            <a href="login.php" id="boton" class="btn btn-primary mt-3">Ir a Iniciar Sesión</a>
        <?php else: ?>
            <form method="post" class="card p-4">
                <div class="mb-3">
                    <label for="contrasena" class="form-label">Nueva contraseña:</label>
                    <input type="password" id="contrasena" name="contrasena" class="form-control" required minlength="6">
                </div>
                <div class="mb-3">
                    <label for="contrasena2" class="form-label">Confirmar nueva contraseña:</label>
                    <input type="password" id="contrasena2" name="contrasena2" class="form-control" required minlength="6">
                </div>
                <button type="submit" id="boton" class="btn btn-primary">Guardar nueva contraseña</button>
            </form>
        <?php endif; ?>
    </div>

</div>
<?php include 'footer_auth.php'; ?>