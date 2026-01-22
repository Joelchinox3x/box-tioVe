-- Tabla de configuración global de la aplicación
-- Esta tabla almacena configuraciones persistentes del sistema

DROP TABLE IF EXISTS `settings`;

CREATE TABLE `settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `setting_value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar valores por defecto
INSERT INTO `settings` (`setting_key`, `setting_value`, `description`) VALUES
('pin_code', '1234', 'PIN de seguridad para la aplicación'),
('igv_percent', '18', 'Porcentaje de IGV aplicable'),
('prices_include_igv', '1', '1 = Precios incluyen IGV, 0 = Precios sin IGV'),
('app_name', 'Tradimacova', 'Nombre de la aplicación que aparece en el home'),
('app_logo', '', 'URL del logo de la aplicación'),
('enable_gps', '0', '1 = GPS habilitado, 0 = GPS deshabilitado'),
('show_header', '1', '1 = Mostrar header, 0 = Ocultar header'),
('show_navbar', '1', '1 = Mostrar navbar, 0 = Ocultar navbar');
