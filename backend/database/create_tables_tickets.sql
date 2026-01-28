-- SISTEMA DE VENTA DE BOLETOS - BOX TIOVE
-- Creación de tablas para el sistema completo

-- 1. TABLA DE EVENTOS
CREATE TABLE IF NOT EXISTS eventos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(200) NOT NULL,
    descripcion TEXT,
    fecha DATE NOT NULL,
    hora TIME,
    direccion VARCHAR(300),
    ciudad VARCHAR(100),
    imagen_banner VARCHAR(500),
    estado ENUM('proximamente','en_curso','finalizado','cancelado') DEFAULT 'proximamente',
    precio_inscripcion_peleador DECIMAL(10,2) DEFAULT 20.00,
    capacidad_total INT DEFAULT 500,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_estado (estado),
    INDEX idx_fecha (fecha)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. TABLA DE TIPOS DE BOLETO (configurable desde admin)
CREATE TABLE IF NOT EXISTS tipos_boleto (
    id INT AUTO_INCREMENT PRIMARY KEY,
    evento_id INT NOT NULL,
    nombre VARCHAR(100) NOT NULL COMMENT 'General, VIP, Ringside, Mesa VIP',
    precio DECIMAL(10, 2) NOT NULL,
    cantidad_total INT NOT NULL DEFAULT 0,
    cantidad_vendida INT NOT NULL DEFAULT 0,
    color_hex VARCHAR(7) DEFAULT '#FFD700' COMMENT 'Color para diseño del boleto PDF',
    descripcion TEXT COMMENT 'Incluye bebida gratis, etc.',
    orden INT DEFAULT 0 COMMENT 'Orden de visualización',
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (evento_id) REFERENCES eventos(id) ON DELETE CASCADE,
    INDEX idx_evento (evento_id),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. TABLA DE VENDEDORES (tiendas/personas que ayudan a vender)
CREATE TABLE IF NOT EXISTS vendedores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    tipo ENUM('tienda', 'vendedor_individual') NOT NULL,
    codigo_vendedor VARCHAR(50) UNIQUE NOT NULL COMMENT 'TIENDA001, VEND042',
    telefono VARCHAR(20),
    email VARCHAR(255),
    comision_porcentaje DECIMAL(5, 2) DEFAULT 10.00 COMMENT 'Porcentaje de comisión por venta',
    estado ENUM('activo', 'inactivo') DEFAULT 'activo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_codigo (codigo_vendedor),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. TABLA DE BOLETOS VENDIDOS (la principal)
CREATE TABLE IF NOT EXISTS boletos_vendidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    evento_id INT NOT NULL,
    tipo_boleto_id INT NOT NULL,
    vendedor_id INT NULL COMMENT 'NULL si es venta directa',

    -- Datos del comprador (ACTUALIZADOS con DNI)
    comprador_nombres_apellidos VARCHAR(255) NOT NULL,
    comprador_telefono VARCHAR(20) NOT NULL,
    comprador_dni VARCHAR(8) NOT NULL COMMENT 'DNI de 8 dígitos',

    -- Detalles de la venta
    cantidad INT DEFAULT 1,
    precio_total DECIMAL(10, 2) NOT NULL,
    codigo_qr VARCHAR(100) UNIQUE NOT NULL COMMENT 'BOX-EJD-2026-001234',

    -- Método de pago
    metodo_pago ENUM('yape', 'transferencia', 'efectivo') DEFAULT 'yape',
    comprobante_pago VARCHAR(500) COMMENT 'URL de imagen del comprobante',

    -- Estados
    estado_pago ENUM('pendiente', 'verificado', 'rechazado') DEFAULT 'pendiente',
    estado_boleto ENUM('activo', 'usado', 'cancelado') DEFAULT 'activo',

    -- Fechas importantes
    fecha_compra TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_validacion TIMESTAMP NULL COMMENT 'Cuando admin aprueba pago',
    fecha_uso TIMESTAMP NULL COMMENT 'Cuando escanean QR en entrada',

    -- Otros
    observaciones TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (evento_id) REFERENCES eventos(id) ON DELETE CASCADE,
    FOREIGN KEY (tipo_boleto_id) REFERENCES tipos_boleto(id) ON DELETE RESTRICT,
    FOREIGN KEY (vendedor_id) REFERENCES vendedores(id) ON DELETE SET NULL,

    INDEX idx_evento (evento_id),
    INDEX idx_codigo_qr (codigo_qr),
    INDEX idx_estado_pago (estado_pago),
    INDEX idx_estado_boleto (estado_boleto),
    INDEX idx_comprador_dni (comprador_dni),
    INDEX idx_fecha_compra (fecha_compra)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. TABLA DE VENTAS POR VENDEDOR (para reportes de comisiones)
CREATE TABLE IF NOT EXISTS ventas_vendedor (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vendedor_id INT NOT NULL,
    boleto_id INT NOT NULL,
    comision_monto DECIMAL(10, 2) NOT NULL,
    pagado TINYINT(1) DEFAULT 0,
    fecha_pago TIMESTAMP NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (vendedor_id) REFERENCES vendedores(id) ON DELETE CASCADE,
    FOREIGN KEY (boleto_id) REFERENCES boletos_vendidos(id) ON DELETE CASCADE,

    INDEX idx_vendedor (vendedor_id),
    INDEX idx_pagado (pagado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- VISTA ÚTIL: Cantidad disponible por tipo de boleto
CREATE OR REPLACE VIEW vista_boletos_disponibles AS
SELECT
    tb.id,
    tb.evento_id,
    e.nombre as evento_nombre,
    tb.nombre as tipo_nombre,
    tb.precio,
    tb.cantidad_total,
    tb.cantidad_vendida,
    (tb.cantidad_total - tb.cantidad_vendida) as cantidad_disponible,
    tb.color_hex,
    tb.descripcion,
    tb.activo
FROM tipos_boleto tb
JOIN eventos e ON tb.evento_id = e.id
WHERE tb.activo = 1 AND e.estado = 'proximamente';
