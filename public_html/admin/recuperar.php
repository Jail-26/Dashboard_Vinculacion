<?php
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';
require_once '../../includes/PHPMailer/src/PHPMailer.php';
require_once '../../includes/PHPMailer/src/SMTP.php';
require_once '../../includes/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

session_start();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $correo = limpiarDatos($_POST['correo']);

    if (empty($correo)) {
        $error = 'El correo electrónico es obligatorio.';
    } else {
        $db = new Database();
        $db->query("SELECT * FROM usuarios WHERE correo = :correo");
        $db->bind(':correo', $correo);
        $usuario = $db->single();

        if ($usuario) {
            $codigo = rand(100000, 999999); // Generar código de verificación
            $_SESSION['recuperar_correo'] = $correo;
            $_SESSION['codigo_verificacion'] = $codigo;
            $_SESSION['codigo_expiracion'] = time() + 600; // 10 minutos

            $mail = new PHPMailer(true);
            try {
                $mail->CharSet = 'UTF-8';
                $mail->SMTPDebug = 0;
                $mail->Debugoutput = 'html';
                $mail->isSMTP();
                $mail->Host = SMTP_HOST;
                $mail->SMTPAuth = true;
                $mail->Username = SMTP_USER; // Cambia esto y usa variables de entorno
                $mail->Password = SMTP_PASS; // Cambia esto y usa variables de entorno
                $mail->SMTPSecure = SMTP_SECURE;
                $mail->Port = SMTP_PORT;
                $mail->addEmbeddedImage('../images/logo-int-blanco.png', 'logo_cid');
                $mail->setFrom('soporte@saboresprohibidos.com', 'Sistema de Vinculación | INT');
                $mail->addAddress($correo);

                $mail->isHTML(true);
                $mail->Subject = 'Código de Verificación';
                $template = file_get_contents('../../includes/template_mail.html');
                $mail->Body = str_replace('{{CODIGO}}', $codigo, $template);

                $mail->send();
                // Redirigir a la página para ingresar el código
                header('Location: verificar_codigo.php');
                exit;
            } catch (Exception $e) {
                $error = 'Error al enviar el correo: ' . $mail->ErrorInfo;
            }
        } else {
            $error = 'No se encontró una cuenta con ese correo electrónico.';
        }
    }
}
?>
<?php include 'header_auth.php'; ?>
<div class="container py-4">
    <h1 class="mb-4">Recuperar Contraseña</h1>

    <div class="form-container">
        <?php if ($error): ?>
            <div class="alert alert-danger"> <?php echo $error; ?> </div>
        <?php endif; ?>
        <form method="post" class="card p-4">
            <div class="mb-3">
                <label for="correo" class="form-label">Correo Electrónico:</label>
                <input type="email" id="correo" name="correo" class="form-control" required>
            </div>
            <button type="submit" id='boton' class="boton btn btn-primary">Enviar</button>
        </form>
    </div>

</div>
<?php include 'footer_auth.php'; ?>
<script>
document.querySelector('form').addEventListener('submit', function(e) {
    document.getElementById('boton').disabled = true;
});
</script>
</body>

</html>