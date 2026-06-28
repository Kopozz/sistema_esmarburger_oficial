<?php
/**
 * ESMAR-BURGER — Checkout (Procesar Pedido)
 */
require_once __DIR__ . '/includes/config.php';
protegerPagina();

// Verificar que haya productos en el carrito
if (empty($_SESSION['carrito'])) {
    setMensaje('Tu carrito está vacío. Agrega productos antes de ordenar.', 'warning');
    header('Location: ' . BASE_URL . '/menu.php');
    exit;
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $direccion = limpiar($_POST['direccion'] ?? '');
    $telefono = limpiar($_POST['telefono'] ?? '');
    $metodo_pago = limpiar($_POST['metodo_pago'] ?? 'efectivo');
    $notas = limpiar($_POST['notas'] ?? '');
    
    $errores = [];
    if (empty($direccion)) $errores[] = 'La dirección de entrega es obligatoria.';
    if (empty($telefono)) $errores[] = 'El teléfono de contacto es obligatorio.';
    
    if (empty($errores)) {
        try {
            $pdo->beginTransaction();
            
            // Usar procedimiento almacenado para crear pedido
            $stmt = $pdo->prepare("CALL sp_registrar_pedido(?, ?, ?, ?, ?, @pedido_id)");
            $stmt->execute([
                $_SESSION['usuario_id'],
                $direccion,
                $telefono,
                $metodo_pago,
                $notas
            ]);
            $stmt->closeCursor();
            
            // Obtener ID del pedido creado
            $result = $pdo->query("SELECT @pedido_id AS pedido_id")->fetch();
            $pedido_id = $result['pedido_id'];
            
            // Insertar detalles del pedido
            $stmtDetalle = $pdo->prepare("INSERT INTO pedido_detalles (pedido_id, producto_id, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?)");
            
            foreach ($_SESSION['carrito'] as $item) {
                $subtotal = $item['precio'] * $item['cantidad'];
                $stmtDetalle->execute([
                    $pedido_id,
                    $item['id'],
                    $item['cantidad'],
                    $item['precio'],
                    $subtotal
                ]);
            }
            
            $pdo->commit();
            
            // Vaciar carrito
            $_SESSION['carrito'] = [];
            
            setMensaje('🎉 ¡Pedido #' . $pedido_id . ' registrado exitosamente! Te lo llevaremos pronto.', 'success');
            header('Location: ' . BASE_URL . '/mis_pedidos.php');
            exit;
            
        } catch (Exception $e) {
            $pdo->rollBack();
            setMensaje('Error al procesar tu pedido. Intenta de nuevo.', 'error');
        }
    }
}

// Calcular totales
$subtotal = totalCarrito();
$impuesto = $subtotal * IGV;
$total = $subtotal + $impuesto;

// Obtener datos del usuario
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$_SESSION['usuario_id']]);
$usuario = $stmt->fetch();

$titulo_pagina = 'Checkout';
require_once __DIR__ . '/includes/header.php';
?>

<div class="pagina-header">
    <h1 class="pagina-titulo">💳 Finalizar Pedido</h1>
    <p class="pagina-subtitulo">Completa tus datos para recibir tu pedido</p>
</div>

<?php if (!empty($errores)): ?>
    <div class="alerta alerta-error">
        <span class="alerta-icono">❌</span>
        <span class="alerta-texto">
            <?php echo implode('<br>', $errores); ?>
        </span>
    </div>
<?php endif; ?>

<form method="POST" action="">
    <div class="checkout-grid">
        <!-- Formulario de datos -->
        <div class="checkout-form-card">
            <h3 style="margin-bottom: 24px; font-size: 1.2rem; font-weight: 700;">📍 Datos de Entrega</h3>
            
            <div class="form-grupo">
                <label for="direccion">Dirección de Entrega *</label>
                <input type="text" id="direccion" name="direccion" class="form-control" 
                       value="<?php echo limpiar($usuario['direccion'] ?? ''); ?>"
                       placeholder="Ej: Av. Central 123, Piso 2" required>
            </div>
            
            <div class="form-grupo">
                <label for="telefono">Teléfono de Contacto *</label>
                <input type="tel" id="telefono" name="telefono" class="form-control"
                       value="<?php echo limpiar($usuario['telefono'] ?? ''); ?>"
                       placeholder="Ej: 999888777" required>
            </div>
            
            <div class="form-grupo">
                <label>Método de Pago *</label>
                <div class="metodo-pago-opciones">
                    <div class="metodo-pago-opcion">
                        <input type="radio" id="pago-efectivo" name="metodo_pago" value="efectivo" checked>
                        <label for="pago-efectivo" class="metodo-pago-label">💵 Efectivo</label>
                    </div>
                    <div class="metodo-pago-opcion">
                        <input type="radio" id="pago-yape" name="metodo_pago" value="yape">
                        <label for="pago-yape" class="metodo-pago-label">📱 Yape</label>
                    </div>
                    <div class="metodo-pago-opcion">
                        <input type="radio" id="pago-plin" name="metodo_pago" value="plin">
                        <label for="pago-plin" class="metodo-pago-label">📱 Plin</label>
                    </div>
                    <div class="metodo-pago-opcion">
                        <input type="radio" id="pago-tarjeta" name="metodo_pago" value="tarjeta">
                        <label for="pago-tarjeta" class="metodo-pago-label">💳 Tarjeta</label>
                    </div>
                </div>
            </div>
            
            <div class="form-grupo">
                <label for="notas">Notas adicionales</label>
                <textarea id="notas" name="notas" class="form-control" 
                          placeholder="Ej: Sin cebolla, tocar timbre..."><?php echo limpiar($_POST['notas'] ?? ''); ?></textarea>
            </div>
        </div>
        
        <!-- Resumen del pedido -->
        <div class="checkout-resumen">
            <h3>📋 Resumen del Pedido</h3>
            
            <?php foreach ($_SESSION['carrito'] as $item): ?>
            <div class="checkout-item">
                <span class="checkout-item-nombre">
                    <?php echo $item['cantidad']; ?>x <?php echo limpiar($item['nombre']); ?>
                </span>
                <span class="checkout-item-precio">
                    <?php echo formatoPrecio($item['precio'] * $item['cantidad']); ?>
                </span>
            </div>
            <?php endforeach; ?>
            
            <hr style="margin: 16px 0; border: 1px solid var(--color-gris-claro);">
            
            <div class="resumen-fila">
                <span>Subtotal</span>
                <span><?php echo formatoPrecio($subtotal); ?></span>
            </div>
            <div class="resumen-fila">
                <span>IGV (18%)</span>
                <span><?php echo formatoPrecio($impuesto); ?></span>
            </div>
            <div class="resumen-fila total">
                <span>Total a Pagar</span>
                <span><?php echo formatoPrecio($total); ?></span>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block" style="margin-top: 20px;">
                ✅ Confirmar Pedido
            </button>
            <a href="<?php echo BASE_URL; ?>/carrito.php" class="btn btn-ghost btn-block" style="color: var(--color-gris); margin-top: 8px;">
                ← Volver al Carrito
            </a>
        </div>
    </div>
</form>

<div style="height: 40px;"></div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
