<?php
/**
 * ESMAR BURGER - Acciones del Carrito (Endpoint JSON)
 * Avance 2 - Ingeniería Web
 */
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$action = $_POST['action'] ?? '';
$prodId = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$qty = isset($_POST['qty']) ? (int)$_POST['qty'] : 1;

if ($prodId <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de producto no válido']);
    exit;
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$success = false;
$message = '';

switch ($action) {
    case 'add':
        if (isset($_SESSION['cart'][$prodId])) {
            $_SESSION['cart'][$prodId] += $qty;
        } else {
            $_SESSION['cart'][$prodId] = $qty;
        }
        $success = true;
        $message = 'Producto añadido';
        break;

    case 'update':
        if ($qty > 0) {
            $_SESSION['cart'][$prodId] = $qty;
            $success = true;
            $message = 'Cantidad actualizada';
        } else {
            unset($_SESSION['cart'][$prodId]);
            $success = true;
            $message = 'Producto eliminado del carrito';
        }
        break;

    case 'remove':
        if (isset($_SESSION['cart'][$prodId])) {
            unset($_SESSION['cart'][$prodId]);
            $success = true;
            $message = 'Producto eliminado';
        }
        break;

    default:
        $message = 'Acción desconocida';
        break;
}

// Calcular total de items
$totalItems = 0;
foreach ($_SESSION['cart'] as $q) {
    $totalItems += $q;
}

echo json_encode([
    'success' => $success,
    'message' => $message,
    'totalItems' => $totalItems
]);
exit;
?>
