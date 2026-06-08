<?php
/**
 * ESMAR BURGER - Mis Pedidos / Seguimiento
 * Avance 2 - Ingeniería Web
 */
require_once 'config.php';
requireLogin();

require_once __DIR__ . '/../includes/header.php';

$pdo = getDBConnection();
$userId = $_SESSION['user_id'];

// Obtener todos los pedidos del usuario
$stmtOrders = $pdo->prepare("SELECT * FROM pedidos WHERE usuario_id = :user_id ORDER BY fecha DESC");
$stmtOrders->execute(['user_id' => $userId]);
$pedidos = $stmtOrders->fetchAll();

// Si se seleccionó un pedido específico o se muestra el último
$selectedPedido = null;
$selectedPedidoId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($selectedPedidoId > 0) {
    // Verificar que el pedido pertenezca a este usuario
    $stmtSel = $pdo->prepare("SELECT * FROM pedidos WHERE id = :id AND usuario_id = :user_id");
    $stmtSel->execute(['id' => $selectedPedidoId, 'user_id' => $userId]);
    $selectedPedido = $stmtSel->fetch();
} elseif (!empty($pedidos)) {
    // Cargar el más reciente por defecto
    $selectedPedido = $pedidos[0];
}

$detalles = [];
if ($selectedPedido) {
    // Obtener detalles del pedido seleccionado
    $stmtDet = $pdo->prepare("
        SELECT pd.*, p.nombre, p.descripcion 
        FROM pedido_detalles pd 
        JOIN productos p ON pd.producto_id = p.id 
        WHERE pd.pedido_id = :pedido_id
    ");
    $stmtDet->execute(['pedido_id' => $selectedPedido['id']]);
    $detalles = $stmtDet->fetchAll();
}
?>

<section style="max-width: 1200px; margin: 3rem auto; padding: 0 2rem;">
    <?php if (isset($_GET['success']) && isset($_GET['id'])): ?>
        <div class="alert alert-success" style="text-align: center; margin-bottom: 2rem;">
            🎉 ¡Pedido <strong>#<?php echo (int)$_GET['id']; ?></strong> registrado con éxito! Tu comida está en camino.
        </div>
    <?php endif; ?>

    <div class="admin-layout" style="grid-template-columns: 320px 1fr;">
        <!-- Lista de pedidos del usuario -->
        <div class="glass-panel" style="padding: 1.5rem; height: fit-content; max-height: 600px; overflow-y: auto;">
            <h3 style="font-size: 1.25rem; margin-bottom: 1.25rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem; color: var(--text-primary);">
                Historial de Pedidos
            </h3>
            
            <?php if (empty($pedidos)): ?>
                <p style="color: var(--text-secondary); font-size: 0.9rem; text-align: center; padding: 2rem 0;">
                    No has realizado pedidos aún.
                </p>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <?php foreach ($pedidos as $ped): ?>
                        <?php 
                            $isActive = $selectedPedido && ($selectedPedido['id'] == $ped['id']);
                            $borderStyle = $isActive ? 'border-color: var(--primary); background: rgba(255,107,0,0.05);' : '';
                        ?>
                        <a href="mis_pedidos.php?id=<?php echo $ped['id']; ?>" class="glass-panel" style="display: block; padding: 1rem; transition: var(--transition); <?php echo $borderStyle; ?>">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                                <span style="font-weight: 700; font-size: 0.95rem; color: var(--text-primary);">
                                    Pedido #<?php echo $ped['id']; ?>
                                </span>
                                <span class="status-badge <?php echo $ped['estado']; ?>" style="font-size: 0.7rem; padding: 0.1rem 0.5rem;">
                                    <?php echo htmlspecialchars($ped['estado']); ?>
                                </span>
                            </div>
                            <div style="display: flex; justify-content: space-between; font-size: 0.8rem; color: var(--text-secondary);">
                                <span><?php echo date('d M, g:i a', strtotime($ped['fecha'])); ?></span>
                                <span style="font-weight: 700; color: var(--text-primary);">S/ <?php echo number_format($ped['total'], 2); ?></span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Panel de seguimiento en vivo -->
        <div>
            <?php if (!$selectedPedido): ?>
                <div class="glass-panel" style="padding: 4rem; text-align: center;">
                    <h3 style="color: var(--text-secondary);">Selecciona un pedido para rastrear</h3>
                </div>
            <?php else: ?>
                <!-- Visual tracking card -->
                <div class="glass-panel tracking-card" style="margin-top: 0; margin-bottom: 2rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); padding-bottom: 1.5rem;">
                        <div>
                            <span style="color: var(--secondary); font-size: 0.9rem; text-transform: uppercase; font-weight: 700; letter-spacing: 0.05em;">Seguimiento en Vivo</span>
                            <h2 style="font-size: 1.8rem; color: var(--text-primary);">Pedido #<?php echo $selectedPedido['id']; ?></h2>
                        </div>
                        <div style="text-align: right;">
                            <span style="font-size: 0.85rem; color: var(--text-secondary);">Método de pago: <strong><?php echo strtoupper(str_replace('_', ' ', $selectedPedido['metodo_pago'])); ?></strong></span>
                            <div style="font-size: 1.5rem; font-weight: 800; color: var(--primary); margin-top: 0.25rem;">
                                S/ <?php echo number_format($selectedPedido['total'], 2); ?>
                            </div>
                        </div>
                    </div>

                    <?php
                        $estado = $selectedPedido['estado'];
                        $progressWidth = '10%';
                        $step1 = 'completed';
                        $step2 = '';
                        $step3 = '';
                        $step4 = '';

                        if ($estado === 'preparando') {
                            $progressWidth = '40%';
                            $step2 = 'active';
                        } elseif ($estado === 'en camino') {
                            $progressWidth = '70%';
                            $step2 = 'completed';
                            $step3 = 'active';
                        } elseif ($estado === 'entregado') {
                            $progressWidth = '100%';
                            $step2 = 'completed';
                            $step3 = 'completed';
                            $step4 = 'completed';
                        } else {
                            // pendiente
                            $step1 = 'active';
                        }
                    ?>

                    <div class="tracking-steps">
                        <div class="tracking-progress-line" style="width: <?php echo $progressWidth; ?>;"></div>
                        
                        <div class="step <?php echo $step1; ?>">
                            <div class="step-icon">1</div>
                            <span class="step-label">Recibido</span>
                        </div>
                        <div class="step <?php echo $step2; ?>">
                            <div class="step-icon">2</div>
                            <span class="step-label">Preparando</span>
                        </div>
                        <div class="step <?php echo $step3; ?>">
                            <div class="step-icon">3</div>
                            <span class="step-label">En Camino</span>
                        </div>
                        <div class="step <?php echo $step4; ?>">
                            <div class="step-icon">✓</div>
                            <span class="step-label">Entregado</span>
                        </div>
                    </div>
                </div>

                <!-- Detalle de productos de ese pedido -->
                <div class="glass-panel" style="padding: 2rem;">
                    <h3 style="font-size: 1.25rem; margin-bottom: 1.25rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem; color: var(--text-primary);">
                        Detalle de la Orden
                    </h3>
                    
                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        <?php foreach ($detalles as $det): ?>
                            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.95rem;">
                                <div>
                                    <span style="font-weight: 700; color: var(--text-primary);"><?php echo htmlspecialchars($det['nombre']); ?></span>
                                    <span style="color: var(--text-secondary); font-size: 0.85rem; margin-left: 0.5rem;">x<?php echo $det['cantidad']; ?></span>
                                    <p style="color: var(--text-secondary); font-size: 0.85rem; margin-top: 0.1rem;"><?php echo htmlspecialchars($det['descripcion']); ?></p>
                                </div>
                                <span style="font-weight: 700; color: var(--text-primary);">
                                    S/ <?php echo number_format($det['precio_unitario'] * $det['cantidad'], 2); ?>
                                </span>
                            </div>
                            <div style="border-bottom: 1px solid var(--border-color); opacity: 0.5;"></div>
                        <?php endforeach; ?>
                    </div>

                    <div style="margin-top: 1.5rem; font-size: 0.9rem; color: var(--text-secondary);">
                        <p>📍 <strong>Dirección de entrega:</strong> <?php echo htmlspecialchars($selectedPedido['direccion_entrega']); ?></p>
                        <p style="margin-top: 0.5rem;">📞 <strong>Contacto:</strong> <?php echo htmlspecialchars($selectedPedido['telefono_contacto']); ?></p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Auto-recarga cada 10 segundos si el pedido está activo (no entregado) para ver cambios en vivo -->
<?php if ($selectedPedido && $selectedPedido['estado'] !== 'entregado'): ?>
<script>
setTimeout(function() {
    window.location.reload();
}, 10000);
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
