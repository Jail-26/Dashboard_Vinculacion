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

// Obtener carrera
$db->query("SELECT * FROM carreras WHERE id_carrera = :id");
$db->bind(':id', $id);
$carrera = $db->single();

if (!$carrera) {
    header('Location: index.php?mensaje=error');
    exit;
}

// Verificar si hay proyectos asociados
$db->query("SELECT COUNT(*) as total FROM docentes WHERE id_carrera = :id");
$db->bind(':id', $id);
$total_docentes = $db->single()['total'];

$db->query("SELECT COUNT(*) as total FROM estudiantes WHERE id_carrera = :id");
$db->bind(':id', $id);
$total_estudiantes = $db->single()['total'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Eliminar carrera
    $db->query("DELETE FROM carreras WHERE id_carrera = :id");
    $db->bind(':id', $id);
    
    if ($db->execute()) {
        $db->registrarCambio($_SESSION['id_usuario'], 'carreras', $id, 'eliminación', $carrera, null);
        header('Location: index.php?mensaje=eliminado');
        exit;
    } else {
        $error = 'Error al eliminar la carrera.';
    }
}

include '../header_admin.php';
?>

<div class="container py-4">
    <h1 class="mb-4">Eliminar Carrera</h1>
    
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
            
            <p class="mb-3">¿Estás seguro de que deseas eliminar la siguiente carrera?</p>
            
            <div class="alert alert-warning mb-3">
                <strong><?php echo $carrera['nombre']; ?></strong>
                <p class="mb-0 mt-2 text-muted"><?php echo $carrera['descripcion']; ?></p>
            </div>
            
            <?php if ($total_docentes > 0 || $total_estudiantes > 0): ?>
                <div class="alert alert-danger">
                    <strong>Advertencia:</strong> Esta carrera tiene:
                    <ul class="mb-0 mt-2">
                        <li><?php echo $total_docentes; ?> docente(s) asociado(s)</li>
                        <li><?php echo $total_estudiantes; ?> estudiante(s) asociado(s)</li>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-2"></i>Sí, eliminar carrera
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
