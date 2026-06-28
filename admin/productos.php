<?php
/**
 * ESMAR-BURGER — Gestión de Productos (CRUD)
 */
require_once __DIR__ . '/../includes/config.php';
$titulo_pagina = 'Productos';
require_once __DIR__ . '/../includes/admin_header.php';

// Categorías para el formulario
$categorias = $pdo->query("SELECT * FROM categorias WHERE activo = 1 ORDER BY nombre")->fetchAll();

// ===== ACCIONES =====

// Eliminar producto
if (isset($_GET['eliminar'])) {
    $id = (int)$_GET['eliminar'];
    $stmt = $pdo->prepare("DELETE FROM productos WHERE id = ?");
    $stmt->execute([$id]);
    setMensaje('Producto eliminado correctamente.', 'success');
    header('Location: ' . BASE_URL . '/admin/productos.php');
    exit;
}

// Guardar producto (crear o editar)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $nombre = limpiar($_POST['nombre'] ?? '');
    $descripcion = limpiar($_POST['descripcion'] ?? '');
    $precio = (float)($_POST['precio'] ?? 0);
    $categoria_id = (int)($_POST['categoria_id'] ?? 0);
    $disponible = isset($_POST['disponible']) ? 1 : 0;
    $imagen = limpiar($_POST['imagen'] ?? 'default.jpg');
    
    if (!empty($nombre) && $precio > 0) {
        if ($id > 0) {
            // Editar
            $stmt = $pdo->prepare("UPDATE productos SET nombre = ?, descripcion = ?, precio = ?, categoria_id = ?, disponible = ?, imagen = ? WHERE id = ?");
            $stmt->execute([$nombre, $descripcion, $precio, $categoria_id, $disponible, $imagen, $id]);
            setMensaje('Producto actualizado correctamente.', 'success');
        } else {
            // Crear
            $stmt = $pdo->prepare("INSERT INTO productos (nombre, descripcion, precio, categoria_id, disponible, imagen) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nombre, $descripcion, $precio, $categoria_id, $disponible, $imagen]);
            setMensaje('Producto creado correctamente.', 'success');
        }
        header('Location: ' . BASE_URL . '/admin/productos.php');
        exit;
    } else {
        setMensaje('Completa todos los campos obligatorios.', 'error');
    }
}

// Cargar producto para editar
$productoEditar = null;
if (isset($_GET['editar'])) {
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ?");
    $stmt->execute([(int)$_GET['editar']]);
    $productoEditar = $stmt->fetch();
}

// Obtener productos
$productos = $pdo->query("SELECT p.*, c.nombre as categoria FROM productos p LEFT JOIN categorias c ON p.categoria_id = c.id ORDER BY c.nombre, p.nombre")->fetchAll();
?>

<div class="admin-seccion-header">
    <h1 class="pagina-titulo">🍔 Gestión de Productos</h1>
    <button class="btn btn-primary" data-modal="modal-producto" id="btn-nuevo-producto">➕ Nuevo Producto</button>
</div>

<!-- Tabla de productos -->
<div class="admin-seccion">
    <div class="tabla-container">
        <table class="tabla">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Categoría</th>
                    <th>Precio</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($productos)): ?>
                    <tr><td colspan="6" style="text-align: center; color: var(--color-gris);">No hay productos registrados</td></tr>
                <?php else: ?>
                    <?php foreach ($productos as $prod): ?>
                    <tr>
                        <td><strong>#<?php echo $prod['id']; ?></strong></td>
                        <td>
                            <strong><?php echo limpiar($prod['nombre']); ?></strong>
                            <br><small style="color: var(--color-gris);"><?php echo limpiar(substr($prod['descripcion'], 0, 50)); ?>...</small>
                        </td>
                        <td><?php echo limpiar($prod['categoria'] ?? 'Sin categoría'); ?></td>
                        <td><strong><?php echo formatoPrecio($prod['precio']); ?></strong></td>
                        <td>
                            <?php if ($prod['disponible']): ?>
                                <span class="badge estado-entregado">✅ Disponible</span>
                            <?php else: ?>
                                <span class="badge estado-cancelado">❌ No disponible</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="tabla-acciones">
                                <a href="<?php echo BASE_URL; ?>/admin/productos.php?editar=<?php echo $prod['id']; ?>" class="btn btn-sm btn-secondary">✏️</a>
                                <a href="<?php echo BASE_URL; ?>/admin/productos.php?eliminar=<?php echo $prod['id']; ?>" class="btn btn-sm btn-danger" data-confirmar="¿Eliminar este producto?">🗑️</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal de Producto -->
<div class="modal-overlay" id="modal-producto" <?php echo $productoEditar ? 'style="display:flex;"' : ''; ?>>
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-titulo"><?php echo $productoEditar ? '✏️ Editar Producto' : '➕ Nuevo Producto'; ?></h3>
            <a href="<?php echo BASE_URL; ?>/admin/productos.php" class="modal-cerrar">✕</a>
        </div>
        <form method="POST" action="">
            <div class="modal-body">
                <input type="hidden" name="id" value="<?php echo $productoEditar['id'] ?? 0; ?>">
                
                <div class="form-grupo">
                    <label for="nombre">Nombre del Producto *</label>
                    <input type="text" id="nombre" name="nombre" class="form-control" 
                           value="<?php echo limpiar($productoEditar['nombre'] ?? ''); ?>" required>
                </div>
                
                <div class="form-grupo">
                    <label for="descripcion">Descripción</label>
                    <textarea id="descripcion" name="descripcion" class="form-control"><?php echo limpiar($productoEditar['descripcion'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-fila">
                    <div class="form-grupo">
                        <label for="precio">Precio (S/.) *</label>
                        <input type="number" id="precio" name="precio" class="form-control" step="0.50" min="0"
                               value="<?php echo $productoEditar['precio'] ?? ''; ?>" required>
                    </div>
                    <div class="form-grupo">
                        <label for="categoria_id">Categoría</label>
                        <select id="categoria_id" name="categoria_id" class="form-control">
                            <?php foreach ($categorias as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo (($productoEditar['categoria_id'] ?? '') == $cat['id']) ? 'selected' : ''; ?>>
                                <?php echo $cat['icono'] . ' ' . limpiar($cat['nombre']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-grupo">
                    <label for="imagen">Nombre de Imagen</label>
                    <input type="text" id="imagen" name="imagen" class="form-control" 
                           value="<?php echo limpiar($productoEditar['imagen'] ?? 'default.jpg'); ?>">
                </div>
                
                <div class="form-grupo">
                    <label>
                        <input type="checkbox" name="disponible" <?php echo ($productoEditar['disponible'] ?? 1) ? 'checked' : ''; ?>>
                        Producto disponible
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <a href="<?php echo BASE_URL; ?>/admin/productos.php" class="btn btn-ghost" style="color: var(--color-gris);">Cancelar</a>
                <button type="submit" class="btn btn-primary">💾 Guardar</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
