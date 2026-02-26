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

include '../header_admin.php';
?>

<div class="container py-4">
    <a href="index.php" class="btn btn-secondary mb-3">
        <i class="fas fa-arrow-left me-2"></i>Volver
    </a>
    
    <h1 class="mb-4"><?php echo $periodo['nombre']; ?></h1>
    
    <div class="card">
        <div class="card-header bg-white">
            <h5 class="card-title mb-0">Información del Período</h5>
        </div>
        <div class="card-body">
            <table class="table table-borderless">
                <tr>
                    <th width="30%">ID:</th>
                    <td><?php echo $periodo['id_periodo']; ?></td>
                </tr>
                <tr>
                    <th>Nombre:</th>
                    <td><?php echo $periodo['nombre']; ?></td>
                </tr>
                <tr>
                    <th>Descripción:</th>
                    <td><?php echo $periodo['descripcion']; ?></td>
                </tr>
                <tr>
                    <th>Fecha Inicio:</th>
                    <td><?php echo $periodo['fecha_inicio'] ? formatearFecha($periodo['fecha_inicio']) : '-'; ?></td>
                </tr>
                <tr>
                    <th>Fecha Fin:</th>
                    <td><?php echo $periodo['fecha_fin'] ? formatearFecha($periodo['fecha_fin']) : '-'; ?></td>
                </tr>
                <tr>
                    <th>Estado:</th>
                    <td>
                        <span class="badge bg-<?php echo ($periodo['estado'] == 'Activo') ? 'success' : (($periodo['estado'] == 'Cerrado') ? 'danger' : 'secondary'); ?>">
                            <?php echo ucfirst($periodo['estado']); ?>
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>Fases Asociadas:</th>
                    <td>
                        <a href="../fases/index.php?id_periodo=<?php echo $periodo['id_periodo']; ?>" class="badge bg-info text-decoration-none">
                            <?php echo $total_fases; ?> fases
                        </a>
                    </td>
                </tr>
            </table>
            
            <?php if ($_SESSION['rol'] != 'visualizador'): ?>
            <div class="mt-3">
                <a href="editar.php?id=<?php echo $periodo['id_periodo']; ?>" class="btn btn-primary">
                    <i class="fas fa-edit me-2"></i>Editar
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../footer_admin.php'; ?>
