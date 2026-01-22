import api from './api';
import { Platform } from 'react-native';

// URL Base de tu API (Asegúrate de que coincida con la que usas en axios/api.ts)
// Si estás en emulador Android usa 10.0.2.2, si es dispositivo físico usa tu IP local o dominio
const API_URL = 'https://boxtiove.com/api'; 

export interface FighterRegistrationData {
  nombre: string;
  apellidos?: string;
  email: string;
  password: string;
  telefono: string;
  apodo: string;
  fecha_nacimiento: string;
  peso_actual: number;
  altura: number;
  genero: string;
  documento_identidad: string;
  club_id: string | number;
  estilo: string;
  experiencia_anos: number;
  foto_perfil?: string | Blob;
}

export interface FighterRegistrationResponse {
  success: boolean;
  message: string;
  peleador_id?: number;
  error_detail?: string;
}

export const fighterService = {
  /**
   * Registra un nuevo peleador usando FETCH nativo para asegurar la subida de imagen
   */
  async register(data: FighterRegistrationData | FormData): Promise<FighterRegistrationResponse> {
    
    // CASO 1: Si es FormData (con imagen) - Usamos FETCH nativo
    if (data instanceof FormData) {
      try {
        console.log('📤 Enviando FormData con fetch nativo...');
        
        const response = await fetch(`${API_URL}/peleadores`, {
          method: 'POST',
          body: data,
          headers: {
            'Accept': 'application/json',
            // IMPORTANTE: NO poner Content-Type aquí. 
            // Fetch detectará el FormData y pondrá 'multipart/form-data; boundary=...' automáticamente.
          },
        });

        const result = await response.json();
        
        if (!response.ok) {
          throw { response: { data: result, status: response.status } };
        }
        
        return result;
      } catch (error) {
        console.error('Error en fetch upload:', error);
        throw error;
      }
    }

    // CASO 2: JSON normal (sin imagen) - Podemos seguir usando Axios o Fetch
    const response = await api.post<FighterRegistrationResponse>('/peleadores', data);
    return response.data;
  },

  async getAll(filtro?: string, club?: string) {
    const params = new URLSearchParams();
    if (filtro) params.append('filtro', filtro);
    if (club) params.append('club', club);

    const response = await api.get(`/peleadores?${params.toString()}`);
    return response.data;
  },

  async getById(id: number) {
    const response = await api.get(`/peleadores/${id}`);
    return response.data;
  },

  async verificarDNI(dni: string): Promise<{ success: boolean; disponible: boolean; mensaje: string }> {
    const response = await api.get(`/peleadores/${dni}/verificar-dni`);
    return response.data;
  },
};