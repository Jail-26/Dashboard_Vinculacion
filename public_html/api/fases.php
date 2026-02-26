<?php
require_once '../../includes/ini.php';
require_once '../../includes/config.php';
require_once '../../includes/db.php';

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

$db = new Database();

try {
    // Verificar si se pide filtrar por proyecto o periodo
    $id_proyecto = isset($_GET['id_proyecto']) ? (int)$_GET['id_proyecto'] : 0;
    $id_periodo = isset($_GET['id_periodo']) ? (int)$_GET['id_periodo'] : 0;

    $sql = "
        SELECT 
            f.id_fase,
            f.nombre_fase,
            f.descripcion,
            f.estado,
            f.comunidad_beneficiaria,
            f.cantidad_beneficiados,
            f.cantidad_estudiantes,
            f.cantidad_profesores,
            f.imagen_url,
            f.pdf_url,
            f.fecha_creacion,
            f.fecha_modificacion,
            p.id_proyecto,
            p.nombre AS nombre_proyecto,
            pa.id_periodo,
            pa.nombre AS nombre_periodo
        FROM fases_proyecto f
        INNER JOIN proyectos p ON f.id_proyecto = p.id_proyecto
        INNER JOIN periodo_academico pa ON f.id_periodo = pa.id_periodo
    ";

    $conditions = [];
    if ($id_proyecto > 0) {
        $conditions[] = "f.id_proyecto = :id_proyecto";
    }
    if ($id_periodo > 0) {
        $conditions[] = "f.id_periodo = :id_periodo";
    }

    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }

    $sql .= " ORDER BY f.fecha_creacion DESC";

    $db->query($sql);

    if ($id_proyecto > 0) {
        $db->bind(':id_proyecto', $id_proyecto);
    }
    if ($id_periodo > 0) {
        $db->bind(':id_periodo', $id_periodo);
    }

    $fases = $db->resultSet();

    echo json_encode([
        'status' => 'success',
        'fases' => $fases
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}