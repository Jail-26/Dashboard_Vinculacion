<?php
require_once '../../includes/ini.php';

require_once '../../includes/config.php';
require_once '../../includes/db.php';

header('Content-Type: application/json');

$db = new Database();

// Obtener datos de programas
$db->query("SELECT id_programa, nombre, fecha_creacion FROM programas ORDER BY fecha_creacion DESC");
$programas = $db->resultSet();

// Obtener datos de proyectos con estadísticas
$db->query("
    SELECT 
        p.id_proyecto, 
        p.id_programa, -- Asegúrate de incluir este campo
        p.nombre,
        p.fase,
        p.estado, 
        p.descripcion_corta, 
        p.descripcion_extendida, 
        p.imagen_url,
        p.pdf_url, 
        p.cantidad_estudiantes, 
        p.cantidad_profesores, 
        p.cantidad_beneficiados, 
        e.nombre AS entidad_nombre, 
        e.latitud AS entidad_latitud, 
        e.longitud AS entidad_longitud 
    FROM proyectos p
    INNER JOIN entidades_receptoras e ON p.id_entidad = e.id_entidad
    ORDER BY p.fecha_creacion DESC
");
$proyectos = $db->resultSet();

// Respuesta en formato JSON
echo json_encode([
    'programas' => $programas,
    'proyectos' => $proyectos
]);
?>