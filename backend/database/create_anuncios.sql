-- ========================================
-- MIGRATION: SISTEMA DE ANUNCIOS
-- ========================================

CREATE TABLE IF NOT EXISTS anuncios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    mensaje TEXT NOT NULL,
    tipo ENUM('info','urgente','promo','contacto','reglas') NOT NULL DEFAULT 'info',
    medio ENUM('texto','imagen','video','link') NOT NULL DEFAULT 'texto',

    -- Media fields
    imagen_filename VARCHAR(255) DEFAULT NULL,
    video_filename VARCHAR(255) DEFAULT NULL,
    link_url VARCHAR(500) DEFAULT NULL,
    link_tipo ENUM('youtube','tiktok','otro') DEFAULT NULL,

    -- Association
    evento_id INT DEFAULT NULL,

    -- Visibility and ordering
    activo TINYINT(1) NOT NULL DEFAULT 0,
    fijado TINYINT(1) NOT NULL DEFAULT 0,
    orden INT NOT NULL DEFAULT 0,

    -- Scheduling
    fecha_publicacion DATETIME DEFAULT NULL,
    fecha_expiracion DATETIME DEFAULT NULL,

    -- Source tracking
    fuente ENUM('admin','telegram') NOT NULL DEFAULT 'admin',
    telegram_message_id BIGINT DEFAULT NULL,

    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Foreign keys
    CONSTRAINT fk_anuncios_evento FOREIGN KEY (evento_id) REFERENCES eventos(id) ON DELETE SET NULL,

    -- Indexes
    INDEX idx_anuncios_activo (activo),
    INDEX idx_anuncios_tipo (tipo),
    INDEX idx_anuncios_fijado (fijado),
    INDEX idx_anuncios_evento (evento_id),
    INDEX idx_anuncios_publicacion (fecha_publicacion),
    INDEX idx_anuncios_expiracion (fecha_expiracion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
