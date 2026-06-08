-- ==========================================
-- ESTRUCTURA DE BASE DE DATOS - ESMAR BURGER
-- Avance 2 - Ingeniería Web
-- ==========================================

-- -----------------------------------------------------
-- OPCIÓN A: CÓDIGO POSTGRESQL (Activo en Neon / Supabase)
-- -----------------------------------------------------

-- 1. Tabla de Usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol VARCHAR(20) CHECK (rol IN ('admin', 'cliente')) NOT NULL DEFAULT 'cliente',
    telefono VARCHAR(20),
    direccion VARCHAR(255),
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Tabla de Productos (Platos del menú)
CREATE TABLE IF NOT EXISTS productos (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10, 2) NOT NULL,
    categoria VARCHAR(50) NOT NULL,
    imagen VARCHAR(100)
);

-- 3. Tabla de Pedidos
CREATE TABLE IF NOT EXISTS pedidos (
    id SERIAL PRIMARY KEY,
    usuario_id INT,
    total DECIMAL(10, 2) NOT NULL,
    direccion_entrega VARCHAR(255) NOT NULL,
    telefono_contacto VARCHAR(20) NOT NULL,
    metodo_pago VARCHAR(50) NOT NULL,
    estado VARCHAR(20) CHECK (estado IN ('pendiente', 'preparando', 'en camino', 'entregado')) NOT NULL DEFAULT 'pendiente',
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- 4. Tabla de Detalles del Pedido
CREATE TABLE IF NOT EXISTS pedido_detalles (
    id SERIAL PRIMARY KEY,
    pedido_id INT,
    producto_id INT,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE SET NULL
);

-- Semilla: Usuarios de Prueba
INSERT INTO usuarios (nombre, email, password, rol, telefono, direccion) VALUES 
('Administrador Esmar', 'admin@esmarburger.com', '$2y$10$xnTM1gq5sR7OXDgoh7VtrOsJHCwLMzbdjNo38K5mbL0PLjGptN9Va', 'admin', '935550240', 'Av. Central 123'),
('Juan Pérez', 'cliente@gmail.com', '$2y$10$U.NzAIOkfldwrPJN7gDVCubNldMkXASNxsfkz9lEsWo.yzx0wRhg6', 'cliente', '921157440', 'Calle Las Flores 456')
ON CONFLICT (email) DO NOTHING;

-- Semilla: Productos del menú
INSERT INTO productos (nombre, descripcion, precio, categoria, imagen) VALUES
('Hawaiana', 'Carne + queso + jamón + piña + lechuga + tomate + papas fritas', 12.00, 'Hamburguesas', 'hawaiana.jpg'),
('Americana', 'Carne + queso + jamón + lechuga + tomate + papas fritas', 10.00, 'Hamburguesas', 'americana.jpg'),
('A lo Pobre', 'Maduro + carne + huevo + lechuga + tomate + papas fritas', 9.00, 'Hamburguesas', 'pobre.jpg'),
('Cheese', 'Carne + queso edam + lechuga + tomate + papas fritas', 7.00, 'Hamburguesas', 'cheese.jpg'),
('Royal', 'Carne + huevo + lechuga + tomate + papas fritas', 7.00, 'Hamburguesas', 'royal.jpg'),
('Clásica Burger', 'Carne + lechuga + tomate + papas fritas', 6.00, 'Hamburguesas', 'clasica.jpg'),
('1/4 de Broaster', '2 piezas de broaster + guarnición a elegir + ensalada', 18.00, 'Broaster', 'un_cuarto.jpg'),
('1/8 de Broaster', 'Broaster + ensalada + guarnición a elegir', 10.00, 'Broaster', 'un_octavo.jpg'),
('Mostrito', 'Broaster + chaufa + guarnición a elegir + ensalada', 12.00, 'Broaster', 'mostrito.jpg'),
('Salchipapa Clásica', 'Salchicha + papa + ensalada', 7.00, 'Salchipapas', 'salchi_clasica.jpg'),
('Choripapa', 'Chorizo parrillero + papa + ensalada', 8.00, 'Salchipapas', 'choripapa.jpg'),
('Salchipapa Especial', 'Salchicha + papa + huevo + chorizo parrillero', 10.00, 'Salchipapas', 'salchi_especial.jpg'),
('Salchibroaster', 'Salchicha + papa + ensalada + broaster', 11.00, 'Salchipapas', 'salchibroaster.jpg'),
('Salchisuprema', 'Papa + chaufa + salchicha + chorizo parrillero + huevo + tiras de pollo frito + ensalada', 18.00, 'Salchipapas', 'salchisuprema.jpg'),
('Combo Patas', '2 choriburgers + porcion de papas + 2 gaseosas', 25.00, 'Combos', 'combo_patas.jpg'),
('Combo Supremo', '2 hamburguesa a lo pobre + salchipapa + una pieza de broaster', 30.00, 'Combos', 'combo_supremo.jpg');


-- -----------------------------------------------------
-- OPCIÓN B: CÓDIGO MYSQL / MARIADB (Para XAMPP / Local)
-- -----------------------------------------------------
/*
CREATE DATABASE IF NOT EXISTS esmar_burger CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE esmar_burger;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'cliente') NOT NULL DEFAULT 'cliente',
    telefono VARCHAR(20),
    direccion VARCHAR(255),
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10, 2) NOT NULL,
    categoria VARCHAR(50) NOT NULL,
    imagen VARCHAR(100)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    total DECIMAL(10, 2) NOT NULL,
    direccion_entrega VARCHAR(255) NOT NULL,
    telefono_contacto VARCHAR(20) NOT NULL,
    metodo_pago VARCHAR(50) NOT NULL,
    estado ENUM('pendiente', 'preparando', 'en camino', 'entregado') NOT NULL DEFAULT 'pendiente',
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS pedido_detalles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT,
    producto_id INT,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE SET NULL
) ENGINE=InnoDB;
*/
