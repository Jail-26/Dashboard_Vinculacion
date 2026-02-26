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

$db->query("SELECT * FROM fases WHERE id_fase = :id");
$db->bind(':id', $id);
$fase = $db->single();

if (!$fase) {
    header('Location: index.php?mensaje=error');
    exit;
}

$db->query("SELECT COUNT(*) as total FROM docentes_fases WHERE id_fase = :id");
$db->bind(':id', $id);
$total_docentes = $db->single()['total'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $db->query("DELETE FROM fases WHERE id_fase = :id");
    $db->bind(':id', $id);
    
    if ($db->execute()) {
        $db->registrarCambio($_SESSION['id_usuario'], 'fases', $id, 'eliminación', $fase, null);
        header('Location: index.php?mensaje=eliminado');
        exit;
    } else {
        $error = 'Error al eliminar la fase.';
    }
}

include '../header_admin.php';
?>

<div class="container py-4">
    <h1 class="mb-4">Eliminar Fase</h1>
    
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
            
            <p class="mb-3">¿Estás seguro de que deseas eliminar la siguiente fase?</p>
            
            <div class="alert alert-warning mb-3">
                <strong><?php echo $fase['nombre']; ?></strong>
            </div>
            
            <?php if ($total_docentes > 0): ?>
                <div class="alert alert-danger">
                    <strong>Advertencia:</strong> Esta fase tiene <?php echo $total_docentes; ?> docente(s) asignado(s).
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-2"></i>Sí, eliminar fase
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
