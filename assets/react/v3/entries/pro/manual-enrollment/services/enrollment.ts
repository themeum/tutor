import { useToast } from '@Atoms/Toast';
import { keepPreviousData, useMutation, useQuery } from '@tanstack/react-query';
import { authApiInstance } from '@Utils/api';
import endpoints from '@Utils/endpoints';
import { ErrorResponse } from '@Utils/form';
import { PaginatedParams, PaginatedResult } from '@Utils/types';

export interface Student {
  ID: string;
  display_name: string;
  user_email: string;
  avatar_url: string;
}

interface Plan {
  id: number;
  payment_type: string;
  plan_type: string;
  restriction_mode: string | null;
  plan_name: string;
  description: string | null;
  is_featured: string;
  featured_text: string | null;
  recurring_value: string;
  recurring_interval: string;
  plan_duration: string;
  regular_price: string;
  sale_price: string;
  sale_price_from: string | null;
  sale_price_to: string | null;
  provide_certificate: string;
  enrollment_fee: string;
  trial_value: string;
  trial_interval: string | null;
  plan_order: string;
  plan_id: string;
  object_name: string;
  object_id: string;
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
  plan_start_price?: string;
  plans?: Plan[];
}

export interface Enrollment {
  course: Course | null;
  students: Student[];
  payment_status: string;
  subscription: string;
}

interface EnrollmentPayload {
  student_ids: string[];
  object_ids: number[];
  payment_status: string;
  order_type: string;
}

interface EnrollmentResponse {
  status_code: number;
  message: string;
}

const createEnrollment = (payload: EnrollmentPayload) => {
  return authApiInstance.post<EnrollmentPayload, EnrollmentResponse>(endpoints.ADMIN_AJAX, {
    action: 'tutor_enroll_bulk_student',
    ...payload,
  });
};

export const useCreateEnrollmentMutation = () => {
  const { showToast } = useToast();

  return useMutation({
    mutationFn: createEnrollment,
    onSuccess: (response) => {
      showToast({ type: 'success', message: response.message });
    },
    onError: (error: ErrorResponse) => {
      showToast({ type: 'danger', message: error.response.data.message });
    },
  });
};

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

interface GetStudentListParams extends PaginatedParams {
  object_id?: number;
}
const getStudentList = (params: GetStudentListParams) => {
  return authApiInstance.post<PaginatedResult<Student>>(endpoints.ADMIN_AJAX, {
    action: 'tutor_unenrolled_users',
    ...params,
  });
};

export const useStudentListQuery = (params: GetStudentListParams) => {
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
