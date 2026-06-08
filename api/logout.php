<?php
/**
 * ESMAR BURGER - Logout Controller
 * Avance 2 - Ingeniería Web
 */
require_once 'config.php';
clearAuthCookie();
session_unset();
session_destroy();
header('Location: /');
exit;
?>
