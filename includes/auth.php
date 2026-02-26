<?php
require_once 'config.php';
require_once 'db.php';

// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Función para iniciar sesión
function login($correo, $contrasena) {
    $db = new Database();
    
    // Buscar el usuario por correo
    $db->query("SELECT * FROM usuarios WHERE correo = :correo AND estado = TRUE");
    $db->bind(':correo', $correo);
    $usuario = $db->single();
    
    // Verificar si el usuario existe
    if (!$usuario) {
        return false;
    }
    
    // Verificar la contraseña
    if (password_verify($contrasena . HASH_KEY, $usuario['contrasena'])) {
        // Actualizar último acceso
        $db->query("UPDATE usuarios SET ultimo_acceso = NOW() WHERE id_usuario = :id");
        $db->bind(':id', $usuario['id_usuario']);
        $db->execute();
        
        // Guardar datos en la sesión
        $_SESSION['id_usuario'] = $usuario['id_usuario'];
        $_SESSION['nombre'] = $usuario['nombre'];
        $_SESSION['apellido'] = $usuario['apellido'];
        $_SESSION['correo'] = $usuario['correo'];
        $_SESSION['rol'] = $usuario['rol'];
        $_SESSION['tiempo_inicio'] = time();
        
        return true;
    } else {
        return false;
    }
}

// Función para cerrar sesión
function logout() {
    // Eliminar todas las variables de sesión
    $_SESSION = array();
    
    // Destruir la sesión
    session_destroy();
    
    // Redirigir al login
    header('Location: ' . ADMIN_URL . '/login.php');
    exit;
}

// Función para verificar si el usuario está logueado
function estaLogueado() {
    if (isset($_SESSION['id_usuario'])) {
        // Verificar el tiempo de la sesión
        if ((time() - $_SESSION['tiempo_inicio']) > SESSION_TIME) {
            logout(); // La sesión ha expirado
            return false;
        }
        
        // Actualizar el tiempo de inicio
        $_SESSION['tiempo_inicio'] = time();
        return true;
    }
    
    return false;
}

// Función para restringir acceso solo a administradores
function soloAdmin() {
    if (!estaLogueado() || $_SESSION['rol'] != 'administrador') {
        // Redirigir a página de error o dashboard según el caso
        if (estaLogueado()) {
            header('Location: ' . ADMIN_URL . '/index.php?error=permisos');
        } else {
            header('Location: ' . ADMIN_URL . '/login.php');
        }
        exit;
    }
}

// Función para generar un hash seguro de contraseña
function hashContrasena($contrasena) {
    return password_hash($contrasena . HASH_KEY, PASSWORD_DEFAULT);
}

// Función para cambiar contraseña
function cambiarContrasena($id_usuario, $contrasena_actual, $contrasena_nueva) {
    $db = new Database();
    
    // Obtener datos del usuario
    $db->query("SELECT contrasena FROM usuarios WHERE id_usuario = :id");
    $db->bind(':id', $id_usuario);
    $usuario = $db->single();
    
    // Verificar contraseña actual
    if (!password_verify($contrasena_actual . HASH_KEY, $usuario['contrasena'])) {
        return false;
    }
    
    // Hashear nueva contraseña
    $hash_nueva = hashContrasena($contrasena_nueva);
    
    // Actualizar contraseña
    $db->query("UPDATE usuarios SET contrasena = :contrasena WHERE id_usuario = :id");
    $db->bind(':contrasena', $hash_nueva);
    $db->bind(':id', $id_usuario);
    
    if ($db->execute()) {
        return true;
    } else {
        return false;
    }
}
?>