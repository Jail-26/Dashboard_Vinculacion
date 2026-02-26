<?php
require_once '../../../includes/config.php';
require_once '../../../includes/db.php';
header('Content-Type: application/json; charset=utf-8');

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
if ($q === '') {
    echo json_encode([]);
    exit;
}

$db = new Database();
$term = '%' . $q . '%';
$db->query("SELECT id_estudiante, CONCAT(nombres, ' ', apellidos) as nombre FROM estudiantes 
            WHERE (CONCAT(apellidos, ' ', nombres) LIKE :q1 OR CONCAT(nombres, ' ', apellidos) LIKE :q2 OR apellidos LIKE :q3 OR nombres LIKE :q4) 
            LIMIT 20");
$db->bind(':q1', $term);
$db->bind(':q2', $term);
$db->bind(':q3', $term);
$db->bind(':q4', $term);
$results = $db->resultSet();

echo json_encode($results);
exit;

?>
