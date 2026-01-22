/*
 Navicat Premium Data Transfer

 Source Server         : joelchino
 Source Server Type    : MySQL
 Source Server Version : 100432 (10.4.32-MariaDB)
 Source Host           : localhost:3306
 Source Schema         : boxevent

 Target Server Type    : MySQL
 Target Server Version : 100432 (10.4.32-MariaDB)
 File Encoding         : 65001

 Date: 24/12/2025 03:46:13
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for compras_entradas
-- ----------------------------
DROP TABLE IF EXISTS `compras_entradas`;
CREATE TABLE `compras_entradas`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `tipo_entrada_id` int NOT NULL,
  `cantidad` int NOT NULL,
  `precio_total` decimal(10, 2) NOT NULL,
  `codigo_qr` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `estado` enum('pendiente','pagado','usado','cancelado') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT 'pendiente',
  `metodo_pago` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `fecha_compra` timestamp NOT NULL DEFAULT current_timestamp,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `codigo_qr`(`codigo_qr` ASC) USING BTREE,
  INDEX `tipo_entrada_id`(`tipo_entrada_id` ASC) USING BTREE,
  INDEX `idx_usuario`(`usuario_id` ASC) USING BTREE,
  INDEX `idx_codigo`(`codigo_qr` ASC) USING BTREE,
  CONSTRAINT `compras_entradas_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `compras_entradas_ibfk_2` FOREIGN KEY (`tipo_entrada_id`) REFERENCES `tipos_entrada` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of compras_entradas
-- ----------------------------

-- ----------------------------
-- Table structure for eventos
-- ----------------------------
DROP TABLE IF EXISTS `eventos`;
CREATE TABLE `eventos`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `fecha_evento` datetime NOT NULL,
  `lugar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `direccion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `capacidad_total` int NULL DEFAULT 500,
  `estado` enum('programado','en_curso','finalizado') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT 'programado',
  `imagen_portada` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_fecha`(`fecha_evento` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of eventos
-- ----------------------------
INSERT INTO `eventos` VALUES (1, 'NOCHE CORPORATIVA DE BOXEO 2025', 'La noche más esperada del año con los mejores prospectos de la región', '2025-03-15 19:00:00', 'Arena Principal', 'Av. Los Campeones 123, Lima', 500, 'programado', 'https://example.com/portada.jpg', '2025-12-19 23:05:05');

-- ----------------------------
-- Table structure for notificaciones
-- ----------------------------
DROP TABLE IF EXISTS `notificaciones`;
CREATE TABLE `notificaciones`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `titulo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mensaje` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` enum('general','pelea','promocion','entrada') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT 'general',
  `leido` tinyint(1) NULL DEFAULT 0,
  `fecha_envio` timestamp NOT NULL DEFAULT current_timestamp,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_usuario_leido`(`usuario_id` ASC, `leido` ASC) USING BTREE,
  CONSTRAINT `notificaciones_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of notificaciones
-- ----------------------------

-- ----------------------------
-- Table structure for patrocinadores
-- ----------------------------
DROP TABLE IF EXISTS `patrocinadores`;
CREATE TABLE `patrocinadores`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `evento_id` int NOT NULL,
  `nombre` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `logo` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `url_sitio` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `nivel` enum('oro','plata','bronce') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT 'bronce',
  `activo` tinyint(1) NULL DEFAULT 1,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_evento`(`evento_id` ASC) USING BTREE,
  CONSTRAINT `patrocinadores_ibfk_1` FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of patrocinadores
-- ----------------------------

-- ----------------------------
-- Table structure for peleadores
-- ----------------------------
DROP TABLE IF EXISTS `peleadores`;
CREATE TABLE `peleadores`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `apodo` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `fecha_nacimiento` date NOT NULL,
  `peso_actual` decimal(5, 2) NOT NULL,
  `altura` decimal(4, 2) NULL DEFAULT NULL,
  `club` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `estilo` enum('fajador','estilista') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT 'fajador',
  `foto_perfil` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `victorias` int NULL DEFAULT 0,
  `derrotas` int NULL DEFAULT 0,
  `empates` int NULL DEFAULT 0,
  `promociones` int NULL DEFAULT 0,
  `estado_inscripcion` enum('pendiente','aprobado','rechazado') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT 'pendiente',
  `documento_identidad` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `experiencia_anos` int NULL DEFAULT 0,
  `fecha_inscripcion` timestamp NOT NULL DEFAULT current_timestamp,
  `notas_admin` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `documento_identidad`(`documento_identidad` ASC) USING BTREE,
  INDEX `idx_usuario`(`usuario_id` ASC) USING BTREE,
  INDEX `idx_estado`(`estado_inscripcion` ASC) USING BTREE,
  INDEX `idx_promociones`(`promociones` ASC) USING BTREE,
  INDEX `idx_documento`(`documento_identidad` ASC) USING BTREE,
  CONSTRAINT `peleadores_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of peleadores
-- ----------------------------

-- ----------------------------
-- Table structure for peleas
-- ----------------------------
DROP TABLE IF EXISTS `peleas`;
CREATE TABLE `peleas`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `evento_id` int NOT NULL,
  `peleador_1_id` int NOT NULL,
  `peleador_2_id` int NOT NULL,
  `categoria_peso` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `numero_rounds` int NULL DEFAULT 3,
  `orden_pelea` int NULL DEFAULT NULL,
  `resultado` enum('pendiente','ganador_1','ganador_2','empate') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT 'pendiente',
  `metodo_victoria` enum('KO','TKO','Decision','Descalificacion') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `votos_peleador_1` int NOT NULL DEFAULT 0,
  `votos_peleador_2` int NOT NULL DEFAULT 0,
  `es_pelea_estelar` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `peleador_1_id`(`peleador_1_id` ASC) USING BTREE,
  INDEX `peleador_2_id`(`peleador_2_id` ASC) USING BTREE,
  INDEX `idx_evento`(`evento_id` ASC) USING BTREE,
  INDEX `idx_resultado`(`resultado` ASC) USING BTREE,
  CONSTRAINT `peleas_ibfk_1` FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `peleas_ibfk_2` FOREIGN KEY (`peleador_1_id`) REFERENCES `peleadores` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `peleas_ibfk_3` FOREIGN KEY (`peleador_2_id`) REFERENCES `peleadores` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of peleas
-- ----------------------------

-- ----------------------------
-- Table structure for promociones
-- ----------------------------
DROP TABLE IF EXISTS `promociones`;
CREATE TABLE `promociones`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NULL DEFAULT NULL,
  `peleador_id` int NOT NULL,
  `tipo_promocion` enum('compartir_whatsapp','compartir_instagram','compartir_facebook') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `fecha_promocion` timestamp NOT NULL DEFAULT current_timestamp,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `usuario_id`(`usuario_id` ASC) USING BTREE,
  INDEX `idx_peleador`(`peleador_id` ASC) USING BTREE,
  INDEX `idx_fecha`(`fecha_promocion` ASC) USING BTREE,
  CONSTRAINT `promociones_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `promociones_ibfk_2` FOREIGN KEY (`peleador_id`) REFERENCES `peleadores` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of promociones
-- ----------------------------

-- ----------------------------
-- Table structure for tipos_entrada
-- ----------------------------
DROP TABLE IF EXISTS `tipos_entrada`;
CREATE TABLE `tipos_entrada`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `evento_id` int NOT NULL,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `precio` decimal(10, 2) NOT NULL,
  `cantidad_disponible` int NOT NULL,
  `cantidad_vendida` int NULL DEFAULT 0,
  `descripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `beneficios` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `activo` tinyint(1) NULL DEFAULT 1,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_evento`(`evento_id` ASC) USING BTREE,
  CONSTRAINT `tipos_entrada_ibfk_1` FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of tipos_entrada
-- ----------------------------
INSERT INTO `tipos_entrada` VALUES (1, 1, 'VIP Ringside', 250.00, 50, 0, 'Asientos junto al ring con bar abierto', NULL, 1);
INSERT INTO `tipos_entrada` VALUES (2, 1, 'General', 80.00, 400, 0, 'Entrada general con tribuna', NULL, 1);
INSERT INTO `tipos_entrada` VALUES (3, 1, 'Estudiante', 50.00, 100, 0, 'Entrada con descuento para estudiantes', NULL, 1);

-- ----------------------------
-- Table structure for usuarios
-- ----------------------------
DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `tipo_usuario` enum('espectador','peleador','admin') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT 'espectador',
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp,
  `activo` tinyint(1) NULL DEFAULT 1,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `email`(`email` ASC) USING BTREE,
  INDEX `idx_email`(`email` ASC) USING BTREE,
  INDEX `idx_tipo`(`tipo_usuario` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of usuarios
-- ----------------------------
INSERT INTO `usuarios` VALUES (1, 'admin@boxevent.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin Principal', NULL, 'admin', '2025-12-19 23:05:05', 1);

-- ----------------------------
-- Table structure for votos_peleas
-- ----------------------------
DROP TABLE IF EXISTS `votos_peleas`;
CREATE TABLE `votos_peleas`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `pelea_id` int NOT NULL,
  `usuario_id` int NULL DEFAULT NULL,
  `peleador_votado_id` int NOT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `fecha_voto` timestamp NOT NULL DEFAULT current_timestamp,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `unique_voto`(`pelea_id` ASC, `ip_address` ASC) USING BTREE,
  INDEX `usuario_id`(`usuario_id` ASC) USING BTREE,
  INDEX `peleador_votado_id`(`peleador_votado_id` ASC) USING BTREE,
  INDEX `idx_pelea`(`pelea_id` ASC) USING BTREE,
  CONSTRAINT `votos_peleas_ibfk_1` FOREIGN KEY (`pelea_id`) REFERENCES `peleas` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `votos_peleas_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `votos_peleas_ibfk_3` FOREIGN KEY (`peleador_votado_id`) REFERENCES `peleadores` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of votos_peleas
-- ----------------------------

SET FOREIGN_KEY_CHECKS = 1;
