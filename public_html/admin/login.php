<?php
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

// Redirigir si ya está logueado
if (estaLogueado()) {
    header('Location: index.php');
    exit;
}

$error = '';
$correo = '';

// Procesar el formulario si se ha enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener y limpiar los datos
    $correo = limpiarDatos($_POST['correo']);
    $contrasena = $_POST['contrasena'];

    // Validar campos
    if (empty($correo) || empty($contrasena)) {
        $error = 'Todos los campos son obligatorios';
    } elseif (!validarEmail($correo)) {
        $error = 'El correo electrónico no es válido';
    } else {
        // Intentar iniciar sesión
        if (login($correo, $contrasena)) {
            // Redireccionar al panel de administración
            header('Location: index.php');
            exit;
        } else {
            $error = 'Correo electrónico o contraseña incorrectos';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - <?php echo PROJECT_NAME; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/admin_style.css">
    <style>
    </style>
</head>

<body>
    <header>
        <img src="../images/logogrisazulado.png" alt="" width="100px">
    </header>
    <div class="login-container">
        <div class="login-logo">
            <i class="fas fa-chart-line"></i>
            <h2 class="mt-3"><?php echo PROJECT_NAME; ?></h2>
            <p class="text-muted">Panel de Administración</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
            <div class="mb-3">
                <label for="correo" class="form-label">Correo electrónico</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input type="email" class="form-control" id="correo" name="correo" value="<?php echo $correo; ?>" required>
                </div>
            </div>
            <div class="mb-3">
                <label for="contrasena" class="form-label">Contraseña</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" class="form-control" id="contrasena" name="contrasena" required>
                </div>
            </div>
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
            </div>
        </form>

        <div class="text-center mt-3">
            <a href="recuperar.php" class="text-decoration-none">¿Olvidaste tu contraseña?</a>
        </div>

        <div class="text-center mt-4">
            <a href="<?php echo BASE_URL; ?>" class="text-decoration-none">
                <i class="fas fa-arrow-left me-2"></i>Volver al dashboard público
            </a>
        </div>
    </div>


    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <?php
    include_once 'footer_auth.php';
    ?>
</body>

</html>