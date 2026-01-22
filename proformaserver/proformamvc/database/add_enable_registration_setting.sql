-- Agregar configuración para habilitar/deshabilitar el registro de usuarios
-- Ejecutar este script después de crear la tabla settings

INSERT INTO settings (setting_key, setting_value, description, created_at, updated_at)
VALUES ('enable_registration', '1', 'Habilitar o deshabilitar el registro de nuevos usuarios (1 = habilitado, 0 = deshabilitado)', NOW(), NOW())
ON DUPLICATE KEY UPDATE
    description = 'Habilitar o deshabilitar el registro de nuevos usuarios (1 = habilitado, 0 = deshabilitado)',
    updated_at = NOW();
