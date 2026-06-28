-- =====================================================
-- 🍔 ESMAR-BURGER — Base de Datos Completa
-- Proyecto Universitario — Ingeniería Web
-- Motor: MySQL / MariaDB (XAMPP)
-- =====================================================

-- Crear la base de datos (Ejecutar directamente sobre tu base de datos seleccionada)

-- =====================================================
-- 📋 TABLAS
-- =====================================================

-- Eliminar vistas previas si existen
DROP VIEW IF EXISTS vista_ventas_diarias;
DROP VIEW IF EXISTS vista_productos_mas_vendidos;
DROP VIEW IF EXISTS vista_pedidos_completos;
DROP VIEW IF EXISTS vista_inventario_bajo;

-- Eliminar tablas previas si existen (en orden inverso de dependencia)
DROP TABLE IF EXISTS pedido_detalles;
DROP TABLE IF EXISTS compra_detalles;
DROP TABLE IF EXISTS compras;
DROP TABLE IF EXISTS pedidos;
DROP TABLE IF EXISTS productos;
DROP TABLE IF EXISTS categorias;
DROP TABLE IF EXISTS proveedores;
DROP TABLE IF EXISTS insumos;
DROP TABLE IF EXISTS usuarios;

-- 1. Tabla de Usuarios (admin y clientes)
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'cliente') NOT NULL DEFAULT 'cliente',
    telefono VARCHAR(20),
    direccion VARCHAR(255),
    activo TINYINT(1) NOT NULL DEFAULT 1,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. Tabla de Categorías de productos
CREATE TABLE IF NOT EXISTS categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    icono VARCHAR(50) DEFAULT '🍔',
    activo TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

-- 3. Tabla de Productos (Menú)
CREATE TABLE IF NOT EXISTS productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    categoria_id INT,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10, 2) NOT NULL,
    imagen VARCHAR(255) DEFAULT 'default.jpg',
    disponible TINYINT(1) NOT NULL DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 4. Tabla de Pedidos (Ventas)
CREATE TABLE IF NOT EXISTS pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    subtotal DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    impuesto DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    total DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    direccion_entrega VARCHAR(255) NOT NULL,
    telefono_contacto VARCHAR(20) NOT NULL,
    metodo_pago ENUM('efectivo', 'yape', 'plin', 'tarjeta') NOT NULL DEFAULT 'efectivo',
    estado ENUM('pendiente', 'confirmado', 'preparando', 'en_camino', 'entregado', 'cancelado') NOT NULL DEFAULT 'pendiente',
    notas TEXT,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 5. Tabla de Detalles del Pedido
CREATE TABLE IF NOT EXISTS pedido_detalles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    producto_id INT,
    cantidad INT NOT NULL DEFAULT 1,
    precio_unitario DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 6. Tabla de Proveedores
CREATE TABLE IF NOT EXISTS proveedores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    ruc VARCHAR(20),
    telefono VARCHAR(20),
    email VARCHAR(100),
    direccion VARCHAR(255),
    activo TINYINT(1) NOT NULL DEFAULT 1,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 7. Tabla de Insumos (Inventario)
CREATE TABLE IF NOT EXISTS insumos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    unidad_medida VARCHAR(30) NOT NULL DEFAULT 'unidad',
    stock_actual DECIMAL(10, 2) NOT NULL DEFAULT 0,
    stock_minimo DECIMAL(10, 2) NOT NULL DEFAULT 5,
    precio_unitario DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    estado ENUM('disponible', 'bajo_stock', 'agotado') NOT NULL DEFAULT 'disponible',
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 8. Tabla de Compras (a proveedores)
CREATE TABLE IF NOT EXISTS compras (
    id INT AUTO_INCREMENT PRIMARY KEY,
    proveedor_id INT,
    usuario_id INT,
    total DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    estado ENUM('pendiente', 'recibida', 'cancelada') NOT NULL DEFAULT 'pendiente',
    notas TEXT,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (proveedor_id) REFERENCES proveedores(id) ON DELETE SET NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 9. Tabla de Detalles de Compra
CREATE TABLE IF NOT EXISTS compra_detalles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    compra_id INT NOT NULL,
    insumo_id INT,
    cantidad DECIMAL(10, 2) NOT NULL,
    precio_unitario DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (compra_id) REFERENCES compras(id) ON DELETE CASCADE,
    FOREIGN KEY (insumo_id) REFERENCES insumos(id) ON DELETE SET NULL
) ENGINE=InnoDB;


