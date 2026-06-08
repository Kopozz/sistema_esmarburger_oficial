<?php
/**
 * ESMAR BURGER - Iniciar Sesión
 * Avance 2 - Ingeniería Web
 */
require_once 'config.php';

// Si ya inició sesión, redirigir
if (isLoggedIn()) {
    if (isAdmin()) {
        header('Location: admin_dashboard.php');
    } else {
        header('Location: index.php');
    }
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!empty($email) && !empty($password)) {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nombre'] = $user['nombre'];
            $_SESSION['user_rol'] = $user['rol'];
            
            if ($user['rol'] === 'admin') {
                header('Location: admin_dashboard.php');
            } else {
                header('Location: index.php');
            }
            exit;
        } else {
            $error = 'Credenciales incorrectas. Inténtalo de nuevo.';
        }
    } else {
        $error = 'Por favor, rellene todos los campos.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Esmar Burger</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div style="flex: 1; display: flex; align-items: center; justify-content: center; padding: 2rem;">
        <div class="form-container glass-panel">
            <h2 class="form-title">Iniciar Sesión</h2>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="form-group">
                    <label class="form-label" for="email">Correo Electrónico</label>
                    <input class="form-input" type="email" id="email" name="email" required placeholder="correo@ejemplo.com">
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Contraseña</label>
                    <input class="form-input" type="password" id="password" name="password" required placeholder="••••••••">
                </div>

                <button class="btn btn-primary" type="submit" style="width: 100%; margin-top: 1rem;">Ingresar</button>
            </form>

            <div class="form-footer-link">
                ¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a>
            </div>
            
            <div style="margin-top: 2rem; border-top: 1px solid var(--border-color); padding-top: 1rem; text-align: center; font-size: 0.8rem; color: var(--text-secondary);">
                <p><strong>Cuentas demo para pruebas del profesor:</strong></p>
                <p>Admin: <code>admin@esmarburger.com</code> / Clave: <code>admin</code></p>
                <p>Cliente: <code>cliente@gmail.com</code> / Clave: <code>cliente</code></p>
            </div>
        </div>
    </div>
</body>
</html>
