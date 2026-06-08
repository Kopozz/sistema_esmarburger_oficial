<?php
/**
 * ESMAR BURGER - Configuración General
 * Avance 2 - Ingeniería Web
 */

// Configurar sesión ANTES de iniciarla
// En Vercel serverless, /tmp es el único directorio escribible
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.save_path', '/tmp');
    ini_set('session.cookie_lifetime', '86400');   // 24h
    ini_set('session.gc_maxlifetime', '86400');
    session_set_cookie_params([
        'lifetime' => 86400,
        'path'     => '/',
        'secure'   => true,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// ---------- Configuración de la base de datos ----------
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

// ---------- Conexión PDO ----------
function getDBConnection() {
    try {
        if (DB_DRIVER_FINAL === 'sqlite') {
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

// ---------- Helpers de autenticación ----------
// Usa session Y cookie de respaldo para Vercel serverless
function _loadAuthFromCookie() {
    if (!isset($_SESSION['user_id']) && isset($_COOKIE['esmar_uid'])) {
        // Restaurar sesión desde cookie firmada (base64 de user_id|user_rol|user_nombre)
        $raw = base64_decode($_COOKIE['esmar_uid']);
        $parts = explode('|', $raw);
        if (count($parts) === 3) {
            $_SESSION['user_id']     = (int)$parts[0];
            $_SESSION['user_rol']    = $parts[1];
            $_SESSION['user_nombre'] = $parts[2];
        }
    }
}
_loadAuthFromCookie();

function setAuthCookie($userId, $rol, $nombre) {
    $data = base64_encode($userId . '|' . $rol . '|' . $nombre);
    setcookie('esmar_uid', $data, [
        'expires'  => time() + 86400,
        'path'     => '/',
        'secure'   => true,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

function clearAuthCookie() {
    setcookie('esmar_uid', '', ['expires' => time() - 3600, 'path' => '/', 'secure' => true, 'httponly' => true, 'samesite' => 'Lax']);
}

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
    if (!isLoggedIn()) {
        // No está logueado en absoluto → login
        header('Location: /login.php');
        exit;
    }
    if (!isAdmin()) {
        // Logueado pero no es admin → inicio (evita loop con login)
        header('Location: /');
        exit;
    }
}

// Inicializar base de datos automáticamente si es necesario
if (!file_exists(DB_SQLITE_FILE) && DB_DRIVER === 'sqlite') {
    require_once __DIR__ . '/init_db.php';
}
?>
