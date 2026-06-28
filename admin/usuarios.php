<?php
/**
 * ESMAR-BURGER — Gestión de Usuarios
 */
require_once __DIR__ . '/../includes/config.php';
$titulo_pagina = 'Usuarios';
require_once __DIR__ . '/../includes/admin_header.php';

// Cambiar rol
if (isset($_GET['cambiar_rol']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $nuevoRol = $_GET['cambiar_rol'] === 'admin' ? 'admin' : 'cliente';
    
    if ($id != $_SESSION['usuario_id']) { // No cambiar su propio rol
        $stmt = $pdo->prepare("UPDATE usuarios SET rol = ? WHERE id = ?");
        $stmt->execute([$nuevoRol, $id]);
        setMensaje('Rol del usuario actualizado a: ' . ucfirst($nuevoRol), 'success');
    } else {
        setMensaje('No puedes cambiar tu propio rol.', 'error');
    }
    header('Location: ' . BASE_URL . '/admin/usuarios.php');
    exit;
}

// Activar/Desactivar
if (isset($_GET['toggle']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $activo = (int)$_GET['toggle'];
    
    if ($id != $_SESSION['usuario_id']) {
        $stmt = $pdo->prepare("UPDATE usuarios SET activo = ? WHERE id = ?");
        $stmt->execute([$activo, $id]);
        setMensaje('Estado del usuario actualizado.', 'success');
    }
    header('Location: ' . BASE_URL . '/admin/usuarios.php');
    exit;
}

// Eliminar
if (isset($_GET['eliminar'])) {
    $id = (int)$_GET['eliminar'];
    if ($id != $_SESSION['usuario_id']) {
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        setMensaje('Usuario eliminado.', 'success');
    }
    header('Location: ' . BASE_URL . '/admin/usuarios.php');
    exit;
}

$usuarios = $pdo->query("SELECT u.*, (SELECT COUNT(*) FROM pedidos WHERE usuario_id = u.id) as total_pedidos FROM usuarios u ORDER BY u.fecha_registro DESC")->fetchAll();
?>

<div class="admin-seccion-header">
    <h1 class="pagina-titulo">👥 Gestión de Usuarios</h1>
</div>

<div class="admin-seccion">
    <div class="tabla-container">
        <table class="tabla">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Teléfono</th>
                    <th>Rol</th>
                    <th>Pedidos</th>
                    <th>Estado</th>
                    <th>Registro</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $u): ?>
                <tr>
                    <td>#<?php echo $u['id']; ?></td>
                    <td><strong><?php echo limpiar($u['nombre']); ?></strong></td>
                    <td><?php echo limpiar($u['email']); ?></td>
                    <td><?php echo limpiar($u['telefono'] ?? '-'); ?></td>
                    <td>
                        <?php if ($u['rol'] === 'admin'): ?>
                            <span class="badge estado-confirmado">👑 Admin</span>
                        <?php else: ?>
                            <span class="badge estado-entregado">👤 Cliente</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo $u['total_pedidos']; ?></td>
                    <td>
                        <?php echo $u['activo'] ? '<span class="badge estado-entregado">✅ Activo</span>' : '<span class="badge estado-cancelado">❌ Inactivo</span>'; ?>
                    </td>
                    <td><small><?php echo formatoFecha($u['fecha_registro']); ?></small></td>
                    <td>
                        <?php if ($u['id'] != $_SESSION['usuario_id']): ?>
                        <div class="tabla-acciones" style="flex-direction: column; gap: 4px;">
                            <?php if ($u['rol'] === 'cliente'): ?>
                                <a href="?id=<?php echo $u['id']; ?>&cambiar_rol=admin" class="btn btn-sm btn-secondary" data-confirmar="¿Hacer admin a este usuario?">👑 Hacer Admin</a>
                            <?php else: ?>
                                <a href="?id=<?php echo $u['id']; ?>&cambiar_rol=cliente" class="btn btn-sm btn-secondary">👤 Hacer Cliente</a>
                            <?php endif; ?>
                            
                            <?php if ($u['activo']): ?>
                                <a href="?id=<?php echo $u['id']; ?>&toggle=0" class="btn btn-sm btn-danger">🔒 Desactivar</a>
                            <?php else: ?>
                                <a href="?id=<?php echo $u['id']; ?>&toggle=1" class="btn btn-sm btn-success">🔓 Activar</a>
                            <?php endif; ?>
                        </div>
                        <?php else: ?>
                            <small style="color: var(--color-gris);">Tú</small>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
