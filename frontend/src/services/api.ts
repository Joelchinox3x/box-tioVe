import axios from 'axios';

// Configuración de la API
const API_BASE_URL = 'https://boxtiove.com/api';

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
    // Aquí puedes agregar tokens de autenticación
    // const token = await AsyncStorage.getItem('token');
    // if (token) {
    //   config.headers.Authorization = `Bearer ${token}`;
    // }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Interceptor de respuestas
api.interceptors.response.use(
  (response) => {
    return response;
  },
  (error) => {
    if (error.response) {
      // Error de respuesta del servidor
      console.error('API Error:', error.response.data);
    } else if (error.request) {
      // Error de red
      console.error('Network Error:', error.request);
    } else {
      console.error('Error:', error.message);
    }
    return Promise.reject(error);
  }
);

export default api;
