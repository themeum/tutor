import { wpAjaxInstance, wpAuthApiInstance } from '@Utils/api';
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
  return wpAjaxInstance
    .get<InstructorListResponse[]>(endpoints.TUTOR_INSTRUCTOR_SEARCH, {
      params: { course_id: courseId },
    })
    .then((response) => response.data);
};

export const useInstructorListQuery = (courseId: string) => {
  return useQuery({
    queryKey: ['InstructorList', courseId],
    queryFn: () =>
      getInstructorList(courseId).then((res) => {
        return res.map((item) => ({
          id: item.id,
          name: item.display_name,
          email: item.user_email,
          avatar_url: item.avatar_url,
        }));
      }),
  });
};
