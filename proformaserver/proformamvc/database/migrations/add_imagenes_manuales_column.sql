-- Migración: Agregar campo imagenes_manuales a proforma_items
-- Fecha: 2025-12-30
-- Descripción: Permite guardar imágenes subidas manualmente en items de proforma

ALTER TABLE proforma_items
ADD COLUMN imagenes_manuales TEXT NULL
COMMENT 'JSON array con paths de imágenes subidas manualmente'
AFTER incluir_galeria;
