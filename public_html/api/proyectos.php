<?php
require_once '../../includes/ini.php';

require_once '../../includes/config.php';
require_once '../../includes/db.php';

header('Content-Type: application/json');

$db = new Database();
$proyecto_id = isset($_GET['proyecto_id']) ? (int)$_GET['proyecto_id'] : 0;
$programa_id = isset($_GET['programa_id']) ? (int)$_GET['programa_id'] : 0;

if ($programa_id > 0) {
    // Obtener proyectos filtrados por programa
    $db->query("
        SELECT 
            p.id_proyecto, 
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
        WHERE p.id_programa = :programa_id
        ORDER BY p.fecha_creacion DESC
    ");
    $db->bind(':programa_id', $programa_id);
} else if ($proyecto_id > 0) {
    // Obtener un proyecto específico
    $db->query("
        SELECT 
            p.id_proyecto, 
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
        WHERE p.id_proyecto = :proyecto_id
    ");
    $db->bind(':proyecto_id', $proyecto_id);
} else {
    // Obtener todos los proyectos
    $db->query("
        SELECT 
            p.id_proyecto, 
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
}

$proyectos = $db->resultSet();

// Respuesta en formato JSON
echo json_encode($proyectos);
