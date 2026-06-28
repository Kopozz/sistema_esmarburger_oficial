<?php
/**
 * ESMAR-BURGER — Login
 */
require_once __DIR__ . '/includes/config.php';

// Si ya está logueado, redirigir
if (estaLogueado()) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = limpiar($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    $errores = [];
    if (empty($email)) $errores[] = 'El email es obligatorio.';
    if (empty($password)) $errores[] = 'La contraseña es obligatoria.';
    
    if (empty($errores)) {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ? AND activo = 1");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();
        
        if ($usuario && password_verify($password, $usuario['password'])) {
            iniciarSesion($usuario);
            setMensaje('¡Bienvenido, ' . $usuario['nombre'] . '!', 'success');
            
            if ($usuario['rol'] === 'admin') {
                header('Location: ' . BASE_URL . '/admin/index.php');
            } else {
                header('Location: ' . BASE_URL . '/index.php');
            }
            exit;
        } else {
            $errores[] = 'Email o contraseña incorrectos.';
        }
    }
}

$titulo_pagina = 'Iniciar Sesión';
require_once __DIR__ . '/includes/header.php';
?>

<div class="form-container">
    <div class="form-card">
        <h1 class="form-titulo"><i class="ph-bold ph-hamburger"></i> Iniciar Sesión</h1>
        <p class="form-subtitulo">Ingresa a tu cuenta para hacer pedidos</p>
        
        <?php if (!empty($errores)): ?>
            <div class="alerta alerta-error">
                <span class="alerta-icono">❌</span>
                <span class="alerta-texto"><?php echo implode('<br>', $errores); ?></span>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-grupo">
                <label for="email">Correo Electrónico</label>
                <input type="email" id="email" name="email" class="form-control" 
                       value="<?php echo limpiar($_POST['email'] ?? ''); ?>"
                       placeholder="tu@email.com" required>
            </div>
            
            <div class="form-grupo">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" class="form-control" 
                       placeholder="Tu contraseña" required>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block btn-lg">Ingresar</button>
        </form>
        
        <p class="form-enlace">
            ¿No tienes cuenta? <a href="<?php echo BASE_URL; ?>/registro.php">Regístrate aquí</a>
        </p>
        
        <div style="margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--color-gris-claro);">
            <p style="font-size: 0.8rem; color: var(--color-gris); text-align: center; margin-bottom: 8px;">
                <strong>Cuentas de prueba:</strong>
            </p>
            <p style="font-size: 0.78rem; color: var(--color-gris); text-align: center;">
                Admin: admin@esmarburger.com / 123456<br>
                Cliente: cliente@gmail.com / 123456
            </p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
