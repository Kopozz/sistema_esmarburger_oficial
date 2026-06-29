<?php
/**
 * ESMAR-BURGER — API para agregar al carrito vía AJAX
 */
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $producto_id = (int)($input['producto_id'] ?? 0);
    
    if ($producto_id > 0) {
        if (!isset($_SESSION['carrito'])) {
            $_SESSION['carrito'] = [];
        }
        
        $stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ? AND disponible = 1");
        $stmt->execute([$producto_id]);
        $producto = $stmt->fetch();
        
        if ($producto) {
            if (isset($_SESSION['carrito'][$producto_id])) {
                $_SESSION['carrito'][$producto_id]['cantidad']++;
            } else {
                $_SESSION['carrito'][$producto_id] = [
                    'id' => $producto['id'],
                    'nombre' => $producto['nombre'],
                    'precio' => $producto['precio'],
                    'cantidad' => 1,
                    'imagen' => $producto['imagen']
                ];
            }
            
            // Calcular número total de items en el carrito
            $total_items = 0;
            foreach ($_SESSION['carrito'] as $item) {
                $total_items += $item['cantidad'];
            }
            
            echo json_encode([
                'success' => true,
                'mensaje' => '✅ ' . $producto['nombre'] . ' agregado al carrito.',
                'total_items' => $total_items
            ]);
            exit;
        }
    }
}

echo json_encode([
    'success' => false,
    'mensaje' => 'Error al agregar el producto.'
]);
exit;