-- =====================================================
-- ⚡ TRIGGERS
-- =====================================================

DROP TRIGGER IF EXISTS trg_calcular_subtotal_detalle;
DROP TRIGGER IF EXISTS trg_actualizar_total_pedido;
DROP TRIGGER IF EXISTS trg_actualizar_stock_compra;
DROP TRIGGER IF EXISTS trg_actualizar_estado_insumo;

-- Trigger 1: Calcular subtotal automático al insertar detalle de pedido
DELIMITER //
CREATE TRIGGER trg_calcular_subtotal_detalle
BEFORE INSERT ON pedido_detalles
FOR EACH ROW
BEGIN
    SET NEW.subtotal = NEW.cantidad * NEW.precio_unitario;
END //
DELIMITER ;

-- Trigger 2: Actualizar total del pedido al insertar detalle
DELIMITER //
CREATE TRIGGER trg_actualizar_total_pedido
AFTER INSERT ON pedido_detalles
FOR EACH ROW
BEGIN
    UPDATE pedidos 
    SET subtotal = (SELECT COALESCE(SUM(subtotal), 0) FROM pedido_detalles WHERE pedido_id = NEW.pedido_id),
        impuesto = (SELECT COALESCE(SUM(subtotal), 0) * 0.18 FROM pedido_detalles WHERE pedido_id = NEW.pedido_id),
        total = (SELECT COALESCE(SUM(subtotal), 0) * 1.18 FROM pedido_detalles WHERE pedido_id = NEW.pedido_id)
    WHERE id = NEW.pedido_id;
END //
DELIMITER ;

-- Trigger 3: Actualizar stock de insumo al registrar compra recibida
DELIMITER //
CREATE TRIGGER trg_actualizar_stock_compra
AFTER INSERT ON compra_detalles
FOR EACH ROW
BEGIN
    UPDATE insumos 
    SET stock_actual = stock_actual + NEW.cantidad,
        precio_unitario = NEW.precio_unitario
    WHERE id = NEW.insumo_id;
END //
DELIMITER ;

-- Trigger 4: Actualizar estado del insumo según stock
DELIMITER //
CREATE TRIGGER trg_actualizar_estado_insumo
BEFORE UPDATE ON insumos
FOR EACH ROW
BEGIN
    IF NEW.stock_actual <= 0 THEN
        SET NEW.estado = 'agotado';
    ELSEIF NEW.stock_actual < NEW.stock_minimo THEN
        SET NEW.estado = 'bajo_stock';
    ELSE
        SET NEW.estado = 'disponible';
    END IF;
END //
DELIMITER ;


-- =====================================================
-- 👁️ VISTAS
-- =====================================================

-- Vista 1: Resumen de ventas diarias
CREATE OR REPLACE VIEW vista_ventas_diarias AS
SELECT 
    DATE(p.fecha) AS fecha,
    COUNT(p.id) AS total_pedidos,
    SUM(p.total) AS total_ventas,
    AVG(p.total) AS promedio_venta
FROM pedidos p
WHERE p.estado != 'cancelado'
GROUP BY DATE(p.fecha)
ORDER BY fecha DESC;

-- Vista 2: Productos más vendidos
CREATE OR REPLACE VIEW vista_productos_mas_vendidos AS
SELECT 
    pr.id,
    pr.nombre,
    pr.precio,
    c.nombre AS categoria,
    SUM(pd.cantidad) AS total_vendido,
    SUM(pd.subtotal) AS ingresos_totales
