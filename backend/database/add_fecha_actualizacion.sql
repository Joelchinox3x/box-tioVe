-- Agregar campo fecha_actualizacion a la tabla eventos
ALTER TABLE eventos
ADD COLUMN fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
