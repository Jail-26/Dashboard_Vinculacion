<?php
require_once '../../includes/ini.php';

require_once '../../includes/config.php';
require_once '../../includes/db.php';

header('Content-Type: application/json');

$db = new Database();

// Obtener datos de programas
$db->query("SELECT id_programa, nombre, descripcion, fecha_creacion FROM programas ORDER BY fecha_creacion DESC");
$programas = $db->resultSet();

// Respuesta en formato JSON
echo json_encode($programas);
?>