// Configuración de sombras - Archivo base
// React Native automáticamente usará shadows.web.ts o shadows.native.ts según la plataforma
// Este archivo es un fallback para TypeScript

export interface ShadowStyle {
  shadowColor?: string;
  shadowOffset?: { width: number; height: number };
  shadowOpacity?: number;
  shadowRadius?: number;
  elevation?: number;
  boxShadow?: string;
}

export interface TextShadowStyle {
  textShadowColor?: string;
  textShadowOffset?: { width: number; height: number };
  textShadowRadius?: number;
  textShadow?: string;
}

/**
 * Crea un estilo de sombra compatible con todas las plataformas
 * En runtime, se usará shadows.web.ts o shadows.native.ts automáticamente
 */
export const createShadow = (
  color: string,
  offsetX: number,
  offsetY: number,
  opacity: number,
  radius: number,
  elevation: number
): ShadowStyle => {
  // Este código nunca se ejecuta en runtime
  // Solo existe para que TypeScript compile correctamente
  return {
    shadowColor: color,
    shadowOffset: { width: offsetX, height: offsetY },
    shadowOpacity: opacity,
    shadowRadius: radius,
    elevation,
  };
};

/**
 * Crea un estilo de sombra de texto compatible con todas las plataformas
 * En runtime, se usará shadows.web.ts o shadows.native.ts automáticamente
 */
export const createTextShadow = (
  color: string,
  offsetX: number,
  offsetY: number,
  radius: number
): TextShadowStyle => {
  // Este código nunca se ejecuta en runtime
  // Solo existe para que TypeScript compile correctamente
  return {
    textShadowColor: color,
    textShadowOffset: { width: offsetX, height: offsetY },
    textShadowRadius: radius,
  };
};
