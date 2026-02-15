-- ========================================
-- MIGRACIÓN: SISTEMA DE CONFIGURACIÓN
-- ========================================

-- 1. Crear tabla de configuraciones del sistema
CREATE TABLE IF NOT EXISTS system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    description VARCHAR(255),
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Insertar configuración inicial para el Borrador de Fondo
-- Valores posibles: 'debug' (Modo V3 actual), 'invisible' (Producción silenciosa)
INSERT INTO system_settings (setting_key, setting_value, description)
VALUES ('bg_remover_mode', 'debug', 'Controla la visibilidad del borrador de fondos: debug (logs visibles) o invisible (spinner nativo)')
ON DUPLICATE KEY UPDATE setting_value = 'debug';

-- Ejemplo de cómo actualizarlo luego:
-- UPDATE system_settings SET setting_value = 'invisible' WHERE setting_key = 'bg_remover_mode';
