import api from './api';

export interface Club {
  id: number;
  nombre: string;
  direccion: string | null;
  telefono: string | null;
  email: string | null;
  logo: string | null;
  descripcion: string | null;
}

export interface ClubsResponse {
  success: boolean;
  total: number;
  clubs: Club[];
}

/**
 * Servicio para manejar operaciones con clubs
 */
export const clubService = {
  /**
   * Obtiene la lista de todos los clubs activos
   */
  async getAll(): Promise<Club[]> {
    try {
      console.log('Fetching clubs from API...');
      const response = await api.get<ClubsResponse>('/clubs');
      console.log('Clubs response:', response.data);
      return response.data.clubs;
    } catch (error) {
      console.error('Error in clubService.getAll():', error);
      throw error;
    }
  },

  /**
   * Obtiene un club por ID
   */
  async getById(id: number): Promise<Club> {
    const response = await api.get(`/clubs/${id}`);
    return response.data.club;
  },

  /**
   * Obtiene los peleadores de un club
   */
  async getPeleadores(id: number) {
    const response = await api.get(`/clubs/${id}/peleadores`);
    return response.data.peleadores;
  },
};
