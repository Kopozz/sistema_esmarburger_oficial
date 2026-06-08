<?php
/**
 * ESMAR BURGER - Registro de Clientes
 * Avance 2 - Ingeniería Web
 */
require_once 'config.php';

// Si ya inició sesión, redirigir
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $telefono = trim($_POST['telefono']);
    $direccion = trim($_POST['direccion']);

    if (!empty($nombre) && !empty($email) && !empty($password)) {
        $pdo = getDBConnection();
        
        // Verificar si el email ya existe
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE email = :email");
        $stmt->execute(['email' => $email]);
        if ($stmt->fetchColumn() > 0) {
            $error = 'Este correo electrónico ya está registrado.';
        } else {
            // Registrar usuario
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password, rol, telefono, direccion) VALUES (:nombre, :email, :password, 'cliente', :telefono, :direccion)");
            
            try {
                $stmt->execute([
                    'nombre' => $nombre,
                    'email' => $email,
                    'password' => $hashedPassword,
                    'telefono' => $telefono,
                    'direccion' => $direccion
                ]);

                // Autologin despues de registro exitoso
                $userId = $pdo->lastInsertId();
                $_SESSION['user_id'] = $userId;
                $_SESSION['user_nombre'] = $nombre;
                $_SESSION['user_rol'] = 'cliente';

                header('Location: index.php');
                exit;
            } catch (PDOException $e) {
                $error = 'Error en el servidor. Inténtalo de nuevo más tarde.';
            }
        }
    } else {
        $error = 'Por favor, rellene todos los campos obligatorios.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Esmar Burger</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <div style="flex: 1; display: flex; align-items: center; justify-content: center; padding: 2rem;">
        <div class="form-container glass-panel" style="max-width: 500px;">
            <h2 class="form-title">Crear Cuenta</h2>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form action="registro.php" method="POST">
                <div class="form-group">
                    <label class="form-label" for="nombre">Nombre Completo *</label>
                    <input class="form-input" type="text" id="nombre" name="nombre" required placeholder="Ej. Juan Pérez">
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Correo Electrónico *</label>
                    <input class="form-input" type="email" id="email" name="email" required placeholder="correo@ejemplo.com">
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Contraseña *</label>
                    <input class="form-input" type="password" id="password" name="password" required placeholder="Mínimo 6 caracteres">
                </div>

                <div class="form-group">
                    <label class="form-label" for="telefono">Número de Celular</label>
                    <input class="form-input" type="tel" id="telefono" name="telefono" placeholder="Ej. 935550240">
                </div>

                <div class="form-group">
                    <label class="form-label" for="direccion">Dirección de Entrega</label>
                    <input class="form-input" type="text" id="direccion" name="direccion" placeholder="Ej. Av. Larco 456, Miraflores">
                </div>

                <button class="btn btn-primary" type="submit" style="width: 100%; margin-top: 1rem;">Registrarse</button>
            </form>

            <div class="form-footer-link">
                ¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a>
            </div>
        </div>
    </div>
</body>
</html>
