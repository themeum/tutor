import axios from 'axios';

// eslint-disable-next-line @typescript-eslint/no-explicit-any
const API_BASE = (window as any).tutorConfig?.ajaxurl || '/wp-admin/admin-ajax.php';
// eslint-disable-next-line @typescript-eslint/no-explicit-any
const NONCE = (window as any).tutorConfig?.nonce || '';
// eslint-disable-next-line @typescript-eslint/no-explicit-any
const NONCE_KEY = (window as any).tutorConfig?.nonce_key || '_tutor_nonce';

export interface SettingsApiResponse {
  success: boolean;
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  data?: any;
  message?: string;
}

export const settingsApi = {
  // Get settings fields structure
  getSettingsFields: async (): Promise<SettingsApiResponse> => {
    const formData = new FormData();
    formData.append('action', 'tutor_get_settings_fields');
    formData.append(NONCE_KEY, NONCE);

    const response = await axios.post(API_BASE, formData);
    return response.data;
  },

  // Get current settings values
  getSettingsValues: async (): Promise<SettingsApiResponse> => {
    const formData = new FormData();
    formData.append('action', 'tutor_get_settings_values');
    formData.append(NONCE_KEY, NONCE);

    const response = await axios.post(API_BASE, formData);
    return response.data;
  },

  // Save settings
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  saveSettings: async (settings: Record<string, any>): Promise<SettingsApiResponse> => {
    const formData = new FormData();
    formData.append('action', 'tutor_option_save');
    formData.append(NONCE_KEY, NONCE);

    // Append settings data
    Object.keys(settings).forEach((key) => {
      formData.append(`tutor_option[${key}]`, settings[key]);
    });

    const response = await axios.post(API_BASE, formData);
    return response.data;
  },

  // Search settings
  searchSettings: async (query: string): Promise<SettingsApiResponse> => {
    const formData = new FormData();
    formData.append('action', 'tutor_search_settings');
    formData.append(NONCE_KEY, NONCE);
    formData.append('query', query);

    const response = await axios.post(API_BASE, formData);
    return response.data;
  },

  // Reset settings to default
  resetSettings: async (section?: string): Promise<SettingsApiResponse> => {
    const formData = new FormData();
    formData.append('action', 'reset_settings_data');
    formData.append(NONCE_KEY, NONCE);
    if (section) {
      formData.append('reset_page', section);
    }

    const response = await axios.post(API_BASE, formData);
    return response.data;
  },
};
