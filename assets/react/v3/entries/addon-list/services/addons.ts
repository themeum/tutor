import endpoints from '@Utils/endpoints';
import { wpAjaxInstance } from '@Utils/api';
import { tutorConfig } from '@Config/config';
import { useQuery } from '@tanstack/react-query';

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
}

interface AddonListResponse {
  addons: Addon[];
  success: boolean;
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
