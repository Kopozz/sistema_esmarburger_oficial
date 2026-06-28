<?php
/**
 * ESMAR-BURGER — Gestión de Pedidos
 */
require_once __DIR__ . '/../includes/config.php';
$titulo_pagina = 'Pedidos';
require_once __DIR__ . '/../includes/admin_header.php';

// Cambiar estado del pedido
if (isset($_GET['estado']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $estado = limpiar($_GET['estado']);
    $estados_validos = ['pendiente', 'confirmado', 'preparando', 'en_camino', 'entregado', 'cancelado'];
    
    if (in_array($estado, $estados_validos)) {
        $stmt = $pdo->prepare("UPDATE pedidos SET estado = ? WHERE id = ?");
        $stmt->execute([$estado, $id]);
        setMensaje('Estado del pedido #' . $id . ' actualizado a: ' . ucfirst(str_replace('_', ' ', $estado)), 'success');
    }
    header('Location: ' . BASE_URL . '/admin/pedidos.php');
    exit;
}

// Filtro por estado
$filtro = $_GET['filtro'] ?? '';
if (!empty($filtro)) {
    $stmt = $pdo->prepare("SELECT * FROM vista_pedidos_completos WHERE estado = ? ORDER BY fecha DESC");
    $stmt->execute([$filtro]);
} else {
    $stmt = $pdo->query("SELECT * FROM vista_pedidos_completos ORDER BY fecha DESC");
}
$pedidos = $stmt->fetchAll();
?>

<div class="admin-seccion-header">
    <h1 class="pagina-titulo">📋 Gestión de Pedidos</h1>
    <div class="admin-filtros">
        <a href="<?php echo BASE_URL; ?>/admin/pedidos.php" class="btn btn-sm <?php echo empty($filtro) ? 'btn-primary' : 'btn-ghost'; ?>" style="<?php echo empty($filtro) ? '' : 'color: var(--color-gris);'; ?>">Todos</a>
        <a href="?filtro=pendiente" class="btn btn-sm <?php echo $filtro === 'pendiente' ? 'btn-primary' : 'btn-ghost'; ?>" style="<?php echo $filtro === 'pendiente' ? '' : 'color: var(--color-gris);'; ?>">⏳ Pendientes</a>
        <a href="?filtro=preparando" class="btn btn-sm <?php echo $filtro === 'preparando' ? 'btn-primary' : 'btn-ghost'; ?>" style="<?php echo $filtro === 'preparando' ? '' : 'color: var(--color-gris);'; ?>">👨‍🍳 Preparando</a>
        <a href="?filtro=en_camino" class="btn btn-sm <?php echo $filtro === 'en_camino' ? 'btn-primary' : 'btn-ghost'; ?>" style="<?php echo $filtro === 'en_camino' ? '' : 'color: var(--color-gris);'; ?>">🛵 En Camino</a>
        <a href="?filtro=entregado" class="btn btn-sm <?php echo $filtro === 'entregado' ? 'btn-primary' : 'btn-ghost'; ?>" style="<?php echo $filtro === 'entregado' ? '' : 'color: var(--color-gris);'; ?>">📦 Entregados</a>
    </div>
</div>

<div class="admin-seccion">
    <div class="tabla-container">
        <table class="tabla">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Cliente</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Pago</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pedidos)): ?>
                    <tr><td colspan="8" style="text-align: center; color: var(--color-gris);">No hay pedidos</td></tr>
                <?php else: ?>
                    <?php foreach ($pedidos as $ped): ?>
                    <tr>
                        <td><strong>#<?php echo $ped['pedido_id']; ?></strong></td>
                        <td>
                            <strong><?php echo limpiar($ped['cliente_nombre'] ?? 'Anónimo'); ?></strong>
                            <br><small style="color: var(--color-gris);"><?php echo limpiar($ped['telefono_contacto']); ?></small>
                        </td>
                        <td><?php echo $ped['cantidad_items']; ?> items</td>
                        <td><strong><?php echo formatoPrecio($ped['total']); ?></strong></td>
                        <td><?php echo metodoPago($ped['metodo_pago']); ?></td>
                        <td><?php echo estadoPedido($ped['estado']); ?></td>
                        <td><small><?php echo formatoFecha($ped['fecha']); ?></small></td>
                        <td>
                            <div class="tabla-acciones" style="flex-direction: column; gap: 4px;">
                                <?php if ($ped['estado'] === 'pendiente'): ?>
                                    <a href="?id=<?php echo $ped['pedido_id']; ?>&estado=confirmado" class="btn btn-sm btn-success">✅ Confirmar</a>
                                <?php elseif ($ped['estado'] === 'confirmado'): ?>
                                    <a href="?id=<?php echo $ped['pedido_id']; ?>&estado=preparando" class="btn btn-sm btn-secondary">👨‍🍳 Preparar</a>
                                <?php elseif ($ped['estado'] === 'preparando'): ?>
                                    <a href="?id=<?php echo $ped['pedido_id']; ?>&estado=en_camino" class="btn btn-sm btn-primary">🛵 Enviar</a>
                                <?php elseif ($ped['estado'] === 'en_camino'): ?>
                                    <a href="?id=<?php echo $ped['pedido_id']; ?>&estado=entregado" class="btn btn-sm btn-success">📦 Entregado</a>
                                <?php endif; ?>
                                <?php if ($ped['estado'] !== 'cancelado' && $ped['estado'] !== 'entregado'): ?>
                                    <a href="?id=<?php echo $ped['pedido_id']; ?>&estado=cancelado" class="btn btn-sm btn-danger" data-confirmar="¿Cancelar este pedido?">❌</a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
