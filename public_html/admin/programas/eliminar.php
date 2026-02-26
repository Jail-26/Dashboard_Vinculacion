<?php
require_once '../../../includes/config.php';
require_once '../../../includes/db.php';
require_once '../../../includes/auth.php';

if (!estaLogueado() || $_SESSION['rol'] != 'administrador') {
    header('Location: ../login.php');
    exit;
}

$id_programa = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$db = new Database();
$db->query("DELETE FROM programas WHERE id_programa = :id");
$db->bind(':id', $id_programa);

$programa = $db->single(); // Assuming this retrieves the program details before deletion

if ($db->execute()) {
    $db->registrarCambio($_SESSION['id_usuario'], 'programas', $id_programa, 'eliminación', $programa, null);
    header('Location: index.php?mensaje=eliminado');
} else {
    header('Location: index.php?mensaje=error');
}
exit;
?>
