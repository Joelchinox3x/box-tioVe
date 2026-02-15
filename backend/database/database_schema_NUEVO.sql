-- ========================================
-- BOXEVENT APP - BASE DE DATOS COMPLETA
-- ========================================

-- Usar la base de datos
USE boxevent;

-- Limpiar tablas existentes (en orden correcto por dependencias)
DROP TABLE IF EXISTS votos;
DROP TABLE IF EXISTS promociones;
DROP TABLE IF EXISTS entradas;
DROP TABLE IF EXISTS peleas;
DROP TABLE IF EXISTS peleadores;
DROP TABLE IF EXISTS usuarios;
DROP TABLE IF EXISTS eventos;
DROP TABLE IF EXISTS clubs;
DROP TABLE IF EXISTS tipos_usuario;

-- ========================================
-- 1. TABLA DE TIPOS DE USUARIO
-- ========================================
CREATE TABLE tipos_usuario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion VARCHAR(255),
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertar tipos por defecto
INSERT INTO tipos_usuario (id, nombre, descripcion) VALUES
(1, 'admin', 'Administrador del sistema'),
(2, 'peleador', 'Peleador/Boxeador registrado'),
(3, 'espectador', 'Espectador/Hincha'),
(4, 'manager_club', 'Manager/Dueño de un club');

-- ========================================
-- 2. TABLA CLUBS
-- ========================================
CREATE TABLE clubs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL UNIQUE,
    direccion VARCHAR(500),
    telefono VARCHAR(20),
    email VARCHAR(255),
    logo VARCHAR(500),
    descripcion TEXT,
    activo BOOLEAN DEFAULT true,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_activo (activo),
    INDEX idx_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertar clubs de ejemplo
INSERT INTO clubs (nombre, direccion, telefono, email, descripcion, activo) VALUES
('Gimnasio El Campeón', 'Av. Rivadavia 5432, CABA', '+54 11 4567-8901', 'info@elcampeon.com', 'Gimnasio de boxeo profesional con más de 20 años de trayectoria', true),
('Box Club Tigre', 'Calle Italia 789, Tigre', '+54 11 4765-4321', 'contacto@boxtigre.com', 'Club deportivo especializado en boxeo amateur y profesional', true),
('Academia Puños de Oro', 'Av. Corrientes 2345, CABA', '+54 11 4876-5432', 'hola@punosdeoro.com', 'Academia de boxeo con entrenadores certificados internacionalmente', true),
('Club Deportivo San Martín', 'Belgrano 1122, San Martín', '+54 11 4234-5678', 'deporte@sanmartin.com', 'Club deportivo multidisciplinario con sección de boxeo', true),
('Gimnasio La Fortaleza', 'Av. San Juan 3456, CABA', '+54 11 4555-6789', 'gym@lafortaleza.com', 'Gimnasio enfocado en boxeo recreativo y competitivo', true),
('Box Center Buenos Aires', 'Av. Córdoba 1890, CABA', '+54 11 4321-9876', 'info@boxcenter.com', 'Centro de entrenamiento de boxeo de alto rendimiento', true),
('Academia Knockout', 'Av. Independencia 2567, CABA', '+54 11 4678-1234', 'knockout@academia.com', 'Academia especializada en técnica de boxeo y defensa personal', true),
('Club Atlético Boxeo', 'Calle Alsina 678, Avellaneda', '+54 11 4987-6543', 'atletico@boxeo.com', 'Club atlético con tradición en boxeo olímpico', true),
('Gimnasio Ring de Fuego', 'Av. Entre Ríos 4321, CABA', '+54 11 4234-8765', 'ring@fuego.com', 'Gimnasio de boxeo para todas las edades y niveles', true),
('Independiente', 'Sin club afiliado', NULL, NULL, 'Opción para peleadores sin club afiliado', true);

