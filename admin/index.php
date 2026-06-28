<?php
/**
 * ESMAR-BURGER — Dashboard Administrativo
 */
require_once __DIR__ . '/../includes/config.php';
$titulo_pagina = 'Dashboard';
require_once __DIR__ . '/../includes/admin_header.php';

// Estadísticas
$ventasHoy = $pdo->query("SELECT COALESCE(SUM(total), 0) as total, COUNT(*) as cantidad FROM pedidos WHERE DATE(fecha) = CURDATE() AND estado != 'cancelado'")->fetch();
$pedidosPendientes = $pdo->query("SELECT COUNT(*) as total FROM pedidos WHERE estado IN ('pendiente', 'confirmado', 'preparando')")->fetch();
$totalProductos = $pdo->query("SELECT COUNT(*) as total FROM productos WHERE disponible = 1")->fetch();
$stockBajo = $pdo->query("SELECT COUNT(*) as total FROM insumos WHERE stock_actual < stock_minimo")->fetch();

// Últimos pedidos
$ultimosPedidos = $pdo->query("SELECT p.*, u.nombre as cliente FROM pedidos p LEFT JOIN usuarios u ON p.usuario_id = u.id ORDER BY p.fecha DESC LIMIT 5")->fetchAll();

// Ventas últimos 7 días para gráfico
$ventasSemana = $pdo->query("SELECT DATE(fecha) as dia, COALESCE(SUM(total), 0) as total FROM pedidos WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND estado != 'cancelado' GROUP BY DATE(fecha) ORDER BY dia")->fetchAll();

$maxVenta = 1;
foreach ($ventasSemana as $v) {
    if ($v['total'] > $maxVenta) $maxVenta = $v['total'];
}
?>

<h1 class="pagina-titulo" style="margin-bottom: 30px;">📊 Dashboard</h1>

<!-- Cards de estadísticas -->
<div class="dashboard-grid">
    <div class="dash-card card-ventas">
        <div class="dash-card-header">
            <span class="dash-card-titulo">Ventas Hoy</span>
            <span class="dash-card-icono">💰</span>
        </div>
        <div class="dash-card-valor"><?php echo formatoPrecio($ventasHoy['total']); ?></div>
        <span class="dash-card-cambio cambio-positivo"><?php echo $ventasHoy['cantidad']; ?> pedido(s)</span>
    </div>
    
    <div class="dash-card card-pedidos">
        <div class="dash-card-header">
            <span class="dash-card-titulo">Pedidos Pendientes</span>
            <span class="dash-card-icono">📋</span>
        </div>
        <div class="dash-card-valor"><?php echo $pedidosPendientes['total']; ?></div>
        <span class="dash-card-cambio">En proceso</span>
    </div>
    
    <div class="dash-card card-productos">
        <div class="dash-card-header">
            <span class="dash-card-titulo">Productos Activos</span>
            <span class="dash-card-icono">🍔</span>
        </div>
        <div class="dash-card-valor"><?php echo $totalProductos['total']; ?></div>
        <span class="dash-card-cambio cambio-positivo">En menú</span>
    </div>
    
    <div class="dash-card card-stock">
        <div class="dash-card-header">
            <span class="dash-card-titulo">Alertas Stock</span>
            <span class="dash-card-icono">⚠️</span>
        </div>
        <div class="dash-card-valor"><?php echo $stockBajo['total']; ?></div>
        <span class="dash-card-cambio <?php echo $stockBajo['total'] > 0 ? 'cambio-negativo' : 'cambio-positivo'; ?>">
            <?php echo $stockBajo['total'] > 0 ? 'Requiere atención' : 'Todo bien'; ?>
        </span>
    </div>
</div>

<!-- Gráfico de ventas y Últimos pedidos -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
    <!-- Gráfico de ventas -->
    <div class="admin-seccion">
        <div class="admin-seccion-header">
            <h3 class="admin-seccion-titulo"><span>📈</span> Ventas Últimos 7 Días</h3>
        </div>
        <div class="grafico-barras">
            <?php 
            if (empty($ventasSemana)):
            ?>
                <p style="text-align: center; color: var(--color-gris); width: 100%; align-self: center;">Sin datos de ventas esta semana</p>
            <?php else: ?>
                <?php foreach ($ventasSemana as $venta): 
                    $porcentaje = ($venta['total'] / $maxVenta) * 100;
                    $dia = date('D', strtotime($venta['dia']));
                    $dias_es = ['Mon' => 'Lun', 'Tue' => 'Mar', 'Wed' => 'Mié', 'Thu' => 'Jue', 'Fri' => 'Vie', 'Sat' => 'Sáb', 'Sun' => 'Dom'];
                ?>
                <div class="barra-item">
                    <span class="barra-valor"><?php echo formatoPrecio($venta['total']); ?></span>
                    <div class="barra" data-height="<?php echo round($porcentaje); ?>" style="height: 0;"></div>
                    <span class="barra-label"><?php echo $dias_es[$dia] ?? $dia; ?></span>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Últimos pedidos -->
    <div class="admin-seccion">
        <div class="admin-seccion-header">
            <h3 class="admin-seccion-titulo"><span>📋</span> Últimos Pedidos</h3>
            <a href="<?php echo BASE_URL; ?>/admin/pedidos.php" class="btn btn-sm btn-primary">Ver Todos</a>
        </div>
        <div class="tabla-container">
            <table class="tabla">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Cliente</th>
                        <th>Total</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($ultimosPedidos)): ?>
                        <tr><td colspan="4" style="text-align: center; color: var(--color-gris);">Sin pedidos</td></tr>
                    <?php else: ?>
                        <?php foreach ($ultimosPedidos as $ped): ?>
                        <tr>
                            <td><strong>#<?php echo $ped['id']; ?></strong></td>
                            <td><?php echo limpiar($ped['cliente'] ?? 'Anónimo'); ?></td>
                            <td><?php echo formatoPrecio($ped['total']); ?></td>
                            <td><?php echo estadoPedido($ped['estado']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Accesos Rápidos -->
<div class="admin-seccion" style="margin-top: 30px;">
    <div class="admin-seccion-header">
        <h3 class="admin-seccion-titulo"><span>⚡</span> Acciones Rápidas</h3>
    </div>
    <div class="acciones-rapidas">
        <a href="<?php echo BASE_URL; ?>/admin/pedidos.php" class="accion-card">
            <span class="accion-icono">📋</span>
            <span class="accion-texto">Gestionar Pedidos</span>
        </a>
        <a href="<?php echo BASE_URL; ?>/admin/productos.php" class="accion-card">
            <span class="accion-icono">🍔</span>
            <span class="accion-texto">Editar Menú</span>
        </a>
        <a href="<?php echo BASE_URL; ?>/admin/compras.php" class="accion-card">
            <span class="accion-icono">🛒</span>
            <span class="accion-texto">Registrar Compra</span>
        </a>
        <a href="<?php echo BASE_URL; ?>/admin/inventario.php" class="accion-card">
            <span class="accion-icono">📦</span>
            <span class="accion-texto">Ver Inventario</span>
        </a>
        <a href="<?php echo BASE_URL; ?>/admin/reportes.php" class="accion-card">
            <span class="accion-icono">📈</span>
            <span class="accion-texto">Ver Reportes</span>
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
