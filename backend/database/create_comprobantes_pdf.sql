-- ========================================
-- MIGRATION: TABLA COMPROBANTES PDF
-- ========================================
-- Tabla para almacenar referencias a los comprobantes PDF generados
-- Relaciona inscripciones con sus comprobantes y tokens de verificación

CREATE TABLE IF NOT EXISTS comprobantes_pdf (
    id INT AUTO_INCREMENT PRIMARY KEY,
    inscripcion_id INT NOT NULL,
    pdf_filename VARCHAR(255) NOT NULL,
    qr_token VARCHAR(100) UNIQUE NOT NULL,
    qr_data TEXT,
    fecha_generacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    -- Foreign key
    CONSTRAINT fk_comprobantes_inscripcion
        FOREIGN KEY (inscripcion_id)
        REFERENCES inscripciones_eventos(id)
        ON DELETE CASCADE,

    -- Indexes
    INDEX idx_inscripcion (inscripcion_id),
    INDEX idx_qr_token (qr_token),
    INDEX idx_fecha_generacion (fecha_generacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Agregar comentarios a la tabla
ALTER TABLE comprobantes_pdf COMMENT = 'Almacena referencias a comprobantes PDF generados para verificación';
