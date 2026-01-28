-- Migrar campos created_at/updated_at a español (fecha_creacion/fecha_actualizacion)
-- Y corregir la vista vista_boletos_disponibles

-- 1. Tabla tipos_boleto
ALTER TABLE tipos_boleto
CHANGE COLUMN created_at fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
CHANGE COLUMN updated_at fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- 2. Tabla vendedores
ALTER TABLE vendedores
CHANGE COLUMN created_at fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
CHANGE COLUMN updated_at fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- 3. Tabla boletos_vendidos
ALTER TABLE boletos_vendidos
CHANGE COLUMN created_at fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
CHANGE COLUMN updated_at fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- 4. Tabla ventas_vendedor
ALTER TABLE ventas_vendedor
CHANGE COLUMN created_at fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
CHANGE COLUMN updated_at fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- 5. Recrear vista con filtro correcto (proximamente en lugar de activo)
DROP VIEW IF EXISTS vista_boletos_disponibles;

CREATE VIEW vista_boletos_disponibles AS
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
