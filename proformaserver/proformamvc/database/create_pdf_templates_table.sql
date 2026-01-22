-- Tabla para Temas PDF
CREATE TABLE IF NOT EXISTS `pdf_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL UNIQUE,
  `descripcion` text DEFAULT NULL,
  `color_brand` varchar(20) NOT NULL DEFAULT '#333333',
  `header_php` varchar(100) NOT NULL DEFAULT 'header.php',
  `fondo_img` varchar(255) DEFAULT NULL,
  `footer_img` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `es_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Data (Datos Iniciales)
INSERT INTO `pdf_templates` (`nombre`, `descripcion`, `color_brand`, `header_php`, `fondo_img`, `footer_img`, `activo`, `es_default`) VALUES
('orange', 'Tema Naranja Original (Tradimacova)', '#f37021', 'header.php', 'orange/header.png', 'orange/footer.png', 1, 1),
('blue', 'Tema Azul Corporativo', '#004481', 'header.php', 'blue/fondo.png', 'blue/footer.png', 1, 0),
('simple', 'Tema Simple (Ahorro de tinta)', '#333333', 'header_simple.php', NULL, NULL, 1, 0);
