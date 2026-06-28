<?php
/**
 * ESMAR-BURGER — Registro de Clientes
 */
require_once __DIR__ . '/includes/config.php';

if (estaLogueado()) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = limpiar($_POST['nombre'] ?? '');
    $email = limpiar($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';
    $telefono = limpiar($_POST['telefono'] ?? '');
    $direccion = limpiar($_POST['direccion'] ?? '');
    
    $errores = [];
    if (empty($nombre)) $errores[] = 'El nombre es obligatorio.';
    if (empty($email)) $errores[] = 'El email es obligatorio.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errores[] = 'El email no es válido.';
    if (strlen($password) < 6) $errores[] = 'La contraseña debe tener al menos 6 caracteres.';
    if ($password !== $password2) $errores[] = 'Las contraseñas no coinciden.';
    
    // Verificar email único
    if (empty($errores)) {
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errores[] = 'Ya existe una cuenta con este email.';
        }
    }
    
    if (empty($errores)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password, rol, telefono, direccion) VALUES (?, ?, ?, 'cliente', ?, ?)");
        $stmt->execute([$nombre, $email, $hash, $telefono, $direccion]);
        
        // Login automático
        $usuario_id = $pdo->lastInsertId();
        iniciarSesion([
            'id' => $usuario_id,
            'nombre' => $nombre,
            'email' => $email,
            'rol' => 'cliente'
        ]);
        
        setMensaje('🎉 ¡Cuenta creada exitosamente! Bienvenido, ' . $nombre, 'success');
        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }
}

$titulo_pagina = 'Registro';
require_once __DIR__ . '/includes/header.php';
?>

<div class="form-container">
    <div class="form-card">
        <h1 class="form-titulo">📝 Crear Cuenta</h1>
        <p class="form-subtitulo">Regístrate para hacer pedidos y más</p>
        
        <?php if (!empty($errores)): ?>
            <div class="alerta alerta-error">
                <span class="alerta-icono">❌</span>
                <span class="alerta-texto"><?php echo implode('<br>', $errores); ?></span>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-grupo">
                <label for="nombre">Nombre Completo *</label>
                <input type="text" id="nombre" name="nombre" class="form-control"
                       value="<?php echo limpiar($_POST['nombre'] ?? ''); ?>"
                       placeholder="Tu nombre completo" required>
            </div>
            
            <div class="form-grupo">
                <label for="email">Correo Electrónico *</label>
                <input type="email" id="email" name="email" class="form-control"
                       value="<?php echo limpiar($_POST['email'] ?? ''); ?>"
                       placeholder="tu@email.com" required>
            </div>
            
            <div class="form-fila">
                <div class="form-grupo">
                    <label for="password">Contraseña *</label>
                    <input type="password" id="password" name="password" class="form-control"
                           placeholder="Mín. 6 caracteres" required>
                </div>
                <div class="form-grupo">
                    <label for="password2">Confirmar Contraseña *</label>
                    <input type="password" id="password2" name="password2" class="form-control"
                           placeholder="Repetir contraseña" required>
                </div>
            </div>
            
            <div class="form-grupo">
                <label for="telefono">Teléfono</label>
                <input type="tel" id="telefono" name="telefono" class="form-control"
                       value="<?php echo limpiar($_POST['telefono'] ?? ''); ?>"
                       placeholder="999888777">
            </div>
            
            <div class="form-grupo">
                <label for="direccion">Dirección</label>
                <input type="text" id="direccion" name="direccion" class="form-control"
                       value="<?php echo limpiar($_POST['direccion'] ?? ''); ?>"
                       placeholder="Tu dirección de entrega">
            </div>
            
            <button type="submit" class="btn btn-primary btn-block btn-lg">Crear Cuenta</button>
        </form>
        
        <p class="form-enlace">
            ¿Ya tienes cuenta? <a href="<?php echo BASE_URL; ?>/login.php">Inicia sesión</a>
        </p>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
