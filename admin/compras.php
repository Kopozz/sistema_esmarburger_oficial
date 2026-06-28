<?php
/**
 * ESMAR-BURGER — Registro de Compras (Producción)
 */
require_once __DIR__ . '/../includes/config.php';
$titulo_pagina = 'Compras';
require_once __DIR__ . '/../includes/admin_header.php';

// Proveedores e insumos para el formulario
$proveedores = $pdo->query("SELECT * FROM proveedores WHERE activo = 1 ORDER BY nombre")->fetchAll();
$insumos = $pdo->query("SELECT * FROM insumos ORDER BY nombre")->fetchAll();

// Registrar compra
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar_compra'])) {
    $proveedor_id = (int)($_POST['proveedor_id'] ?? 0);
    $notas = limpiar($_POST['notas'] ?? '');
    $insumo_ids = $_POST['insumo_id'] ?? [];
    $cantidades = $_POST['cantidad'] ?? [];
    $precios = $_POST['precio_unitario'] ?? [];
    
    if ($proveedor_id > 0 && !empty($insumo_ids)) {
        try {
            $pdo->beginTransaction();
            
            // Calcular total
            $total = 0;
            for ($i = 0; $i < count($insumo_ids); $i++) {
                $total += (float)$cantidades[$i] * (float)$precios[$i];
            }
            
            // Crear compra
            $stmt = $pdo->prepare("INSERT INTO compras (proveedor_id, usuario_id, total, estado, notas) VALUES (?, ?, ?, 'recibida', ?)");
            $stmt->execute([$proveedor_id, $_SESSION['usuario_id'], $total, $notas]);
            $compra_id = $pdo->lastInsertId();
            
            // Insertar detalles (el trigger actualizará el stock)
            $stmtDet = $pdo->prepare("INSERT INTO compra_detalles (compra_id, insumo_id, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?)");
            for ($i = 0; $i < count($insumo_ids); $i++) {
                $cant = (float)$cantidades[$i];
                $precio = (float)$precios[$i];
                $subtotal = $cant * $precio;
                $stmtDet->execute([$compra_id, (int)$insumo_ids[$i], $cant, $precio, $subtotal]);
            }
            
            $pdo->commit();
            setMensaje('Compra #' . $compra_id . ' registrada. El stock se actualizó automáticamente (trigger).', 'success');
            header('Location: ' . BASE_URL . '/admin/compras.php');
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            setMensaje('Error al registrar la compra: ' . $e->getMessage(), 'error');
        }
    } else {
        setMensaje('Selecciona un proveedor y al menos un insumo.', 'error');
    }
}

// Historial de compras
$compras = $pdo->query("SELECT c.*, p.nombre as proveedor, u.nombre as registrado_por FROM compras c LEFT JOIN proveedores p ON c.proveedor_id = p.id LEFT JOIN usuarios u ON c.usuario_id = u.id ORDER BY c.fecha DESC")->fetchAll();
?>

<div class="admin-seccion-header">
    <h1 class="pagina-titulo"><i class="ph-bold ph-shopping-cart"></i> Compras de Insumos</h1>
    <button class="btn btn-primary" data-modal="modal-compra">➕ Nueva Compra</button>
</div>

<!-- Historial de compras -->
<div class="admin-seccion">
    <div class="admin-seccion-header">
        <h3 class="admin-seccion-titulo"><span>📋</span> Historial de Compras</h3>
    </div>
    <div class="tabla-container">
        <table class="tabla">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Proveedor</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Registrado por</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($compras)): ?>
                    <tr><td colspan="6" style="text-align: center; color: var(--color-gris);">No hay compras registradas</td></tr>
                <?php else: ?>
                    <?php foreach ($compras as $c): ?>
                    <tr>
                        <td><strong>#<?php echo $c['id']; ?></strong></td>
                        <td><?php echo limpiar($c['proveedor'] ?? 'Desconocido'); ?></td>
                        <td><strong><?php echo formatoPrecio($c['total']); ?></strong></td>
                        <td>
                            <?php if ($c['estado'] === 'recibida'): ?>
                                <span class="badge estado-entregado">✅ Recibida</span>
                            <?php elseif ($c['estado'] === 'pendiente'): ?>
                                <span class="badge estado-pendiente">⏳ Pendiente</span>
                            <?php else: ?>
                                <span class="badge estado-cancelado">❌ Cancelada</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo limpiar($c['registrado_por'] ?? '-'); ?></td>
                        <td><small><?php echo formatoFecha($c['fecha']); ?></small></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Nueva Compra -->
<div class="modal-overlay" id="modal-compra">
    <div class="modal" style="max-width: 700px;">
        <div class="modal-header">
            <h3 class="modal-titulo">➕ Registrar Nueva Compra</h3>
            <button class="modal-cerrar">✕</button>
        </div>
        <form method="POST" action="">
            <div class="modal-body">
                <div class="form-grupo">
                    <label for="proveedor_id">Proveedor *</label>
                    <select id="proveedor_id" name="proveedor_id" class="form-control" required>
                        <option value="">-- Seleccionar Proveedor --</option>
                        <?php foreach ($proveedores as $prov): ?>
                        <option value="<?php echo $prov['id']; ?>"><?php echo limpiar($prov['nombre']); ?> (<?php echo limpiar($prov['ruc'] ?? ''); ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <h4 style="margin: 20px 0 12px;">📦 Insumos a Comprar</h4>
                <div id="items-compra">
                    <div class="item-compra" style="display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: 10px; margin-bottom: 10px; align-items: end;">
                        <div class="form-grupo" style="margin-bottom: 0;">
                            <label>Insumo</label>
                            <select name="insumo_id[]" class="form-control" required>
                                <option value="">-- Seleccionar --</option>
                                <?php foreach ($insumos as $ins): ?>
                                <option value="<?php echo $ins['id']; ?>"><?php echo limpiar($ins['nombre']); ?> (<?php echo $ins['unidad_medida']; ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-grupo" style="margin-bottom: 0;">
                            <label>Cantidad</label>
                            <input type="number" name="cantidad[]" class="form-control" step="0.5" min="0.5" required>
                        </div>
                        <div class="form-grupo" style="margin-bottom: 0;">
                            <label>Precio Unit.</label>
                            <input type="number" name="precio_unitario[]" class="form-control" step="0.50" min="0" required>
                        </div>
                        <div></div>
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-secondary" onclick="agregarItemCompra()">+ Agregar Item</button>
                
                <div class="form-grupo" style="margin-top: 20px;">
                    <label for="notas">Notas</label>
                    <textarea id="notas" name="notas" class="form-control" placeholder="Notas adicionales..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost modal-cerrar" style="color: var(--color-gris);">Cancelar</button>
                <button type="submit" name="registrar_compra" class="btn btn-primary">💾 Registrar Compra</button>
            </div>
        </form>
    </div>
</div>

<script>
function agregarItemCompra() {
    var container = document.getElementById('items-compra');
    var template = container.querySelector('.item-compra').cloneNode(true);
    template.querySelectorAll('input').forEach(function(input) { input.value = ''; });
    template.querySelectorAll('select').forEach(function(select) { select.selectedIndex = 0; });
    
    // Agregar botón eliminar
    var lastDiv = template.querySelector('div:last-child');
    lastDiv.innerHTML = '<button type="button" class="btn btn-sm btn-danger" onclick="this.closest(\'.item-compra\').remove()">🗑️</button>';
    
    container.appendChild(template);
}
</script>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
