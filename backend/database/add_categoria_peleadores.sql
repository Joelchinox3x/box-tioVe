-- ========================================
-- AGREGAR COLUMNA CATEGORIA A PELEADORES
-- ========================================
-- Este script agrega la columna categoria como ENUM
-- y actualiza los registros existentes según su peso

USE eventobox_db;

-- ========================================
-- 1. AGREGAR COLUMNA CATEGORIA
-- ========================================
ALTER TABLE peleadores
ADD COLUMN categoria ENUM(
    'Mosca',
    'Supermosca',
    'Gallo',
    'Supergallo',
    'Pluma',
    'Superpluma',
    'Ligero',
    'Superligero',
    'Welter',
    'Superwelter',
    'Mediano',
    'Supermediano',
    'Mediopesado',
    'Semipesado',
    'Pesado'
) NULL
COMMENT 'Categoría de peso del peleador'
AFTER peso_actual;

-- ========================================
-- 2. ACTUALIZAR CATEGORIAS EXISTENTES
-- ========================================
-- Actualizar todos los peleadores existentes según su peso actual

UPDATE peleadores SET categoria =
    CASE
        -- Mosca: hasta 50.8 kg (112 lbs)
        WHEN peso_actual <= 50.8 THEN 'Mosca'

        -- Supermosca: hasta 52.2 kg (115 lbs)
        WHEN peso_actual <= 52.2 THEN 'Supermosca'

        -- Gallo: hasta 53.5 kg (118 lbs)
        WHEN peso_actual <= 53.5 THEN 'Gallo'

        -- Supergallo: hasta 55.3 kg (122 lbs)
        WHEN peso_actual <= 55.3 THEN 'Supergallo'

        -- Pluma: hasta 57.2 kg (126 lbs)
        WHEN peso_actual <= 57.2 THEN 'Pluma'

        -- Superpluma: hasta 58.9 kg (130 lbs)
        WHEN peso_actual <= 58.9 THEN 'Superpluma'

        -- Ligero: hasta 61.2 kg (135 lbs)
        WHEN peso_actual <= 61.2 THEN 'Ligero'

        -- Superligero: hasta 63.5 kg (140 lbs)
        WHEN peso_actual <= 63.5 THEN 'Superligero'

        -- Welter: hasta 66.7 kg (147 lbs)
        WHEN peso_actual <= 66.7 THEN 'Welter'

        -- Superwelter: hasta 69.9 kg (154 lbs)
        WHEN peso_actual <= 69.9 THEN 'Superwelter'

        -- Mediano: hasta 72.6 kg (160 lbs)
        WHEN peso_actual <= 72.6 THEN 'Mediano'

        -- Supermediano: hasta 76.2 kg (168 lbs)
        WHEN peso_actual <= 76.2 THEN 'Supermediano'

        -- Mediopesado: hasta 79.4 kg (175 lbs)
        WHEN peso_actual <= 79.4 THEN 'Mediopesado'

        -- Semipesado: hasta 90.7 kg (200 lbs)
        WHEN peso_actual <= 90.7 THEN 'Semipesado'

        -- Pesado: más de 90.7 kg (200+ lbs)
        ELSE 'Pesado'
    END
WHERE peso_actual IS NOT NULL;

-- ========================================
-- 3. VERIFICACIÓN
-- ========================================
-- Ver los peleadores con sus nuevas categorías
SELECT
    p.id,
    p.apodo,
    p.peso_actual,
    p.categoria,
    CONCAT(u.nombre, ' ', u.apellidos) as nombre_completo
FROM peleadores p
JOIN usuarios u ON p.usuario_id = u.id
ORDER BY p.peso_actual ASC;

-- Resumen por categoría
SELECT
    categoria,
    COUNT(*) as total_peleadores,
    MIN(peso_actual) as peso_minimo,
    MAX(peso_actual) as peso_maximo
FROM peleadores
WHERE categoria IS NOT NULL
GROUP BY categoria
ORDER BY peso_minimo ASC;