FROM pedido_detalles pd
INNER JOIN productos pr ON pd.producto_id = pr.id
LEFT JOIN categorias c ON pr.categoria_id = c.id
INNER JOIN pedidos p ON pd.pedido_id = p.id
WHERE p.estado != 'cancelado'
GROUP BY pr.id, pr.nombre, pr.precio, c.nombre
ORDER BY total_vendido DESC;

-- Vista 3: Pedidos completos con datos del cliente
CREATE OR REPLACE VIEW vista_pedidos_completos AS
SELECT 
    p.id AS pedido_id,
    p.fecha,
    p.estado,
    p.total,
    p.metodo_pago,
    p.direccion_entrega,
    p.telefono_contacto,
    u.nombre AS cliente_nombre,
    u.email AS cliente_email,
    (SELECT COUNT(*) FROM pedido_detalles WHERE pedido_id = p.id) AS cantidad_items
FROM pedidos p
LEFT JOIN usuarios u ON p.usuario_id = u.id
ORDER BY p.fecha DESC;

-- Vista 4: Inventario con stock bajo
CREATE OR REPLACE VIEW vista_inventario_bajo AS
SELECT 
    i.id,
    i.nombre,
    i.unidad_medida,
    i.stock_actual,
    i.stock_minimo,
    i.precio_unitario,
    i.estado,
    CASE 
        WHEN i.stock_actual <= 0 THEN 'AGOTADO'
        WHEN i.stock_actual < i.stock_minimo THEN 'BAJO STOCK'
        ELSE 'NORMAL'
    END AS alerta
FROM insumos i
ORDER BY i.stock_actual ASC;


-- =====================================================
-- 🔧 PROCEDIMIENTOS ALMACENADOS
-- =====================================================

DROP PROCEDURE IF EXISTS sp_registrar_pedido;
DROP PROCEDURE IF EXISTS sp_reporte_ventas;
DROP PROCEDURE IF EXISTS sp_actualizar_inventario;

-- Procedimiento 1: Registrar un pedido completo
DELIMITER //
CREATE PROCEDURE sp_registrar_pedido(
    IN p_usuario_id INT,
    IN p_direccion VARCHAR(255),
    IN p_telefono VARCHAR(20),
    IN p_metodo_pago VARCHAR(20),
    IN p_notas TEXT,
    OUT p_pedido_id INT
)
BEGIN
    INSERT INTO pedidos (usuario_id, direccion_entrega, telefono_contacto, metodo_pago, notas)
    VALUES (p_usuario_id, p_direccion, p_telefono, p_metodo_pago, p_notas);
    
    SET p_pedido_id = LAST_INSERT_ID();
END //
DELIMITER ;

-- Procedimiento 2: Reporte de ventas por rango de fechas
DELIMITER //
CREATE PROCEDURE sp_reporte_ventas(
    IN p_fecha_inicio DATE,
    IN p_fecha_fin DATE
)
BEGIN
    SELECT 
        DATE(p.fecha) AS dia,
        COUNT(p.id) AS pedidos,
        SUM(p.subtotal) AS subtotal,
        SUM(p.impuesto) AS impuesto,
        SUM(p.total) AS total
    FROM pedidos p
    WHERE DATE(p.fecha) BETWEEN p_fecha_inicio AND p_fecha_fin
      AND p.estado != 'cancelado'
    GROUP BY DATE(p.fecha)
    ORDER BY dia ASC;
END //
DELIMITER ;

-- Procedimiento 3: Actualizar inventario
DELIMITER //
CREATE PROCEDURE sp_actualizar_inventario(
    IN p_insumo_id INT,
    IN p_cantidad DECIMAL(10,2),
    IN p_operacion VARCHAR(10) -- 'sumar' o 'restar'
)
BEGIN
    IF p_operacion = 'sumar' THEN
        UPDATE insumos SET stock_actual = stock_actual + p_cantidad WHERE id = p_insumo_id;
    ELSEIF p_operacion = 'restar' THEN
        UPDATE insumos SET stock_actual = GREATEST(stock_actual - p_cantidad, 0) WHERE id = p_insumo_id;
    END IF;
