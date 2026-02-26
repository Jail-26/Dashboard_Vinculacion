<?php
require_once '../../../includes/config.php';
require_once '../../../includes/db.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/functions.php';
require_once '../../../includes/PHPMailer/src/PHPMailer.php';
require_once '../../../includes/PHPMailer/src/SMTP.php';
require_once '../../../includes/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

if (!estaLogueado()) {
    header('Location: ../login.php');
    exit;
}

$db = new Database();

$db->query("SELECT * FROM docentes WHERE estado = 'Activo' ORDER BY apellidos, nombres");
$docentes = $db->resultSet();

$db->query("SELECT f. *, p.nombre AS nombre_proyecto FROM fases f INNER JOIN proyectos p on f.id_proyecto = p.id_proyecto  WHERE f.estado IN ('Pendiente', 'En ejecución') ORDER BY f.nombre;");
$fases = $db->resultSet();

$pre_id_fase = isset($_GET['fase']) ? (int)$_GET['fase'] : 0;

/**
 * Función para notificar al docente sobre su nueva asignación
 */
function notificarDocente($datosDocente, $datosFase, $rol) {
    $mail = new PHPMailer(true);
    
    try {
        $mail->CharSet = 'UTF-8';
        $mail->SMTPDebug = 0;
        $mail->Debugoutput = 'html';
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port = SMTP_PORT;
        $mail->addEmbeddedImage('../../images/logo-int-blanco.png', 'logo_cid');
        $mail->addEmbeddedImage('../../images/image.png', 'logo_covins');
        $mail->setFrom('info@vinculacionint.com', 'Sistema de Vinculación | INT');
        $mail->addAddress($datosDocente['correo']);

        $mail->isHTML(true);
        $mail->Subject = 'Nueva Asignación de Fase - Sistema de Vinculación';
        
        // Determinar el texto según el rol
        $textoRol = ($rol == 'Responsable') 
            ? 'como <strong>Docente Responsable</strong> de la fase' 
            : 'como <strong>Docente Colaborador</strong> en la fase';
        
       // Crear el cuerpo del correo
        $cuerpoCorreo = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background-color: #e63946; padding: 20px 40px; text-align: center;'>
                <div style='display: inline-block; vertical-align: middle; width: 45%; text-align: left;'>
                    <img src='cid:logo_cid' alt='Logo INT' style='max-width: 140px; height: auto;'>
                </div>
                <div style='display: inline-block; vertical-align: middle; width: 45%; text-align: right;'>
                    <img src='cid:logo_covins' alt='Logo Covins' style='max-width: 175px; height: auto;'>
                </div>
            </div>

            <div style='padding: 30px; background-color: #f8f9fa;'>
                <h2 style='color: #333;'>Hola {$datosDocente['nombres']} {$datosDocente['apellidos']},</h2>
                <p style='font-size: 16px; color: #555;'>
                    Te informamos que has sido asignado(a) {$textoRol} en un proyecto de Vinculación con la Sociedad del Instituto Nelson Torres.
                </p>
                <div style='background-color: white; padding: 20px; border-radius: 5px; margin: 20px 0;'>
                    <h3 style='color: #e63946; margin-top: 0;'>Detalles de la Asignación:</h3>
                    <p><strong>Proyecto:</strong> {$datosFase['nombre_proyecto']}</p>
                    <p><strong>Fase:</strong> {$datosFase['nombre']}</p>
                    <p><strong>Rol:</strong> {$rol}</p>
                </div>

                <p style='font-size: 12px; color: #999; margin-top: 30px;'>
                    Este es un correo automático, por favor no responder.
                </p>
            </div>
        </div>
        ";
        
        $mail->Body = $cuerpoCorreo;
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Error al enviar correo de notificación: ' . $mail->ErrorInfo);
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_docente = (int)$_POST['id_docente'];
    $id_fase = (int)$_POST['id_fase'];
    $rol = $_POST['rol'];
    $notificar = isset($_POST['notificar_docente']) ? true : false;

    $db->query("SELECT id_docente_fase FROM docentes_fases WHERE id_docente = :id_doc AND id_fase = :id_fase");
    $db->bind(':id_doc', $id_docente);
    $db->bind(':id_fase', $id_fase);
    if ($db->single()) {
        $error = 'Este docente ya está asignado a esta fase.';
    } else {
        $db->query("INSERT INTO docentes_fases (id_docente, id_fase, rol) VALUES (:id_docente, :id_fase, :rol)");
        $db->bind(':id_docente', $id_docente);
        $db->bind(':id_fase', $id_fase);
        $db->bind(':rol', $rol);

        if ($db->execute()) {
            $id_asign = $db->lastInsertId();
            $db->registrarCambio($_SESSION['id_usuario'], 'docentes_fases', $id_asign, 'creación', null, ['rol' => $rol]);
            
            // Si se marcó la opción de notificar, enviar correo
            if ($notificar) {
                // Obtener datos del docente
                $db->query("SELECT * FROM docentes WHERE id_docente = :id_doc");
                $db->bind(':id_doc', $id_docente);
                $datosDocente = $db->single();
                
                // Obtener datos de la fase
                $db->query("SELECT f.*, p.nombre AS nombre_proyecto FROM fases f INNER JOIN proyectos p ON f.id_proyecto = p.id_proyecto WHERE f.id_fase = :id_fase");
                $db->bind(':id_fase', $id_fase);
                $datosFase = $db->single();
                
                if ($datosDocente && $datosFase) {
                    $resultadoNotificacion = notificarDocente($datosDocente, $datosFase, $rol);
                    
                    if ($resultadoNotificacion) {
                        header('Location: index.php?mensaje=creado&notificado=si');
                    } else {
                        header('Location: index.php?mensaje=creado&notificado=error');
                    }
                } else {
                    header('Location: index.php?mensaje=creado&notificado=error');
                }
            } else {
                header('Location: index.php?mensaje=creado');
            }
            exit;
        } else {
            $error = 'Error al crear la asignación.';
        }
    }
}

include '../header_admin.php';
?>

<div class="container py-4">
    <h1 class="mb-4">Nueva Asignación de Docente</h1>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-body">
            <form method="POST" id="form-crear-docente-fase">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="id_docente" class="form-label">Docente *</label>
                            <input type="text" id="docente_search" class="form-control" placeholder="Buscar docente por nombre..." autocomplete="off">
                            <div id="docente_suggestions" class="list-group position-absolute w-100" style="z-index:1050; display:none;"></div>
                            <input type="hidden" id="id_docente" name="id_docente" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="id_fase" class="form-label">Fase *</label>
                            <select class="form-select" id="id_fase" name="id_fase" required>
                                <option value="">Selecciona una fase</option>
                                <?php foreach ($fases as $fase): ?>
                                    <option value="<?php echo $fase['id_fase']; ?>" <?php echo ($pre_id_fase && $pre_id_fase == $fase['id_fase']) ? 'selected' : ''; ?>>
                                        <?php echo $fase['nombre']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="rol" class="form-label">Rol</label>
                    <select class="form-select" id="rol" name="rol">
                        <option value="Colaborador" selected>Colaborador</option>
                        <option value="Responsable">Responsable</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="notificar_docente" name="notificar_docente" checked>
                        <label class="form-check-label" for="notificar_docente">
                            <i class="fas fa-envelope me-1"></i>
                            Notificar al docente por correo electrónico
                        </label>
                        <div class="form-text">El docente recibirá un correo con los detalles de su asignación.</div>
                    </div>
                </div>
                
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Crear Asignación
                    </button>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../footer_admin.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', function(){
    const docenteSearch = document.getElementById('docente_search');
    const idDocHidden = document.getElementById('id_docente');
    const suggestions = document.getElementById('docente_suggestions');
    let debounceTimer;

    docenteSearch.addEventListener('input', function(){
        const q = this.value.trim();
        idDocHidden.value = ''; // reset id when typing
        if (debounceTimer) clearTimeout(debounceTimer);
        if (q.length < 2) { suggestions.style.display = 'none'; return; }
        debounceTimer = setTimeout(function(){
            fetch('../ajax/buscar_docentes.php?q=' + encodeURIComponent(q))
                .then(r => r.json())
                .then(data => {
                    suggestions.innerHTML = '';
                    if (!data || !data.length) { suggestions.style.display = 'none'; return; }
                    data.forEach(function(item){
                        const el = document.createElement('button');
                        el.type = 'button';
                        el.className = 'list-group-item list-group-item-action';
                        el.textContent = item.nombre;
                        el.dataset.id = item.id_docente;
                        el.addEventListener('click', function(){
                            docenteSearch.value = item.nombre;
                            idDocHidden.value = item.id_docente;
                            suggestions.style.display = 'none';
                        });
                        suggestions.appendChild(el);
                    });
                    suggestions.style.display = 'block';
                }).catch(()=>{ suggestions.style.display = 'none'; });
        }, 300);
    });

    document.addEventListener('click', function(e){
        if (!document.getElementById('docente_suggestions').contains(e.target) && e.target !== docenteSearch) {
            suggestions.style.display = 'none';
        }
    });

    // On submit, ensure an id is set
    document.getElementById('form-crear-docente-fase').addEventListener('submit', function(e){
        if (!idDocHidden.value) {
            e.preventDefault();
            alert('Selecciona un docente válido desde el buscador (haz click en la sugerencia).');
        }
    });
});
</script>