<?php
require_once '../../../includes/config.php';
require_once '../../../includes/db.php';
require_once '../../../includes/auth.php';

if (!estaLogueado()) {
    header('Location: ../login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id_periodo = (int)$_GET['id'];
$db = new Database();
$db->query("SELECT * FROM periodo_academico WHERE id_periodo = :id");
$db->bind(':id', $id_periodo, PDO::PARAM_INT);
$periodo = $db->single();

if (!$periodo) {
    header('Location: index.php');
    exit;
}

include '../header_admin.php';
?>

<div class="container py-4">
    <h1>Detalle del Periodo Académico</h1>
    <table class="table table-bordered">
        <tr><th>ID</th><td><?php echo $periodo['id_periodo']; ?></td></tr>
        <tr><th>Nombre</th><td><?php echo htmlspecialchars($periodo['nombre']); ?></td></tr>
        <tr><th>Fecha de Inicio</th><td><?php echo $periodo['fecha_inicio']; ?></td></tr>
        <tr><th>Fecha de Fin</th><td><?php echo $periodo['fecha_fin']; ?></td></tr>
        <tr><th>Activo</th><td><?php echo $periodo['activo'] ? 'Sí' : 'No'; ?></td></tr>
    </table>
    <a href="index.php" class="btn btn-secondary">Volver</a>
</div>

<?php include '../footer_admin.php'; ?>