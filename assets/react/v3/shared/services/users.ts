import { authApiInstance, wpAuthApiInstance } from '@Utils/api';
import endpoints from '@Utils/endpoints';
import { useQuery } from '@tanstack/react-query';
import type { AxiosResponse } from 'axios';

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

export interface InstructorListResponse {
  id: number;
  avatar_url: string;
  display_name: string;
  user_email: string;
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

const getInstructorList = (courseId: string) => {
  return authApiInstance.post<string, AxiosResponse<InstructorListResponse[]>>(endpoints.ADMIN_AJAX, {
    action: 'tutor_course_instructor_search',
    course_id: courseId,
  });
};

export const useInstructorListQuery = (courseId: string) => {
  return useQuery({
    queryKey: ['InstructorList', courseId],
    queryFn: () =>
      getInstructorList(courseId).then((res) => {
        return res.data.map((item) => ({
          id: item.id,
          name: item.display_name,
          email: item.user_email,
          avatar_url: item.avatar_url,
        }));
      }),
  });
};
