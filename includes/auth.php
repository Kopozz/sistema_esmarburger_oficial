<?php
/**
 * ESMAR-BURGER — Funciones de Autenticación
 */

/**
 * Verificar si el usuario está logueado
 */
function estaLogueado() {
    return isset($_SESSION['usuario_id']);
}

/**
 * Verificar si el usuario es administrador
 */
function esAdmin() {
    return isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin';
}

/**
 * Proteger página: redirigir al login si no está logueado
 */
function protegerPagina() {
    if (!estaLogueado()) {
        setMensaje('Debes iniciar sesión para acceder a esta página.', 'warning');
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

/**
 * Proteger página de admin: redirigir si no es admin
 */
function protegerAdmin() {
    protegerPagina();
    if (!esAdmin()) {
        setMensaje('No tienes permisos para acceder al panel de administración.', 'error');
        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }
}

/**
 * Iniciar sesión del usuario
 */
function iniciarSesion($usuario) {
    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['usuario_nombre'] = $usuario['nombre'];
    $_SESSION['usuario_email'] = $usuario['email'];
    $_SESSION['usuario_rol'] = $usuario['rol'];
}

/**
 * Cerrar sesión
 */
function cerrarSesion() {
    session_unset();
    session_destroy();
}

/**
 * Obtener datos del usuario actual
 */
function getUsuarioActual() {
    if (!estaLogueado()) return null;
    return [
        'id' => $_SESSION['usuario_id'],
        'nombre' => $_SESSION['usuario_nombre'],
        'email' => $_SESSION['usuario_email'],
        'rol' => $_SESSION['usuario_rol']
    ];
}
