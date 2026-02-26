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
$db->query("DELETE FROM fases_proyecto WHERE id_fase = :id");
$db->bind(':id', $id_fase, PDO::PARAM_INT);

if ($db->execute()) {
    header('Location: index.php?mensaje=eliminado');
    exit;
} else {
    header('Location: index.php?error=error_eliminar');
    exit;
}