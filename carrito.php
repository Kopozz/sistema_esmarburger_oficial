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
                'cantidad' => 1,
                'imagen' => $producto['imagen']
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
    <h1 class="pagina-titulo"><i class="ph-bold ph-shopping-cart"></i> Mi Carrito</h1>
    <p class="pagina-subtitulo">Revisa tus productos antes de ordenar</p>
</div>

<?php if (empty($_SESSION['carrito'])): ?>
    <div class="carrito-vacio">
        <span class="carrito-vacio-emoji"><i class="ph-fill ph-shopping-cart"></i></span>
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
                            <div class="carrito-producto-img-peq">
                                <?php if (!empty($item['imagen']) && $item['imagen'] !== 'default.jpg'): ?>
                                    <img src="<?php echo BASE_URL; ?>/img/productos/<?php echo limpiar($item['imagen']); ?>" alt="<?php echo limpiar($item['nombre']); ?>" class="foto-producto-peq">
                                <?php else: ?>
                                    <div class="no-imagen-peq"><span>NO IMG</span></div>
                                <?php endif; ?>
                            </div>
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

<hr style="border: 0; border-top: 1px solid var(--color-borde); margin: 50px 0;">

<div class="pagina-header">
    <h2 class="pagina-titulo"><i class="ph-bold ph-plus-circle"></i> Agregar más productos</h2>
    <p class="pagina-subtitulo">¿Te antojaste de algo más?</p>
</div>

<?php
// Obtener productos para el menú rápido
$stmt_menu = $pdo->query("SELECT p.*, c.nombre as categoria FROM productos p LEFT JOIN categorias c ON p.categoria_id = c.id WHERE p.disponible = 1 ORDER BY c.id, p.nombre");
$productos_menu = $stmt_menu->fetchAll();
?>

<div class="productos-grid">
    <?php foreach ($productos_menu as $producto): ?>
    <div class="producto-card animar" data-categoria="<?php echo limpiar($producto['categoria'] ?? ''); ?>">
        <div class="producto-img-container">
            <div class="producto-img">
                <?php if (!empty($producto['imagen']) && $producto['imagen'] !== 'default.jpg'): ?>
                    <img src="<?php echo BASE_URL; ?>/img/productos/<?php echo limpiar($producto['imagen']); ?>" alt="<?php echo limpiar($producto['nombre']); ?>" class="foto-producto">
                <?php else: ?>
                    <div class="no-imagen"><span>NO IMAGEN</span></div>
                <?php endif; ?>
            </div>
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

<div style="height: 40px;"></div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