-- ========================================
-- 3. TABLA USUARIOS
-- ========================================
-- LOGICA:
-- tipo_id: 1=admin, 2=peleador, 3=espectador, 4=manager_club
-- club_id:
--   - Si tipo_id=2 (peleador) -> Es INTEGRANTE del club
--   - Si tipo_id=3 (espectador) -> Es HINCHA del club
--   - Si tipo_id=4 (manager_club) -> Es DUEÑO/MANAGER del club
-- ========================================
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    telefono VARCHAR(20),

    -- Tipo de usuario (1=admin, 2=peleador, 3=espectador, 4=manager_club)
    tipo_id INT NOT NULL DEFAULT 3,

    -- Club del usuario
    -- Si es peleador (tipo_id=2): club donde entrena/pertenece
    -- Si es espectador (tipo_id=3): club del que es hincha
    -- Si es manager_club (tipo_id=4): club que administra
    club_id INT DEFAULT NULL,

    -- Otros datos
    foto_perfil VARCHAR(500),
    puntos_promocion INT DEFAULT 0 COMMENT 'Puntos por promocionar peleadores/eventos',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('activo', 'suspendido', 'eliminado') DEFAULT 'activo',

    -- Foreign Keys
    FOREIGN KEY (tipo_id) REFERENCES tipos_usuario(id) ON DELETE RESTRICT,
    FOREIGN KEY (club_id) REFERENCES clubs(id) ON DELETE SET NULL,

    -- Indices
    INDEX idx_email (email),
    INDEX idx_tipo (tipo_id),
    INDEX idx_club (club_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================
-- 4. TABLA EVENTOS
-- ========================================
CREATE TABLE eventos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(200) NOT NULL,
    descripcion TEXT,
    fecha DATE NOT NULL,
    hora TIME NOT NULL,
    direccion VARCHAR(300) NOT NULL,
    ciudad VARCHAR(100),
    precio_entrada_general DECIMAL(10,2),
    precio_entrada_vip DECIMAL(10,2),
    capacidad_total INT DEFAULT 500,
    entradas_vendidas INT DEFAULT 0,
    imagen_portada VARCHAR(500),
    estado ENUM('proximamente', 'en_curso', 'finalizado', 'cancelado') DEFAULT 'proximamente',
    reglas_torneo TEXT,
    premios TEXT,
    patrocinadores TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_estado (estado),
    INDEX idx_fecha (fecha)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================
-- 5. TABLA PELEADORES
-- ========================================
CREATE TABLE peleadores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,

    -- Datos personales
    apodo VARCHAR(100),
    fecha_nacimiento DATE NULL,
    edad INT NULL,
    peso_actual DECIMAL(5,2) NOT NULL COMMENT 'Peso en kg',
    altura DECIMAL(4,2) COMMENT 'Altura en metros (ej: 1.75)',

    -- Club al que pertenece
    club_id INT DEFAULT NULL,

    -- Estilo y experiencia
    estilo ENUM('fajador', 'estilista', 'mixto') DEFAULT 'fajador',
    experiencia_anos INT DEFAULT 0,
    documento_identidad VARCHAR(20) UNIQUE NOT NULL,

    -- Foto
    foto_perfil VARCHAR(500),

    -- Récord de peleas
    victorias INT DEFAULT 0,
    derrotas INT DEFAULT 0,
    empates INT DEFAULT 0,

    -- Estadísticas de promoción
    total_promociones INT DEFAULT 0 COMMENT 'Cuántas veces lo han compartido',
    ranking_popularidad INT DEFAULT 0 COMMENT 'Posición en ranking',

    -- Estado de inscripción
    estado_inscripcion ENUM('pendiente', 'aprobado', 'rechazado') DEFAULT 'pendiente',

    -- Datos adicionales
    notas_admin TEXT,
    fecha_inscripcion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    -- Foreign Keys
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (club_id) REFERENCES clubs(id) ON DELETE SET NULL,

    -- Indices
    INDEX idx_club (club_id),
    INDEX idx_estado (estado_inscripcion),
    INDEX idx_popularidad (total_promociones DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================
-- 6. TABLA PELEAS (Cartelera/Matchmaking)
-- ========================================
CREATE TABLE peleas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    evento_id INT NOT NULL,
    peleador_1_id INT NOT NULL,
    peleador_2_id INT NOT NULL,

    -- Detalles de la pelea
    categoria_peso VARCHAR(50),
    numero_rounds INT DEFAULT 3,
    orden_pelea INT,

    -- Resultado
    resultado ENUM('pendiente', 'ganador_1', 'ganador_2', 'empate') DEFAULT 'pendiente',
    metodo_victoria ENUM('KO', 'TKO', 'Decision', 'Descalificacion'),

    -- Sistema de votación
    votos_peleador_1 INT DEFAULT 0,
    votos_peleador_2 INT DEFAULT 0,

    -- Destacados
    es_pelea_estelar BOOLEAN DEFAULT FALSE,
    entradas_agotadas BOOLEAN DEFAULT FALSE,

    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (evento_id) REFERENCES eventos(id) ON DELETE CASCADE,
    FOREIGN KEY (peleador_1_id) REFERENCES peleadores(id) ON DELETE CASCADE,
    FOREIGN KEY (peleador_2_id) REFERENCES peleadores(id) ON DELETE CASCADE,

    INDEX idx_evento (evento_id),
    INDEX idx_resultado (resultado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================
-- 7. TABLA ENTRADAS (Tickets)
-- ========================================
CREATE TABLE entradas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    evento_id INT NOT NULL,
    usuario_id INT NOT NULL,
    tipo_entrada ENUM('general', 'vip') NOT NULL,
    precio_pagado DECIMAL(10,2) NOT NULL,
    cantidad INT DEFAULT 1,

    -- Estado
    estado ENUM('reservada', 'pagada', 'usada', 'cancelada') DEFAULT 'reservada',
    codigo_qr VARCHAR(255) UNIQUE,

    -- Datos de compra
    fecha_compra TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    metodo_pago VARCHAR(50),

    FOREIGN KEY (evento_id) REFERENCES eventos(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,

    INDEX idx_usuario (usuario_id),
    INDEX idx_evento (evento_id),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================
-- 8. TABLA PROMOCIONES (Shares/Popularidad)
-- ========================================
CREATE TABLE promociones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    peleador_id INT NOT NULL,
    usuario_promotor_id INT NOT NULL,
    plataforma ENUM('whatsapp', 'instagram', 'facebook', 'twitter', 'otro') NOT NULL,
    link_compartido VARCHAR(500),
    fecha_promocion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (peleador_id) REFERENCES peleadores(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_promotor_id) REFERENCES usuarios(id) ON DELETE CASCADE,

    INDEX idx_peleador (peleador_id),
    INDEX idx_fecha (fecha_promocion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================
-- 9. TABLA VOTOS (Sistema de Predicciones)
-- ========================================
CREATE TABLE votos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pelea_id INT NOT NULL,
    usuario_id INT NOT NULL,
    peleador_votado_id INT NOT NULL,
    fecha_voto TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (pelea_id) REFERENCES peleas(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (peleador_votado_id) REFERENCES peleadores(id) ON DELETE CASCADE,

    UNIQUE KEY unique_voto (pelea_id, usuario_id),
    INDEX idx_pelea (pelea_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================
-- DATOS DE PRUEBA
-- ========================================

-- Insertar admin
INSERT INTO usuarios (nombre, email, password_hash, tipo_id) VALUES
('Admin Principal', 'admin@boxevent.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);

-- Insertar manager de club de ejemplo
INSERT INTO usuarios (nombre, email, password_hash, tipo_id, club_id) VALUES
('Juan Pérez', 'juan@elcampeon.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4, 1);

-- Insertar evento principal
INSERT INTO eventos (nombre, descripcion, fecha, hora, direccion, ciudad, precio_entrada_general, precio_entrada_vip, capacidad_total, estado, reglas_torneo) VALUES
('EL JAB DORADO 2026',
 'El evento de boxeo más esperado del año. Peleas emocionantes, ambiente familiar y grandes premios.',
 '2025-03-15',
 '19:00:00',
 'Parque Los Llanos, Los Llanos - Santa Clara',
 'Lima',
 10.00,
 15.00,
 1000,
 'proximamente',
 '- Mínimo 18 años\n- Examen médico obligatorio\n- 3 rounds de 3 minutos\n- Guantes de 10 oz\n- No profesionales');

-- Usuarios espectadores de ejemplo
INSERT INTO usuarios (nombre, email, password_hash, tipo_id, club_id) VALUES
('Carlos Ramirez', 'carlos@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, 1),
('Maria Torres', 'maria@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, 2);

COMMIT;

-- ========================================
-- RESUMEN DE LA ESTRUCTURA:
-- ========================================
-- TIPOS DE USUARIO:
--   1 = admin
--   2 = peleador (integrante de club)
--   3 = espectador (hincha de club)
--   4 = manager_club (dueño/administrador de club)
--
-- RELACIÓN CON CLUBS:
--   - Peleador (tipo_id=2) + club_id = INTEGRANTE del club
--   - Espectador (tipo_id=3) + club_id = HINCHA del club
--   - Manager (tipo_id=4) + club_id = DUEÑO del club
--
-- VENTAJAS:
--   ✅ Simple y directo
--   ✅ Un campo para todo (club_id)
--   ✅ Múltiples managers pueden tener el mismo club_id
--   ✅ No necesita tablas pivot adicionales
-- ========================================
