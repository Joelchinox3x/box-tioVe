-- =============================================
-- BOXEVENT APP - NUEVA TABLA PARA TARJETAS
-- =============================================

USE boxevent;

CREATE TABLE IF NOT EXISTS fighter_cards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    peleador_id INT NOT NULL,
    is_primary TINYINT(1) DEFAULT 0 COMMENT '1 si es la tarjeta principal del luchador',
    baked_url VARCHAR(500) COMMENT 'URL de la imagen PNG final (quemada)',
    composition_json LONGTEXT COMMENT 'Metadatos JSON para reconstruir la tarjeta en el editor',
    layout_type VARCHAR(50) DEFAULT 'standard' COMMENT 'Tipo de diseño (ej: standard, vintage, event)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Relación con la tabla peleadores
    FOREIGN KEY (peleador_id) REFERENCES peleadores(id) ON DELETE CASCADE,
    
    -- Índices para optimización
    INDEX idx_peleador (peleador_id),
    INDEX idx_primary (is_primary)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Nota: Esta tabla permite que un luchador tenga múltiples diseños o variantes de tarjeta.
