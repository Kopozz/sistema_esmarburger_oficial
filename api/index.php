<?php
/**
 * ESMAR-BURGER — Vercel Serverless Entrypoint & Router
 */

// Obtener la URI solicitada
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Si es un archivo estático, no procesar con PHP
if (preg_match('/\.(?:png|jpg|jpeg|gif|css|js|ico|svg)$/', $uri)) {
    return false;
}

// Mapear al archivo físico en la raíz del proyecto
$file = dirname(__DIR__) . $uri;

if (is_dir($file)) {
    $file = rtrim($file, '/') . '/index.php';
} else if (!file_exists($file) && file_exists($file . '.php')) {
    $file .= '.php';
}

if (file_exists($file) && is_file($file)) {
    // Cambiar al directorio del archivo para que los require/include relativos funcionen
    chdir(dirname($file));
    require $file;
} else {
    http_response_code(404);
    echo "<h1>404 Not Found</h1>";
    echo "<p>El recurso solicitado <strong>" . htmlspecialchars($uri) . "</strong> no existe en este servidor.</p>";
}
