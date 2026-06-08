<?php
/**
 * ESMAR BURGER - Logout Controller
 * Avance 2 - Ingeniería Web
 */
session_start();
session_unset();
session_destroy();
header('Location: /index.php');
exit;
?>
