// Configuración de sombras para Web
// Este archivo se usa SOLO en plataforma web (navegadores)

export interface ShadowStyle {
  boxShadow: string;
}

export interface TextShadowStyle {
  textShadow: string;
}

/**
 * Crea un estilo de sombra compatible con Web usando boxShadow
 *
 * @param color - Color de la sombra en formato hex (#000000)
 * @param offsetX - Desplazamiento horizontal de la sombra
 * @param offsetY - Desplazamiento vertical de la sombra
 * @param opacity - Opacidad de la sombra (0 a 1)
 * @param radius - Radio de difuminado de la sombra
 * @param elevation - No se usa en web, solo para compatibilidad de firma
 * @returns Objeto con la prop boxShadow para React Native Web
 */
// Helper para convertir color hex a rgba
const hexToRgba = (hex: string, alpha: number): string => {
  // Remover el # si existe
  const cleanHex = hex.replace('#', '');

  // Convertir hex a RGB
  const r = parseInt(cleanHex.substring(0, 2), 16);
  const g = parseInt(cleanHex.substring(2, 4), 16);
  const b = parseInt(cleanHex.substring(4, 6), 16);

  return `rgba(${r}, ${g}, ${b}, ${alpha})`;
};

export const createShadow = (
  color: string,
  offsetX: number,
  offsetY: number,
  opacity: number,
  radius: number,
  elevation?: number // Opcional, no se usa en web
): ShadowStyle => {
  const shadowColor = hexToRgba(color, opacity);

  return {
    boxShadow: `${offsetX}px ${offsetY}px ${radius}px ${shadowColor}`,
  };
};

/**
 * Crea un estilo de sombra de texto compatible con Web usando textShadow
 *
 * @param color - Color de la sombra en formato hex o rgba
 * @param offsetX - Desplazamiento horizontal de la sombra
 * @param offsetY - Desplazamiento vertical de la sombra
 * @param radius - Radio de difuminado de la sombra
 * @returns Objeto con la prop textShadow para React Native Web
 */
export const createTextShadow = (
  color: string,
  offsetX: number,
  offsetY: number,
  radius: number
): TextShadowStyle => {
  // Si el color ya viene en formato rgba, usarlo directamente
  const shadowColor = color.startsWith('rgba') || color.startsWith('rgb')
    ? color
    : color;

  return {
    textShadow: `${offsetX}px ${offsetY}px ${radius}px ${shadowColor}`,
  };
};
