// Configuración de sombras para iOS y Android
// Este archivo se usa SOLO en plataformas nativas (iOS/Android)

export interface ShadowStyle {
  shadowColor: string;
  shadowOffset: { width: number; height: number };
  shadowOpacity: number;
  shadowRadius: number;
  elevation: number;
}

export interface TextShadowStyle {
  textShadowColor: string;
  textShadowOffset: { width: number; height: number };
  textShadowRadius: number;
}

/**
 * Crea un estilo de sombra compatible con iOS y Android
 *
 * @param color - Color de la sombra en formato hex (#000000)
 * @param offsetX - Desplazamiento horizontal de la sombra
 * @param offsetY - Desplazamiento vertical de la sombra
 * @param opacity - Opacidad de la sombra (0 a 1)
 * @param radius - Radio de difuminado de la sombra
 * @param elevation - Elevación para Android (afecta la sombra)
 * @returns Objeto con las props de sombra para React Native
 */
export const createShadow = (
  color: string,
  offsetX: number,
  offsetY: number,
  opacity: number,
  radius: number,
  elevation: number
): ShadowStyle => {
  return {
    shadowColor: color,
    shadowOffset: { width: offsetX, height: offsetY },
    shadowOpacity: opacity,
    shadowRadius: radius,
    elevation, // Importante para Android
  };
};

/**
 * Crea un estilo de sombra de texto compatible con iOS y Android
 *
 * @param color - Color de la sombra en formato hex o rgba
 * @param offsetX - Desplazamiento horizontal de la sombra
 * @param offsetY - Desplazamiento vertical de la sombra
 * @param radius - Radio de difuminado de la sombra
 * @returns Objeto con las props de sombra de texto para React Native
 */
export const createTextShadow = (
  color: string,
  offsetX: number,
  offsetY: number,
  radius: number
): TextShadowStyle => {
  return {
    textShadowColor: color,
    textShadowOffset: { width: offsetX, height: offsetY },
    textShadowRadius: radius,
  };
};
