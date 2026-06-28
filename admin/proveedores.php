<?php
/**
 * ESMAR-BURGER — Gestión de Proveedores
 */
require_once __DIR__ . '/../includes/config.php';
$titulo_pagina = 'Proveedores';
require_once __DIR__ . '/../includes/admin_header.php';

// Eliminar
if (isset($_GET['eliminar'])) {
    $stmt = $pdo->prepare("DELETE FROM proveedores WHERE id = ?");
    $stmt->execute([(int)$_GET['eliminar']]);
    setMensaje('Proveedor eliminado.', 'success');
    header('Location: ' . BASE_URL . '/admin/proveedores.php');
    exit;
}

// Guardar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $nombre = limpiar($_POST['nombre'] ?? '');
    $ruc = limpiar($_POST['ruc'] ?? '');
    $telefono = limpiar($_POST['telefono'] ?? '');
    $email = limpiar($_POST['email'] ?? '');
    $direccion = limpiar($_POST['direccion'] ?? '');
    $activo = isset($_POST['activo']) ? 1 : 0;
    
    if (!empty($nombre)) {
        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE proveedores SET nombre=?, ruc=?, telefono=?, email=?, direccion=?, activo=? WHERE id=?");
            $stmt->execute([$nombre, $ruc, $telefono, $email, $direccion, $activo, $id]);
            setMensaje('Proveedor actualizado.', 'success');
        } else {
            $stmt = $pdo->prepare("INSERT INTO proveedores (nombre, ruc, telefono, email, direccion) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nombre, $ruc, $telefono, $email, $direccion]);
            setMensaje('Proveedor registrado.', 'success');
        }
        header('Location: ' . BASE_URL . '/admin/proveedores.php');
        exit;
    }
}

// Editar
$proveedorEditar = null;
if (isset($_GET['editar'])) {
    $stmt = $pdo->prepare("SELECT * FROM proveedores WHERE id = ?");
    $stmt->execute([(int)$_GET['editar']]);
    $proveedorEditar = $stmt->fetch();
}

$proveedores = $pdo->query("SELECT * FROM proveedores ORDER BY nombre")->fetchAll();
?>

<div class="admin-seccion-header">
    <h1 class="pagina-titulo">🏭 Gestión de Proveedores</h1>
    <button class="btn btn-primary" data-modal="modal-proveedor">➕ Nuevo Proveedor</button>
</div>

<div class="admin-seccion">
    <div class="tabla-container">
        <table class="tabla">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>RUC</th>
                    <th>Teléfono</th>
                    <th>Email</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($proveedores)): ?>
                    <tr><td colspan="7" style="text-align: center; color: var(--color-gris);">No hay proveedores</td></tr>
                <?php else: ?>
                    <?php foreach ($proveedores as $prov): ?>
                    <tr>
                        <td><strong>#<?php echo $prov['id']; ?></strong></td>
                        <td><strong><?php echo limpiar($prov['nombre']); ?></strong></td>
                        <td><?php echo limpiar($prov['ruc'] ?? '-'); ?></td>
                        <td><?php echo limpiar($prov['telefono'] ?? '-'); ?></td>
                        <td><?php echo limpiar($prov['email'] ?? '-'); ?></td>
                        <td>
                            <?php echo $prov['activo'] ? '<span class="badge estado-entregado">✅ Activo</span>' : '<span class="badge estado-cancelado">❌ Inactivo</span>'; ?>
                        </td>
                        <td>
                            <div class="tabla-acciones">
                                <a href="?editar=<?php echo $prov['id']; ?>" class="btn btn-sm btn-secondary">✏️</a>
                                <a href="?eliminar=<?php echo $prov['id']; ?>" class="btn btn-sm btn-danger" data-confirmar="¿Eliminar proveedor?">🗑️</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div class="modal-overlay" id="modal-proveedor" <?php echo $proveedorEditar ? 'style="display:flex;"' : ''; ?>>
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-titulo"><?php echo $proveedorEditar ? '✏️ Editar Proveedor' : '➕ Nuevo Proveedor'; ?></h3>
            <a href="<?php echo BASE_URL; ?>/admin/proveedores.php" class="modal-cerrar">✕</a>
        </div>
        <form method="POST" action="">
            <div class="modal-body">
                <input type="hidden" name="id" value="<?php echo $proveedorEditar['id'] ?? 0; ?>">
                
                <div class="form-grupo">
                    <label for="nombre">Nombre *</label>
                    <input type="text" id="nombre" name="nombre" class="form-control"
                           value="<?php echo limpiar($proveedorEditar['nombre'] ?? ''); ?>" required>
                </div>
                <div class="form-fila">
                    <div class="form-grupo">
                        <label for="ruc">RUC</label>
                        <input type="text" id="ruc" name="ruc" class="form-control"
                               value="<?php echo limpiar($proveedorEditar['ruc'] ?? ''); ?>">
                    </div>
                    <div class="form-grupo">
                        <label for="telefono">Teléfono</label>
                        <input type="text" id="telefono" name="telefono" class="form-control"
                               value="<?php echo limpiar($proveedorEditar['telefono'] ?? ''); ?>">
                    </div>
                </div>
                <div class="form-grupo">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control"
                           value="<?php echo limpiar($proveedorEditar['email'] ?? ''); ?>">
                </div>
                <div class="form-grupo">
                    <label for="direccion">Dirección</label>
                    <input type="text" id="direccion" name="direccion" class="form-control"
                           value="<?php echo limpiar($proveedorEditar['direccion'] ?? ''); ?>">
                </div>
                <?php if ($proveedorEditar): ?>
                <div class="form-grupo">
                    <label><input type="checkbox" name="activo" <?php echo $proveedorEditar['activo'] ? 'checked' : ''; ?>> Proveedor activo</label>
                </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <a href="<?php echo BASE_URL; ?>/admin/proveedores.php" class="btn btn-ghost" style="color: var(--color-gris);">Cancelar</a>
                <button type="submit" class="btn btn-primary">💾 Guardar</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
