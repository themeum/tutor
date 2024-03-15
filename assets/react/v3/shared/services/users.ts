import { wpAuthApiInstance } from '@Utils/api';
import endpoints from '@Utils/endpoints';
import { useQuery } from '@tanstack/react-query';

export interface User {
	id: number;
	name: string;
	email: string;
	avatar_url: string;
}

export interface UserResponse {
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
	context: string;
	roles: string[];
	search?: string;
}

const getUserList = (params: UserParams) => {
	return wpAuthApiInstance.get<UserResponse[]>(endpoints.USERS, { params });
};

export const useUserListQuery = (params: UserParams) => {
	return useQuery({
		queryKey: ['UserList', params],
		queryFn: () => getUserList(params).then((res) => res.data),
	});
};
