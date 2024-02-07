import { useQuery } from '@tanstack/react-query';
import { authWPApiInstance } from '@Utils/api';
import endpoints from '@Utils/endpoints';

export interface User {
  id: number;
  name: string;
  email: string;
  avatar_urls: {
    24: string;
    48: string;
    96: string;
  };
}

export interface UserParams {
  [key: string]: number | string | string[]
}

const getUserList = (params: UserParams) => {
  return authWPApiInstance.get<User[]>(endpoints.USERS, { params });
};

export const useUserListQuery = (params: UserParams) => {
  return useQuery({
    queryKey: ['UserList', params],
    queryFn: () => getUserList(params).then((res) => res.data),
  });
};
