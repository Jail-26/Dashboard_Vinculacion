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

// Consultas para llenar los combos del formulario
$db->query("SELECT * FROM estudiantes ORDER BY apellidos, nombres");
$estudiantes = $db->resultSet();

$db->query("SELECT f.*, p.nombre AS nombre_proyecto FROM fases f INNER JOIN proyectos p on f.id_proyecto = p.id_proyecto WHERE f.estado IN ('Pendiente', 'En ejecución') ORDER BY f.nombre;");
$fases = $db->resultSet();

$pre_id_fase = isset($_GET['fase']) ? (int)$_GET['fase'] : 0;

/**
 * Función para notificar al estudiante
 */
function notificarEstudiante($datosEstudiante, $datosFase, $horasAsignadas, $nombreTutor) {
    $mail = new PHPMailer(true);
    try {
        $mail->CharSet = 'UTF-8';
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port = SMTP_PORT;
        
        $mail->setFrom('info@vinculacionint.com', 'Sistema de Vinculación | INT');
        $mail->addAddress($datosEstudiante['correo']);

        // Adjuntos
        if(file_exists('../../images/logo-int-blanco.png')) {
            $mail->addEmbeddedImage('../../images/logo-int-blanco.png', 'logo_cid');
            $mail->addEmbeddedImage('../../images/image.png', 'logo_covins');
        }

        $mail->isHTML(true);
        $mail->Subject = 'Nueva Asignación de Fase - Sistema de Vinculación';
        
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
                <h2 style='color: #333;'>Hola {$datosEstudiante['nombres']} {$datosEstudiante['apellidos']},</h2>
                <p style='font-size: 16px; color: #555;'>
                    Te informamos que has sido asignado(a) a una nueva fase en un proyecto de Vinculación con la Sociedad del Instituto Nelson Torres.</p>
                <div style='background-color: white; padding: 20px; border-radius: 5px; margin: 20px 0; border: 1px solid #ddd;'>
                    <h3 style='color: #e63946; margin-top: 0;'>Detalles de la Asignación:</h3>
                    <p><strong>Proyecto:</strong> {$datosFase['nombre_proyecto']}</p>
                    <p><strong>Fase:</strong> {$datosFase['nombre']}</p>
                    <p><strong>Horas Asignadas:</strong> {$horasAsignadas}</p>
                    <p><strong>Docente Tutor Asignado:</strong> {$nombreTutor}</p><br>
                    <p style='font-style: italic; margin: 0;'>
                        Por favor contáctate con tu Docente Tutor para mayor información
                    </p>
                </div>
                <p style='font-size: 12px; color: #999; margin-top: 30px;'>
                    Este es un correo automático, por favor no responder.
                </p>
            </div>
        </div>";
        
        $mail->Body = $cuerpoCorreo; // CRITICO: Asignar el cuerpo
        $mail->AltBody = "Asignación de fase: " . $datosFase['nombre'];

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Error Mail: ' . $mail->ErrorInfo);
        return false;
    }
}

