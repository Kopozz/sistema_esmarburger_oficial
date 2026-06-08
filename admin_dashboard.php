<?php
/**
 * ESMAR BURGER - Panel Admin Dashboard
 * Avance 2 - Ingeniería Web
 */
require_once 'config.php';
requireAdmin();

$pdo = getDBConnection();

// KPI 1: Ingresos Totales
$stmtSales = $pdo->query("SELECT SUM(total) FROM pedidos WHERE estado = 'entregado'");
$totalSales = (float)$stmtSales->fetchColumn();

// KPI 2: Pedidos Activos (No entregados)
$stmtActive = $pdo->query("SELECT COUNT(*) FROM pedidos WHERE estado != 'entregado'");
$activeOrders = (int)$stmtActive->fetchColumn();

// KPI 3: Total Clientes Registrados
$stmtUsers = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'cliente'");
$totalClientes = (int)$stmtUsers->fetchColumn();

// KPI 4: Platos Vendidos (Suma de cantidades)
$stmtQty = $pdo->query("SELECT SUM(cantidad) FROM pedido_detalles");
$totalPlatosVendidos = (int)$stmtQty->fetchColumn();

// Obtener los 5 pedidos más recientes
$stmtRecent = $pdo->query("
    SELECT p.*, u.nombre as cliente_nombre 
    FROM pedidos p 
    LEFT JOIN usuarios u ON p.usuario_id = u.id 
    ORDER BY p.fecha DESC 
    LIMIT 5
");
$recentOrders = $stmtRecent->fetchAll();

// Obtener productos más populares
$stmtPopular = $pdo->query("
    SELECT pr.nombre, pr.categoria, SUM(pd.cantidad) as total_vendido 
    FROM pedido_detalles pd
    JOIN productos pr ON pd.producto_id = pr.id
    GROUP BY pd.producto_id
    ORDER BY total_vendido DESC
    LIMIT 3
");
$popularProducts = $stmtPopular->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Esmar Burger</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <div class="nav-container">
            <a href="index.php" class="logo-link">
                <svg class="logo-svg" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="50" cy="50" r="46" fill="none" stroke="#00baf2" stroke-width="4" />
                    <circle cx="50" cy="50" r="40" fill="none" stroke="#ff6b00" stroke-dasharray="6,4" stroke-width="1.5" />
                    <path d="M30 46 C30 35, 70 35, 70 46 Z" fill="#ff6b00" />
                    <rect x="26" y="50" width="48" height="6" rx="3" fill="#00baf2" />
                    <path d="M28 60 C28 66, 72 66, 72 60 Z" fill="#ff6b00" />
                </svg>
                <span class="logo-text">ESMAR PANEL</span>
            </a>

            <div class="nav-actions">
                <span style="font-size: 0.9rem; color: var(--text-secondary);">Sesión: <strong>Admin</strong></span>
                <a href="logout.php" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.85rem;">Cerrar Sesión</a>
            </div>
        </div>
    </header>

    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="glass-panel admin-sidebar">
            <ul class="admin-nav-list">
                <li>
                    <a href="admin_dashboard.php" class="admin-nav-link active">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z"></path>
                        </svg>
                        Dashboard
                    </a>
                </li>
                <li>
                    <a href="admin_pedidos.php" class="admin-nav-link">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                        </svg>
                        Gestionar Pedidos
                        <?php if ($activeOrders > 0): ?>
                            <span class="badge" style="position: static; margin-left: auto; width: 18px; height: 18px; font-size: 0.7rem;"><?php echo $activeOrders; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li>
                    <a href="index.php" class="admin-nav-link">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        Ver Carta Pública
                    </a>
                </li>
            </ul>
        </aside>

        <!-- Main Workspace -->
        <main>
            <div style="margin-bottom: 2rem;">
                <h2 style="font-size: 2.2rem; color: var(--text-primary);">Panel de Control</h2>
                <p style="color: var(--text-secondary);">Resumen operativo en tiempo real para Esmar Burger.</p>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="glass-panel stat-card" style="border-left: 4px solid var(--success);">
                    <span class="stat-label">Ventas Entregadas</span>
                    <div class="stat-value">S/ <?php echo number_format($totalSales, 2); ?></div>
                    <span class="stat-diff">Actualizado</span>
                </div>

                <div class="glass-panel stat-card" style="border-left: 4px solid var(--warning);">
                    <span class="stat-label">Pedidos en Cola</span>
                    <div class="stat-value"><?php echo $activeOrders; ?></div>
                    <span class="stat-diff" style="color: var(--warning);">En preparación/envío</span>
                </div>

                <div class="glass-panel stat-card" style="border-left: 4px solid var(--secondary);">
                    <span class="stat-label">Clientes Activos</span>
                    <div class="stat-value"><?php echo $totalClientes; ?></div>
                    <span class="stat-diff" style="color: var(--secondary);">Usuarios registrados</span>
                </div>

                <div class="glass-panel stat-card" style="border-left: 4px solid var(--primary);">
                    <span class="stat-label">Productos Vendidos</span>
                    <div class="stat-value"><?php echo $totalPlatosVendidos; ?></div>
                    <span class="stat-diff" style="color: var(--primary);">Unidades totales</span>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1.6fr 1fr; gap: 2rem;">
                <!-- Recientes -->
                <div class="glass-panel" style="padding: 2rem;">
                    <h3 style="font-size: 1.25rem; margin-bottom: 1rem; color: var(--text-primary);">Pedidos Recientes</h3>
                    <div class="table-wrapper">
                        <table class="custom-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Cliente</th>
                                    <th>Total</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentOrders as $ped): ?>
                                    <tr>
                                        <td><strong>#<?php echo $ped['id']; ?></strong></td>
                                        <td><?php echo htmlspecialchars($ped['cliente_nombre'] ?? 'Invitado'); ?></td>
                                        <td>S/ <?php echo number_format($ped['total'], 2); ?></td>
                                        <td>
                                            <span class="status-badge <?php echo $ped['estado']; ?>">
                                                <?php echo htmlspecialchars($ped['estado']); ?>
                                            </span>
                                        </td>
                                        <td style="font-size: 0.8rem; color: var(--text-secondary);">
                                            <?php echo date('d M, h:i A', strtotime($ped['fecha'])); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Populares -->
                <div class="glass-panel" style="padding: 2rem;">
                    <h3 style="font-size: 1.25rem; margin-bottom: 1.5rem; color: var(--text-primary);">Los Más Vendidos</h3>
                    <div style="display: flex; flex-direction: column; gap: 1.25rem;">
                        <?php foreach ($popularProducts as $idx => $pop): ?>
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div style="width: 36px; height: 36px; border-radius: 50%; background: rgba(255,107,0,0.1); border: 1px solid var(--primary); display: flex; align-items: center; justify-content: center; font-weight: 800; color: var(--primary);">
                                    <?php echo $idx + 1; ?>
                                </div>
                                <div style="flex-grow: 1;">
                                    <h4 style="font-size: 1rem; color: var(--text-primary);"><?php echo htmlspecialchars($pop['nombre']); ?></h4>
                                    <span style="font-size: 0.8rem; color: var(--text-secondary);"><?php echo htmlspecialchars($pop['categoria']); ?></span>
                                </div>
                                <span style="font-weight: 800; color: var(--secondary); font-size: 1.1rem;">
                                    <?php echo $pop['total_vendido']; ?> unds
                                </span>
                            </div>
                            <div style="border-bottom: 1px solid var(--border-color); opacity: 0.5;"></div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
