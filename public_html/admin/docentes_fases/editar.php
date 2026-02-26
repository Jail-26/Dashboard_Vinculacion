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

$db->query("SELECT * FROM docentes_fases WHERE id_docente_fase = :id");
$db->bind(':id', $id);
$asignacion = $db->single();

if (!$asignacion) {
    header('Location: index.php?mensaje=error');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $rol = $_POST['rol'];
    $datos_anteriores = $asignacion;

    $db->query("UPDATE docentes_fases SET rol = :rol WHERE id_docente_fase = :id");
    $db->bind(':rol', $rol);
    $db->bind(':id', $id);

    if ($db->execute()) {
        $datos_nuevos = ['rol' => $rol];
        $db->registrarCambio($_SESSION['id_usuario'], 'docentes_fases', $id, 'modificación', $datos_anteriores, $datos_nuevos);
        header('Location: index.php?mensaje=actualizado');
        exit;
    } else {
        $error = 'Error al actualizar la asignación.';
    }
}

$db->query("SELECT nombres, apellidos FROM docentes WHERE id_docente = :id");
$db->bind(':id', $asignacion['id_docente']);
$docente = $db->single();

$db->query("SELECT nombre FROM fases WHERE id_fase = :id");
$db->bind(':id', $asignacion['id_fase']);
$fase = $db->single();

include '../header_admin.php';
?>

<div class="container py-4">
    <h1 class="mb-4">Editar Asignación</h1>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="card mb-4">
        <div class="card-body">
            <p><strong>Docente:</strong> <?php echo $docente['nombres'] . ' ' . $docente['apellidos']; ?></p>
            <p><strong>Fase:</strong> <?php echo $fase['nombre']; ?></p>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label for="rol" class="form-label">Rol</label>
                    <select class="form-select" id="rol" name="rol">
                        <option value="Colaborador" <?php echo ($asignacion['rol'] == 'Colaborador') ? 'selected' : ''; ?>>Colaborador</option>
                        <option value="Responsable" <?php echo ($asignacion['rol'] == 'Responsable') ? 'selected' : ''; ?>>Responsable</option>
                    </select>
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
