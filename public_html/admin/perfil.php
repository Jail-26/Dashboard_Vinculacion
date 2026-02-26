<?php
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

if (!estaLogueado()) {
    header('Location: login.php');
    exit;
}

$db = new Database();
$error = '';
$success = '';
$id_usuario = $_SESSION['id_usuario'];

// Obtener datos del usuario
$db->query("SELECT * FROM usuarios WHERE id_usuario = :id");
$db->bind(':id', $id_usuario);
$usuario = $db->single();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['actualizar_datos'])) {
        $nombre = limpiarDatos($_POST['nombre']);
        $apellido = limpiarDatos($_POST['apellido']);
        $correo = limpiarDatos($_POST['correo']);

        if (empty($nombre) || empty($apellido) || empty($correo)) {
            $error = 'Todos los campos son obligatorios.';
        } elseif (!validarEmail($correo)) {
            $error = 'El correo electrónico no es válido.';
        } else {
            $db->query("UPDATE usuarios SET nombre = :nombre, apellido = :apellido, correo = :correo WHERE id_usuario = :id");
            $db->bind(':nombre', $nombre);
            $db->bind(':apellido', $apellido);
            $db->bind(':correo', $correo);
            $db->bind(':id', $id_usuario);

            if ($db->execute()) {
                $success = 'Datos actualizados correctamente.';
                $_SESSION['nombre'] = $nombre;
                $_SESSION['apellido'] = $apellido;
                $_SESSION['correo'] = $correo;
            } else {
                $error = 'Error al actualizar los datos.';
            }
        }
    } elseif (isset($_POST['cambiar_contrasena'])) {
        $contrasena_actual = $_POST['contrasena_actual'];
        $contrasena_nueva = $_POST['contrasena_nueva'];
        $confirmar_contrasena = $_POST['confirmar_contrasena'];

        if (empty($contrasena_actual) || empty($contrasena_nueva) || empty($confirmar_contrasena)) {
            $error = 'Todos los campos son obligatorios.';
        } elseif ($contrasena_nueva !== $confirmar_contrasena) {
            $error = 'Las contraseñas no coinciden.';
        } elseif (!preg_match('/.{8,}/', $contrasena_nueva) ||
                  !preg_match('/[A-Z]/', $contrasena_nueva) ||
                  !preg_match('/[a-z]/', $contrasena_nueva) ||
                  !preg_match('/[0-9]/', $contrasena_nueva) ||
                  !preg_match('/[!@#$%^&*()_+\-=[\]{};:\"\\|,.<>\/?]/', $contrasena_nueva)) {
            $error = 'La nueva contraseña no cumple los requisitos de seguridad. Debe tener al menos 8 caracteres, incluir mayúsculas, minúsculas, números y un carácter especial.';
        } else {
            if (cambiarContrasena($id_usuario, $contrasena_actual, $contrasena_nueva)) {
                $success = 'Contraseña actualizada correctamente.';
            } else {
                $error = 'La contraseña actual es incorrecta.';
            }
        }
    }
}

include 'header_admin.php';
?>

<div class="container py-4">
    <h1>Mi Perfil</h1>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <form method="post">
        <h3>Actualizar Datos</h3>
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
        <button type="submit" name="actualizar_datos" class="btn btn-primary">Actualizar Datos</button>
    </form>

    <hr>

    <form method="post">
        <h3>Cambiar Contraseña</h3>
        <div class="mb-3">
            <label for="contrasena_actual" class="form-label">Contraseña Actual</label>
            <input type="password" class="form-control" id="contrasena_actual" name="contrasena_actual" required>
        </div>
        <div class="mb-3">
            <label for="contrasena_nueva" class="form-label">Nueva Contraseña</label>
                <input maxlength="20" type="password" class="form-control" id="contrasena_nueva" name="contrasena_nueva" required>
                <div id="password_help" class="form-text">La contraseña debe tener al menos 8 caracteres, incluir mayúsculas, minúsculas, números y un carácter especial.</div>
                <ul id="password_requirements" class="small mt-2">
                    <li id="pr_len" class="text-muted">Mínimo 8 caracteres</li>
                    <li id="pr_upper" class="text-muted">Una letra mayúscula</li>
                    <li id="pr_lower" class="text-muted">Una letra minúscula</li>
                    <li id="pr_digit" class="text-muted">Un número</li>
                    <li id="pr_special" class="text-muted">Un carácter especial (ej. !@#$%)</li>
                </ul>
        </div>
        <div class="mb-3">
            <label for="confirmar_contrasena" class="form-label">Confirmar Nueva Contraseña</label>
            <input type="password" class="form-control" id="confirmar_contrasena" name="confirmar_contrasena" required>
        </div>
        <button type="submit" name="cambiar_contrasena" class="btn btn-primary">Cambiar Contraseña</button>
    </form>
</div>

<?php include 'footer_admin.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', function(){
    const pwInput = document.getElementById('contrasena_nueva');
    const prLen = document.getElementById('pr_len');
    const prUpper = document.getElementById('pr_upper');
    const prLower = document.getElementById('pr_lower');
    const prDigit = document.getElementById('pr_digit');
    const prSpecial = document.getElementById('pr_special');
    const submitBtn = document.querySelector('button[name="cambiar_contrasena"]');

    function testPassword(pw){
        const tests = {
            len: /.{8,}/.test(pw),
            upper: /[A-Z]/.test(pw),
            lower: /[a-z]/.test(pw),
            digit: /[0-9]/.test(pw),
            special: /[!@#$%^&*()_+\-=[\]{};:\"\\|,.<>\/?]/.test(pw)
        };
        return tests;
    }

    pwInput.addEventListener('input', function(){
        const v = this.value;
        const t = testPassword(v);
        prLen.className = t.len ? 'text-success' : 'text-muted';
        prUpper.className = t.upper ? 'text-success' : 'text-muted';
        prLower.className = t.lower ? 'text-success' : 'text-muted';
        prDigit.className = t.digit ? 'text-success' : 'text-muted';
        prSpecial.className = t.special ? 'text-success' : 'text-muted';

        // Enable submit only if all tests pass and confirm matches
        const confirmInput = document.getElementById('confirmar_contrasena');
        const canSubmit = t.len && t.upper && t.lower && t.digit && t.special && confirmInput && confirmInput.value === v;
        submitBtn.disabled = !canSubmit;
    });

    const confirmInput = document.getElementById('confirmar_contrasena');
    if (confirmInput) {
        confirmInput.addEventListener('input', function(){
            const v = pwInput.value;
            const canSubmit = testPassword(v).len && testPassword(v).upper && testPassword(v).lower && testPassword(v).digit && testPassword(v).special && this.value === v;
            submitBtn.disabled = !canSubmit;
        });
    }
});
</script>