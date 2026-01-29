import api from './api';

export interface CardTemplate {
    filename: string;
    url: string;
    type: 'backgrounds' | 'borders' | 'stickers';
}

export const cardTemplateService = {
    /**
     * Obtener fondos disponibles
     */
    getBackgrounds: async (): Promise<CardTemplate[]> => {
        try {
            const response = await api.get('/card-templates/backgrounds');
            // La API retorna { success: true, data: [...] }
            return response.data.success ? response.data.data : [];
        } catch (error) {
            console.error('Error fetching backgrounds:', error);
            return [];
        }
    },

    /**
     * Obtener bordes disponibles
     */
    getBorders: async (): Promise<CardTemplate[]> => {
        try {
            const response = await api.get('/card-templates/borders');
            return response.data.success ? response.data.data : [];
        } catch (error) {
            console.error('Error fetching borders:', error);
            return [];
        }
    },

    /**
     * Obtener stickers disponibles
     */
    getStickers: async (): Promise<CardTemplate[]> => {
        try {
            const response = await api.get('/card-templates/stickers');
            return response.data.success ? response.data.data : [];
        } catch (error) {
            console.error('Error fetching stickers:', error);
            return [];
        }
    }
};
