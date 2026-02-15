/**
 * Calcular categoría de peso según el peso actual del peleador
 * Categorías oficiales del boxeo profesional
 */
export const getCategoria = (peso: number | string | undefined): string => {
    if (!peso) return '---';

    const kg = typeof peso === 'string' ? parseFloat(peso) : peso;
    if (isNaN(kg)) return '---';

    if (kg <= 47.6) return 'PESO PAJA';
    if (kg <= 49.0) return 'MINIMOSCA';
    if (kg <= 50.8) return 'PESO MOSCA';
    if (kg <= 52.2) return 'SUPERMOSCA';
    if (kg <= 53.5) return 'PESO GALLO';
    if (kg <= 55.3) return 'SUPERGALLO';
    if (kg <= 57.2) return 'PESO PLUMA';
    if (kg <= 59.0) return 'SUPERPLUMA';
    if (kg <= 61.2) return 'PESO LIGERO';
    if (kg <= 63.5) return 'SUPERLIGERO';
    if (kg <= 66.7) return 'PESO WELTER';
    if (kg <= 69.9) return 'SUPERWELTER';
    if (kg <= 72.6) return 'PESO MEDIANO';
    if (kg <= 76.2) return 'SUPERMEDIANO';
    if (kg <= 79.4) return 'SEMIPESADO';
    if (kg <= 90.7) return 'PESO PESADO';
    return 'PESO PESADO';
};

/**
 * Lista de todas las categorías de peso del boxeo
 */
export const CATEGORIAS_BOXEO = [
    { nombre: 'PESO PAJA', peso_max: 47.6 },
    { nombre: 'MINIMOSCA', peso_max: 49.0 },
    { nombre: 'PESO MOSCA', peso_max: 50.8 },
    { nombre: 'SUPERMOSCA', peso_max: 52.2 },
    { nombre: 'PESO GALLO', peso_max: 53.5 },
    { nombre: 'SUPERGALLO', peso_max: 55.3 },
    { nombre: 'PESO PLUMA', peso_max: 57.2 },
    { nombre: 'SUPERPLUMA', peso_max: 59.0 },
    { nombre: 'PESO LIGERO', peso_max: 61.2 },
    { nombre: 'SUPERLIGERO', peso_max: 63.5 },
    { nombre: 'PESO WELTER', peso_max: 66.7 },
    { nombre: 'SUPERWELTER', peso_max: 69.9 },
    { nombre: 'PESO MEDIANO', peso_max: 72.6 },
    { nombre: 'SUPERMEDIANO', peso_max: 76.2 },
    { nombre: 'SEMIPESADO', peso_max: 79.4 },
    { nombre: 'PESO PESADO', peso_max: 90.7 },
    { nombre: 'PESO PESADO', peso_max: Infinity },
] as const;
