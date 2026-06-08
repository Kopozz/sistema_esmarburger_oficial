<?php
/**
 * ESMAR BURGER - Configuración General
 * Avance 2 - Ingeniería Web
 */

// Iniciar sesión si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuración de la base de datos
// Prioriza variables de entorno para producción (Vercel).
define('DB_DRIVER', getenv('DB_DRIVER') ?: 'mysql'); // 'sqlite', 'mysql' o 'pgsql'

define('DB_SQLITE_FILE', __DIR__ . '/database/esmar_burger.db');

$dbUrl = getenv('DATABASE_URL');
if ($dbUrl) {
    $dbparts = parse_url($dbUrl);
    define('DB_HOST', $dbparts['host'] ?? 'localhost');
    define('DB_PORT', $dbparts['port'] ?? null);
    define('DB_USER', $dbparts['user'] ?? 'root');
    define('DB_PASS', $dbparts['pass'] ?? '');
    define('DB_NAME', ltrim($dbparts['path'] ?? '/esmar_burger', '/'));
    if (strpos($dbUrl, 'postgres') !== false) {
        define('DB_DRIVER_FINAL', 'pgsql');
    } else {
        define('DB_DRIVER_FINAL', DB_DRIVER);
    }
} else {
    define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
    define('DB_PORT', getenv('DB_PORT') ?: null);
    define('DB_NAME', getenv('DB_NAME') ?: 'esmar_burger');
    define('DB_USER', getenv('DB_USER') ?: 'root');
    define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
    define('DB_DRIVER_FINAL', DB_DRIVER);
}

// Conexión PDO
function getDBConnection() {
    try {
        if (DB_DRIVER_FINAL === 'sqlite') {
            // Asegurarse de que el directorio de la base de datos exista
            $dbDir = dirname(DB_SQLITE_FILE);
            if (!file_exists($dbDir)) {
                mkdir($dbDir, 0777, true);
            }
            $pdo = new PDO('sqlite:' . DB_SQLITE_FILE);
        } elseif (DB_DRIVER_FINAL === 'pgsql') {
            $dsn = "pgsql:host=" . DB_HOST;
            if (defined('DB_PORT') && DB_PORT) {
                $dsn .= ";port=" . DB_PORT;
            }
            $dsn .= ";dbname=" . DB_NAME;
            $pdo = new PDO($dsn, DB_USER, DB_PASS);
        } else {
            $dsn = "mysql:host=" . DB_HOST;
            if (defined('DB_PORT') && DB_PORT) {
                $dsn .= ";port=" . DB_PORT;
            }
            $dsn .= ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $pdo = new PDO($dsn, DB_USER, DB_PASS);
        }
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch (PDOException $e) {
        die("Error de conexión a la base de datos: " . $e->getMessage());
    }
}

// Helpers de sesión y roles
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_rol']) && $_SESSION['user_rol'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /login.php');
        exit;
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        header('Location: /login.php');
        exit;
    }
}

// Inicializar base de datos automáticamente si es necesario
if (!file_exists(DB_SQLITE_FILE) && DB_DRIVER === 'sqlite') {
    require_once __DIR__ . '/init_db.php';
}
?>
