<?php
require_once '../../../includes/config.php';
require_once '../../../includes/db.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/functions.php';

if (!estaLogueado() || $_SESSION['rol'] == 'visualizador') {
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
    $db->query("DELETE FROM docentes_fases WHERE id_docente_fase = :id");
    $db->bind(':id', $id);
    
    if ($db->execute()) {
        $db->registrarCambio($_SESSION['id_usuario'], 'docentes_fases', $id, 'eliminación', $asignacion, null);
        header('Location: index.php?mensaje=eliminado');
        exit;
    } else {
        $error = 'Error al eliminar la asignación.';
    }
}

include '../header_admin.php';
?>

<div class="container py-4">
    <h1 class="mb-4">Eliminar Asignación</h1>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="card border-danger">
        <div class="card-body">
            <h5 class="card-title text-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>Confirmar eliminación
            </h5>
            
            <p class="mb-3">¿Estás seguro de que deseas eliminar esta asignación?</p>
            
            <div class="alert alert-warning mb-3">
                <strong>ID:</strong> <?php echo $asignacion['id_docente_fase']; ?>
                <p class="mb-0 mt-2">Rol: <strong><?php echo ucfirst($asignacion['rol']); ?></strong></p>
            </div>
            
            <form method="POST">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-2"></i>Sí, eliminar
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
