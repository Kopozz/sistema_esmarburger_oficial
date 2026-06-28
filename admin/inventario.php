<?php
/**
 * ESMAR-BURGER — Control de Inventario
 * Usa la vista SQL: vista_inventario_bajo
 */
require_once __DIR__ . '/../includes/config.php';
$titulo_pagina = 'Inventario';
require_once __DIR__ . '/../includes/admin_header.php';

// Ajustar stock manualmente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajustar_stock'])) {
    $insumo_id = (int)$_POST['insumo_id'];
    $cantidad = (float)$_POST['cantidad_ajuste'];
    $operacion = $_POST['operacion'] ?? 'sumar';
    
    // Usar procedimiento almacenado
    $stmt = $pdo->prepare("CALL sp_actualizar_inventario(?, ?, ?)");
    $stmt->execute([$insumo_id, $cantidad, $operacion]);
    $stmt->closeCursor();
    
    setMensaje('Stock actualizado correctamente (procedimiento almacenado).', 'success');
    header('Location: ' . BASE_URL . '/admin/inventario.php');
    exit;
}

// Guardar insumo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_insumo'])) {
    $id = (int)($_POST['id'] ?? 0);
    $nombre = limpiar($_POST['nombre'] ?? '');
    $unidad = limpiar($_POST['unidad_medida'] ?? 'unidad');
    $stock_minimo = (float)($_POST['stock_minimo'] ?? 5);
    $precio = (float)($_POST['precio_unitario'] ?? 0);
    
    if (!empty($nombre)) {
        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE insumos SET nombre=?, unidad_medida=?, stock_minimo=?, precio_unitario=? WHERE id=?");
            $stmt->execute([$nombre, $unidad, $stock_minimo, $precio, $id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO insumos (nombre, unidad_medida, stock_minimo, precio_unitario) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nombre, $unidad, $stock_minimo, $precio]);
        }
        setMensaje('Insumo guardado correctamente.', 'success');
        header('Location: ' . BASE_URL . '/admin/inventario.php');
        exit;
    }
}

// Obtener inventario usando la VISTA SQL
$inventario = $pdo->query("SELECT * FROM vista_inventario_bajo")->fetchAll();

// Editar
$insumoEditar = null;
if (isset($_GET['editar'])) {
    $stmt = $pdo->prepare("SELECT * FROM insumos WHERE id = ?");
    $stmt->execute([(int)$_GET['editar']]);
    $insumoEditar = $stmt->fetch();
}

// Conteos por alerta
$alertas = ['AGOTADO' => 0, 'BAJO STOCK' => 0, 'NORMAL' => 0];
foreach ($inventario as $item) {
    $alertas[$item['alerta']]++;
}
?>

<div class="admin-seccion-header">
    <h1 class="pagina-titulo">📦 Control de Inventario</h1>
    <button class="btn btn-primary" data-modal="modal-insumo">➕ Nuevo Insumo</button>
</div>

<!-- Resumen de alertas -->
<div class="dashboard-grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 24px;">
    <div class="dash-card" style="border-left: 4px solid var(--color-exito);">
        <div class="dash-card-valor" style="color: var(--color-exito);"><?php echo $alertas['NORMAL']; ?></div>
        <span class="dash-card-titulo">✅ Stock Normal</span>
    </div>
    <div class="dash-card" style="border-left: 4px solid var(--color-advertencia);">
        <div class="dash-card-valor" style="color: var(--color-advertencia);"><?php echo $alertas['BAJO STOCK']; ?></div>
        <span class="dash-card-titulo">⚠️ Bajo Stock</span>
    </div>
    <div class="dash-card" style="border-left: 4px solid var(--color-peligro);">
        <div class="dash-card-valor" style="color: var(--color-peligro);"><?php echo $alertas['AGOTADO']; ?></div>
        <span class="dash-card-titulo">❌ Agotado</span>
    </div>
</div>

