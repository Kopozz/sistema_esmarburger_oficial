<?php
/**
 * ESMAR-BURGER — Funciones Utilitarias
 */

/**
 * Limpiar input contra XSS
 */
function limpiar($dato) {
    return htmlspecialchars(trim($dato), ENT_QUOTES, 'UTF-8');
}

/**
 * Formatear precio con moneda
 */
function formatoPrecio($precio) {
    return MONEDA . ' ' . number_format($precio, 2);
}

/**
 * Formatear fecha legible
 */
function formatoFecha($fecha) {
    $timestamp = strtotime($fecha);
    $meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
    $mes = $meses[date('n', $timestamp) - 1];
    return date('d', $timestamp) . ' ' . $mes . ' ' . date('Y', $timestamp) . ', ' . date('h:i A', $timestamp);
}

/**
 * Formatear estado del pedido con clase CSS
 */
function estadoPedido($estado) {
    $estados = [
        'pendiente'  => ['texto' => 'Pendiente',  'clase' => 'estado-pendiente',  'icono' => '⏳'],
        'confirmado' => ['texto' => 'Confirmado', 'clase' => 'estado-confirmado', 'icono' => '✅'],
        'preparando' => ['texto' => 'Preparando', 'clase' => 'estado-preparando', 'icono' => '👨‍🍳'],
        'en_camino'  => ['texto' => 'En Camino',  'clase' => 'estado-encamino',   'icono' => '🛵'],
        'entregado'  => ['texto' => 'Entregado',  'clase' => 'estado-entregado',  'icono' => '📦'],
        'cancelado'  => ['texto' => 'Cancelado',  'clase' => 'estado-cancelado',  'icono' => '❌']
    ];
    
    $info = $estados[$estado] ?? ['texto' => $estado, 'clase' => '', 'icono' => '❓'];
    return '<span class="badge ' . $info['clase'] . '">' . $info['icono'] . ' ' . $info['texto'] . '</span>';
}

/**
 * Obtener conteo del carrito
 */
function conteoCarrito() {
    if (!isset($_SESSION['carrito'])) return 0;
    $total = 0;
    foreach ($_SESSION['carrito'] as $item) {
        $total += $item['cantidad'];
    }
    return $total;
}

/**
 * Obtener total del carrito
 */
function totalCarrito() {
    if (!isset($_SESSION['carrito'])) return 0;
    $total = 0;
    foreach ($_SESSION['carrito'] as $item) {
        $total += $item['precio'] * $item['cantidad'];
    }
    return $total;
}

/**
 * Generar paginación
 */
function paginacion($total_registros, $por_pagina, $pagina_actual, $url_base) {
    $total_paginas = ceil($total_registros / $por_pagina);
    if ($total_paginas <= 1) return '';
    
    $html = '<div class="paginacion">';
    
    if ($pagina_actual > 1) {
        $html .= '<a href="' . $url_base . '?pagina=' . ($pagina_actual - 1) . '" class="pag-btn">← Anterior</a>';
    }
    
    for ($i = 1; $i <= $total_paginas; $i++) {
        $activa = ($i == $pagina_actual) ? ' pag-activa' : '';
        $html .= '<a href="' . $url_base . '?pagina=' . $i . '" class="pag-btn' . $activa . '">' . $i . '</a>';
    }
    
    if ($pagina_actual < $total_paginas) {
        $html .= '<a href="' . $url_base . '?pagina=' . ($pagina_actual + 1) . '" class="pag-btn">Siguiente →</a>';
    }
    
    $html .= '</div>';
    return $html;
}

/**
 * Método de pago con icono
 */
function metodoPago($metodo) {
    $metodos = [
        'efectivo' => '💵 Efectivo',
        'yape'     => '📱 Yape',
        'plin'     => '📱 Plin',
        'tarjeta'  => '💳 Tarjeta'
    ];
    return $metodos[$metodo] ?? $metodo;
}
