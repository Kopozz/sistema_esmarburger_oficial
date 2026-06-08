<?php
/**
 * ESMAR BURGER - Configuración General
 * Avance 2 - Ingeniería Web
 */

// ---------- Sesión (configurada antes de iniciar) ----------
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.save_path', '/tmp');
    ini_set('session.cookie_lifetime', '86400');
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
define('DB_DRIVER', getenv('DB_DRIVER') ?: 'mysql');
define('DB_SQLITE_FILE', __DIR__ . '/database/esmar_burger.db');

$dbUrl = getenv('DATABASE_URL');
if ($dbUrl) {
    $dbparts = parse_url($dbUrl);
    define('DB_HOST', $dbparts['host'] ?? 'localhost');
    define('DB_PORT', $dbparts['port'] ?? null);
    define('DB_USER', $dbparts['user'] ?? 'root');
    define('DB_PASS', $dbparts['pass'] ?? '');
    define('DB_NAME', ltrim($dbparts['path'] ?? '/esmar_burger', '/'));
    define('DB_DRIVER_FINAL', strpos($dbUrl, 'postgres') !== false ? 'pgsql' : DB_DRIVER);
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
            if (!file_exists($dbDir)) mkdir($dbDir, 0777, true);
            $pdo = new PDO('sqlite:' . DB_SQLITE_FILE);
        } elseif (DB_DRIVER_FINAL === 'pgsql') {
            $dsn = "pgsql:host=" . DB_HOST;
            if (defined('DB_PORT') && DB_PORT) $dsn .= ";port=" . DB_PORT;
            $dsn .= ";dbname=" . DB_NAME;
            $pdo = new PDO($dsn, DB_USER, DB_PASS);
        } else {
            $dsn = "mysql:host=" . DB_HOST;
            if (defined('DB_PORT') && DB_PORT) $dsn .= ";port=" . DB_PORT;
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

// =============================================================
// COOKIES DE RESPALDO PARA VERCEL SERVERLESS
// Las sesiones PHP no siempre persisten entre instancias Lambda.
// Usamos cookies firmadas como respaldo para auth y carrito.
// =============================================================

// --- Auth cookie ---
function _loadAuthFromCookie() {
    // Si la cookie dice LOGGED_OUT, el usuario se deslogueó → no restaurar
    if (isset($_COOKIE['esmar_uid']) && $_COOKIE['esmar_uid'] === 'LOGGED_OUT') {
        unset($_SESSION['user_id'], $_SESSION['user_nombre'], $_SESSION['user_rol']);
        return;
    }
    // Restaurar sesión desde cookie si la sesión está vacía
    if (!isset($_SESSION['user_id']) && isset($_COOKIE['esmar_uid'])) {
        $raw = base64_decode($_COOKIE['esmar_uid']);
        $parts = explode('|', $raw, 3);
        if (count($parts) === 3 && is_numeric($parts[0])) {
            $_SESSION['user_id']     = (int)$parts[0];
            $_SESSION['user_rol']    = $parts[1];
            $_SESSION['user_nombre'] = $parts[2];
        }
    }
}

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
    // Usar 'LOGGED_OUT' como marcador en lugar de borrar la cookie
    // Esto garantiza que aunque la cookie persista, la sesión no se restaure
    setcookie('esmar_uid', 'LOGGED_OUT', [
        'expires'  => time() + 86400,
        'path'     => '/',
        'secure'   => true,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

// --- Cart cookie ---
function _loadCartFromCookie() {
    if (empty($_SESSION['cart']) && isset($_COOKIE['esmar_cart'])) {
        $raw  = base64_decode($_COOKIE['esmar_cart']);
        $cart = json_decode($raw, true);
        if (is_array($cart)) {
            $_SESSION['cart'] = $cart;
        }
    }
}

function saveCartCookie() {
    $cart = $_SESSION['cart'] ?? [];
    $data = base64_encode(json_encode($cart));
    setcookie('esmar_cart', $data, [
        'expires'  => time() + 86400,
        'path'     => '/',
        'secure'   => true,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

function clearCartCookie() {
    setcookie('esmar_cart', '', [
        'expires'  => time() - 3600,
        'path'     => '/',
        'secure'   => true,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    unset($_SESSION['cart']);
}

// Cargar datos desde cookies al inicio de cada request
_loadAuthFromCookie();
_loadCartFromCookie();

// ---------- Helpers de autenticación ----------
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
        header('Location: /login.php');
        exit;
    }
    if (!isAdmin()) {
        // Logueado pero no admin → inicio (evita loop redirect)
        header('Location: /');
        exit;
    }
}

// ---------- Inicializar DB si es SQLite y no existe ----------
if (!file_exists(DB_SQLITE_FILE) && DB_DRIVER === 'sqlite') {
    require_once __DIR__ . '/init_db.php';
}
?>
