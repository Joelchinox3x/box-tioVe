-- Agregar columnas direccion y fecha_modificacion a clientes_pendientes
-- direccion: Guarda la dirección obtenida de la consulta SUNAT (solo para RUC)
-- fecha_modificacion: Se actualiza automáticamente cuando se modifica el registro

ALTER TABLE clientes_pendientes
ADD COLUMN direccion TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
AFTER telefono;

ALTER TABLE clientes_pendientes
ADD COLUMN fecha_modificacion TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
AFTER fecha_creacion;
