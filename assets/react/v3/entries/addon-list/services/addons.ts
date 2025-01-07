import endpoints from '@TutorShared/utils/endpoints';
import { wpAjaxInstance } from '@TutorShared/utils/api';
import { tutorConfig } from '@TutorShared/config/config';
import { useMutation, useQuery } from '@tanstack/react-query';
import { convertToErrorMessage } from '@TutorShared/utils/util';
import { type ErrorResponse } from '@TutorShared/utils/form';
import { useToast } from '@TutorShared/atoms/Toast';

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
  is_new?: boolean;
}

interface AddonListResponse {
  addons: Addon[];
  success: boolean;
}

interface Response {
  success: boolean;
}

interface AddonPayload {
  addonFieldNames: string;
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
