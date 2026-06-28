<?php
/**
 * ESMAR-BURGER — Página Principal (Landing)
 */
require_once __DIR__ . '/includes/config.php';

// Obtener productos destacados
$stmt = $pdo->query("SELECT p.*, c.nombre as categoria FROM productos p LEFT JOIN categorias c ON p.categoria_id = c.id WHERE p.disponible = 1 ORDER BY RAND() LIMIT 6");
$productosDestacados = $stmt->fetchAll();

// Obtener categorías
$stmt = $pdo->query("SELECT * FROM categorias WHERE activo = 1");
$categorias = $stmt->fetchAll();

$titulo_pagina = 'Inicio';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero Section -->
<section class="hero" style="margin: -70px -20px 0; padding: 0 20px;">
    <div class="container">
        <div class="hero-content">
            <p class="hero-subtitle">🔥 Delivery a tu puerta</p>
            <h1>Las Mejores <span>Hamburguesas</span> Artesanales de la Ciudad</h1>
            <p class="hero-desc">
                Disfruta de nuestras deliciosas hamburguesas preparadas con los ingredientes más frescos. 
                Hacemos delivery rápido para que disfrutes en la comodidad de tu hogar.
            </p>
            <div class="hero-buttons">
                <a href="<?php echo BASE_URL; ?>/menu.php" class="btn btn-primary btn-lg">
                    🍔 Ver Menú Completo
                </a>
                <a href="#productos-destacados" class="btn btn-outline btn-lg">
                    ⬇️ Explorar
                </a>
            </div>
        </div>
        <div class="hero-emoji">🍔</div>
    </div>
</section>

<!-- Features -->
<section class="seccion" style="margin: 0 -20px; padding-left: 20px; padding-right: 20px;">
    <div class="container">
        <div class="seccion-titulo">
            <h2>¿Por qué elegirnos?</h2>
            <p>Calidad, sabor y rapidez en cada pedido que realizas</p>
            <div class="titulo-linea"></div>
        </div>
        <div class="features-grid">
            <div class="feature-card animar">
                <span class="feature-icono">🍖</span>
                <h3>Ingredientes Frescos</h3>
                <p>Seleccionamos los mejores ingredientes diariamente para garantizar la calidad de cada hamburguesa.</p>
            </div>
            <div class="feature-card animar">
                <span class="feature-icono">🚀</span>
                <h3>Delivery Rápido</h3>
                <p>Tu pedido llega caliente a tu puerta. Nos comprometemos con tiempos de entrega ágiles.</p>
            </div>
            <div class="feature-card animar">
                <span class="feature-icono">💰</span>
                <h3>Precios Accesibles</h3>
                <p>Las mejores hamburguesas al mejor precio. Combos y promociones especiales todos los días.</p>
            </div>
        </div>
    </div>
</section>

<!-- Productos Destacados -->
<section class="seccion seccion-oscura" id="productos-destacados" style="margin: 0 -20px; padding-left: 20px; padding-right: 20px;">
    <div class="container">
        <div class="seccion-titulo">
            <h2>Nuestros Favoritos</h2>
            <p>Los platos más pedidos por nuestros clientes</p>
            <div class="titulo-linea"></div>
        </div>
        <div class="productos-grid">
            <?php foreach ($productosDestacados as $producto): ?>
            <div class="producto-card animar">
                <div class="producto-img-container">
                    <div class="producto-img">
                        <?php 
                        $emojis = ['🍔' => 'Hamburguesas', '🍗' => 'Broaster', '🍟' => 'Salchipapas', '🎉' => 'Combos', '🥤' => 'Bebidas'];
                        $emoji = '🍔';
                        foreach ($emojis as $e => $cat) {
                            if ($producto['categoria'] === $cat) { $emoji = $e; break; }
                        }
                        echo $emoji;
                        ?>
                    </div>
                    <span class="producto-categoria"><?php echo limpiar($producto['categoria'] ?? 'General'); ?></span>
                </div>
                <div class="producto-info">
                    <h3 class="producto-nombre"><?php echo limpiar($producto['nombre']); ?></h3>
                    <p class="producto-desc"><?php echo limpiar($producto['descripcion']); ?></p>
                    <div class="producto-footer">
                        <span class="producto-precio"><?php echo formatoPrecio($producto['precio']); ?></span>
                        <a href="<?php echo BASE_URL; ?>/carrito.php?agregar=<?php echo $producto['id']; ?>" class="btn-agregar">
                            🛒 Agregar
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div style="text-align: center; margin-top: 40px;">
            <a href="<?php echo BASE_URL; ?>/menu.php" class="btn btn-secondary btn-lg">
                Ver Todo el Menú →
            </a>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="seccion" style="margin: 0 -20px; padding-left: 20px; padding-right: 20px;">
    <div class="container" style="text-align: center;">
        <div class="animar">
            <h2 style="font-size: 2.5rem; font-weight: 900; margin-bottom: 16px;">¿Listo para ordenar?</h2>
            <p style="color: var(--color-gris); font-size: 1.1rem; max-width: 500px; margin: 0 auto 30px;">
                Regístrate y haz tu primer pedido. ¡Es rápido y fácil!
            </p>
            <div class="hero-buttons" style="justify-content: center;">
                <a href="<?php echo BASE_URL; ?>/registro.php" class="btn btn-primary btn-lg">Crear Cuenta Gratis</a>
                <a href="<?php echo BASE_URL; ?>/login.php" class="btn btn-sm btn-ghost" style="color: var(--color-gris);">Ya tengo cuenta</a>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
