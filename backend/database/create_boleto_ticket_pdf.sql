-- Template PDF para boletos de evento
INSERT INTO pdf_templates (
    document_type, code, name, view_path, format_spec, orientation,
    margin_top, margin_bottom, margin_left, margin_right,
    is_active, is_default, sort_order
) VALUES (
    'boleto_ticket',
    'boleto_ticket_tpl_01',
    'Boleto de Evento - Template 01',
    'boleto_ticket/template_01/body',
    'A5',
    'L',
    6, 6, 6, 6,
    1, 1, 1
);
