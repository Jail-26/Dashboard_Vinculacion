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

$db->query("SELECT * FROM planes WHERE id_plan = :id");
$db->bind(':id', $id);
$plan = $db->single();

if (!$plan) {
    header('Location: index.php?mensaje=error');
    exit;
}

$db->query("SELECT COUNT(*) as total FROM programas WHERE id_plan = :id");
$db->bind(':id', $id);
$total_programas = $db->single()['total'];

include '../header_admin.php';
?>

<div class="container py-4">
    <a href="index.php" class="btn btn-secondary mb-3">
        <i class="fas fa-arrow-left me-2"></i>Volver
    </a>
    
    <h1 class="mb-4"><?php echo $plan['nombre']; ?></h1>
    
    <div class="card">
        <div class="card-header bg-white">
            <h5 class="card-title mb-0">Información del Plan</h5>
        </div>
        <div class="card-body">
            <table class="table table-borderless">
                <tr>
                    <th width="30%">ID:</th>
                    <td><?php echo $plan['id_plan']; ?></td>
                </tr>
                <tr>
                    <th>Nombre:</th>
                    <td><?php echo $plan['nombre']; ?></td>
                </tr>
                <tr>
                    <th>Descripción:</th>
                    <td><?php echo nl2br($plan['descripcion']); ?></td>
                </tr>
                <tr>
                    <th>Fecha Inicio:</th>
                    <td><?php echo $plan['fecha_inicio'] ? formatearFecha($plan['fecha_inicio']) : '-'; ?></td>
                </tr>
                <tr>
                    <th>Fecha Fin:</th>
                    <td><?php echo $plan['fecha_fin'] ? formatearFecha($plan['fecha_fin']) : '-'; ?></td>
                </tr>
                <tr>
                    <th>Estado:</th>
                    <td>
                        <span class="badge bg-<?php echo ($plan['estado'] == 'Activo') ? 'success' : (($plan['estado'] == 'Finalizado') ? 'secondary' : 'warning'); ?>">
                            <?php echo ucfirst($plan['estado']); ?>
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>Programas Asociados:</th>
                    <td>
                        <a href="../programas/index.php?id_plan=<?php echo $plan['id_plan']; ?>" class="badge bg-info text-decoration-none">
                            <?php echo $total_programas; ?> programas
                        </a>
                    </td>
                </tr>
            </table>
            
            <?php if ($_SESSION['rol'] != 'visualizador'): ?>
            <div class="mt-3">
                <a href="editar.php?id=<?php echo $plan['id_plan']; ?>" class="btn btn-primary">
                    <i class="fas fa-edit me-2"></i>Editar
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../footer_admin.php'; ?>
