<?php
/**
 * ESMAR BURGER - Carrito de Compras
 * Avance 2 - Ingeniería Web
 */
require_once 'config.php';
require_once __DIR__ . '/../includes/header.php';

$pdo = getDBConnection();
$cartItems = [];
$total = 0;

if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    // Generar marcadores para la consulta IN (?, ?, ...)
    $ids = array_keys($_SESSION['cart']);
    $placeholders = str_repeat('?,', count($ids) - 1) . '?';
    
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $products = $stmt->fetchAll();

    foreach ($products as $prod) {
        $qty = $_SESSION['cart'][$prod['id']];
        $subtotal = $prod['precio'] * $qty;
        $total += $subtotal;
        
        $cartItems[] = [
            'id' => $prod['id'],
            'nombre' => $prod['nombre'],
            'descripcion' => $prod['descripcion'],
            'precio' => $prod['precio'],
            'categoria' => $prod['categoria'],
            'cantidad' => $qty,
            'subtotal' => $subtotal
        ];
    }
}
?>

<section style="max-width: 1200px; margin: 3rem auto; padding: 0 2rem;">
    <h2 class="title-decor" style="font-size: 2.2rem; border-bottom: 1px solid var(--border-color); padding-bottom: 1rem; margin-bottom: 2rem;">
        Tu <span>Carrito</span>
    </h2>

    <?php if (empty($cartItems)): ?>
        <div class="glass-panel" style="padding: 4rem; text-align: center;">
            <svg width="80" height="80" fill="none" stroke="var(--text-secondary)" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="margin-bottom: 1.5rem;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
            </svg>
            <h3 style="font-size: 1.5rem; margin-bottom: 0.5rem; color: var(--text-primary);">Tu carrito está vacío</h3>
            <p style="color: var(--text-secondary); margin-bottom: 2rem;">Añade deliciosas hamburguesas y combos de nuestra carta para comenzar.</p>
            <a href="index.php" class="btn btn-primary">Ver la Carta</a>
        </div>
    <?php else: ?>
        <div class="cart-layout">
            <!-- Lista de items -->
            <div class="cart-items">
                <?php foreach ($cartItems as $item): ?>
                    <div class="cart-item glass-panel" id="cart-item-row-<?php echo $item['id']; ?>">
                        <div class="cart-item-info">
                            <h3 class="cart-item-title"><?php echo htmlspecialchars($item['nombre']); ?></h3>
                            <p class="cart-item-desc"><?php echo htmlspecialchars($item['descripcion']); ?></p>
                            <span style="font-size: 0.9rem; color: var(--primary); font-weight: 700;">
                                S/ <?php echo number_format($item['precio'], 2); ?> c/u
                            </span>
                        </div>

                        <!-- Controles de Cantidad -->
                        <div class="cart-item-qty">
                            <button class="qty-btn minus-btn" data-id="<?php echo $item['id']; ?>">−</button>
                            <span id="qty-label-<?php echo $item['id']; ?>" style="font-weight: 700; width: 20px; text-align: center;">
                                <?php echo $item['cantidad']; ?>
                            </span>
                            <button class="qty-btn plus-btn" data-id="<?php echo $item['id']; ?>">+</button>
                        </div>

                        <!-- Subtotal individual -->
                        <div style="text-align: right; min-width: 100px;">
                            <span id="subtotal-label-<?php echo $item['id']; ?>" style="font-weight: 800; font-size: 1.15rem; color: var(--text-primary);">
                                S/ <?php echo number_format($item['subtotal'], 2); ?>
                            </span>
                        </div>

                        <!-- Botón Eliminar -->
                        <button class="qty-btn delete-btn" data-id="<?php echo $item['id']; ?>" style="color: #ef4444; margin-left: 0.5rem;" title="Eliminar del carrito">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Resumen de Pedido -->
            <div class="cart-summary glass-panel">
                <h3 class="summary-title">Resumen del Pedido</h3>
                
                <div class="summary-row">
                    <span>Productos (<?php echo count($cartItems); ?>)</span>
                    <span id="summary-subtotal">S/ <?php echo number_format($total, 2); ?></span>
                </div>
                
                <div class="summary-row">
                    <span>Costo de Envío</span>
                    <span style="color: var(--success); font-weight: 700;">GRATIS</span>
                </div>

                <div class="summary-row" style="margin-top: 1rem; font-size: 0.85rem; background: rgba(0, 186, 242, 0.05); padding: 0.75rem; border-radius: var(--radius-sm); border: 1px dashed var(--secondary);">
                    <span style="color: var(--secondary); font-weight: 700;">🔥 Oferta de Avance:</span>
                    <span style="color: var(--secondary);">¡Todos los platos incluyen bebida!</span>
                </div>

                <div class="summary-row total">
                    <span>Total</span>
                    <span id="summary-total">S/ <?php echo number_format($total, 2); ?></span>
                </div>

                <a href="checkout.php" class="btn btn-primary" style="width: 100%; margin-top: 1.5rem; text-align: center;">
                    Proceder al Pago
                </a>
                
                <a href="index.php" class="btn btn-secondary" style="width: 100%; margin-top: 0.75rem; text-align: center;">
                    Seguir Comprando
                </a>
            </div>
        </div>
    <?php endif; ?>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const updateCartQty = (id, qtyChange) => {
        const qtyLabel = document.getElementById(`qty-label-${id}`);
        const currentQty = parseInt(qtyLabel.textContent);
        const newQty = currentQty + qtyChange;
        
        if (newQty < 1) return;

        fetch('cart_action.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=update&id=${id}&qty=${newQty}`
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.location.reload(); // Forma más simple para recalcular todos los subtotales y cabecera en el Avance 2
            }
        });
    };

    // Agregar eventos a botones de cantidad
    document.querySelectorAll('.minus-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            updateCartQty(this.getAttribute('data-id'), -1);
        });
    });

    document.querySelectorAll('.plus-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            updateCartQty(this.getAttribute('data-id'), 1);
        });
    });

    // Eliminar producto
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            fetch('cart_action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=remove&id=${id}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                }
            });
        });
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
