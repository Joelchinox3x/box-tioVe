-- ========================================
-- INSERTAR 10 PELEADORES DE EJEMPLO
-- ========================================

-- Primero crear los usuarios (tipo_id = 2 para peleadores)
INSERT INTO usuarios (nombre, email, password_hash, tipo_id, club_id, telefono) VALUES
('Miguel "El Rayo" Fernández', 'miguel.rayo@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, 1, '+54 11 4567-1111'),
('Carlos "Martillo" Gonzalez', 'carlos.martillo@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, 2, '+54 11 4567-2222'),
('Antonio "El Tigre" Morales', 'antonio.tigre@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, 3, '+54 11 4567-3333'),
('Diego "Dinamita" Ruiz', 'diego.dinamita@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, 1, '+54 11 4567-4444'),
('Roberto "El Tanque" Silva', 'roberto.tanque@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, 4, '+54 11 4567-5555'),
('Fernando "El Guapo" Ramírez', 'fernando.guapo@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, 5, '+54 11 4567-6666'),
('Luis "El Loco" Méndez', 'luis.loco@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, 2, '+54 11 4567-7777'),
('Javier "El Halcón" Torres', 'javier.halcon@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, 6, '+54 11 4567-8888'),
('Pablo "El Relámpago" Castro', 'pablo.relampago@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, 3, '+54 11 4567-9999'),
('Sebastián "El Tornado" Vega', 'sebastian.tornado@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, 7, '+54 11 4567-0000');

-- Ahora crear los perfiles de peleadores
-- Nota: Ajustar los usuario_id según corresponda (asumiendo que los IDs son consecutivos desde 2)
INSERT INTO peleadores (
    usuario_id,
    apodo,
    fecha_nacimiento,
    peso_actual,
    altura,
    club_id,
    estilo,
    documento_identidad,
    experiencia_anos,
    victorias,
    derrotas,
    empates,
    estado_inscripcion,
    total_promociones
) VALUES
-- Miguel "El Rayo" Fernández
((SELECT id FROM usuarios WHERE email = 'miguel.rayo@gmail.com'),
 'El Rayo',
 '1995-03-15',
 68.5,
 1.75,
 1,
 'estilista',
 '35678901',
 5,
 12,
 3,
 1,
 'aprobado',
 45),

-- Carlos "Martillo" Gonzalez
((SELECT id FROM usuarios WHERE email = 'carlos.martillo@gmail.com'),
 'Martillo',
 '1992-07-22',
 75.2,
 1.78,
 2,
 'fajador',
 '36789012',
 8,
 18,
 5,
 2,
 'aprobado',
 67),

-- Antonio "El Tigre" Morales
((SELECT id FROM usuarios WHERE email = 'antonio.tigre@gmail.com'),
 'El Tigre',
 '1998-11-10',
 72.0,
 1.80,
 3,
 'mixto',
 '37890123',
 3,
 8,
 2,
 0,
 'aprobado',
 32),

-- Diego "Dinamita" Ruiz
((SELECT id FROM usuarios WHERE email = 'diego.dinamita@gmail.com'),
 'Dinamita',
 '1994-05-18',
 70.5,
 1.76,
 1,
 'fajador',
 '38901234',
 6,
 15,
 4,
 1,
 'aprobado',
 58),

-- Roberto "El Tanque" Silva
((SELECT id FROM usuarios WHERE email = 'roberto.tanque@gmail.com'),
 'El Tanque',
 '1990-09-30',
 82.0,
 1.82,
 4,
 'fajador',
 '39012345',
 10,
 22,
 6,
 3,
 'aprobado',
 89),

-- Fernando "El Guapo" Ramírez
((SELECT id FROM usuarios WHERE email = 'fernando.guapo@gmail.com'),
 'El Guapo',
 '1996-02-14',
 66.0,
 1.73,
 5,
 'estilista',
 '40123456',
 4,
 10,
 3,
 0,
 'aprobado',
 41),

-- Luis "El Loco" Méndez
((SELECT id FROM usuarios WHERE email = 'luis.loco@gmail.com'),
 'El Loco',
 '1993-12-05',
 71.5,
 1.77,
 2,
 'mixto',
 '41234567',
 7,
 16,
 5,
 2,
 'aprobado',
 52),

-- Javier "El Halcón" Torres
((SELECT id FROM usuarios WHERE email = 'javier.halcon@gmail.com'),
 'El Halcón',
 '1997-08-20',
 69.0,
 1.74,
 6,
 'estilista',
 '42345678',
 4,
 11,
 2,
 1,
 'aprobado',
 38),

-- Pablo "El Relámpago" Castro
((SELECT id FROM usuarios WHERE email = 'pablo.relampago@gmail.com'),
 'El Relámpago',
 '1999-04-25',
 67.5,
 1.72,
 3,
 'estilista',
 '43456789',
 2,
 6,
 1,
 0,
 'aprobado',
 28),

-- Sebastián "El Tornado" Vega
((SELECT id FROM usuarios WHERE email = 'sebastian.tornado@gmail.com'),
 'El Tornado',
 '1991-06-12',
 73.5,
 1.79,
 7,
 'fajador',
 '44567890',
 9,
 20,
 7,
 2,
 'aprobado',
 73);

-- Verificar cuántos peleadores se insertaron
SELECT COUNT(*) AS total_peleadores FROM peleadores;

-- Mostrar resumen de peleadores por club
SELECT
    c.nombre AS club,
    COUNT(p.id) AS cantidad_peleadores
FROM clubs c
LEFT JOIN peleadores p ON c.id = p.club_id
GROUP BY c.id, c.nombre
ORDER BY cantidad_peleadores DESC;

-- Mostrar todos los peleadores con su información
SELECT
    p.id,
    u.nombre,
    p.apodo,
    CONCAT(p.victorias, 'V - ', p.derrotas, 'D - ', p.empates, 'E') AS record,
    c.nombre AS club,
    p.estilo,
    p.peso_actual AS peso_kg,
    p.altura AS altura_m
FROM peleadores p
JOIN usuarios u ON p.usuario_id = u.id
JOIN clubs c ON p.club_id = c.id
ORDER BY p.total_promociones DESC;

COMMIT;
