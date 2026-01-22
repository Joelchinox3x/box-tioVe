-- Tabla para gestionar folletos PDF de productos
-- Soporte para folletos generados desde fotos o subidos directamente

CREATE TABLE `producto_folletos` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `producto_id` INT NOT NULL,
  `nombre` VARCHAR(255) NOT NULL COMMENT 'Nombre descriptivo del folleto',
  `tipo` ENUM('generado', 'subido') NOT NULL DEFAULT 'subido',
  `categoria` ENUM('general', 'tecnico', 'comercial') NOT NULL DEFAULT 'general',
  `ruta_pdf` VARCHAR(255) NOT NULL COMMENT 'Ruta del archivo PDF final',
  `imagenes_fuente` TEXT NULL COMMENT 'JSON array con rutas de imágenes usadas para generar PDF',
  `tamanio` INT NULL COMMENT 'Tamaño del archivo en bytes',
  `activo` TINYINT(1) DEFAULT 1,
  `descargas` INT DEFAULT 0,
  `orden` INT DEFAULT 0,
  `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`producto_id`) REFERENCES `productos`(`id`) ON DELETE CASCADE,
  INDEX `idx_producto_id` (`producto_id`),
  INDEX `idx_categoria` (`categoria`),
  INDEX `idx_activo` (`activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
