import { keepPreviousData, useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { format, isBefore, parseISO } from 'date-fns';

import { wpAjaxInstance } from '@/v3/shared/utils/api';
import endpoints from '@/v3/shared/utils/endpoints';
import { useToast } from '@Atoms/Toast';
import { DateFormats } from '@Config/constants';
import { type WPMedia } from '@Hooks/useWpMedia';
import { type ErrorResponse } from '@Utils/form';
import {
  type PaginatedParams,
  type PaginatedResult,
  type TutorCategory,
  type TutorMutationResponse,
  type TutorSellingOption,
  type WPPostStatus,
  type WPUser,
} from '@Utils/types';
import { convertToErrorMessage, convertToGMT } from '@Utils/util';

export type CourseBundleRibbonType = 'in_percentage' | 'in_amount' | 'none';
interface CourseBundleOverview {
  total_courses: number;
  total_topics: number;
  total_quizzes: number;
  total_assignments: number;
  total_video_contents: number;
  total_video_duration: string;
  total_resources: number;
  total_duration: string;
  certificate: boolean;
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
}

export interface Bundle {
  ID: number;
  post_name: string;
  post_title: string;
  post_date: string;
  post_date_gmt: string;
  post_content: string;
  post_status: WPPostStatus;
  post_password: string;
  post_modified: string;
  guid: string;
  ribbon_type: CourseBundleRibbonType;
  course_benefits: string;
  thumbnail: WPMedia;
  regular_price: string;
  sale_price: string;
  course_selling_option: TutorSellingOption;
  details: {
    overview: CourseBundleOverview;
    authors: WPUser[];
    courses: Course[];
    categories: TutorCategory[];
    subtotal_price: string;
    subtotal_raw_price: number;
    course_ids: number[];
  };
}

export interface BundleFormData {
  post_name: string;
  post_title: string;
  post_date: string;
  post_content: string;
  post_status: WPPostStatus;
  post_password: string;
  post_modified: string;
  course_benefits: string;
  visibility: 'publish' | 'private' | 'password_protected';
  thumbnail: WPMedia;
  ribbon_type: CourseBundleRibbonType;
  schedule_date: string;
  schedule_time: string;
  showScheduleForm: boolean;
  isScheduleEnabled: boolean;
  regular_price: string;
  sale_price: string;
  course_selling_option: TutorSellingOption;
  courses: Course[];
  overview: CourseBundleOverview;
  categories: number[];
  instructors: WPUser[];
}

export interface BundlePayload {
  ID?: number;
  post_name: string;
  post_title: string;
  post_content: string;
  post_date?: string;
  post_date_gmt?: string;
  post_status: string;
  post_password: string;
  post_modified: string;
  course_benefits: string;
  thumbnail_id: number | null;
  ribbon_type: string;
  sale_price: string;
  course_selling_option: string;
}

export const defaultCourseBundleData: BundleFormData = {
  post_name: '',
  post_title: '',
  post_date: '',
  post_content: '',
  post_status: 'draft',
  post_password: '',
  post_modified: '',
  course_benefits: '',
  visibility: 'publish',
  thumbnail: {
    id: 0,
    url: '',
    title: '',
  },
  ribbon_type: 'in_percentage',
  schedule_date: '',
  schedule_time: '',
  showScheduleForm: false,
  isScheduleEnabled: false,
  regular_price: '',
  sale_price: '',
  course_selling_option: 'one_time',
  courses: [],
  overview: {
    total_courses: 0,
    total_topics: 0,
    total_quizzes: 0,
    total_assignments: 0,
    total_video_contents: 0,
    total_video_duration: '',
    total_resources: 0,
    total_duration: '',
    certificate: false,
  },
  categories: [],
  instructors: [],
};

export const convertBundleToFormData = (courseBundle: Bundle): BundleFormData => {
  return {
    post_name: courseBundle.post_name ?? '',
    post_title: courseBundle.post_title ?? '',
    post_date: courseBundle.post_date ?? '',
    post_content: courseBundle.post_content ?? '',
    post_status: courseBundle.post_status ?? 'draft',
    post_password: courseBundle.post_password ?? '',
    post_modified: courseBundle.post_modified,
    course_benefits: courseBundle.course_benefits ?? '',
    visibility: (() => {
      if (courseBundle.post_password?.length) {
        return 'password_protected';
      }
      if (courseBundle.post_status === 'private') {
        return 'private';
      }
      return 'publish';
    })(),
    thumbnail: courseBundle.thumbnail,
    ribbon_type: courseBundle.ribbon_type ?? 'in_percentage',
    isScheduleEnabled: isBefore(new Date(), new Date(courseBundle.post_date)),
    showScheduleForm: !isBefore(new Date(), new Date(courseBundle.post_date)),
    schedule_date: !isBefore(parseISO(courseBundle.post_date), new Date())
      ? format(parseISO(courseBundle.post_date), DateFormats.yearMonthDay)
      : '',
    schedule_time: !isBefore(parseISO(courseBundle.post_date), new Date())
      ? format(parseISO(courseBundle.post_date), DateFormats.hoursMinutes)
      : '',
    regular_price: courseBundle.regular_price ?? '',
    sale_price: courseBundle.sale_price ?? '',
    course_selling_option: courseBundle.course_selling_option ?? 'one_time',
    courses: courseBundle.details.courses ?? [],
    overview: courseBundle.details.overview ?? defaultCourseBundleData.overview,
    categories: (courseBundle.details.categories ?? []).map((category) => category.term_id),
    instructors: courseBundle.details.authors ?? [],
  };
};

export const convertBundleFormDataToPayload = (data: BundleFormData): BundlePayload => {
  return {
    ...(data.isScheduleEnabled && {
      post_date: format(
        new Date(`${data.schedule_date} ${data.schedule_time}`),
        DateFormats.yearMonthDayHourMinuteSecond24H,
      ),
      post_date_gmt: convertToGMT(new Date(`${data.schedule_date} ${data.schedule_time}`)),
    }),
    post_name: data.post_name,
    post_title: data.post_title,
    post_content: data.post_content,
    post_status: data.visibility === 'private' ? 'private' : 'publish',
    post_password: data.visibility === 'password_protected' ? data.post_password : '',
    post_modified: data.post_modified,
    course_benefits: data.course_benefits,
    thumbnail_id: data.thumbnail?.id ?? null,
    ribbon_type: data.ribbon_type,
    sale_price: data.sale_price,
    course_selling_option: data.course_selling_option,
  };
};

const getBundleDetails = async (bundleId: number) => {
  return wpAjaxInstance.get<Bundle>(endpoints.GET_BUNDLE_DETAILS, {
    params: {
      bundle_id: bundleId,
    },
  });
};

export const useGetBundleDetails = (bundleId: number) => {
  return useQuery({
    queryKey: ['CourseBundle', bundleId],
    queryFn: () => getBundleDetails(bundleId).then((response) => response.data),
  });
};

const saveCourseBundle = async (payload: BundlePayload) => {
  return wpAjaxInstance.post<BundlePayload, TutorMutationResponse<string>>(endpoints.UPDATE_BUNDLE, payload);
};

export const useSaveCourseBundle = () => {
  const { showToast } = useToast();
  return useMutation({
    mutationFn: saveCourseBundle,
    onSuccess: (response) => {
      showToast({
        message: response.message,
        type: 'success',
      });
    },
    onError: (error: ErrorResponse) => {
      showToast({
        message: convertToErrorMessage(error),
        type: 'danger',
      });
    },
  });
};

const getCourseList = (params: PaginatedParams) => {
  return wpAjaxInstance.get<PaginatedResult<Course>>(endpoints.COUPON_APPLIES_TO, {
    params: {
      ...params,
      applies_to: 'specific_courses',
    },
  });
};

export const useCurseListQuery = (params: PaginatedParams) => {
  return useQuery({
    queryKey: ['CurseList', params],
    placeholderData: keepPreviousData,
    queryFn: () => getCourseList(params).then((response) => response.data),
  });
};

interface AddCourseToBundlePayload {
  ID: number;
  course_id: number;
  user_action: 'add_course' | 'remove_course';
}

const addCourseToBundle = async (payload: AddCourseToBundlePayload) => {
  return wpAjaxInstance.post<AddCourseToBundlePayload, TutorMutationResponse<Pick<Bundle, 'details'>>>(
    endpoints.ADD_COURSE_TO_BUNDLE,
    payload,
  );
};

export const useAddCourseToBundleMutation = () => {
  const { showToast } = useToast();
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: addCourseToBundle,
    onSuccess: (response, payload) => {
      showToast({
        message: response.message,
        type: 'success',
      });

      queryClient.setQueryData(['CourseBundle', payload.ID], (oldData: Bundle) => {
        return {
          ...oldData,
          details: {
            ...oldData.details,
            ...response.data,
          },
        };
      });
    },
    onError: (error: ErrorResponse) => {
      showToast({
        message: convertToErrorMessage(error),
        type: 'danger',
      });
    },
  });
};
