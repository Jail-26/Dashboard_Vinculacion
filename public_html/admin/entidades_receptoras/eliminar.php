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
$db->query("DELETE FROM entidades_receptoras WHERE id_entidad = :id_entidad");
$db->bind(':id_entidad', $id_entidad);

if ($db->execute()) {
    header('Location: index.php?mensaje=entidad_eliminada');
} else {
    header('Location: index.php?mensaje=error');
}
exit;
?>