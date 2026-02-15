import api from './api';

export const SettingsService = {
    getSetting: async (key: string) => {
        try {
            const response = await api.get(`/settings/${key}`);
            return response.data;
        } catch (error) {
            console.error(`Error fetching setting ${key}:`, error);
            return { success: false, value: null };
        }
    },

    getAllSettings: async () => {
        try {
            const response = await api.get('/settings');
            return response.data;
        } catch (error) {
            console.error('Error fetching all settings:', error);
            return { success: false, settings: [] };
        }
    },

    updateSetting: async (key: string, value: string, description?: string) => {
        try {
            const response = await api.put(`/settings/${key}`, { value, description });
            return response.data;
        } catch (error) {
            console.error(`Error updating setting ${key}:`, error);
            return { success: false };
        }
    },

    getCropToolVersion: async () => {
        const res = await SettingsService.getSetting('crop_tool_version');
        if (res.success && res.value) return res.value;
        return 'legacy';
    }
};
