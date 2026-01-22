-- Agregar campo protegido a la tabla productos
-- Este campo reemplaza al campo bloqueado para mantener consistencia con clientes

ALTER TABLE `productos`
ADD COLUMN `protegido` TINYINT(1) DEFAULT 0 AFTER `bloqueado`;

-- Copiar datos de bloqueado a protegido (si existen)
UPDATE `productos` SET `protegido` = `bloqueado` WHERE `bloqueado` = 1;

-- Opcional: Eliminar columna antigua bloqueado (descomentar si se desea)
-- ALTER TABLE `productos` DROP COLUMN `bloqueado`;
