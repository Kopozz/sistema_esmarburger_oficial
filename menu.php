<?php
/**
 * ESMAR-BURGER — Menú / Catálogo de Productos
 */
require_once __DIR__ . '/includes/config.php';

// Obtener categorías
$stmt = $pdo->query("SELECT * FROM categorias WHERE activo = 1 ORDER BY id");
$categorias = $stmt->fetchAll();

// Filtro por categoría
$filtro_categoria = isset($_GET['categoria']) ? (int)$_GET['categoria'] : 0;

if ($filtro_categoria > 0) {
    $stmt = $pdo->prepare("SELECT p.*, c.nombre as categoria FROM productos p LEFT JOIN categorias c ON p.categoria_id = c.id WHERE p.disponible = 1 AND p.categoria_id = ? ORDER BY p.nombre");
    $stmt->execute([$filtro_categoria]);
} else {
    $stmt = $pdo->query("SELECT p.*, c.nombre as categoria FROM productos p LEFT JOIN categorias c ON p.categoria_id = c.id WHERE p.disponible = 1 ORDER BY c.id, p.nombre");
}
$productos = $stmt->fetchAll();

$titulo_pagina = 'Menú';
require_once __DIR__ . '/includes/header.php';
?>

<div class="pagina-header">
    <h1 class="pagina-titulo"><i class="ph-bold ph-book-open-text"></i> Nuestro Menú</h1>
    <p class="pagina-subtitulo">Descubre todos nuestros deliciosos platos</p>
</div>

<!-- Filtros de categoría -->
<div class="filtros-categoria">
    <a href="<?php echo BASE_URL; ?>/menu.php" class="filtro-btn <?php echo $filtro_categoria === 0 ? 'activo' : ''; ?>">
        🍽️ Todos
    </a>
    <?php foreach ($categorias as $cat): ?>
    <a href="<?php echo BASE_URL; ?>/menu.php?categoria=<?php echo $cat['id']; ?>" 
       class="filtro-btn <?php echo $filtro_categoria === (int)$cat['id'] ? 'activo' : ''; ?>">
        <?php echo $cat['icono']; ?> <?php echo limpiar($cat['nombre']); ?>
    </a>
    <?php endforeach; ?>
</div>

<!-- Grid de productos -->
<?php if (empty($productos)): ?>
    <div class="carrito-vacio">
        <span class="carrito-vacio-emoji">🔍</span>
        <h2>No hay productos disponibles</h2>
        <p>Vuelve pronto, estamos actualizando nuestro menú.</p>
    </div>
<?php else: ?>
    <div class="productos-grid">
        <?php 
        $emojis_cat = ['Hamburguesas' => 'ph-hamburger', 'Broaster' => 'ph-bone', 'Salchipapas' => 'ph-french-fries', 'Combos' => 'ph-confetti', 'Bebidas' => 'ph-coffee'];
        foreach ($productos as $producto): 
            $emoji = $emojis_cat[$producto['categoria'] ?? ''] ?? 'ph-hamburger';
        ?>
        <div class="producto-card animar" data-categoria="<?php echo limpiar($producto['categoria'] ?? ''); ?>">
            <div class="producto-img-container">
                <div class="producto-img"><i class="ph-fill <?php echo $emoji; ?>"></i></div>
                <span class="producto-categoria"><?php echo limpiar($producto['categoria'] ?? 'General'); ?></span>
            </div>
            <div class="producto-info">
                <h3 class="producto-nombre"><?php echo limpiar($producto['nombre']); ?></h3>
                <p class="producto-desc"><?php echo limpiar($producto['descripcion']); ?></p>
                <div class="producto-footer">
                    <span class="producto-precio">
                        <?php echo formatoPrecio($producto['precio']); ?>
                    </span>
                    <a href="<?php echo BASE_URL; ?>/carrito.php?agregar=<?php echo $producto['id']; ?>" class="btn-agregar">
                        <i class="ph-bold ph-shopping-cart-simple"></i> Agregar
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div style="height: 40px;"></div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
