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

// Verificar si el tema es el activo
$db->query("
    SELECT id_tema
    FROM configuracion_dashboard
    ORDER BY fecha_seleccion DESC
    LIMIT 1
");
$temaActivo = $db->single();
$idTemaActivo = $temaActivo ? $temaActivo['id_tema'] : null;

if ($id_tema == $idTemaActivo) {
    header('Location: index.php?mensaje=tema_activo_no_eliminable');
    exit;
}

// Eliminar el tema
$db->query("DELETE FROM temas WHERE id_tema = :id_tema");
$db->bind(':id_tema', $id_tema);

if ($db->execute()) {
    header('Location: index.php?mensaje=tema_eliminado');
} else {
    header('Location: index.php?mensaje=error');
}
exit;
?>