<?php
/**
 * ESMAR BURGER - Panel Admin de Pedidos
 * Avance 2 - Ingeniería Web
 */
require_once 'config.php';
requireAdmin();

$pdo = getDBConnection();

// Manejar actualización de estado del pedido
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $pedidoId = (int)$_POST['pedido_id'];
    $nuevoEstado = trim($_POST['estado']);

    if (in_array($nuevoEstado, ['pendiente', 'preparando', 'en camino', 'entregado'])) {
        $stmtUpdate = $pdo->prepare("UPDATE pedidos SET estado = :estado WHERE id = :id");
        $stmtUpdate->execute(['estado' => $nuevoEstado, 'id' => $pedidoId]);
        $message = "Pedido #$pedidoId actualizado a " . strtoupper($nuevoEstado);
    }
}

// Obtener la lista completa de pedidos con el nombre del cliente
$stmtOrders = $pdo->query("
    SELECT p.*, u.nombre as cliente_nombre 
    FROM pedidos p 
    LEFT JOIN usuarios u ON p.usuario_id = u.id 
    ORDER BY p.fecha DESC
");
$allOrders = $stmtOrders->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Pedidos - Esmar Burger</title>
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
                    <a href="admin_dashboard.php" class="admin-nav-link">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z"></path>
                        </svg>
                        Dashboard
                    </a>
                </li>
                <li>
                    <a href="admin_pedidos.php" class="admin-nav-link active">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                        </svg>
                        Gestionar Pedidos
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
            <div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h2 style="font-size: 2.2rem; color: var(--text-primary);">Gestión de Pedidos</h2>
                    <p style="color: var(--text-secondary);">Cambia los estados de preparación y envío de cada orden.</p>
                </div>
            </div>

            <?php if (!empty($message)): ?>
                <div class="alert alert-success" style="margin-bottom: 2rem;">
                    🎉 <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <div class="glass-panel" style="padding: 2rem;">
                <?php if (empty($allOrders)): ?>
                    <p style="text-align: center; color: var(--text-secondary); padding: 3rem 0;">No se han registrado pedidos todavía en el sistema.</p>
                <?php else: ?>
                    <div class="table-wrapper">
                        <table class="custom-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Cliente</th>
                                    <th>Dirección de Entrega</th>
                                    <th>Celular</th>
                                    <th>Pago</th>
                                    <th>Total</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allOrders as $ped): ?>
                                    <tr>
                                        <td><strong>#<?php echo $ped['id']; ?></strong></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($ped['cliente_nombre'] ?? 'Invitado'); ?></strong>
                                            <div style="font-size: 0.75rem; color: var(--text-secondary);">
                                                <?php echo date('d M, h:i A', strtotime($ped['fecha'])); ?>
                                            </div>
                                        </td>
                                        <td style="max-width: 250px; font-size: 0.85rem; word-wrap: break-word;">
                                            <?php echo htmlspecialchars($ped['direccion_entrega']); ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($ped['telefono_contacto']); ?></td>
                                        <td style="text-transform: uppercase; font-size: 0.8rem;">
                                            <?php echo htmlspecialchars(str_replace('_', ' ', $ped['metodo_pago'])); ?>
                                        </td>
                                        <td style="font-weight: 700; color: var(--primary);">
                                            S/ <?php echo number_format($ped['total'], 2); ?>
                                        </td>
                                        <td>
                                            <span class="status-badge <?php echo $ped['estado']; ?>">
                                                <?php echo htmlspecialchars($ped['estado']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <!-- Formulario simple para actualizar estado -->
                                            <form action="admin_pedidos.php" method="POST" style="display: flex; gap: 0.5rem; align-items: center;">
                                                <input type="hidden" name="pedido_id" value="<?php echo $ped['id']; ?>">
                                                <input type="hidden" name="update_status" value="1">
                                                
                                                <select name="estado" class="form-input" style="padding: 0.25rem 0.5rem; font-size: 0.8rem; width: auto; border-radius: var(--radius-sm);">
                                                    <option value="pendiente" <?php echo $ped['estado'] === 'pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                                                    <option value="preparando" <?php echo $ped['estado'] === 'preparando' ? 'selected' : ''; ?>>Preparando</option>
                                                    <option value="en camino" <?php echo $ped['estado'] === 'en camino' ? 'selected' : ''; ?>>En Camino</option>
                                                    <option value="entregado" <?php echo $ped['estado'] === 'entregado' ? 'selected' : ''; ?>>Entregado</option>
                                                </select>
                                                
                                                <button type="submit" class="btn btn-primary" style="padding: 0.35rem 0.75rem; font-size: 0.8rem; border-radius: var(--radius-sm); width: auto;">
                                                    Guardar
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
