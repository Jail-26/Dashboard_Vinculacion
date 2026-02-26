<?php
// modulos/reportes/generar_csv.php
require_once '../../../includes/config.php';
require_once '../../../includes/db.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/functions.php';

// Seguridad: Solo admin o coordinador
if (!estaLogueado() || ($_SESSION['rol'] != 'administrador' && $_SESSION['rol'] != 'coordinador')) {
    die("Acceso denegado.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $db = new Database();
    
    $tipo_reporte = $_POST['tipo_reporte'] ?? '';
    $periodo = $_POST['periodo'] ?? 'TODOS';
    
    $sql = "";
    $params = [];
    $nombre_archivo = "reporte_" . date('Y-m-d_H-i');

    // Selección de la Vista SQL según la opción
    switch ($tipo_reporte) {
        case 'maestro':
            $sql = "SELECT * FROM vw_reporte_maestro_proyectos";
            $nombre_archivo = "Reporte_Maestro_Proyectos_" . date('Ymd');
            break;

        case 'estudiantes':
            $nombre_archivo = "Cumplimiento_Estudiantes_" . date('Ymd');
            if ($periodo !== 'TODOS') {
                $sql = "SELECT * FROM vw_reporte_cumplimiento_estudiantes WHERE periodo_academico = :periodo";
                $params[':periodo'] = $periodo;
            } else {
                $sql = "SELECT * FROM vw_reporte_cumplimiento_estudiantes";
            }
            break;

        case 'docentes':
            $nombre_archivo = "Carga_Docente_" . date('Ymd');
            if ($periodo !== 'TODOS') {
                $sql = "SELECT * FROM vw_reporte_carga_docente WHERE periodo_academico = :periodo";
                $params[':periodo'] = $periodo;
            } else {
                $sql = "SELECT * FROM vw_reporte_carga_docente";
            }
            break;

        case 'fases':
            $sql = "SELECT * FROM vw_seguimiento_operativo_fases";
            $nombre_archivo = "Seguimiento_Fases_" . date('Ymd');
            break;

        case 'auditoria':
            $sql = "SELECT * FROM vw_auditoria_usuarios_sistema";
            $nombre_archivo = "Auditoria_Usuarios_" . date('Ymd');
            break;

        default:
            die("Tipo de reporte no válido.");
    }

    // Ejecutar consulta
    $db->query($sql);
    
    // Bindear parámetros si existen
    foreach ($params as $key => $value) {
        $db->bind($key, $value);
    }
    
    $resultados = $db->resultSet();

    // --- GENERACIÓN DEL CSV ---

    // 1. Cabeceras HTTP para forzar descarga
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $nombre_archivo . '.csv"');
    
    // 2. Abrir salida de PHP
    $output = fopen('php://output', 'w');

    // 3. Fix para caracteres especiales (tildes/ñ) en Excel
    // Escribir BOM (Byte Order Mark) para UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    // 4. Si hay resultados, generar columnas dinámicamente
    if (count($resultados) > 0) {
        // Obtener nombres de columnas (Keys del primer array)
        $columnas = array_keys($resultados[0]);
        
        // Convertir nombres de columnas a mayúsculas para que se vea bonito en el Excel
        $cabeceras_bonitas = array_map('strtoupper', $columnas);
        $cabeceras_bonitas = array_map(function($str) {
            return str_replace('_', ' ', $str); // Reemplazar guiones bajos por espacios
        }, $cabeceras_bonitas);

        fputcsv($output, $cabeceras_bonitas, ';');
        foreach ($resultados as $fila) {
            fputcsv($output, $fila, ';');
        }
        
    } else {
        fputcsv($output, ['No se encontraron registros para este reporte y filtros seleccionados.']);
    }

    fclose($output);
    exit;
} else {
    header('Location: index.php');
}
?>