END //
DELIMITER ;


-- =====================================================
-- 🌱 DATOS DE PRUEBA (SEEDS)
-- =====================================================

-- Usuarios de prueba (password: 123456)
INSERT INTO usuarios (nombre, email, password, rol, telefono, direccion) VALUES
('Administrador Esmar', 'admin@esmarburger.com', '$2y$10$oAn3tFhH6IuUAZ65QGBygevZWkqDXWQpjp69x9RsLVN2uk22LGlku', 'admin', '935550240', 'Av. Central 123'),
('Juan Pérez', 'cliente@gmail.com', '$2y$10$oAn3tFhH6IuUAZ65QGBygevZWkqDXWQpjp69x9RsLVN2uk22LGlku', 'cliente', '921157440', 'Calle Las Flores 456'),
('María García', 'maria@gmail.com', '$2y$10$oAn3tFhH6IuUAZ65QGBygevZWkqDXWQpjp69x9RsLVN2uk22LGlku', 'cliente', '987654321', 'Jr. Progreso 789');

-- Categorías
INSERT INTO categorias (nombre, descripcion, icono) VALUES
('Hamburguesas', 'Nuestras deliciosas hamburguesas artesanales', '🍔'),
('Broaster', 'Pollo broaster crujiente y jugoso', '🍗'),
('Salchipapas', 'Salchipapas con variedad de combinaciones', '🍟'),
('Combos', 'Combos familiares y para compartir', '🎉'),
('Bebidas', 'Refrescos, jugos y bebidas', '🥤');

-- Productos
INSERT INTO productos (categoria_id, nombre, descripcion, precio, imagen) VALUES
-- Hamburguesas
(1, 'Hawaiana', 'Carne + queso + jamón + piña + lechuga + tomate + papas fritas', 12.00, 'hawaiana.jpg'),
(1, 'Americana', 'Carne + queso + jamón + lechuga + tomate + papas fritas', 10.00, 'americana.jpg'),
(1, 'A lo Pobre', 'Maduro + carne + huevo + lechuga + tomate + papas fritas', 9.00, 'pobre.jpg'),
(1, 'Cheese Burger', 'Carne + queso edam + lechuga + tomate + papas fritas', 7.00, 'cheese.jpg'),
(1, 'Royal', 'Carne + huevo + lechuga + tomate + papas fritas', 7.00, 'royal.jpg'),
(1, 'Clásica Burger', 'Carne + lechuga + tomate + papas fritas', 6.00, 'clasica.jpg'),
-- Broaster
(2, '1/4 de Broaster', '2 piezas de broaster + guarnición a elegir + ensalada', 18.00, 'un_cuarto.jpg'),
(2, '1/8 de Broaster', 'Broaster + ensalada + guarnición a elegir', 10.00, 'un_octavo.jpg'),
(2, 'Mostrito', 'Broaster + chaufa + guarnición a elegir + ensalada', 12.00, 'mostrito.jpg'),
-- Salchipapas
(3, 'Salchipapa Clásica', 'Salchicha + papa + ensalada', 7.00, 'salchi_clasica.jpg'),
(3, 'Choripapa', 'Chorizo parrillero + papa + ensalada', 8.00, 'choripapa.jpg'),
(3, 'Salchipapa Especial', 'Salchicha + papa + huevo + chorizo parrillero', 10.00, 'salchi_especial.jpg'),
(3, 'Salchibroaster', 'Salchicha + papa + ensalada + broaster', 11.00, 'salchibroaster.jpg'),
(3, 'Salchisuprema', 'Papa + chaufa + salchicha + chorizo + huevo + tiras de pollo + ensalada', 18.00, 'salchisuprema.jpg'),
-- Combos
(4, 'Combo Patas', '2 choriburgers + porción de papas + 2 gaseosas', 25.00, 'combo_patas.jpg'),
(4, 'Combo Supremo', '2 hamburguesas a lo pobre + salchipapa + una pieza de broaster', 30.00, 'combo_supremo.jpg'),
-- Bebidas
(5, 'Gaseosa Personal', 'Coca-Cola, Inca Kola o Fanta 500ml', 3.00, 'gaseosa.jpg'),
(5, 'Chicha Morada', 'Vaso de chicha morada natural', 3.00, 'chicha.jpg'),
(5, 'Agua Mineral', 'Agua San Mateo 600ml', 2.00, 'agua.jpg');

