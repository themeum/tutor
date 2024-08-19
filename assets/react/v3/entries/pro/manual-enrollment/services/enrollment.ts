import { keepPreviousData, useQuery } from '@tanstack/react-query';
import { authApiInstance } from '@Utils/api';
import endpoints from '@Utils/endpoints';
import { PaginatedParams, PaginatedResult } from '@Utils/types';

export interface Student {
  id: number;
  display_name: string;
  user_email: string;
  avatar_url: string;
}

export interface Course {
  id: number;
  title: string;
  image: '';
  author: string;
  regular_price: string;
  sale_price: string | null;
}

export interface Enrollment {
  course: Course | null;
  students: Student[];
  payment_status: string;
  subscription: string;
}

const getStudentList = (params: PaginatedParams) => {
  return authApiInstance.post<PaginatedResult<Student>>(endpoints.ADMIN_AJAX, {
    action: 'tutor_user_list',
    ...params,
  });
};

export const useStudentListQuery = (params: PaginatedParams) => {
  return useQuery({
    queryKey: ['StudentList', params],
    placeholderData: keepPreviousData,
    queryFn: () => {
      return getStudentList(params).then((res) => {
        return res.data;
      });
    },
  });
};
