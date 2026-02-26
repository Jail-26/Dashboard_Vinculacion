<?php
require_once 'config.php';

// Función para limpiar datos de entrada
function limpiarDatos($datos) {
    $datos = trim($datos);
    $datos = stripslashes($datos);
    $datos = htmlspecialchars($datos);
    return $datos;
}

// Función para validar si una fecha tiene formato válido
function validarFecha($fecha) {
    $formato = 'Y-m-d';
    $fecha_obj = DateTime::createFromFormat($formato, $fecha);
    return $fecha_obj && $fecha_obj->format($formato) === $fecha;
}

// Función para registrar cambios en el historial
function registrarCambio($db, $id_usuario, $tabla, $id_registro, $tipo, $datos_anteriores = null, $datos_nuevos = null) {
    if ($datos_anteriores) {
        $datos_anteriores = json_encode($datos_anteriores);
    }
    
    if ($datos_nuevos) {
        $datos_nuevos = json_encode($datos_nuevos);
    }
    
    $ip = $_SERVER['REMOTE_ADDR'];
    
    $db->query("INSERT INTO historial_cambios (id_usuario, tabla_afectada, id_registro, tipo_cambio, 
                datos_anteriores, datos_nuevos, ip_usuario) 
                VALUES (:id_usuario, :tabla, :id_registro, :tipo, :datos_anteriores, :datos_nuevos, :ip)");
                
    $db->bind(':id_usuario', $id_usuario);
    $db->bind(':tabla', $tabla);
    $db->bind(':id_registro', $id_registro);
    $db->bind(':tipo', $tipo);
    $db->bind(':datos_anteriores', $datos_anteriores);
    $db->bind(':datos_nuevos', $datos_nuevos);
    $db->bind(':ip', $ip);
    
    return $db->execute();
}

// Función para subir imágenes
function subirImagen($archivo, $subdirectorio = null) {
    // Construir ruta base
    $ruta_base = UPLOAD_PATH;
    
    // Si se proporciona un subdirectorio, agregarlo
    if ($subdirectorio) {
        $ruta_base = UPLOAD_PATH . $subdirectorio . '/';
    }
    
    // Verificar si existe el directorio, si no, crearlo
    if (!is_dir($ruta_base)) {
        mkdir($ruta_base, 0755, true);
    }
    
    // Obtener información del archivo
    $nombre_archivo = $archivo['name'];
    $tipo_archivo = $archivo['type'];
    $tamano_archivo = $archivo['size'];
    $temp_archivo = $archivo['tmp_name'];
    $error_archivo = $archivo['error'];
    
    // Verificar si hay errores
    if ($error_archivo !== 0) {
        return array('exito' => false, 'error' => 'Error al cargar el archivo: ' . $error_archivo);
    }
    
    // Verificar tamaño del archivo
    if ($tamano_archivo > MAX_FILE_SIZE) {
        return array('exito' => false, 'error' => 'El archivo es demasiado grande. Máximo permitido: ' . (MAX_FILE_SIZE / 1048576) . 'MB');
    }
    
    // Verificar extensión
    $extension = strtolower(pathinfo($nombre_archivo, PATHINFO_EXTENSION));
    if (!in_array($extension, ALLOWED_EXTENSIONS)) {
        return array('exito' => false, 'error' => 'Extensión no permitida. Use: ' . implode(', ', ALLOWED_EXTENSIONS));
    }
    
    // Generar nombre único para el archivo
    $nombre_unico = uniqid() . '_' . time() . '.' . $extension;
    $ruta_destino = $ruta_base . $nombre_unico;
    
    // Mover el archivo
    if (move_uploaded_file($temp_archivo, $ruta_destino)) {
        return array(
            'exito' => true,
            'nombre_original' => $nombre_archivo,
            'nombre_unico' => $nombre_unico,
            'ruta' => $nombre_unico,
            'url' => UPLOAD_URL . ($subdirectorio ? $subdirectorio . '/' : '') . $nombre_unico
        );
    } else {
        return array('exito' => false, 'error' => 'Error al mover el archivo subido');
    }
}

// Función para subir documentos (PDF e imágenes) con límite 5MB
function subirDocumento($archivo, $directorio = null) {
    if (!$directorio) {
        $directorio = UPLOAD_PATH;
    }

    if (!is_dir($directorio)) {
        mkdir($directorio, 0755, true);
    }

    $nombre_archivo = $archivo['name'];
    $tamano_archivo = $archivo['size'];
    $temp_archivo = $archivo['tmp_name'];
    $error_archivo = $archivo['error'];

    if ($error_archivo !== 0) {
        return array('exito' => false, 'error' => 'Error al cargar el archivo: ' . $error_archivo);
    }

    if ($tamano_archivo > MAX_FILE_SIZE) {
        return array('exito' => false, 'error' => 'El archivo es demasiado grande. Máximo permitido: ' . (MAX_FILE_SIZE / 1048576) . ' MB');
    }

    $extension = strtolower(pathinfo($nombre_archivo, PATHINFO_EXTENSION));
    $allowed = array_merge(ALLOWED_EXTENSIONS, ['pdf']);
    if (!in_array($extension, $allowed)) {
        return array('exito' => false, 'error' => 'Extensión no permitida. Use: ' . implode(', ', $allowed));
    }

    // Generar nombre único y mover
    $nombre_unico = uniqid() . '_' . time() . '.' . $extension;
    $ruta_destino = $directorio . $nombre_unico;

    if (move_uploaded_file($temp_archivo, $ruta_destino)) {
        // Guardamos en DB el nombre relativo (nombre_unico)
        return array('exito' => true, 'ruta' => $nombre_unico, 'ruta_completa' => $ruta_destino, 'url' => UPLOAD_URL . $nombre_unico);
    } else {
        return array('exito' => false, 'error' => 'Error al mover el archivo subido');
    }
}


// Función para formatear fechas
function formatearFecha($fecha, $formato = 'd/m/Y') {
    $fecha_obj = new DateTime($fecha);
    return $fecha_obj->format($formato);
}

// Función para truncar texto
function truncarTexto($texto, $longitud = 100, $puntos = "...") {
    if (mb_strlen($texto) <= $longitud) {
        return $texto;
    }
    
    $texto = mb_substr($texto, 0, $longitud);
    $ultimo_espacio = mb_strrpos($texto, ' ');
    
    if ($ultimo_espacio !== false) {
        $texto = mb_substr($texto, 0, $ultimo_espacio);
    }
    
    return $texto . $puntos;
}

// Función para generar slug amigable para URLs
function generarSlug($texto) {
    // Convertir a minúsculas
    $texto = mb_strtolower($texto);
    
    // Reemplazar caracteres especiales
    $texto = str_replace(
        array('á', 'é', 'í', 'ó', 'ú', 'ñ', 'ü'),
        array('a', 'e', 'i', 'o', 'u', 'n', 'u'),
        $texto
    );
    
    // Eliminar caracteres que no sean letras, números o guiones
    $texto = preg_replace('/[^a-z0-9\-]/', ' ', $texto);
    
    // Reemplazar espacios con guiones
    $texto = preg_replace('/\s+/', '-', trim($texto));
    
    // Eliminar guiones múltiples
    $texto = preg_replace('/-+/', '-', $texto);
    
    return $texto;
}

// Función para validar correo electrónico
function validarEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Generar un token único
function generarToken() {
    return bin2hex(random_bytes(16));
}
?>