// PROCESAMIENTO DEL FORMULARIO
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_estudiante = (int)$_POST['id_estudiante'];
    $id_fase = (int)$_POST['id_fase'];
    $horas_asignadas = (int)$_POST['horas_asignadas'];
    $estado = $_POST['estado'];
    $notificar = isset($_POST['notificar_estudiante']);

    // 1. Verificar duplicados
    $db->query("SELECT id_participacion FROM participaciones_estudiantes WHERE id_estudiante = :id_est AND id_fase = :id_fase");
    $db->bind(':id_est', $id_estudiante);
    $db->bind(':id_fase', $id_fase);
    
    if ($db->single()) {
        $error = 'Este estudiante ya participa en esta fase.';
    } else {
        // 2. Insertar registro
        $db->query("INSERT INTO participaciones_estudiantes (id_estudiante, id_fase, horas_asignadas, estado) 
                    VALUES (:id_estudiante, :id_fase, :horas_asignadas, :estado)");
        $db->bind(':id_estudiante', $id_estudiante);
        $db->bind(':id_fase', $id_fase);
        $db->bind(':horas_asignadas', $horas_asignadas);
        $db->bind(':estado', $estado);

        if ($db->execute()) {
            if ($notificar) {
                // OBTENER DATOS PARA EL CORREO
                $db->query("SELECT * FROM estudiantes WHERE id_estudiante = :id_est");
                $db->bind(':id_est', $id_estudiante);
                $datosEstudiante = $db->single();
                
                $db->query("SELECT f.*, p.nombre AS nombre_proyecto FROM fases f INNER JOIN proyectos p ON f.id_proyecto = p.id_proyecto WHERE f.id_fase = :id_fase");
                $db->bind(':id_fase', $id_fase);
                $datosFase = $db->single();

                // BUSCAR AL TUTOR (RESPONSABLE)
                $db->query("SELECT d.nombres, d.apellidos FROM docentes_fases df 
                            INNER JOIN docentes d ON df.id_docente = d.id_docente 
                            WHERE df.id_fase = :id_fase AND df.rol = 'Responsable' LIMIT 1");
                $db->bind(':id_fase', $id_fase);
                $tutor = $db->single();
                $nombreTutor = ($tutor) ? $tutor['nombres'] . ' ' . $tutor['apellidos'] : 'Por asignar';

                $resultado = notificarEstudiante($datosEstudiante, $datosFase, $horas_asignadas, $nombreTutor);
                $notif_status = $resultado ? 'si' : 'error';
                header("Location: index.php?mensaje=creado&notificado=$notif_status");
            } else {
                header('Location: index.php?mensaje=creado');
            }
            exit;
        }
    }
}

include '../header_admin.php';
?>

<div class="container py-4">
    <h1 class="mb-4">Nueva Participación de Estudiante</h1>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-body">
            <form method="POST" id="form-crear-participacion">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="id_estudiante" class="form-label">Estudiante *</label>
                            <input type="text" id="estudiante_search" class="form-control" placeholder="Buscar estudiante por nombre..." autocomplete="off">
                            <div id="estudiante_suggestions" class="list-group position-absolute w-100" style="z-index:1050; display:none;"></div>
                            <input type="hidden" id="id_estudiante" name="id_estudiante" required>
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
                    <label for="horas_asignadas" class="form-label">Horas Asignadas</label>
                    <input type="number" class="form-control" id="horas_asignadas" name="horas_asignadas" value="0">
                </div>
                
                <div class="mb-3">
                    <label for="estado" class="form-label">Estado</label>
                    <select class="form-select" id="estado" name="estado">
                        <option value="Activo" selected>Activo</option>
                        <option value="Finalizado">Finalizado</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="notificar_estudiante" name="notificar_estudiante" checked>
                        <label class="form-check-label" for="notificar_estudiante">
                            <i class="fas fa-envelope me-1"></i>
                            Notificar al estudiante por correo electrónico
                        </label>
                        <div class="form-text">El estudiante recibirá un correo con los detalles de su asignación.</div>
                    </div>
                </div>
                
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Crear Participación
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
    const estudianteSearch = document.getElementById('estudiante_search');
    const idEstHidden = document.getElementById('id_estudiante');
    const suggestions = document.getElementById('estudiante_suggestions');
    let debounceTimer;

    estudianteSearch.addEventListener('input', function(){
        const q = this.value.trim();
        idEstHidden.value = '';
        if (debounceTimer) clearTimeout(debounceTimer);
        if (q.length < 2) { suggestions.style.display = 'none'; return; }
        debounceTimer = setTimeout(function(){
            fetch('../ajax/buscar_estudiantes.php?q=' + encodeURIComponent(q))
                .then(r => r.json())
                .then(data => {
                    suggestions.innerHTML = '';
                    if (!data || !data.length) { suggestions.style.display = 'none'; return; }
                    data.forEach(function(item){
                        const el = document.createElement('button');
                        el.type = 'button';
                        el.className = 'list-group-item list-group-item-action';
                        el.textContent = item.nombre;
                        el.dataset.id = item.id_estudiante;
                        el.addEventListener('click', function(){
                            estudianteSearch.value = item.nombre;
                            idEstHidden.value = item.id_estudiante;
                            suggestions.style.display = 'none';
                        });
                        suggestions.appendChild(el);
                    });
                    suggestions.style.display = 'block';
                }).catch(()=>{ suggestions.style.display = 'none'; });
        }, 300);
    });

    document.addEventListener('click', function(e){
        if (!document.getElementById('estudiante_suggestions').contains(e.target) && e.target !== estudianteSearch) {
            suggestions.style.display = 'none';
        }
    });

    document.getElementById('form-crear-participacion').addEventListener('submit', function(e){
        if (!idEstHidden.value) {
            e.preventDefault();
            alert('Selecciona un estudiante válido desde el buscador (haz click en la sugerencia).');
        }
    });
});
</script>