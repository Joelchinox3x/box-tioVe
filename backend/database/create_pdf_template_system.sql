-- ========================================
-- SISTEMA DE TEMPLATES PDF MULTI-DOCUMENTO
-- ========================================

CREATE TABLE IF NOT EXISTS pdf_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(80) NOT NULL UNIQUE,
    document_type VARCHAR(80) NOT NULL,
    name VARCHAR(120) NOT NULL,
    description VARCHAR(255) NULL,
    view_path VARCHAR(255) NOT NULL,
    format_spec VARCHAR(30) NOT NULL DEFAULT 'A4',
    orientation ENUM('P','L') NOT NULL DEFAULT 'P',
    margin_top INT NOT NULL DEFAULT 10,
    margin_bottom INT NOT NULL DEFAULT 10,
    margin_left INT NOT NULL DEFAULT 10,
    margin_right INT NOT NULL DEFAULT 10,
    background_path VARCHAR(255) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    is_default TINYINT(1) NOT NULL DEFAULT 0,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_doc_type (document_type),
    INDEX idx_doc_type_active (document_type, is_active, is_default)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS pdf_documents (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    document_type VARCHAR(80) NOT NULL,
    template_code VARCHAR(80) NOT NULL,
    entity_type VARCHAR(50) NOT NULL,
    entity_id INT NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_url VARCHAR(255) NOT NULL,
    verification_token VARCHAR(120) NULL,
    metadata_json JSON NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_doc_type (document_type),
    INDEX idx_template (template_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO pdf_templates
(
    code, document_type, name, description, view_path, format_spec, orientation,
    margin_top, margin_bottom, margin_left, margin_right, background_path,
    is_active, is_default, sort_order
)
VALUES
(
    'fighter_ticket_tpl_01',
    'fighter_inscripcion_ticket',
    'Ticket Peleador 01',
    'Template principal para inscripción de peleadores',
    'fighter_inscripcion_ticket/template_01/body',
    '190x100',
    'L',
    6, 6, 6, 6,
    NULL,
    1, 1, 1
),
(
    'fighter_ticket_tpl_02',
    'fighter_inscripcion_ticket',
    'Ticket Peleador 02',
    'Template alternativo 02',
    'fighter_inscripcion_ticket/template_02/body',
    '190x100',
    'L',
    6, 6, 6, 6,
    NULL,
    1, 0, 2
),
(
    'fighter_ticket_tpl_03',
    'fighter_inscripcion_ticket',
    'Ticket Peleador 03',
    'Template alternativo 03',
    'fighter_inscripcion_ticket/template_03/body',
    '190x100',
    'L',
    6, 6, 6, 6,
    NULL,
    1, 0, 3
),
(
    'fighter_ticket_tpl_04',
    'fighter_inscripcion_ticket',
    'Ticket Peleador 04',
    'Template alternativo 04',
    'fighter_inscripcion_ticket/template_04/body',
    '190x100',
    'L',
    6, 6, 6, 6,
    NULL,
    1, 0, 4
),
(
    'fighter_comprobante_tpl_01',
    'fighter_inscripcion_comprobante',
    'Comprobante Inscripción 01',
    'Template principal para comprobante unificado de inscripción',
    'fighter_inscripcion_comprobante/template_01/body',
    '374.12x120.91',
    'L',
    0, 0, 0, 0,
    'views/pdf_templates/fighter_inscripcion_comprobante/fondo_comprob_01.png',
    1, 1, 1
)
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    description = VALUES(description),
    view_path = VALUES(view_path),
    format_spec = VALUES(format_spec),
    orientation = VALUES(orientation),
    margin_top = VALUES(margin_top),
    margin_bottom = VALUES(margin_bottom),
    margin_left = VALUES(margin_left),
    margin_right = VALUES(margin_right),
    background_path = VALUES(background_path),
    is_active = VALUES(is_active),
    sort_order = VALUES(sort_order);

INSERT INTO system_settings (setting_key, setting_value, description)
VALUES (
    'pdf_template_fighter_inscripcion',
    'fighter_ticket_tpl_01',
    'Template activo para PDF de inscripción de peleadores'
)
ON DUPLICATE KEY UPDATE
    setting_value = VALUES(setting_value),
    description = VALUES(description);

INSERT INTO system_settings (setting_key, setting_value, description)
VALUES (
    'pdf_template_fighter_inscripcion_comprobante',
    'fighter_comprobante_tpl_01',
    'Template activo para comprobante PDF unificado de inscripción de peleadores'
)
ON DUPLICATE KEY UPDATE
    setting_value = VALUES(setting_value),
    description = VALUES(description);
