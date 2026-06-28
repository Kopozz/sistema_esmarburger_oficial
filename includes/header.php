<?php
/**
 * ESMAR-BURGER — Header Público
 */
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/funciones.php';
$usuario = getUsuarioActual();
$carritoConteo = conteoCarrito();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="ESMAR BURGER — Las mejores hamburguesas artesanales con delivery a tu puerta. Hamburguesas, broaster, salchipapas y más.">
    <title><?php echo isset($titulo_pagina) ? $titulo_pagina . ' | ' : ''; ?>ESMAR BURGER</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/style.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar" id="navbar">
        <div class="container navbar-container">
            <a href="<?php echo BASE_URL; ?>/index.php" class="navbar-logo">
                <span class="logo-icon"><i class="ph-fill ph-hamburger"></i></span>
                <span class="logo-text">ESMAR<span class="logo-highlight">BURGER</span></span>
            </a>

            <button class="navbar-toggle" id="navbar-toggle" aria-label="Abrir menú">
                <span class="toggle-bar"></span>
                <span class="toggle-bar"></span>
                <span class="toggle-bar"></span>
            </button>

            <ul class="navbar-menu" id="navbar-menu">
                <li><a href="<?php echo BASE_URL; ?>/index.php" class="nav-link">Inicio</a></li>
                <li><a href="<?php echo BASE_URL; ?>/menu.php" class="nav-link">Menú</a></li>
                <?php if (estaLogueado()): ?>
                    <li><a href="<?php echo BASE_URL; ?>/mis_pedidos.php" class="nav-link">Mis Pedidos</a></li>
                <?php endif; ?>
                <li>
                    <a href="<?php echo BASE_URL; ?>/carrito.php" class="nav-link nav-carrito">
                        <i class="ph-fill ph-shopping-cart"></i> Carrito
                        <?php if ($carritoConteo > 0): ?>
                            <span class="carrito-badge" id="carrito-badge"><?php echo $carritoConteo; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <?php if (estaLogueado()): ?>
                    <li class="nav-usuario">
                        <span class="nav-saludo">Hola, <?php echo limpiar($usuario['nombre']); ?></span>
                        <?php if (esAdmin()): ?>
                            <a href="<?php echo BASE_URL; ?>/admin/index.php" class="btn btn-sm btn-outline">Panel Admin</a>
                        <?php endif; ?>
                        <a href="<?php echo BASE_URL; ?>/logout.php" class="btn btn-sm btn-ghost">Salir</a>
                    </li>
                <?php else: ?>
                    <li><a href="<?php echo BASE_URL; ?>/login.php" class="btn btn-sm btn-primary">Iniciar Sesión</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <!-- Contenido principal -->
    <main class="main-content">
        <div class="container">
            <?php mostrarMensaje(); ?>
