<?php
/**
 * ESMAR-BURGER — Carrito de Compras
 */
require_once __DIR__ . '/includes/config.php';

// Inicializar carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// ===== ACCIONES DEL CARRITO =====

// Agregar producto
if (isset($_GET['agregar'])) {
    $producto_id = (int)$_GET['agregar'];
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ? AND disponible = 1");
    $stmt->execute([$producto_id]);
    $producto = $stmt->fetch();
    
    if ($producto) {
        if (isset($_SESSION['carrito'][$producto_id])) {
            $_SESSION['carrito'][$producto_id]['cantidad']++;
        } else {
            $_SESSION['carrito'][$producto_id] = [
                'id' => $producto['id'],
                'nombre' => $producto['nombre'],
                'precio' => $producto['precio'],
                'cantidad' => 1
            ];
        }
        setMensaje('✅ ' . $producto['nombre'] . ' agregado al carrito.', 'success');
    }
    header('Location: ' . BASE_URL . '/carrito.php');
    exit;
}

// Aumentar cantidad
if (isset($_GET['sumar'])) {
    $id = (int)$_GET['sumar'];
    if (isset($_SESSION['carrito'][$id])) {
        $_SESSION['carrito'][$id]['cantidad']++;
    }
    header('Location: ' . BASE_URL . '/carrito.php');
    exit;
}

// Disminuir cantidad
if (isset($_GET['restar'])) {
    $id = (int)$_GET['restar'];
    if (isset($_SESSION['carrito'][$id])) {
        $_SESSION['carrito'][$id]['cantidad']--;
        if ($_SESSION['carrito'][$id]['cantidad'] <= 0) {
            unset($_SESSION['carrito'][$id]);
        }
    }
    header('Location: ' . BASE_URL . '/carrito.php');
    exit;
}

// Eliminar producto
if (isset($_GET['eliminar'])) {
    $id = (int)$_GET['eliminar'];
    unset($_SESSION['carrito'][$id]);
    setMensaje('Producto eliminado del carrito.', 'info');
    header('Location: ' . BASE_URL . '/carrito.php');
    exit;
}

// Vaciar carrito
if (isset($_GET['vaciar'])) {
    $_SESSION['carrito'] = [];
    setMensaje('Carrito vaciado.', 'info');
    header('Location: ' . BASE_URL . '/carrito.php');
    exit;
}

$titulo_pagina = 'Carrito';
require_once __DIR__ . '/includes/header.php';
?>

<div class="pagina-header">
    <h1 class="pagina-titulo">🛒 Mi Carrito</h1>
    <p class="pagina-subtitulo">Revisa tus productos antes de ordenar</p>
</div>

<?php if (empty($_SESSION['carrito'])): ?>
    <div class="carrito-vacio">
        <span class="carrito-vacio-emoji">🛒</span>
        <h2>Tu carrito está vacío</h2>
        <p>¡Agrega tus hamburguesas favoritas y haz tu pedido!</p>
        <a href="<?php echo BASE_URL; ?>/menu.php" class="btn btn-primary btn-lg">Ver Menú</a>
    </div>
<?php else: ?>
    <div style="overflow-x: auto;">
        <table class="carrito-tabla">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Precio</th>
                    <th>Cantidad</th>
                    <th>Subtotal</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $subtotal = 0;
                foreach ($_SESSION['carrito'] as $item): 
                    $itemSubtotal = $item['precio'] * $item['cantidad'];
                    $subtotal += $itemSubtotal;
                ?>
                <tr>
                    <td>
                        <div class="carrito-producto">
                            <span class="carrito-producto-emoji">🍔</span>
                            <span class="carrito-producto-nombre"><?php echo limpiar($item['nombre']); ?></span>
                        </div>
                    </td>
                    <td><?php echo formatoPrecio($item['precio']); ?></td>
                    <td>
                        <div class="cantidad-control">
                            <a href="<?php echo BASE_URL; ?>/carrito.php?restar=<?php echo $item['id']; ?>" class="cantidad-btn">−</a>
                            <span class="cantidad-numero"><?php echo $item['cantidad']; ?></span>
                            <a href="<?php echo BASE_URL; ?>/carrito.php?sumar=<?php echo $item['id']; ?>" class="cantidad-btn">+</a>
                        </div>
                    </td>
                    <td><strong><?php echo formatoPrecio($itemSubtotal); ?></strong></td>
                    <td>
                        <a href="<?php echo BASE_URL; ?>/carrito.php?eliminar=<?php echo $item['id']; ?>" 
                           class="btn btn-sm btn-danger" 
                           data-confirmar="¿Eliminar este producto del carrito?">
                            🗑️
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php 
    $impuesto = $subtotal * IGV;
    $total = $subtotal + $impuesto;
    ?>
    <div class="carrito-resumen">
        <div class="resumen-fila">
            <span>Subtotal</span>
            <span><?php echo formatoPrecio($subtotal); ?></span>
        </div>
        <div class="resumen-fila">
            <span>IGV (18%)</span>
            <span><?php echo formatoPrecio($impuesto); ?></span>
        </div>
        <div class="resumen-fila total">
            <span>Total</span>
            <span><?php echo formatoPrecio($total); ?></span>
        </div>
        <div style="margin-top: 20px; display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="<?php echo BASE_URL; ?>/checkout.php" class="btn btn-primary btn-block">Proceder al Pago →</a>
            <a href="<?php echo BASE_URL; ?>/carrito.php?vaciar=1" class="btn btn-sm btn-ghost" style="color: var(--color-gris);" data-confirmar="¿Vaciar todo el carrito?">
                🗑️ Vaciar Carrito
            </a>
        </div>
    </div>
<?php endif; ?>

<div style="height: 40px;"></div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
