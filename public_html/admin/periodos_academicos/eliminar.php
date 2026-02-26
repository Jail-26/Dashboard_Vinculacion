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

$db->query("SELECT * FROM periodos_academicos WHERE id_periodo = :id");
$db->bind(':id', $id);
$periodo = $db->single();

if (!$periodo) {
    header('Location: index.php?mensaje=error');
    exit;
}

$db->query("SELECT COUNT(*) as total FROM fases WHERE id_periodo = :id");
$db->bind(':id', $id);
$total_fases = $db->single()['total'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $db->query("DELETE FROM periodos_academicos WHERE id_periodo = :id");
    $db->bind(':id', $id);
    
    if ($db->execute()) {
        $db->registrarCambio($_SESSION['id_usuario'], 'periodos_academicos', $id, 'eliminación', $periodo, null);
        header('Location: index.php?mensaje=eliminado');
        exit;
    } else {
        $error = 'Error al eliminar el período.';
    }
}

include '../header_admin.php';
?>

<div class="container py-4">
    <h1 class="mb-4">Eliminar Período Académico</h1>
    
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
            
            <p class="mb-3">¿Estás seguro de que deseas eliminar el siguiente período?</p>
            
            <div class="alert alert-warning mb-3">
                <strong><?php echo $periodo['nombre']; ?></strong>
                <p class="mb-0 mt-2 text-muted"><?php echo formatearFecha($periodo['fecha_inicio']); ?> - <?php echo formatearFecha($periodo['fecha_fin']); ?></p>
            </div>
            
            <?php if ($total_fases > 0): ?>
                <div class="alert alert-danger">
                    <strong>Advertencia:</strong> Este período tiene <?php echo $total_fases; ?> fase(s) asociada(s).
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-2"></i>Sí, eliminar período
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
