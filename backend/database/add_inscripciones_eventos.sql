-- ========================================
-- SISTEMA DE INSCRIPCIONES Y PAGOS
-- ========================================
-- Este script agrega:
-- 1. Campo precio_inscripcion_peleador a la tabla eventos
-- 2. Nueva tabla inscripciones_eventos para manejar pagos

USE boxevent;

-- ========================================
-- 1. AGREGAR CAMPO A EVENTOS
-- ========================================
-- Agregar el precio de inscripción que cada peleador debe pagar para participar
ALTER TABLE eventos
ADD COLUMN precio_inscripcion_peleador DECIMAL(10,2) DEFAULT 20.00
COMMENT 'Precio que paga cada peleador para participar en este evento (en soles)'
AFTER precio_entrada_vip;

-- Actualizar eventos existentes con precio por defecto
UPDATE eventos
SET precio_inscripcion_peleador = 20.00
WHERE precio_inscripcion_peleador IS NULL;

-- ========================================
-- 2. CREAR TABLA DE INSCRIPCIONES
-- ========================================
-- Relaciona peleadores con eventos y maneja el estado de pago
CREATE TABLE inscripciones_eventos (
    id INT AUTO_INCREMENT PRIMARY KEY,

    -- Relaciones
    peleador_id INT NOT NULL,
    evento_id INT NOT NULL,

    -- Estado de pago
    estado_pago ENUM('pendiente', 'pagado') DEFAULT 'pendiente',
    monto_pagado DECIMAL(10,2) NULL COMMENT 'Monto que pagó (puede ser diferente al precio del evento)',

    -- Fechas
    fecha_inscripcion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_pago TIMESTAMP NULL,

    -- Información de pago
    metodo_pago ENUM('efectivo', 'transferencia', 'yape', 'plin', 'deposito', 'otro') NULL COMMENT 'Método de pago utilizado',
    comprobante_pago VARCHAR(500) NULL COMMENT 'URL o path del comprobante de pago',

    -- Notas administrativas
    notas_admin TEXT NULL,

    -- Foreign Keys
    FOREIGN KEY (peleador_id) REFERENCES peleadores(id) ON DELETE CASCADE,
    FOREIGN KEY (evento_id) REFERENCES eventos(id) ON DELETE CASCADE,

    -- Un peleador solo puede inscribirse una vez por evento
    UNIQUE KEY unique_inscripcion (peleador_id, evento_id),

    -- Índices para búsquedas rápidas
    INDEX idx_estado_pago (estado_pago),
    INDEX idx_evento (evento_id),
    INDEX idx_peleador (peleador_id),
    INDEX idx_fecha_inscripcion (fecha_inscripcion)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Inscripciones de peleadores a eventos y control de pagos';

-- ========================================
-- 3. DATOS DE EJEMPLO (OPCIONAL)
-- ========================================
-- Puedes comentar esta sección si no quieres datos de ejemplo

-- Insertar inscripciones de ejemplo para peleadores ya aprobados
-- (solo si hay eventos y peleadores en la BD)
INSERT INTO inscripciones_eventos (peleador_id, evento_id, estado_pago, monto_pagado, fecha_pago, metodo_pago)
SELECT
    p.id as peleador_id,
    e.id as evento_id,
    'pagado' as estado_pago,
    e.precio_inscripcion_peleador as monto_pagado,
    NOW() as fecha_pago,
    'transferencia' as metodo_pago
FROM peleadores p
CROSS JOIN eventos e
WHERE p.estado_inscripcion = 'aprobado'
  AND e.estado = 'proximamente'
LIMIT 5;

-- ========================================
-- 4. VERIFICACIÓN
-- ========================================
-- Ver la nueva estructura
DESCRIBE eventos;
DESCRIBE inscripciones_eventos;

-- Ver resumen de inscripciones
SELECT
    'RESUMEN DE INSCRIPCIONES' as info,
    COUNT(*) as total_inscripciones,
    SUM(CASE WHEN estado_pago = 'pagado' THEN 1 ELSE 0 END) as pagadas,
    SUM(CASE WHEN estado_pago = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
    SUM(CASE WHEN estado_pago = 'pagado' THEN monto_pagado ELSE 0 END) as total_recaudado
FROM inscripciones_eventos;
