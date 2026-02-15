/**
 * Configuración centralizada de URLs y dominios
 *
 * Para cambiar el dominio en el futuro, solo modifica la variable BASE_URL en .env
 * Variable de entorno: EXPO_PUBLIC_API_URL
 */

// Obtener la URL de la API desde variables de entorno o usar fallback
const API_URL = process.env.EXPO_PUBLIC_API_URL || 'https://boxtiove.com/api';

// Extraer la URL base quitando '/api' del final
const BASE_URL = API_URL.replace(/\/api\/?$/, '');

/**
 * Configuración de URLs del proyecto
 */
export const Config = {
  // URL base del dominio (sin trailing slash)
  BASE_URL,

  // URL de la API
  API_URL,

  // Rutas de archivos públicos
  // NOTA: STORAGE_PATH no se usa - el backend guarda rutas completas (ej: "files/peleadores/foto.jpg")
  // Por lo tanto, solo concatenamos BASE_URL + ruta desde BD
  FILES_PATH: `${BASE_URL}/files/`,
  UPLOADS_PATH: `${BASE_URL}/uploads/`,
  ASSETS_PATH: `${BASE_URL}/assets/`,

  // Rutas específicas
  PELEADORES_FILES_PATH: `${BASE_URL}/files/peleadores/`,
  CARDS_PATH: `${BASE_URL}/files/cards/`, // ⚠️ Deprecated: Las cards ahora se guardan en /files/peleadores/{id}/
  COMPROBANTES_PATH: `${BASE_URL}/uploads/comprobantes/`,
  EVENTOS_ASSETS_PATH: `${BASE_URL}/assets/eventos/`,
  ANUNCIOS_FILES_PATH: `${BASE_URL}/files/anuncios/`,

  // Endpoints específicos
  BG_REMOVER_ENDPOINT: `${API_URL}/bg-remover`,
  TEMP_UPLOAD_ENDPOINT: `${API_URL}/temp-upload`,
  SETTINGS_BG_REMOVER_MODE: `${API_URL}/settings/bg_remover_mode`,

  // Info de pagos (personaliza en .env si aplica)
  PAYMENT_YAPE_PHONE: process.env.EXPO_PUBLIC_YAPE_PHONE || '934 567 890',
  PAYMENT_YAPE_QR_URL: process.env.EXPO_PUBLIC_YAPE_QR_URL || '',
};

/**
 * Obtener la URL completa de un archivo
 */
export const getFileUrl = (path: string, type: 'peleadores' | 'cards' | 'comprobantes' | 'eventos' | 'files' | 'uploads' | 'assets' = 'files'): string => {
  // Si la ruta ya es una URL completa, devolverla tal cual
  if (path.startsWith('http://') || path.startsWith('https://')) {
    return path;
  }

  // Remover slash inicial si existe
  const cleanPath = path.replace(/^\/+/, '');

  switch (type) {
    case 'peleadores':
      return `${Config.PELEADORES_FILES_PATH}${cleanPath}`;
    case 'cards':
      return `${Config.CARDS_PATH}${cleanPath}`;
    case 'comprobantes':
      return `${Config.COMPROBANTES_PATH}${cleanPath}`;
    case 'eventos':
      return `${Config.EVENTOS_ASSETS_PATH}${cleanPath}`;
    case 'files':
      return `${Config.FILES_PATH}${cleanPath}`;
    case 'uploads':
      return `${Config.UPLOADS_PATH}${cleanPath}`;
    case 'assets':
      return `${Config.ASSETS_PATH}${cleanPath}`;
    default:
      // Por defecto, usa BASE_URL directamente (para rutas que ya incluyen el directorio)
      return `${Config.BASE_URL}/${cleanPath}`;
  }
};

/**
 * Validar si una ruta es relativa o absoluta
 */
export const isAbsoluteUrl = (path: string): boolean => {
  return path.startsWith('http://') || path.startsWith('https://');
};

/**
 * Convertir una ruta relativa a URL absoluta
 */
export const toAbsoluteUrl = (path: string): string => {
  if (isAbsoluteUrl(path)) {
    return path;
  }
  return `${BASE_URL}${path.startsWith('/') ? '' : '/'}${path}`;
};

export default Config;
