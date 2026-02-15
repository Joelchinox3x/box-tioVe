import axios from 'axios';
import { Config } from '../config/config';

// Configuración de la API desde archivo centralizado
const API_BASE_URL = Config.API_URL;

// Crear instancia de axios
const api = axios.create({
  baseURL: API_BASE_URL,
  timeout: 30000,
  headers: {
    'Content-Type': 'application/json',
    // Nota: 'Host' es automático y lo maneja el navegador
  },
});

// Interceptor de solicitudes
api.interceptors.request.use(
  (config) => {
    // console.log('🌐 [API REQUEST]', {
    //   method: config.method?.toUpperCase(),
    //   url: config.url,
    //   baseURL: config.baseURL,
    //   fullURL: `${config.baseURL}${config.url}`
    // });
    // Aquí puedes agregar tokens de autenticación
    // const token = await AsyncStorage.getItem('token');
    // if (token) {
    //   config.headers.Authorization = `Bearer ${token}`;
    // }
    return config;
  },
  (error) => {
    // console.error('❌ [API REQUEST ERROR]', error);
    return Promise.reject(error);
  }
);

// Interceptor de respuestas
api.interceptors.response.use(
  (response) => {
    // console.log('✅ [API RESPONSE]', {
    //   status: response.status,
    //   url: response.config.url,
    //   data: response.data
    // });
    return response;
  },
  (error) => {
    if (error.response) {
      // Error de respuesta del servidor
      // console.error('❌ [API ERROR - Response]', {
      //   status: error.response.status,
      //   data: error.response.data,
      //   url: error.config?.url
      // });
    } else if (error.request) {
      // Error de red
      // console.error('❌ [API ERROR - Network]', {
      //   message: 'No se pudo conectar con el servidor',
      //   url: error.config?.url,
      //   baseURL: error.config?.baseURL
      // });
    } else {
      // console.error('❌ [API ERROR - Unknown]', error.message);
    }
    return Promise.reject(error);
  }
);

export default api;
