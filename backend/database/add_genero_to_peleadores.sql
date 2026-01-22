-- ========================================
-- MIGRACIÓN: Agregar campos a peleadores y usuarios
-- ========================================

USE boxevent;

-- ========================================
-- 1. Agregar columna género a la tabla peleadores
-- ========================================
ALTER TABLE peleadores
ADD COLUMN genero ENUM('masculino', 'femenino') NOT NULL DEFAULT 'masculino'
AFTER altura;

-- Agregar índice para búsquedas por género
ALTER TABLE peleadores
ADD INDEX idx_genero (genero);

-- ========================================
-- 2. Agregar columna apellidos a la tabla usuarios
-- ========================================
ALTER TABLE usuarios
ADD COLUMN apellidos VARCHAR(100) NULL
AFTER nombre;

-- ========================================
-- Verificar las estructuras actualizadas
-- ========================================
DESCRIBE peleadores;
DESCRIBE usuarios;
