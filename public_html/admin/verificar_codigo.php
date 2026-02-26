<?php

session_start();
require_once '../../includes/functions.php';

$error = '';
$success = '';

if (!isset($_SESSION['recuperar_correo']) || !isset($_SESSION['codigo_verificacion'])) {
    header('Location: recuperar.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $codigo_ingresado = limpiarDatos($_POST['codigo']);

    if (empty($codigo_ingresado)) {
        $error = 'Debes ingresar el código de verificación.';
    } elseif (time() > $_SESSION['codigo_expiracion']) {
        $error = 'El código ha expirado. Solicita uno nuevo.';
        session_unset();
        session_destroy();
    } elseif ($codigo_ingresado == $_SESSION['codigo_verificacion']) {
        // Código correcto, permitir cambio de contraseña
        $_SESSION['codigo_verificado'] = true;
        header('Location: nueva_contrasena.php');
        exit;
    } else {
        $error = 'El código ingresado es incorrecto.';
    }
}
?>
<?php include 'header_auth.php'; ?>
<div class="container py-4">
    <h1 class="mb-4">Verificar Código</h1>
    <div class="form-container">
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="post" class="card p-4">
            <div class="mb-3">
                <label for="codigo" class="form-label">Código de verificación:</label>
                <input type="text" id="codigo" name="codigo" class="form-control" maxlength="6" required>
            </div>
            <button type="submit" id="boton" class="btn btn-primary">Verificar</button>
        </form>
        <div class="mt-3">
            <a href="recuperar.php" class="links">¿No recibiste el código? Solicitar uno nuevo</a>
        </div>
    </div>

</div>
<?php include 'footer_auth.php'; ?>