<!-- Tabla de inventario -->
<div class="admin-seccion">
    <div class="admin-seccion-header">
        <h3 class="admin-seccion-titulo"><span>📋</span> Inventario Actual <small style="font-weight: 400; color: var(--color-gris);">(Vista SQL: vista_inventario_bajo)</small></h3>
    </div>
    <div class="tabla-container">
        <table class="tabla">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Insumo</th>
                    <th>Unidad</th>
                    <th>Stock Actual</th>
                    <th>Stock Mínimo</th>
                    <th>Precio Unit.</th>
                    <th>Alerta</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($inventario as $item): ?>
                <tr>
                    <td>#<?php echo $item['id']; ?></td>
                    <td><strong><?php echo limpiar($item['nombre']); ?></strong></td>
                    <td><?php echo limpiar($item['unidad_medida']); ?></td>
                    <td>
                        <strong style="color: <?php 
                            echo $item['alerta'] === 'AGOTADO' ? 'var(--color-peligro)' : 
                                ($item['alerta'] === 'BAJO STOCK' ? 'var(--color-advertencia)' : 'var(--color-exito)'); 
                        ?>;">
                            <?php echo number_format($item['stock_actual'], 2); ?>
                        </strong>
                    </td>
                    <td><?php echo number_format($item['stock_minimo'], 2); ?></td>
                    <td><?php echo formatoPrecio($item['precio_unitario']); ?></td>
                    <td>
                        <?php if ($item['alerta'] === 'AGOTADO'): ?>
                            <span class="badge estado-cancelado">❌ AGOTADO</span>
                        <?php elseif ($item['alerta'] === 'BAJO STOCK'): ?>
                            <span class="badge estado-pendiente">⚠️ BAJO STOCK</span>
                        <?php else: ?>
                            <span class="badge estado-entregado">✅ NORMAL</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="tabla-acciones">
                            <a href="?editar=<?php echo $item['id']; ?>" class="btn btn-sm btn-secondary">✏️</a>
                            <button type="button" class="btn btn-sm btn-success" 
                                    onclick="abrirAjuste(<?php echo $item['id']; ?>, '<?php echo addslashes($item['nombre']); ?>')">📦 Ajustar</button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Ajustar Stock -->
<div class="modal-overlay" id="modal-ajuste">
    <div class="modal" style="max-width: 400px;">
        <div class="modal-header">
            <h3 class="modal-titulo">📦 Ajustar Stock</h3>
            <button class="modal-cerrar">✕</button>
        </div>
        <form method="POST" action="">
            <div class="modal-body">
                <input type="hidden" name="insumo_id" id="ajuste-insumo-id">
                <p style="margin-bottom: 16px;"><strong id="ajuste-nombre"></strong></p>
                <div class="form-fila">
                    <div class="form-grupo">
                        <label for="operacion">Operación</label>
                        <select name="operacion" id="operacion" class="form-control">
                            <option value="sumar">➕ Sumar</option>
                            <option value="restar">➖ Restar</option>
                        </select>
                    </div>
                    <div class="form-grupo">
                        <label for="cantidad_ajuste">Cantidad</label>
                        <input type="number" name="cantidad_ajuste" id="cantidad_ajuste" class="form-control" step="0.5" min="0.5" required>
                    </div>
                </div>
                <p style="font-size: 0.8rem; color: var(--color-gris);">Se usa el procedimiento almacenado: sp_actualizar_inventario</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost modal-cerrar" style="color: var(--color-gris);">Cancelar</button>
                <button type="submit" name="ajustar_stock" class="btn btn-primary">📦 Ajustar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Nuevo/Editar Insumo -->
<div class="modal-overlay" id="modal-insumo" <?php echo $insumoEditar ? 'style="display:flex;"' : ''; ?>>
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-titulo"><?php echo $insumoEditar ? '✏️ Editar Insumo' : '➕ Nuevo Insumo'; ?></h3>
            <a href="<?php echo BASE_URL; ?>/admin/inventario.php" class="modal-cerrar">✕</a>
        </div>
        <form method="POST" action="">
            <div class="modal-body">
                <input type="hidden" name="id" value="<?php echo $insumoEditar['id'] ?? 0; ?>">
                <div class="form-grupo">
                    <label>Nombre *</label>
                    <input type="text" name="nombre" class="form-control" value="<?php echo limpiar($insumoEditar['nombre'] ?? ''); ?>" required>
                </div>
                <div class="form-fila">
                    <div class="form-grupo">
                        <label>Unidad de Medida</label>
                        <select name="unidad_medida" class="form-control">
                            <?php foreach (['unidad', 'kg', 'litro', 'paquete', 'caja'] as $u): ?>
                            <option value="<?php echo $u; ?>" <?php echo ($insumoEditar['unidad_medida'] ?? '') === $u ? 'selected' : ''; ?>><?php echo ucfirst($u); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-grupo">
                        <label>Stock Mínimo</label>
                        <input type="number" name="stock_minimo" class="form-control" step="1" value="<?php echo $insumoEditar['stock_minimo'] ?? 5; ?>">
                    </div>
                </div>
                <div class="form-grupo">
                    <label>Precio Unitario</label>
                    <input type="number" name="precio_unitario" class="form-control" step="0.50" value="<?php echo $insumoEditar['precio_unitario'] ?? 0; ?>">
                </div>
            </div>
            <div class="modal-footer">
                <a href="<?php echo BASE_URL; ?>/admin/inventario.php" class="btn btn-ghost" style="color: var(--color-gris);">Cancelar</a>
                <button type="submit" name="guardar_insumo" class="btn btn-primary">💾 Guardar</button>
            </div>
        </form>
    </div>
</div>

<script>
function abrirAjuste(id, nombre) {
    document.getElementById('ajuste-insumo-id').value = id;
    document.getElementById('ajuste-nombre').textContent = nombre;
    document.getElementById('modal-ajuste').classList.add('activo');
    document.body.style.overflow = 'hidden';
}
</script>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
