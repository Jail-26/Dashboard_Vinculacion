<?php
require_once '../../../includes/config.php';
require_once '../../../includes/db.php';
require_once '../../../includes/auth.php';

if (!estaLogueado()) {
    header('Location: ../login.php');
    exit;
}

$id_entidad = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$db = new Database();

// Obtener la entidad receptora
$db->query("SELECT * FROM entidades_receptoras WHERE id_entidad = :id_entidad");
$db->bind(':id_entidad', $id_entidad);
$entidad = $db->single();

if (!$entidad) {
    header('Location: index.php?mensaje=entidad_no_encontrada');
    exit;
}

include '../header_admin.php';
?>

<div class="container py-4">
    <h1>Detalles de la Entidad Receptora</h1>
    <a href="index.php" class="btn btn-secondary mb-3">Volver</a>
    <div class="card">
        <div class="card-body">
            <h5 class="card-title"><?php echo htmlspecialchars($entidad['nombre']); ?></h5>
            <p><strong>Representante Legal:</strong> <?php echo htmlspecialchars($entidad['representante_legal']); ?></p>
            <p><strong>Dirección:</strong> <?php echo htmlspecialchars($entidad['direccion']); ?></p>
            <p><strong>Cantón:</strong> <?php echo htmlspecialchars($entidad['canton']); ?></p>
            <p><strong>Provincia:</strong> <?php echo htmlspecialchars($entidad['provincia']); ?></p>
            <p><strong>Latitud:</strong> <?php echo htmlspecialchars($entidad['latitud']); ?></p>
            <p><strong>Longitud:</strong> <?php echo htmlspecialchars($entidad['longitud']); ?></p>
            <p><strong>Fecha de Creación:</strong> <?php echo htmlspecialchars($entidad['fecha_creacion']); ?></p>
            <p><strong>Última Modificación:</strong> <?php echo htmlspecialchars($entidad['fecha_modificacion']); ?></p>
        </div>
    </div>
</div>

<?php include '../footer_admin.php'; ?>