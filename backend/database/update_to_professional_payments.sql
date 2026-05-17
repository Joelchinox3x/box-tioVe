-- ==========================================================
-- MIGRACIÓN PROFESIONAL: NORMALIZACIÓN DE MÉTODOS DE PAGO
-- ==========================================================

-- 1. Flexibilizar la columna metodo_pago (cambiar ENUM a VARCHAR)
-- Esto evita errores de "Data truncated" al usar nuevos métodos como 'plin'
ALTER TABLE boletos_vendidos MODIFY COLUMN metodo_pago VARCHAR(50) DEFAULT 'yape';

-- 2. Añadir la columna de ID para la relación profesional
-- Se añade después de la columna de texto original
ALTER TABLE boletos_vendidos ADD COLUMN metodo_pago_id INT NULL AFTER metodo_pago;

-- 3. Establecer la relación de Clave Foránea (Integridad Referencial)
-- Permite que si se borra un método de pago, el registro de boleto quede como NULL de forma segura
ALTER TABLE boletos_vendidos 
ADD CONSTRAINT fk_boleto_metodo_pago 
FOREIGN KEY (metodo_pago_id) REFERENCES metodos_pago(id) 
ON DELETE SET NULL;

-- 4. Sincronizar datos históricos
-- Vincula los boletos existentes con los IDs correspondientes basándose en el código de texto
UPDATE boletos_vendidos bv
INNER JOIN metodos_pago mp ON bv.metodo_pago = mp.codigo
SET bv.metodo_pago_id = mp.id
WHERE bv.metodo_pago_id IS NULL;

-- 5. Crear índice para mejorar el rendimiento de búsquedas por ID de pago
CREATE INDEX idx_metodo_pago_id ON boletos_vendidos(metodo_pago_id);

-- ==========================================================
-- FIN DE LA MIGRACIÓN
-- ==========================================================
