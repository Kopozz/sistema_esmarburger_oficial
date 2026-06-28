<?php
/**
 * ESMAR-BURGER — Cerrar Sesión
 */
require_once __DIR__ . '/includes/config.php';
cerrarSesion();
session_start();
setMensaje('Has cerrado sesión correctamente.', 'info');
header('Location: ' . BASE_URL . '/index.php');
exit;
