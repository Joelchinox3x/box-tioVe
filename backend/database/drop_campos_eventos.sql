-- Eliminar campos obsoletos de la tabla eventos
-- Estos campos serán reemplazados por el sistema de tipos_boleto

ALTER TABLE eventos DROP COLUMN precio_entrada_general;
ALTER TABLE eventos DROP COLUMN precio_entrada_vip;
ALTER TABLE eventos DROP COLUMN entradas_vendidas;
