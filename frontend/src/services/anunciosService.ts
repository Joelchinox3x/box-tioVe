import api from './api';
import { Platform } from 'react-native';
import type { Anuncio } from '../types';

export const anunciosService = {
    /**
     * Obtener anuncios publicos (activos y publicados)
     */
    getPublicos: async (eventoId?: number, limit?: number): Promise<Anuncio[]> => {
        try {
            let url = '/anuncios?publico=1';
            if (eventoId) url += `&evento_id=${eventoId}`;
            if (limit) url += `&limit=${limit}`;
            const response = await api.get(url);
            return response.data.success ? response.data.data : [];
        } catch (error) {
            console.error('Error fetching anuncios publicos:', error);
            return [];
        }
    },

    /**
     * Obtener todos los anuncios (admin)
     */
    getAll: async (): Promise<Anuncio[]> => {
        try {
            const response = await api.get('/anuncios');
            return response.data.success ? response.data.data : [];
        } catch (error) {
            console.error('Error fetching all anuncios:', error);
            return [];
        }
    },

    /**
     * Crear anuncio con texto (JSON)
     */
    crear: async (data: {
        titulo: string;
        mensaje: string;
        tipo: Anuncio['tipo'];
        medio?: Anuncio['medio'];
        fijado?: boolean;
        evento_id?: number | null;
        fecha_publicacion?: string | null;
        fecha_expiracion?: string | null;
        link_url?: string;
    }): Promise<any> => {
        try {
            const response = await api.post('/anuncios', data);
            return response.data;
        } catch (error) {
            console.error('Error creando anuncio:', error);
            throw error;
        }
    },

    /**
     * Crear anuncio con media (imagen o video)
     */
    crearConMedia: async (
        data: {
            titulo: string;
            mensaje: string;
            tipo: Anuncio['tipo'];
            fijado?: boolean;
            evento_id?: number | null;
            fecha_publicacion?: string | null;
            fecha_expiracion?: string | null;
        },
        mediaUri: string,
        mediaType: 'imagen' | 'video',
        fileName: string,
        fileType: string,
    ): Promise<any> => {
        const formData = new FormData();

        formData.append('titulo', data.titulo);
        formData.append('mensaje', data.mensaje);
        formData.append('tipo', data.tipo);
        formData.append('medio', mediaType);
        if (data.fijado) formData.append('fijado', '1');
        if (data.evento_id) formData.append('evento_id', data.evento_id.toString());
        if (data.fecha_publicacion) formData.append('fecha_publicacion', data.fecha_publicacion);
        if (data.fecha_expiracion) formData.append('fecha_expiracion', data.fecha_expiracion);

        const fieldName = mediaType === 'imagen' ? 'imagen' : 'video';

        if (Platform.OS === 'web') {
            const response = await fetch(mediaUri);
            const blob = await response.blob();
            formData.append(fieldName, blob, fileName);
        } else {
            formData.append(fieldName, {
                uri: mediaUri,
                name: fileName,
                type: fileType,
            } as any);
        }

        try {
            const response = await api.post('/anuncios', formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
            });
            return response.data;
        } catch (error) {
            console.error('Error creando anuncio con media:', error);
            throw error;
        }
    },

    /**
     * Actualizar anuncio
     */
    actualizar: async (id: number, data: Partial<Anuncio>): Promise<any> => {
        try {
            const response = await api.put(`/anuncios/${id}`, data);
            return response.data;
        } catch (error) {
            console.error('Error actualizando anuncio:', error);
            throw error;
        }
    },

    /**
     * Toggle activo/inactivo
     */
    toggleActive: async (id: number, activo: boolean): Promise<any> => {
        try {
            const response = await api.put(`/anuncios/${id}`, { activo: activo ? 1 : 0 });
            return response.data;
        } catch (error) {
            console.error('Error toggling anuncio:', error);
            throw error;
        }
    },

    /**
     * Toggle fijado
     */
    toggleFijado: async (id: number, fijado: boolean): Promise<any> => {
        try {
            const response = await api.put(`/anuncios/${id}`, { fijado: fijado ? 1 : 0 });
            return response.data;
        } catch (error) {
            console.error('Error toggling fijado:', error);
            throw error;
        }
    },

    /**
     * Eliminar anuncio
     */
    eliminar: async (id: number): Promise<any> => {
        try {
            const response = await api.delete(`/anuncios/${id}`);
            return response.data;
        } catch (error) {
            console.error('Error eliminando anuncio:', error);
            throw error;
        }
    },
};
