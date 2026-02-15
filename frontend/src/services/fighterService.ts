import api from './api';
import { Platform } from 'react-native';
import { Config } from '../config/config';

// URL Base de tu API desde configuración centralizada
const API_URL = Config.API_URL;

export interface FighterRegistrationData {
  nombre: string;
  apellidos?: string;
  email: string;
  password: string;
  telefono: string;
  apodo: string;
  edad?: number | string;
  fecha_nacimiento?: string | null;
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
  baked_url?: string;
  error_detail?: string;
  debug_info?: any;
}

export const fighterService = {
  /**
   * Registra un nuevo peleador usando FETCH nativo para asegurar la subida de imagen
   */
  async register(data: FighterRegistrationData | FormData): Promise<FighterRegistrationResponse> {

    // CASO 1: Si es FormData (con imagen) - Usamos FETCH nativo
    if (data instanceof FormData) {
      try {
        console.log('📤 [UPLOAD] Enviando FormData con fetch nativo...');
        console.log('📤 [UPLOAD] API_URL:', API_URL);
        console.log('📤 [UPLOAD] Endpoint completo:', `${API_URL}/peleadores`);

        const response = await fetch(`${API_URL}/peleadores`, {
          method: 'POST',
          body: data,
          headers: {
            'Accept': 'application/json',
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

  async verificarEmail(email: string): Promise<{ success: boolean; disponible: boolean; mensaje: string }> {
    const response = await api.get(`/usuarios/verificar-email?email=${encodeURIComponent(email)}`);
    return response.data;
  },

  /**
   * Obtiene los datos de un peleador por su user_id
   */
  async getByUserId(userId: number) {
    const response = await api.get(`/peleadores/usuario/${userId}`);
    return response.data;
  },

  /**
   * Obtiene el estado de inscripción al evento activo
   */
  async getInscripcionEvento(peleadorId: number) {
    const response = await api.get(`/peleadores/${peleadorId}/inscripcion-evento`);
    return response.data;
  },

  /**
   * Crear inscripción al evento (sin pago aún)
   */
  async crearInscripcion(peleadorId: number, eventoId: number) {
    const response = await api.post(`/peleadores/${peleadorId}/crear-inscripcion`, {
      evento_id: eventoId,
    });
    return response.data;
  },

  /**
   * Enviar pago de inscripción al evento
   */
  async inscribirEvento(
    peleadorId: number,
    eventoId: number,
    metodoPago: string,
    comprobante?: { uri: string; name: string; type: string }
  ) {
    if (comprobante) {
      const formData = new FormData();
      formData.append('evento_id', String(eventoId));
      formData.append('metodo_pago', metodoPago);

      if (Platform.OS === 'web') {
        const response = await fetch(comprobante.uri);
        const blob = await response.blob();
        formData.append('comprobante', blob, comprobante.name);
      } else {
        formData.append('comprobante', {
          uri: comprobante.uri,
          name: comprobante.name,
          type: comprobante.type,
        } as any);
      }

      const response = await api.post(`/peleadores/${peleadorId}/inscribir-evento`, formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });
      return response.data;
    }

    const response = await api.post(`/peleadores/${peleadorId}/inscribir-evento`, {
      evento_id: eventoId,
      metodo_pago: metodoPago,
    });
    return response.data;
  },

  /**
   * Obtiene el manager activo para contacto por WhatsApp segun rol
   */
  async getManagerContacto(rol: 'manager_peleadores' | 'manager_cobros' | 'manager_general' = 'manager_peleadores'): Promise<{ success: boolean; manager: { id: number; nombre_visible: string; telefono_whatsapp: string; mensaje_base: string; rol: string } | null }> {
    const response = await api.get(`/peleadores/manager-contacto?rol=${rol}`);
    return response.data;
  },

  /**
   * Registra asignacion de manager a peleador
   */
  async registrarAsignacion(peleadorId: number, managerId: number, motivo: 'registro' | 'pago' | 'soporte' | 'manual' = 'registro') {
    const response = await api.post(`/peleadores/${peleadorId}/asignar-manager`, {
      manager_id: managerId,
      motivo,
      canal: 'whatsapp',
    });
    return response.data;
  },

  /**
   * Actualiza el perfil de un peleador
   */
  async updateProfile(id: number, data: FormData | any) {
    if (data instanceof FormData) {
      const response = await fetch(`${API_URL}/peleadores/${id}`, {
        method: 'POST',
        body: data,
        headers: {
          'Accept': 'application/json',
        },
      });
      const result = await response.json();
      if (!response.ok) throw { response: { data: result, status: response.status } };
      return result;
    }

    const response = await api.put(`/peleadores/${id}`, data);
    return response.data;
  },
};
