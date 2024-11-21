import { TutorRoles } from '@Config/constants';
import { wpAjaxInstance } from '@Utils/api';
import endpoints from '@Utils/endpoints';
import { useQuery } from '@tanstack/react-query';

export interface User {
  id: number;
  name: string;
  email: string;
  avatar_url: string;
}

export interface UserResponse {
  results: {
    id: number;
    display_name: string;
    user_email: string;
    avatar_url: string;
  }[];
  total_items: number;
}

export interface InstructorListResponse {
  id: number;
  avatar_url: string;
  display_name: string;
  user_email: string;
}

const getUserList = (search: string) => {
  return wpAjaxInstance.get<UserResponse>(endpoints.USERS_LIST, {
    params: {
      filter: {
        search,
        role: [TutorRoles.ADMINISTRATOR, TutorRoles.TUTOR_INSTRUCTOR],
      },
    },
  });
};

export const useUserListQuery = (search: string) => {
  return useQuery({
    queryKey: ['UserList', search],
    queryFn: () =>
      getUserList(search).then((res) =>
        res.data.results.map((user) => ({
          id: user.id,
          name: user.display_name,
          email: user.user_email,
          avatar_url: user.avatar_url,
        })),
      ),
  });
};

const getInstructorList = (courseId: string) => {
  return wpAjaxInstance
    .get<InstructorListResponse[]>(endpoints.TUTOR_INSTRUCTOR_SEARCH, {
      params: { course_id: courseId },
    })
    .then((response) => response.data);
};

export const useInstructorListQuery = (courseId: string, isEnabled: boolean) => {
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
    enabled: isEnabled,
  });
};
