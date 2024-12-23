import { useToast } from '@Atoms/Toast';
import { wpAjaxInstance } from '@Utils/api';
import endpoints from '@Utils/endpoints';
import type { ErrorResponse } from '@Utils/form';
import type { PaginatedParams, PaginatedResult } from '@Utils/types';
import { convertToErrorMessage } from '@Utils/util';
import { keepPreviousData, useMutation, useQuery } from '@tanstack/react-query';

export interface Student {
  ID: string;
  display_name: string;
  user_email: string;
  is_enrolled: number;
  enrollment_status: string;
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
  is_purchasable: boolean;
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
  return wpAjaxInstance.post<EnrollmentPayload, EnrollmentResponse>(endpoints.CREATE_ENROLLMENT, payload);
};

export const useCreateEnrollmentMutation = () => {
  const { showToast } = useToast();

  return useMutation({
    mutationFn: createEnrollment,
    onSuccess: (response) => {
      showToast({ type: 'success', message: response.message });
    },
    onError: (error: ErrorResponse) => {
      showToast({ type: 'danger', message: convertToErrorMessage(error) });
    },
  });
};

const getCourseList = (params: PaginatedParams) => {
  return wpAjaxInstance.get<PaginatedResult<Course>>(endpoints.GET_COURSE_BUNDLE_LIST, {
    params,
  });
};

export const useCurseListQuery = (params: PaginatedParams) => {
  return useQuery({
    queryKey: ['CurseList', params],
    placeholderData: keepPreviousData,
    queryFn: () => getCourseList(params).then((response) => response.data),
  });
};

interface GetStudentListParams extends PaginatedParams {
  object_id?: number;
}
const getStudentList = (params: GetStudentListParams) => {
  return wpAjaxInstance.get<PaginatedResult<Student>>(endpoints.GET_UNENROLLED_USERS, {
    params,
  });
};

export const useStudentListQuery = (params: GetStudentListParams) => {
  return useQuery({
    queryKey: ['StudentList', params],
    placeholderData: keepPreviousData,
    queryFn: () => getStudentList(params).then((response) => response.data),
  });
};
