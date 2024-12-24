// import { wpAjaxInstance } from '@Utils/api';
// import endpoints from '@Utils/endpoints';
import { useQuery } from '@tanstack/react-query';

export interface Membership {
  id: number;
  name: string;
  price: number;
  duration: number;
  duration_type: string;
  description: string;
  is_default: boolean;
}

export interface MembershipSettings {
  memberships: Membership[];
}

const getMembershipSettings = () => {
  return Promise.resolve<MembershipSettings | null>(null);
  //   return wpAjaxInstance.get<MembershipSettings>(endpoints.GET_MEMBERSHIP_SETTINGS).then((response) => response.data);
};

export const useMembershipSettingsQuery = () => {
  return useQuery({
    queryKey: ['MembershipSettings'],
    queryFn: getMembershipSettings,
  });
};
