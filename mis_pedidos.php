<?php
/**
 * ESMAR-BURGER — Mis Pedidos (Historial del Cliente)
 */
require_once __DIR__ . '/includes/config.php';
protegerPagina();

// Obtener pedidos del usuario
$stmt = $pdo->prepare("SELECT * FROM pedidos WHERE usuario_id = ? ORDER BY fecha DESC");
$stmt->execute([$_SESSION['usuario_id']]);
$pedidos = $stmt->fetchAll();

$titulo_pagina = 'Mis Pedidos';
require_once __DIR__ . '/includes/header.php';
?>

<div class="pagina-header">
    <h1 class="pagina-titulo">📋 Mis Pedidos</h1>
    <p class="pagina-subtitulo">Historial y seguimiento de tus pedidos</p>
</div>

<?php if (empty($pedidos)): ?>
    <div class="carrito-vacio">
        <span class="carrito-vacio-emoji">📦</span>
        <h2>No tienes pedidos aún</h2>
        <p>¡Haz tu primer pedido y disfruta de nuestras hamburguesas!</p>
        <a href="<?php echo BASE_URL; ?>/menu.php" class="btn btn-primary btn-lg">Ver Menú</a>
    </div>
<?php else: ?>
    <div class="pedidos-lista">
        <?php foreach ($pedidos as $pedido): 
            // Obtener detalles del pedido
            $stmtDet = $pdo->prepare("SELECT pd.*, p.nombre as producto_nombre FROM pedido_detalles pd LEFT JOIN productos p ON pd.producto_id = p.id WHERE pd.pedido_id = ?");
            $stmtDet->execute([$pedido['id']]);
            $detalles = $stmtDet->fetchAll();
            
            // Determinar progreso
            $estados_orden = ['pendiente', 'confirmado', 'preparando', 'en_camino', 'entregado'];
            $estado_idx = array_search($pedido['estado'], $estados_orden);
            if ($estado_idx === false) $estado_idx = -1;
        ?>
        <div class="pedido-card">
            <div class="pedido-header">
                <div>
                    <div class="pedido-id">Pedido #<?php echo $pedido['id']; ?></div>
                    <div class="pedido-fecha"><?php echo formatoFecha($pedido['fecha']); ?></div>
                </div>
                <div style="display: flex; align-items: center; gap: 16px;">
                    <?php echo estadoPedido($pedido['estado']); ?>
                    <span class="pedido-total-header"><?php echo formatoPrecio($pedido['total']); ?></span>
                </div>
            </div>
            <div class="pedido-body">
                <!-- Barra de progreso -->
                <?php if ($pedido['estado'] !== 'cancelado'): ?>
                <div class="progreso-pedido">
                    <?php 
                    $progreso_width = $estado_idx >= 0 ? ($estado_idx / 4) * 100 : 0;
                    ?>
                    <div class="progreso-barra" style="width: <?php echo $progreso_width; ?>%;"></div>
                    
                    <?php 
                    $pasos = [
                        ['icono' => '⏳', 'texto' => 'Pendiente'],
                        ['icono' => '✅', 'texto' => 'Confirmado'],
                        ['icono' => '👨‍🍳', 'texto' => 'Preparando'],
                        ['icono' => '🛵', 'texto' => 'En Camino'],
                        ['icono' => '📦', 'texto' => 'Entregado']
                    ];
                    foreach ($pasos as $idx => $paso): 
                        $clase = '';
                        if ($idx < $estado_idx) $clase = 'completado';
                        elseif ($idx === $estado_idx) $clase = 'actual';
                    ?>
                    <div class="progreso-paso <?php echo $clase; ?>">
                        <div class="progreso-icono"><?php echo $paso['icono']; ?></div>
                        <div class="progreso-texto"><?php echo $paso['texto']; ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <!-- Info del pedido -->
                <div class="pedido-info-grid">
                    <div class="pedido-info-item">
                        <span class="pedido-info-label">Dirección</span>
                        <span class="pedido-info-valor"><?php echo limpiar($pedido['direccion_entrega']); ?></span>
                    </div>
                    <div class="pedido-info-item">
                        <span class="pedido-info-label">Teléfono</span>
                        <span class="pedido-info-valor"><?php echo limpiar($pedido['telefono_contacto']); ?></span>
                    </div>
                    <div class="pedido-info-item">
                        <span class="pedido-info-label">Método de Pago</span>
                        <span class="pedido-info-valor"><?php echo metodoPago($pedido['metodo_pago']); ?></span>
                    </div>
                </div>
                
                <!-- Detalle de productos -->
                <table class="tabla" style="margin-top: 16px;">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cant.</th>
                            <th>Precio</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($detalles as $det): ?>
                        <tr>
                            <td><?php echo limpiar($det['producto_nombre'] ?? 'Producto eliminado'); ?></td>
                            <td><?php echo $det['cantidad']; ?></td>
                            <td><?php echo formatoPrecio($det['precio_unitario']); ?></td>
                            <td><strong><?php echo formatoPrecio($det['subtotal']); ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div class="carrito-resumen" style="margin-top: 16px;">
                    <div class="resumen-fila">
                        <span>Subtotal</span>
                        <span><?php echo formatoPrecio($pedido['subtotal']); ?></span>
                    </div>
                    <div class="resumen-fila">
                        <span>IGV</span>
                        <span><?php echo formatoPrecio($pedido['impuesto']); ?></span>
                    </div>
                    <div class="resumen-fila total">
                        <span>Total</span>
                        <span><?php echo formatoPrecio($pedido['total']); ?></span>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div style="height: 40px;"></div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
