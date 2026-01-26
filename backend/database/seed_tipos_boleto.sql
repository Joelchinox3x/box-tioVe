-- Script para insertar tipos de boleto de prueba
-- Evento: EL JAB DORADO 2026 (id = 1)

INSERT INTO tipos_boleto (evento_id, nombre, precio, cantidad_total, cantidad_vendida, color_hex, descripcion, orden, activo) VALUES
(1, 'General', 30.00, 500, 0, '#3498db', 'Acceso general al evento. Asientos de tribuna.', 1, 1),
(1, 'VIP', 80.00, 100, 0, '#f39c12', 'Zona VIP con mejor vista. Incluye bebida de cortesía.', 2, 1),
(1, 'Ringside', 150.00, 50, 0, '#e74c3c', 'Primera fila junto al ring. Experiencia premium. Incluye 2 bebidas.', 3, 1),
(1, 'Mesa VIP', 500.00, 10, 0, '#9b59b6', 'Mesa exclusiva para 6 personas. Servicio de mesero y bebidas ilimitadas.', 4, 1);

-- Verificar inserción
SELECT
    id,
    nombre,
    precio,
    cantidad_total,
    cantidad_disponible,
    color_hex,
    activo
FROM vista_boletos_disponibles
WHERE evento_id = 1;
