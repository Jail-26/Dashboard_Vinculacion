<?php
require_once '../../../includes/config.php';
require_once '../../../includes/db.php';
require_once '../../../includes/auth.php';

if (!estaLogueado()) {
    header('Location: ../login.php');
    exit;
}

$id_tema = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$db = new Database();
$db->query("UPDATE configuracion_dashboard SET id_tema = :id_tema WHERE id_configuracion = 3");
$db->bind(':id_tema', $id_tema);

if ($db->execute()) {
    header('Location: index.php?mensaje=tema_activado');
} else {
    header('Location: index.php?mensaje=error');
}
exit;
?>