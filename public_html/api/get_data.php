<?php
// api/v1/getData.php

require_once '../../includes/ini.php'; // Si tienes configuraciones de inicio
require_once '../../includes/config.php';
require_once '../../includes/db.php';

// Cabeceras para permitir CORS (opcional si el front está en otro dominio) y JSON
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');

try {
    $db = new Database();

    // ---------------------------------------------------------
    // 1. OBTENER PROGRAMAS
    // ---------------------------------------------------------
    $db->query("SELECT id_programa, nombre, descripcion, fecha_inicio, fecha_fin FROM programas WHERE estado = 'Activo' ORDER BY fecha_inicio DESC");
    $rawProgramas = $db->resultSet();
    
    // Indexamos programas por ID para acceso rápido
    $programasMap = [];
    foreach ($rawProgramas as $prog) {
        $prog['proyectos'] = []; // Inicializamos el array de proyectos
        $programasMap[$prog['id_programa']] = $prog;
    }

    // ---------------------------------------------------------
    // 2. OBTENER PROYECTOS (Con Estadísticas Totales + Entidad)
    // ---------------------------------------------------------
    // Nota: Usamos subconsultas para calcular los totales a nivel de PROYECTO (sumando/contando únicos de sus fases)
    $sqlProyectos = "
        SELECT 
            p.id_proyecto, 
            p.id_programa,
            p.nombre,
            p.descripcion_corta,
            p.estado,
            p.banner, -- Asegúrate que esta columna exista, si no bórrala
            p.fecha_inicio,
            p.fecha_fin,
            
            -- ESTADÍSTICAS DEL PROYECTO (Agregadas)
            (SELECT COALESCE(SUM(f.cantidad_beneficiados), 0) 
             FROM fases f WHERE f.id_proyecto = p.id_proyecto) as total_beneficiarios,
             
            (SELECT COUNT(DISTINCT pe.id_estudiante) 
             FROM participaciones_estudiantes pe 
             INNER JOIN fases f2 ON pe.id_fase = f2.id_fase 
             WHERE f2.id_proyecto = p.id_proyecto) as total_estudiantes,
             
            (SELECT COUNT(DISTINCT df.id_docente) 
             FROM docentes_fases df 
             INNER JOIN fases f3 ON df.id_fase = f3.id_fase 
             WHERE f3.id_proyecto = p.id_proyecto) as total_docentes,

            -- DATOS DE LA ENTIDAD RECEPTORA
            e.nombre AS entidad_nombre,
            e.representante_legal AS entidad_representante,
            e.direccion AS entidad_direccion,
            e.canton AS entidad_canton,
            e.provincia AS entidad_provincia,
            e.latitud AS entidad_latitud, 
            e.longitud AS entidad_longitud 
        FROM proyectos p
        LEFT JOIN entidades_receptoras e ON p.id_entidad = e.id_entidad
        WHERE p.publicado = 1 -- Solo mostrar proyectos públicos
        ORDER BY p.fecha_inicio DESC
    ";

    $db->query($sqlProyectos);
    $rawProyectos = $db->resultSet();

    // Indexamos proyectos por ID
    $proyectosMap = [];
    foreach ($rawProyectos as $y) {
        $y['fases'] = []; // Inicializamos el array de fases
        $proyectosMap[$y['id_proyecto']] = $y;
    }

    // ---------------------------------------------------------
    // 3. OBTENER FASES (Con Estadísticas por Fase)
    // ---------------------------------------------------------
    $sqlFases = "
        SELECT 
            f.id_fase,
            f.id_proyecto,
            p.nombre as periodo_academico,
            f.nombre,
            f.banner,
            f.descripcion,
            f.fecha_inicio,
            f.fecha_fin,
            f.estado,
            f.cantidad_beneficiados, -- Beneficiarios específicos de esta fase
            
            -- Conteo de estudiantes en esta fase específica
            (SELECT COUNT(*) FROM participaciones_estudiantes pe WHERE pe.id_fase = f.id_fase) as estudiantes_fase,
            
            -- Conteo de docentes en esta fase específica
            (SELECT COUNT(*) FROM docentes_fases df WHERE df.id_fase = f.id_fase) as docentes_fase,
            (SELECT archivo_url from documentos_fases WHERE id_fase = f.id_fase and tipo = 'Acta' LIMIT 1) as documento_url 
        FROM fases f
        INNER JOIN periodos_academicos as p on f.id_periodo = p.id_periodo
        WHERE f.publicado = 1
        ORDER BY f.fecha_inicio ASC
    ";

    $db->query($sqlFases);
    $rawFases = $db->resultSet();

    // ---------------------------------------------------------
    // 4. ARMAR EL ÁRBOL JERÁRQUICO (Programas -> Proyectos -> Fases)
    // ---------------------------------------------------------
    
    // A) Asignar Fases a sus Proyectos
    foreach ($rawFases as $fase) {
        $id_proy = $fase['id_proyecto'];
        if (isset($proyectosMap[$id_proy])) {
            $proyectosMap[$id_proy]['fases'][] = $fase;
        }
    }

    // B) Asignar Proyectos a sus Programas
    foreach ($proyectosMap as $proyecto) {
        $id_prog = $proyecto['id_programa'];
        if (isset($programasMap[$id_prog])) {
            // Limpiamos datos redundantes antes de insertar (opcional)
            unset($proyecto['id_programa']); 
            $programasMap[$id_prog]['proyectos'][] = $proyecto;
        }
    }

    // C) Convertir el mapa de programas a un array indexado simple para JSON
    $respuestaFinal = array_values($programasMap);

    echo json_encode([
        'status' => 'success',
        'data' => $respuestaFinal,
        'timestamp' => date('c')
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Error al procesar la solicitud: ' . $e->getMessage()
    ]);
}
?>