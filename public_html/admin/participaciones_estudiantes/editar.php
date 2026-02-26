<?php
require_once '../../../includes/config.php';
require_once '../../../includes/db.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/functions.php';

if (!estaLogueado()) {
    header('Location: ../login.php');
    exit;
}

$db = new Database();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$db->query("SELECT * FROM participaciones_estudiantes WHERE id_participacion = :id");
$db->bind(':id', $id);
$participacion = $db->single();

if (!$participacion) {
    header('Location: index.php?mensaje=error');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $horas_asignadas = (int)$_POST['horas_asignadas'];
    $horas_cumplidas = (int)$_POST['horas_cumplidas'];
    $calificacion = !empty($_POST['calificacion']) ? (float)$_POST['calificacion'] : null;
    $estado = $_POST['estado'];
    $observaciones = limpiarDatos($_POST['observaciones']);
    
    $datos_anteriores = $participacion;

    $db->query("UPDATE participaciones_estudiantes SET horas_asignadas = :horas_asignadas, horas_cumplidas = :horas_cumplidas, calificacion = :calificacion, estado = :estado, observaciones = :observaciones WHERE id_participacion = :id");
    $db->bind(':horas_asignadas', $horas_asignadas);
    $db->bind(':horas_cumplidas', $horas_cumplidas);
    $db->bind(':calificacion', $calificacion);
    $db->bind(':estado', $estado);
    $db->bind(':observaciones', $observaciones);
    $db->bind(':id', $id);

    if ($db->execute()) {
        $datos_nuevos = ['estado' => $estado, 'calificacion' => $calificacion];
        $db->registrarCambio($_SESSION['id_usuario'], 'participaciones_estudiantes', $id, 'modificación', $datos_anteriores, $datos_nuevos);
        header('Location: index.php?mensaje=actualizado');
        exit;
    } else {
        $error = 'Error al actualizar la participación.';
    }
}

$db->query("SELECT e.nombres, e.apellidos FROM estudiantes e WHERE e.id_estudiante = :id");
$db->bind(':id', $participacion['id_estudiante']);
$estudiante = $db->single();

$db->query("SELECT f.nombre FROM fases f WHERE f.id_fase = :id");
$db->bind(':id', $participacion['id_fase']);
$fase = $db->single();

include '../header_admin.php';
?>

<div class="container py-4">
    <h1 class="mb-4">Editar Participación</h1>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="card mb-4">
        <div class="card-body">
            <p><strong>Estudiante:</strong> <?php echo $estudiante['nombres'] . ' ' . $estudiante['apellidos']; ?></p>
            <p><strong>Fase:</strong> <?php echo $fase['nombre']; ?></p>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body">
            <form method="POST">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="horas_asignadas" class="form-label">Horas Asignadas</label>
                            <input type="number" class="form-control" id="horas_asignadas" name="horas_asignadas" value="<?php echo $participacion['horas_asignadas']; ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="horas_cumplidas" class="form-label">Horas Cumplidas</label>
                            <input type="number" class="form-control" id="horas_cumplidas" name="horas_cumplidas" value="<?php echo $participacion['horas_cumplidas']; ?>">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="calificacion" class="form-label">Calificación</label>
                            <input type="number" class="form-control" id="calificacion" name="calificacion" step="0.01" value="<?php echo $participacion['calificacion']; ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="estado" class="form-label">Estado</label>
                            <select class="form-select" id="estado" name="estado">
                                <option value="Activo" <?php echo ($participacion['estado'] == 'Activo') ? 'selected' : ''; ?>>Activo</option>
                                <option value="Finalizado" <?php echo ($participacion['estado'] == 'Finalizado') ? 'selected' : ''; ?>>Finalizado</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="observaciones" class="form-label">Observaciones</label>
                    <textarea class="form-control" id="observaciones" name="observaciones" rows="3"><?php echo $participacion['observaciones']; ?></textarea>
                </div>
                
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Actualizar
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
