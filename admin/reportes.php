<?php
/**
 * ESMAR-BURGER — Reportes y Estadísticas
 * Usa: vista_ventas_diarias, vista_productos_mas_vendidos, sp_reporte_ventas
 */
require_once __DIR__ . '/../includes/config.php';
$titulo_pagina = 'Reportes';
require_once __DIR__ . '/../includes/admin_header.php';

// Fechas para filtro
$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01'); // Primer día del mes
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');

// Reporte de ventas usando PROCEDIMIENTO ALMACENADO
$reporteVentas = [];
try {
    $stmt = $pdo->prepare("CALL sp_reporte_ventas(?, ?)");
    $stmt->execute([$fecha_inicio, $fecha_fin]);
    $reporteVentas = $stmt->fetchAll();
    $stmt->closeCursor();
} catch (Exception $e) {
    // Si falla el SP, usar query directa
    $stmt = $pdo->prepare("SELECT DATE(fecha) as dia, COUNT(id) as pedidos, SUM(subtotal) as subtotal, SUM(impuesto) as impuesto, SUM(total) as total FROM pedidos WHERE DATE(fecha) BETWEEN ? AND ? AND estado != 'cancelado' GROUP BY DATE(fecha) ORDER BY dia");
    $stmt->execute([$fecha_inicio, $fecha_fin]);
    $reporteVentas = $stmt->fetchAll();
}

// Totales del periodo
$totalPeriodo = 0;
$totalPedidos = 0;
$maxVenta = 1;
foreach ($reporteVentas as $r) {
    $totalPeriodo += $r['total'];
    $totalPedidos += $r['pedidos'];
    if ($r['total'] > $maxVenta) $maxVenta = $r['total'];
}

// Productos más vendidos usando VISTA SQL
$productosTop = $pdo->query("SELECT * FROM vista_productos_mas_vendidos LIMIT 10")->fetchAll();

// Ventas diarias usando VISTA SQL
$ventasDiarias = $pdo->query("SELECT * FROM vista_ventas_diarias LIMIT 7")->fetchAll();
?>

<h1 class="pagina-titulo" style="margin-bottom: 30px;">📈 Reportes y Estadísticas</h1>

<!-- Filtro de fechas -->
<div class="admin-seccion" style="margin-bottom: 24px;">
    <form method="GET" action="" style="display: flex; gap: 16px; align-items: flex-end; flex-wrap: wrap;">
        <div class="form-grupo" style="margin-bottom: 0;">
            <label for="fecha_inicio">Desde</label>
            <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control" value="<?php echo $fecha_inicio; ?>">
        </div>
        <div class="form-grupo" style="margin-bottom: 0;">
            <label for="fecha_fin">Hasta</label>
            <input type="date" id="fecha_fin" name="fecha_fin" class="form-control" value="<?php echo $fecha_fin; ?>">
        </div>
        <button type="submit" class="btn btn-primary">🔍 Filtrar</button>
    </form>
</div>

<!-- Resumen del periodo -->
<div class="dashboard-grid" style="grid-template-columns: repeat(3, 1fr);">
    <div class="dash-card card-ventas">
        <div class="dash-card-header">
            <span class="dash-card-titulo">Total Ventas</span>
            <span class="dash-card-icono">💰</span>
        </div>
        <div class="dash-card-valor"><?php echo formatoPrecio($totalPeriodo); ?></div>
        <span class="dash-card-cambio">Periodo seleccionado</span>
    </div>
    <div class="dash-card card-pedidos">
        <div class="dash-card-header">
            <span class="dash-card-titulo">Total Pedidos</span>
            <span class="dash-card-icono">📋</span>
        </div>
        <div class="dash-card-valor"><?php echo $totalPedidos; ?></div>
        <span class="dash-card-cambio">Pedidos completados</span>
    </div>
    <div class="dash-card card-productos">
        <div class="dash-card-header">
            <span class="dash-card-titulo">Promedio/Pedido</span>
            <span class="dash-card-icono">📊</span>
        </div>
        <div class="dash-card-valor"><?php echo $totalPedidos > 0 ? formatoPrecio($totalPeriodo / $totalPedidos) : formatoPrecio(0); ?></div>
        <span class="dash-card-cambio">Ticket promedio</span>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-top: 30px;">
    <!-- Gráfico de ventas por día -->
    <div class="admin-seccion">
        <div class="admin-seccion-header">
            <h3 class="admin-seccion-titulo"><span>📊</span> Ventas por Día</h3>
            <small style="color: var(--color-gris);">Proc. Almacenado: sp_reporte_ventas</small>
        </div>
        <div class="grafico-barras">
            <?php if (empty($reporteVentas)): ?>
                <p style="text-align: center; color: var(--color-gris); width: 100%; align-self: center;">Sin datos en este periodo</p>
            <?php else: ?>
                <?php foreach ($reporteVentas as $r): 
                    $porcentaje = ($r['total'] / $maxVenta) * 100;
                ?>
                <div class="barra-item">
                    <span class="barra-valor"><?php echo formatoPrecio($r['total']); ?></span>
                    <div class="barra" data-height="<?php echo round($porcentaje); ?>"></div>
                    <span class="barra-label"><?php echo date('d/m', strtotime($r['dia'])); ?></span>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Productos más vendidos -->
    <div class="admin-seccion">
        <div class="admin-seccion-header">
            <h3 class="admin-seccion-titulo"><span>🏆</span> Top Productos</h3>
            <small style="color: var(--color-gris);">Vista: vista_productos_mas_vendidos</small>
        </div>
        <div class="tabla-container">
            <table class="tabla">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Producto</th>
                        <th>Vendidos</th>
                        <th>Ingresos</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($productosTop)): ?>
                        <tr><td colspan="4" style="text-align: center; color: var(--color-gris);">Sin datos</td></tr>
                    <?php else: ?>
                        <?php $pos = 1; foreach ($productosTop as $prod): ?>
                        <tr>
                            <td>
                                <?php 
                                $medallas = ['🥇', '🥈', '🥉'];
                                echo $medallas[$pos - 1] ?? $pos;
                                ?>
                            </td>
                            <td>
                                <strong><?php echo limpiar($prod['nombre']); ?></strong>
                                <br><small style="color: var(--color-gris);"><?php echo limpiar($prod['categoria'] ?? ''); ?></small>
                            </td>
                            <td><?php echo $prod['total_vendido']; ?> uds</td>
                            <td><strong><?php echo formatoPrecio($prod['ingresos_totales']); ?></strong></td>
                        </tr>
                        <?php $pos++; endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Detalle de ventas -->
<div class="admin-seccion" style="margin-top: 30px;">
    <div class="admin-seccion-header">
        <h3 class="admin-seccion-titulo"><span>📋</span> Detalle de Ventas Diarias</h3>
        <small style="color: var(--color-gris);">Vista: vista_ventas_diarias</small>
    </div>
    <div class="tabla-container">
        <table class="tabla">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Pedidos</th>
                    <th>Total Ventas</th>
                    <th>Promedio</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($ventasDiarias)): ?>
                    <tr><td colspan="4" style="text-align: center; color: var(--color-gris);">Sin datos</td></tr>
                <?php else: ?>
                    <?php foreach ($ventasDiarias as $vd): ?>
                    <tr>
                        <td><strong><?php echo formatoFecha($vd['fecha']); ?></strong></td>
                        <td><?php echo $vd['total_pedidos']; ?></td>
                        <td><strong><?php echo formatoPrecio($vd['total_ventas']); ?></strong></td>
                        <td><?php echo formatoPrecio($vd['promedio_venta']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
