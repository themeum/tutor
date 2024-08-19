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
  image: string;
  regular_price: string;
  sale_price: string | null;
  total_course?: number;
  course_duration: string;
  last_updated: string;
  total_enrolled: number;
}

export interface Enrollment {
  course: Course | null;
  students: Student[];
  payment_status: string;
  subscription: string;
}

const getCourseList = (params: PaginatedParams) => {
  return authApiInstance.post<PaginatedResult<Course>>(endpoints.ADMIN_AJAX, {
    action: 'tutor_course_bundle_list',
    ...params,
  });
};

export const useCurseListQuery = (params: PaginatedParams) => {
  return useQuery({
    queryKey: ['CurseList', params],
    placeholderData: keepPreviousData,
    queryFn: () => {
      return getCourseList(params).then((res) => {
        return res.data;
      });
    },
  });
};

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
