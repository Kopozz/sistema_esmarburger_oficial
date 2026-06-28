<?php
/**
 * ESMAR-BURGER — Header Panel Admin
 */
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/funciones.php';
protegerAdmin();
$usuario = getUsuarioActual();

// Determinar página actual para resaltar menú
$pagina_actual = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($titulo_pagina) ? $titulo_pagina . ' | ' : ''; ?>Admin — ESMAR BURGER</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/admin.css">
</head>
<body class="admin-body">
    <!-- Sidebar -->
    <aside class="admin-sidebar" id="admin-sidebar">
        <div class="sidebar-header">
            <a href="<?php echo BASE_URL; ?>/admin/index.php" class="sidebar-logo">
                <span class="logo-icon"><i class="ph-fill ph-hamburger"></i></span>
                <span class="logo-text">ESMAR<span class="logo-highlight">BURGER</span></span>
            </a>
            <span class="sidebar-badge">Admin</span>
        </div>

        <nav class="sidebar-nav">
            <a href="<?php echo BASE_URL; ?>/admin/index.php" class="sidebar-link <?php echo $pagina_actual === 'index' ? 'activo' : ''; ?>">
                <span class="sidebar-icon"><i class="ph ph-squares-four"></i></span> Dashboard
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/pedidos.php" class="sidebar-link <?php echo $pagina_actual === 'pedidos' ? 'activo' : ''; ?>">
                <span class="sidebar-icon"><i class="ph ph-receipt"></i></span> Pedidos
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/productos.php" class="sidebar-link <?php echo $pagina_actual === 'productos' ? 'activo' : ''; ?>">
                <span class="sidebar-icon"><i class="ph ph-hamburger"></i></span> Productos
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/compras.php" class="sidebar-link <?php echo $pagina_actual === 'compras' ? 'activo' : ''; ?>">
                <span class="sidebar-icon"><i class="ph ph-shopping-cart"></i></span> Compras
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/proveedores.php" class="sidebar-link <?php echo $pagina_actual === 'proveedores' ? 'activo' : ''; ?>">
                <span class="sidebar-icon"><i class="ph ph-factory"></i></span> Proveedores
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/inventario.php" class="sidebar-link <?php echo $pagina_actual === 'inventario' ? 'activo' : ''; ?>">
                <span class="sidebar-icon"><i class="ph ph-package"></i></span> Inventario
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/usuarios.php" class="sidebar-link <?php echo $pagina_actual === 'usuarios' ? 'activo' : ''; ?>">
                <span class="sidebar-icon"><i class="ph ph-users"></i></span> Usuarios
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/reportes.php" class="sidebar-link <?php echo $pagina_actual === 'reportes' ? 'activo' : ''; ?>">
                <span class="sidebar-icon"><i class="ph ph-chart-line-up"></i></span> Reportes
            </a>
        </nav>

        <div class="sidebar-footer">
            <a href="<?php echo BASE_URL; ?>/index.php" class="sidebar-link">
                <span class="sidebar-icon"><i class="ph ph-globe"></i></span> Ver Tienda
            </a>
            <a href="<?php echo BASE_URL; ?>/logout.php" class="sidebar-link">
                <span class="sidebar-icon"><i class="ph ph-sign-out"></i></span> Cerrar Sesión
            </a>
        </div>
    </aside>

    <!-- Contenido Principal Admin -->
    <div class="admin-main">
        <!-- Top bar -->
        <header class="admin-topbar">
            <button class="sidebar-toggle" id="sidebar-toggle" aria-label="Toggle sidebar">
                <span class="toggle-bar"></span>
                <span class="toggle-bar"></span>
                <span class="toggle-bar"></span>
            </button>
            <div class="topbar-info">
                <span class="topbar-saludo">Hola, <strong><?php echo limpiar($usuario['nombre']); ?></strong></span>
            </div>
        </header>

        <div class="admin-content">
            <?php mostrarMensaje(); ?>
