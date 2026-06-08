<?php
/**
 * ESMAR BURGER - Proceso de Pago/Finalizar Pedido
 * Avance 2 - Ingeniería Web
 */
require_once 'config.php';

// Validar que esté logueado
requireLogin();

// Validar que el carrito no esté vacío
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: index.php');
    exit;
}

$pdo = getDBConnection();
$userId = $_SESSION['user_id'];

// Obtener datos por defecto del usuario
$stmtUser = $pdo->prepare("SELECT * FROM usuarios WHERE id = :id");
$stmtUser->execute(['id' => $userId]);
$userData = $stmtUser->fetch();

// Obtener los productos en el carrito para calcular el total
$ids = array_keys($_SESSION['cart']);
$placeholders = str_repeat('?,', count($ids) - 1) . '?';
$stmtProd = $pdo->prepare("SELECT * FROM productos WHERE id IN ($placeholders)");
$stmtProd->execute($ids);
$products = $stmtProd->fetchAll();

$total = 0;
$checkoutItems = [];
foreach ($products as $prod) {
    $qty = $_SESSION['cart'][$prod['id']];
    $subtotal = $prod['precio'] * $qty;
    $total += $subtotal;
    $checkoutItems[] = [
        'id' => $prod['id'],
        'precio' => $prod['precio'],
        'cantidad' => $qty
    ];
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $direccion = trim($_POST['direccion']);
    $telefono = trim($_POST['telefono']);
    $metodo_pago = trim($_POST['metodo_pago']);

    if (!empty($direccion) && !empty($telefono) && !empty($metodo_pago)) {
        try {
            $pdo->beginTransaction();

            // Insertar el pedido
            $stmtOrder = $pdo->prepare("INSERT INTO pedidos (usuario_id, total, direccion_entrega, telefono_contacto, metodo_pago, estado) 
                VALUES (:usuario_id, :total, :direccion, :telefono, :metodo_pago, 'pendiente')");
            $stmtOrder->execute([
                'usuario_id' => $userId,
                'total' => $total,
                'direccion' => $direccion,
                'telefono' => $telefono,
                'metodo_pago' => $metodo_pago
            ]);

            $pedidoId = $pdo->lastInsertId();

            // Insertar el detalle del pedido
            $stmtDetail = $pdo->prepare("INSERT INTO pedido_detalles (pedido_id, producto_id, cantidad, precio_unitario) 
                VALUES (:pedido_id, :producto_id, :cantidad, :precio_unitario)");
            
            foreach ($checkoutItems as $item) {
                $stmtDetail->execute([
                    'pedido_id' => $pedidoId,
                    'producto_id' => $item['id'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio']
                ]);
            }

            $pdo->commit();

            // Vaciar el carrito
            unset($_SESSION['cart']);

            // Redirigir a mis_pedidos.php con ID de confirmación
            header("Location: mis_pedidos.php?success=1&id=$pedidoId");
            exit;

        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = 'Hubo un error al procesar tu pedido. Inténtalo de nuevo. Error: ' . $e->getMessage();
        }
    } else {
        $error = 'Por favor, rellene todos los campos.';
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<section style="max-width: 1000px; margin: 3rem auto; padding: 0 2rem;">
    <h2 class="title-decor" style="font-size: 2.2rem; border-bottom: 1px solid var(--border-color); padding-bottom: 1rem; margin-bottom: 2rem;">
        Finalizar <span>Pedido</span>
    </h2>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="cart-layout" style="grid-template-columns: 1.2fr 0.8fr;">
        <!-- Formulario de Entrega -->
        <div class="glass-panel" style="padding: 2.5rem;">
            <h3 style="font-size: 1.5rem; margin-bottom: 1.5rem; color: var(--text-primary);">Datos de Entrega</h3>
            
            <form action="checkout.php" method="POST">
                <div class="form-group">
                    <label class="form-label" for="direccion">Dirección Exacta de Envío</label>
                    <input class="form-input" type="text" id="direccion" name="direccion" required 
                           value="<?php echo htmlspecialchars($userData['direccion'] ?? ''); ?>" 
                           placeholder="Ej. Calle Las Lilas 123, Dpto 402, Urb. San Isidro">
                </div>

                <div class="form-group">
                    <label class="form-label" for="telefono">Teléfono / Celular de Contacto</label>
                    <input class="form-input" type="tel" id="telefono" name="telefono" required 
                           value="<?php echo htmlspecialchars($userData['telefono'] ?? ''); ?>" 
                           placeholder="Ej. 935550240">
                </div>

                <div class="form-group">
                    <label class="form-label" for="metodo_pago">Método de Pago</label>
                    <select class="form-input" id="metodo_pago" name="metodo_pago" required>
                        <option value="efectivo">Efectivo contra entrega</option>
                        <option value="yape_plin">Yape / Plin (Pago digital contra entrega)</option>
                        <option value="tarjeta">Tarjeta Visa/Mastercard (POS físico contra entrega)</option>
                    </select>
                </div>

                <div style="margin-top: 2rem; border-top: 1px solid var(--border-color); padding-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary" style="width: 100%; font-size: 1.1rem; padding: 1rem;">
                        Confirmar Pedido (S/ <?php echo number_format($total, 2); ?>)
                    </button>
                </div>
            </form>
        </div>

        <!-- Resumen Breve -->
        <div class="glass-panel" style="padding: 2rem; height: fit-content;">
            <h3 style="font-size: 1.25rem; margin-bottom: 1.25rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">Resumen</h3>
            
            <div style="display: flex; flex-direction: column; gap: 0.75rem; max-height: 250px; overflow-y: auto;">
                <?php foreach ($products as $prod): ?>
                    <?php $qty = $_SESSION['cart'][$prod['id']]; ?>
                    <div style="display: flex; justify-content: space-between; font-size: 0.9rem;">
                        <span style="color: var(--text-secondary);">
                            <?php echo htmlspecialchars($prod['nombre']); ?> (x<?php echo $qty; ?>)
                        </span>
                        <span style="font-weight: 600; color: var(--text-primary);">
                            S/ <?php echo number_format($prod['precio'] * $qty, 2); ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>

            <div style="border-top: 1px solid var(--border-color); margin-top: 1.5rem; padding-top: 1rem; display: flex; justify-content: space-between; font-weight: 800; font-size: 1.25rem;">
                <span>Total a Pagar</span>
                <span style="color: var(--primary);">S/ <?php echo number_format($total, 2); ?></span>
            </div>

            <div style="margin-top: 1rem; font-size: 0.8rem; color: var(--text-secondary); text-align: center;">
                <p>Tu pedido será enviado de forma inmediata una vez sea aceptado por nuestro equipo de soporte.</p>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
