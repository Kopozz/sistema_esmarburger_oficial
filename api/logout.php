<?php
/**
 * ESMAR BURGER - Logout Controller
 * Avance 2 - Ingeniería Web
 */
require_once 'config.php';

// Marcar cookie como LOGGED_OUT (más fiable que borrarla en serverless)
clearAuthCookie();
clearCartCookie();

// Destruir sesión PHP
session_unset();
session_destroy();

// Prevenir que el browser cache la respuesta (causa el "sigue logueado")
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

header('Location: /');
exit;
?>
