<?php
/**
 * ESMAR-BURGER — Configuración y Conexión a Base de Datos
 * Archivo central de configuración del sistema
 */

// Iniciar sesión si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// =====================================================
// CONSTANTES DEL SISTEMA
// =====================================================
define('NOMBRE_SISTEMA', 'ESMAR BURGER');
define('VERSION', '1.0.0');
define('MONEDA', 'S/.');
define('IGV', 0.18);

// =====================================================
// CONFIGURACIÓN DE BASE DE DATOS (XAMPP - MySQL / Vercel Env Vars)
// =====================================================
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'esmar_burger');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
define('DB_CHARSET', 'utf8mb4');

// =====================================================
// RUTAS DEL SISTEMA
// =====================================================
// Detectar la ruta base automáticamente
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));

// Si estamos en un subdirectorio admin/, subir un nivel
if (strpos($scriptDir, '/admin') !== false) {
    $baseDir = dirname($scriptDir);
} else {
    $baseDir = $scriptDir;
}
$baseDir = rtrim($baseDir, '/');

define('BASE_URL', $protocol . '://' . $host . $baseDir);

// =====================================================
// CONEXIÓN PDO
// =====================================================
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die('<div style="text-align:center;padding:50px;font-family:Arial;">
        <h1>⚠️ Error de Conexión</h1>
        <p>No se pudo conectar a la base de datos.</p>
        <p>Verifica que XAMPP esté ejecutando MySQL y que la base de datos <strong>esmar_burger</strong> exista.</p>
        <p style="color:#999;font-size:12px;">Error: ' . $e->getMessage() . '</p>
    </div>');
}

// =====================================================
// FUNCIONES DE SESIÓN
// =====================================================

/**
 * Mostrar mensaje flash y limpiarlo de la sesión
 */
function mostrarMensaje() {
    if (isset($_SESSION['mensaje'])) {
        $tipo = $_SESSION['mensaje_tipo'] ?? 'info';
        $mensaje = $_SESSION['mensaje'];
        unset($_SESSION['mensaje'], $_SESSION['mensaje_tipo']);
        
        $iconos = [
            'success' => '✅',
            'error'   => '❌',
            'warning' => '⚠️',
            'info'    => 'ℹ️'
        ];
        $icono = $iconos[$tipo] ?? 'ℹ️';
        
        echo '<div class="alerta alerta-' . $tipo . '" id="alerta-flash">
                <span class="alerta-icono">' . $icono . '</span>
                <span class="alerta-texto">' . htmlspecialchars($mensaje) . '</span>
                <button class="alerta-cerrar" onclick="this.parentElement.remove()">✕</button>
              </div>';
    }
}

/**
 * Establecer mensaje flash en la sesión
 */
function setMensaje($mensaje, $tipo = 'info') {
    $_SESSION['mensaje'] = $mensaje;
    $_SESSION['mensaje_tipo'] = $tipo;
}

// =====================================================
// INCLUIR FUNCIONES COMUNES Y AUTENTICACIÓN
// =====================================================
require_once __DIR__ . '/funciones.php';
require_once __DIR__ . '/auth.php';
