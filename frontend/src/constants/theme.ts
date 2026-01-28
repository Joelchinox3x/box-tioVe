// Tema de la aplicación - Evento Box
import { createShadow } from '../utils/shadows';

export const COLORS = {
  // Colores principales
  primary: '#FFD700',        // Dorado
  secondary: '#000000',      // Negro
  background: '#000000',     // Fondo negro
  surface: '#1a1a1a',       // Superficie oscura

  // Colores de estado
  success: '#10B981',
  warning: '#F59E0B',
  error: '#EF4444',
  info: '#3B82F6',

  // Colores de texto
  text: {
    primary: '#FFFFFF',
    secondary: '#D1D5DB',
    tertiary: '#9CA3AF',
    inverse: '#000000',
  },

  // Colores de borde
  border: {
    primary: '#333333',
    secondary: '#FFD700',
    light: '#444444',
  },

  // Overlays
  overlay: 'rgba(0, 0, 0, 0.8)',
  overlayLight: 'rgba(0, 0, 0, 0.5)',
};

export const SPACING = {
  xs: 4,
  sm: 8,
  md: 16,
  lg: 24,
  xl: 32,
  xxl: 48,
};

export const TYPOGRAPHY = {
  fontSize: {
    xs: 10,
    sm: 12,
    md: 14,
    lg: 16,
    xl: 20,
    xxl: 24,
    xxxl: 32,
  },
  fontWeight: {
    regular: '400' as const,
    medium: '500' as const,
    semibold: '600' as const,
    bold: '700' as const,
  },
};

export const BORDER_RADIUS = {
  sm: 4,
  md: 8,
  lg: 12,
  xl: 16,
  full: 9999,
};

// Sombras con soporte multi-plataforma (Web + iOS/Android)
// Automáticamente usa boxShadow en web y shadowColor/shadowOffset en nativo
export const SHADOWS = {
  sm: createShadow('#000', 0, 1, 0.2, 2, 2),
  md: createShadow('#000', 0, 2, 0.25, 4, 4),
  lg: createShadow('#FFD700', 0, 4, 0.3, 8, 6),
  xl: createShadow('#000', 0, 10, 0.3, 20, 10),
};
