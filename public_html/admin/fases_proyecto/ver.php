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

$id_fase = (int)$_GET['id'];
$db = new Database();
$db->query("SELECT f.*, p.nombre AS proyecto_nombre, pe.nombre AS periodo_nombre
            FROM fases_proyecto f
            JOIN proyectos p ON f.id_proyecto = p.id_proyecto
            JOIN periodo_academico pe ON f.id_periodo = pe.id_periodo
            WHERE f.id_fase = :id");
$db->bind(':id', $id_fase, PDO::PARAM_INT);
$fase = $db->single();

if (!$fase) {
    header('Location: index.php');
    exit;
}

include '../header_admin.php';
?>

<div class="container py-4">
    <h1>Detalle de la Fase</h1>
    <table class="table table-bordered">
        <tr><th>ID</th><td><?php echo $fase['id_fase']; ?></td></tr>
        <tr><th>Proyecto</th><td><?php echo htmlspecialchars($fase['proyecto_nombre']); ?></td></tr>
        <tr><th>Periodo Académico</th><td><?php echo htmlspecialchars($fase['periodo_nombre']); ?></td></tr>
        <tr><th>Nombre Fase</th><td><?php echo htmlspecialchars($fase['nombre_fase']); ?></td></tr>
        <tr><th>Descripción</th><td><?php echo $fase['descripcion']; ?></td></tr>
        <tr><th>Estado</th><td><?php echo $fase['estado']; ?></td></tr>
        <tr><th>Comunidad Beneficiaria</th><td><?php echo htmlspecialchars($fase['comunidad_beneficiaria']); ?></td></tr>
        <tr><th>Cantidad Beneficiados</th><td><?php echo $fase['cantidad_beneficiados']; ?></td></tr>
        <tr><th>Cantidad Estudiantes</th><td><?php echo $fase['cantidad_estudiantes']; ?></td></tr>
        <tr><th>Cantidad Profesores</th><td><?php echo $fase['cantidad_profesores']; ?></td></tr>
        <tr><th>Imagen</th><td><?php if($fase['imagen_url']): ?><img src="<?php echo $fase['imagen_url']; ?>" width="150"><?php endif; ?></td></tr>
        <tr><th>PDF</th><td><?php if($fase['pdf_url']): ?><a href="<?php echo $fase['pdf_url']; ?>" target="_blank">Ver PDF</a><?php endif; ?></td></tr>
        <tr><th>Fecha Creación</th><td><?php echo $fase['fecha_creacion']; ?></td></tr>
        <tr><th>Última Modificación</th><td><?php echo $fase['fecha_modificacion']; ?></td></tr>
    </table>
    <a href="index.php" class="btn btn-secondary">Volver</a>
</div>

<?php include '../footer_admin.php'; ?>