<?php
require_once '../../../includes/config.php';
require_once '../../../includes/db.php';
require_once '../../../includes/auth.php';

if (!estaLogueado() || $_SESSION['rol'] != 'administrador') {
    header('Location: ../login.php');
    exit;
}

$id_proyecto = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$db = new Database();

// Obtener la URL de la imagen antes de eliminar el proyecto
$db->query("SELECT banner FROM proyectos WHERE id_proyecto = :id");
$db->bind(':id', $id_proyecto, PDO::PARAM_INT);
$proyecto = $db->single();

if ($proyecto && $proyecto['banner']) {
    // Eliminar la imagen del servidor
    $banner = $_SERVER['DOCUMENT_ROOT'] . $proyecto['banner'];
    if (file_exists($banner)) {
        unlink($banner);
    }
}

// Eliminar el proyecto
$db->query("DELETE FROM proyectos WHERE id_proyecto = :id");
$db->bind(':id', $id_proyecto, PDO::PARAM_INT);

if ($db->execute()) {
    $db->registrarCambio($_SESSION['id_usuario'], 'proyectos', $id_proyecto, 'eliminación', $proyecto, null);
    header('Location: index.php?mensaje=eliminado');
} else {
    header('Location: index.php?mensaje=error');
}
exit;
?>
