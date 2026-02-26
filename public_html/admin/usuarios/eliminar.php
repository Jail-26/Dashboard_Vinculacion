<?php
require_once '../../../includes/config.php';
require_once '../../../includes/db.php';
require_once '../../../includes/auth.php';

if (!estaLogueado() || $_SESSION['rol'] != 'administrador') {
    header('Location: ../login.php');
    exit;
}

$id_usuario = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$db = new Database();

// Eliminar el usuario
$db->query("DELETE FROM usuarios WHERE id_usuario = :id");
$db->bind(':id', $id_usuario);

if ($db->execute()) {
    header('Location: index.php?mensaje=eliminado');
} else {
    header('Location: index.php?mensaje=error');
}
exit;