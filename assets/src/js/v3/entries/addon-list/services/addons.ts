import { useMutation, useQuery } from '@tanstack/react-query';
import { useToast } from '@TutorShared/atoms/Toast';
import { tutorConfig } from '@TutorShared/config/config';
import { wpAjaxInstance } from '@TutorShared/utils/api';
import endpoints from '@TutorShared/utils/endpoints';
import { type ErrorResponse } from '@TutorShared/utils/form';
import { convertToErrorMessage } from '@TutorShared/utils/util';

export interface Addon {
  name: string;
  basename?: string;
  description: string;
  url: string;
  is_enabled: boolean | number;
  base_name?: string;
  path?: string;
  required_settings?: boolean;
  required_title?: string;
  required_message?: string;
  thumb_url?: string;
  plugins_required?: string[];
  depend_plugins?: Record<string, string>[];
  is_dependents_installed?: boolean;
  required_pro_plugin?: boolean;
  is_new?: boolean;
}

interface AddonListResponse {
  addons: Addon[];
  success: boolean;
}

interface Response {
  success: boolean;
  data?: {
    message?: string;
  };
}

interface AddonPayload {
  addonFieldNames: string;
  checked: boolean;
}

const getAddonList = () => {
  return wpAjaxInstance.get<AddonListResponse>(endpoints.GET_ADDON_LIST).then((response) => response.data);
};

export const useAddonListQuery = () => {
  return useQuery({
    enabled: !!tutorConfig.tutor_pro_url,
    queryKey: ['AddonList'],
    queryFn: () => getAddonList(),
  });
};

const addonEnableDisable = (payload: AddonPayload) => {
  return wpAjaxInstance.post<AddonPayload, Response>(endpoints.ADDON_ENABLE_DISABLE, {
    ...payload,
  });
};

export const useEnableDisableAddon = () => {
  const { showToast } = useToast();

  return useMutation({
    mutationFn: addonEnableDisable,
    onError: (error: ErrorResponse) => {
      showToast({ type: 'danger', message: convertToErrorMessage(error) });
    },
  });
};

interface InstallPluginPayload {
  plugin_slug: string;
}

interface InstallPluginResponse {
  data: string;
  message: string;
  status_code: number;
}

const installPlugin = (payload: InstallPluginPayload) => {
  return wpAjaxInstance.post<InstallPluginPayload, InstallPluginResponse>(endpoints.TUTOR_INSTALL_PLUGIN, {
    ...payload,
  });
};

export const useInstallPlugin = () => {
  const { showToast } = useToast();

  return useMutation({
    mutationFn: installPlugin,
    onError: (error: ErrorResponse) => {
      showToast({ type: 'danger', message: convertToErrorMessage(error) });
    },
  });
};
