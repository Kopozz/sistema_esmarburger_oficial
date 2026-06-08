<?php
/**
 * ESMAR BURGER - Header
 * Avance 2 - Ingeniería Web
 */
require_once __DIR__ . '/../config.php';

// Calcular total de items en el carrito
$cartCount = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $qty) {
        $cartCount += $qty;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Esmar Burger - Premium Delivery SAAS</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <div class="nav-container">
            <a href="index.php" class="logo-link">
                <!-- SVG Premium que emula el logo provisto -->
                <svg class="logo-svg" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="50" cy="50" r="46" fill="none" stroke="#00baf2" stroke-width="4" />
                    <circle cx="50" cy="50" r="40" fill="none" stroke="#ff6b00" stroke-dasharray="6,4" stroke-width="1.5" />
                    <!-- Burger shape simplified -->
                    <path d="M30 46 C30 35, 70 35, 70 46 Z" fill="#ff6b00" />
                    <rect x="26" y="50" width="48" height="6" rx="3" fill="#00baf2" />
                    <path d="M28 60 C28 66, 72 66, 72 60 Z" fill="#ff6b00" />
                </svg>
                <span class="logo-text">ESMAR BURGER</span>
            </a>

            <nav>
                <ul class="nav-menu">
                    <li><a href="index.php" class="nav-link">Carta</a></li>
                    <?php if (isLoggedIn()): ?>
                        <li><a href="mis_pedidos.php" class="nav-link">Mis Pedidos</a></li>
                        <?php if (isAdmin()): ?>
                            <li><a href="admin_dashboard.php" class="nav-link" style="color: var(--secondary);">Panel Admin</a></li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
            </nav>

            <div class="nav-actions">
                <a href="cart.php" class="btn-icon" title="Ver Carrito">
                    <!-- Icono Carrito -->
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                    <?php if ($cartCount > 0): ?>
                        <span class="badge"><?php echo $cartCount; ?></span>
                    <?php endif; ?>
                </a>

                <?php if (isLoggedIn()): ?>
                    <span style="font-size: 0.9rem; color: var(--text-secondary);">Hola, <strong><?php echo htmlspecialchars($_SESSION['user_nombre']); ?></strong></span>
                    <a href="logout.php" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.85rem;">Salir</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.85rem;">Ingresar</a>
                    <a href="registro.php" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.85rem;">Registrarse</a>
                <?php endif; ?>
            </div>
        </div>
    </header>
    <main style="flex: 1;">
