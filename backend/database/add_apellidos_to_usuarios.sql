-- Agregar campo apellidos a la tabla usuarios
-- Fecha: 2026-01-11

USE boxevent;

-- Verificar si ya existe el campo antes de agregarlo
ALTER TABLE usuarios
ADD COLUMN IF NOT EXISTS apellidos VARCHAR(100) DEFAULT NULL
AFTER nombre;

-- Verificar que se agregó correctamente
DESCRIBE usuarios;