-- Proveedores
INSERT INTO proveedores (nombre, ruc, telefono, email, direccion) VALUES
('Distribuidora San Fernando', '20100154308', '01-6303030', 'ventas@sanfernando.com', 'Av. Industrial 455, Ate'),
('Carnes del Norte SAC', '20567891234', '044-234567', 'contacto@carnesdelnorte.com', 'Jr. Unión 123, Trujillo'),
('Verduras Frescas EIRL', '10456789012', '999888777', 'info@verdurasfrescas.com', 'Mercado Mayorista, Lima'),
('Panificadora Los Andes', '20345678901', '01-4567890', 'pedidos@losandes.com', 'Av. Argentina 890, Callao');

-- Insumos
INSERT INTO insumos (nombre, unidad_medida, stock_actual, stock_minimo, precio_unitario) VALUES
('Carne de res molida', 'kg', 25.00, 10.00, 18.00),
('Pan de hamburguesa', 'unidad', 100.00, 30.00, 0.80),
('Queso edam', 'kg', 8.00, 3.00, 22.00),
('Lechuga', 'unidad', 15.00, 5.00, 1.50),
('Tomate', 'kg', 10.00, 4.00, 3.00),
('Papa', 'kg', 30.00, 10.00, 2.50),
('Aceite vegetal', 'litro', 12.00, 5.00, 7.50),
('Pollo entero', 'unidad', 20.00, 8.00, 15.00),
('Salchicha hot dog', 'paquete', 18.00, 5.00, 6.00),
('Chorizo parrillero', 'kg', 5.00, 3.00, 16.00),
('Jamón', 'kg', 4.00, 2.00, 14.00),
('Piña', 'unidad', 3.00, 2.00, 4.00),
('Huevo', 'unidad', 50.00, 20.00, 0.50),
('Plátano maduro', 'unidad', 12.00, 5.00, 0.80),
('Gaseosa 500ml', 'unidad', 48.00, 12.00, 1.80);

-- Compra de ejemplo
INSERT INTO compras (proveedor_id, usuario_id, total, estado, notas) VALUES
(1, 1, 590.00, 'recibida', 'Compra semanal de insumos principales');

INSERT INTO compra_detalles (compra_id, insumo_id, cantidad, precio_unitario, subtotal) VALUES
(1, 1, 10.00, 18.00, 180.00),
(1, 2, 50.00, 0.80, 40.00),
(1, 8, 10.00, 15.00, 150.00),
(1, 6, 20.00, 2.50, 50.00),
(1, 7, 10.00, 7.50, 75.00),
(1, 9, 10.00, 6.00, 60.00),
(1, 13, 30.00, 0.50, 15.00),
(1, 15, 20.00, 1.00, 20.00);

-- Pedido de ejemplo
INSERT INTO pedidos (usuario_id, subtotal, impuesto, total, direccion_entrega, telefono_contacto, metodo_pago, estado) VALUES
(2, 29.00, 5.22, 34.22, 'Calle Las Flores 456', '921157440', 'yape', 'entregado');

INSERT INTO pedido_detalles (pedido_id, producto_id, cantidad, precio_unitario, subtotal) VALUES
(1, 1, 1, 12.00, 12.00),
(1, 10, 1, 7.00, 7.00),
(1, 17, 2, 3.00, 6.00),
(1, 3, 1, 9.00, 9.00);
