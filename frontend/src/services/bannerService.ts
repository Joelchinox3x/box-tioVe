import api from './api';
import { Platform } from 'react-native';

export interface Banner {
    id: number;
    filename: string;
    original_name: string;
    active: boolean;
    orden: number;
    url: string;
}

export const bannerService = {
    /**
     * Obtener lista de banners
     * @param admin Si es true, trae todos (incluso inactivos)
     */
    getAll: async (admin = false): Promise<Banner[]> => {
        try {
            const url = admin ? '/banners?admin=1' : '/banners';
            const response = await api.get(url);
            return response.data.success ? response.data.data : [];
        } catch (error) {
            console.error('Error fetching banners:', error);
            return [];
        }
    },

    /**
     * Subir nuevo banner
     */
    upload: async (fileUri: string, fileName: string, fileType: string): Promise<any> => {
        const formData = new FormData();

        // Preparar archivo para FormData
        // En React Native el objeto file es diferente para Web y Nativo
        if (Platform.OS === 'web') {
            // En web necesitamos convertir el URI blob a File si viene de un picker
            const response = await fetch(fileUri);
            const blob = await response.blob();
            formData.append('imagen', blob, fileName);
        } else {
            // En nativo
            formData.append('imagen', {
                uri: fileUri,
                name: fileName,
                type: fileType,
            } as any);
        }

        try {
            const response = await api.post('/banners', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            });
            return response.data;
        } catch (error) {
            console.error('Error uploading banner:', error);
            throw error;
        }
    },

    /**
     * Activar/Desactivar banner
     */
    toggleActive: async (id: number, active: boolean): Promise<any> => {
        try {
            const response = await api.put(`/banners/${id}`, { active });
            return response.data;
        } catch (error) {
            console.error('Error toggling banner:', error);
            throw error;
        }
    },

    /**
     * Eliminar banner
     */
    delete: async (id: number): Promise<any> => {
        try {
            const response = await api.delete(`/banners/${id}`);
            return response.data;
        } catch (error) {
            console.error('Error deleting banner:', error);
            throw error;
        }
    }
};
