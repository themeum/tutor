import { useToast } from '@Atoms/Toast';
import type { Media } from '@Components/fields/FormImageInput';
import { tutorConfig } from '@Config/config';
import type { Tag } from '@Services/tags';
import type { InstructorListResponse, User } from '@Services/users';
import { authApiInstance } from '@Utils/api';
import endpoints from '@Utils/endpoints';
import type { ErrorResponse } from '@Utils/form';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import type { AxiosResponse } from 'axios';

const currentUser = tutorConfig.current_user.data;

type CourseLevel = 'all_levels' | 'beginner' | 'intermediate' | 'expert';

export const courseDefaultData: CourseFormData = {
  post_date: '',
  post_name: '',
  post_title: '',
  post_content: '',
  post_status: 'publish',
  post_password: '',
  post_author: {
    id: Number(currentUser.id),
    name: currentUser.display_name,
    email: currentUser.user_email,
    avatar_url: '',
  },
  thumbnail: null,
  video: {
    source_type: '',
    source: '',
  },
  course_price_type: 'free',
  course_price: '',
  course_sale_price: '',
  course_categories: [],
  course_tags: [],
  course_instructors: [],
  enable_qna: false,
  is_public_course: false,
  course_level: 'beginner',
  maximum_students: null,
  enrollment_expiry: 0,
  course_benefits: '',
  course_requirements: '',
  course_target_audience: '',
  course_material_includes: '',
  course_duration_hours: 0,
  course_duration_minutes: 0,
  attachments: null,
  isContentDripEnabled: false,
  contentDripType: '',
  course_product_id: '',
  preview_link: '',
  course_prerequisites_ids: [],
};

export interface CourseFormData {
  post_date: string;
  post_title: string;
  post_name: string;
  post_content: string;
  post_status: 'publish' | 'private' | 'password_protected';
  post_password: string;
  post_author: User | null;
  thumbnail: Media | null;
  video: {
    source_type: string;
    source: string;
  };
  course_price_type: string;
  course_price: string;
  course_sale_price: string;
  course_categories: number[];
  course_tags: Tag[];
  course_instructors: User[];
  enable_qna: boolean;
  is_public_course: boolean;
  course_level: CourseLevel;
  maximum_students: number | null;
  enrollment_expiry: number;
  course_benefits: string;
  course_requirements: string;
  course_target_audience: string;
  course_material_includes: string;
  course_duration_hours: number;
  course_duration_minutes: number;
  attachments: Media[] | null;
  isContentDripEnabled: boolean;
  contentDripType: 'unlock_by_date' | 'specific_days' | 'unlock_sequentially' | 'after_finishing_prerequisites' | '';
  course_product_id: string;
  preview_link: string;
  course_prerequisites_ids: string[];
}

export interface CoursePayload {
  course_id?: number;
  post_date: string;
  post_title: string;
  post_name: string;
  post_content: string;
  post_status: 'publish' | 'private';
  post_password: string;
  post_author: number | null;
  thumbnail_id: number | null;
  video?: {
    source_type: string;
    source: string;
  };
  course_price_type?: string;
  course_price?: string;
  course_sale_price?: string;
  course_categories: number[];
  course_tags: number[];
  course_instructors?: number[];
  enable_qna: string;
  is_public_course: string;
  course_level: CourseLevel;
  course_settings: {
    maximum_students?: number;
    enable_content_drip?: boolean;
    content_drip_type?: string;
    enrollment_expiry?: number;
    enable_tutor_bp?: boolean;
  };
  additional_content?: {
    course_benefits?: string;
    course_target_audience?: string;
    course_duration?: {
      hours: number;
      minutes: number;
    };
    course_material_includes?: string;
    course_requirements?: string;
  };
  preview_link: string;
  _tutor_course_prerequisites_ids: string[];
}

interface CourseDetailsPayload {
  action: string;
  course_id: number;
}
export type CourseBuilderSteps = 'basic' | 'curriculum' | 'additional';
export interface CourseDetailsResponse {
  ID: number;
  post_author: {
    ID: string;
    display_name: string;
    user_email: string;
    user_login: string;
    user_nicename: string;
    tutor_profile_job_title: string;
    tutor_profile_bio: string;
    tutor_profile_photo: string;
    tutor_profile_photo_url: string;
  };
  post_date: string;
  post_date_gmt: string;
  post_content: string;
  post_title: string;
  post_excerpt: string;
  post_status: string;
  comment_status: string;
  ping_status: string;
  post_password: string;
  post_name: string;
  to_ping: string;
  pinged: string;
  post_modified: string;
  post_modified_gmt: string;
  post_content_filtered: string;
  post_parent: number;
  guid: string;
  menu_order: number;
  post_type: string;
  post_mime_type: string;
  comment_count: string;
  filter: string;
  // biome-ignore lint/suspicious/noExplicitAny: <Allow for now>
  ancestors: any[];
  page_template: string;
  // biome-ignore lint/suspicious/noExplicitAny: <Allow for now>
  post_category: any[];
  // biome-ignore lint/suspicious/noExplicitAny: <Allow for now>
  tags_input: any[];
  course_categories: {
    term_id: number;
    name: string;
    slug: string;
    term_group: number;
    term_taxonomy_id: number;
    taxonomy: string;
    description: string;
    parent: number;
    count: number;
    filter: string;
  }[];
  course_tags: {
    term_id: number;
    name: string;
    slug: string;
    term_group: number;
    term_taxonomy_id: number;
    taxonomy: string;
    description: string;
    parent: number;
    count: number;
    filter: string;
  }[];
  thumbnail: string;
  enable_qna: string;
  is_public_course: string;
  course_level: CourseLevel;
  video: {
    source: string;
    source_video_id: string;
    poster: string;
    source_external_url: string;
    source_shortcode: string;
    source_youtube: string;
    source_vimeo: string;
    source_embedded: string;
  };
  course_duration: {
    hours: number;
    minutes: number;
    seconds: number;
  };
  course_benefits: string;
  course_requirements: string;
  course_target_audience: string;
  course_material_includes: string;
  course_sale_price: string;
  course_settings: {
    maximum_students: number;
    content_drip_type:
      | 'unlock_by_date'
      | 'specific_days'
      | 'unlock_sequentially'
      | 'after_finishing_prerequisites'
      | '';
    enable_content_drip: number;
    enrollment_expiry: number;
  };
  step_completion_status: Record<CourseBuilderSteps, boolean>;
  course_pricing: {
    price: string;
    product_id: string;
    product_name: string;
    sale_price: string;
    type: string;
  };
  course_instructors: InstructorListResponse[];
  preview_link: string;
  _tutor_course_prerequisites_ids: string[];
}

interface CourseResponse {
  data: number;
  message: string;
  status_code: number;
}

interface WcProduct {
  ID: string;
  post_title: string;
}

interface GetProductsPayload {
  action: string;
}

interface WcProductDetailsPayload {
  action: string;
  product_id: string;
}

interface WcProductDetailsResponse {
  name: string;
  regular_price: string;
  sale_price: string;
}

interface GetPrerequisiteCoursesPayload {
  action: string;
  exclude: string[];
}

export interface PrerequisiteCourses {
  id: number;
  post_title: string;
  featured_image: string;
}

const createCourse = (payload: CoursePayload) => {
  return authApiInstance.post<CoursePayload, CourseResponse>(endpoints.ADMIN_AJAX, {
    action: 'tutor_create_course',
    ...payload,
  });
};

export const useCreateCourseMutation = () => {
  const { showToast } = useToast();

  return useMutation({
    mutationFn: createCourse,
    onSuccess: (response) => {
      showToast({ type: 'success', message: response.message });
    },
    onError: (error: ErrorResponse) => {
      showToast({ type: 'danger', message: error.response.data.message });
    },
  });
};

const updateCourse = (payload: CoursePayload) => {
  return authApiInstance.post<CoursePayload, CourseResponse>(endpoints.ADMIN_AJAX, {
    action: 'tutor_update_course',
    ...payload,
  });
};

export const useUpdateCourseMutation = () => {
  const { showToast } = useToast();
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: updateCourse,
    onSuccess: (response) => {
      showToast({ type: 'success', message: response.message });
      queryClient.invalidateQueries({
        queryKey: ['CourseDetails', response.data],
      });
    },
    onError: (error: ErrorResponse) => {
      showToast({ type: 'danger', message: error.response.data.message });
    },
  });
};

const getCourseDetails = (courseId: number) => {
  return authApiInstance.post<CourseDetailsPayload, AxiosResponse<CourseDetailsResponse>>(endpoints.ADMIN_AJAX, {
    action: 'tutor_course_details',
    course_id: courseId,
  });
};

export const useCourseDetailsQuery = (courseId: number) => {
  return useQuery({
    queryKey: ['CourseDetails', courseId],
    queryFn: () => getCourseDetails(courseId).then((res) => res.data),
    enabled: !!courseId,
  });
};

const getWcProducts = () => {
  return authApiInstance.post<GetProductsPayload, AxiosResponse<WcProduct[]>>(endpoints.ADMIN_AJAX, {
    action: 'tutor_get_wc_products',
  });
};

export const useGetProductsQuery = () => {
  return useQuery({
    queryKey: ['WcProducts'],
    queryFn: () => getWcProducts().then((res) => res.data),
  });
};

const getProductDetails = (productId: string, courseId: string) => {
  return authApiInstance.post<WcProductDetailsPayload, AxiosResponse<WcProductDetailsResponse>>(endpoints.ADMIN_AJAX, {
    action: 'tutor_get_wc_product',
    product_id: productId,
    course_id: courseId,
  });
};

export const useProductDetailsQuery = (productId: string, courseId: string, coursePriceType: string) => {
  const { showToast } = useToast();

  return useQuery({
    queryKey: ['WcProductDetails', productId],
    queryFn: () =>
      getProductDetails(productId, courseId).then((res) => {
        if (typeof res.data === 'string') {
          showToast({ type: 'danger', message: res.data });
          return null;
        }
        return res.data;
      }),
    enabled: !!productId && coursePriceType === 'paid',
  });
};

const getPrerequisiteCourses = (excludedCourseIds: string[]) => {
  return authApiInstance.post<GetPrerequisiteCoursesPayload, AxiosResponse<PrerequisiteCourses[]>>(
    endpoints.ADMIN_AJAX,
    {
      action: 'tutor_course_list',
      exclude: excludedCourseIds,
    }
  );
};

export const usePrerequisiteCoursesQuery = (excludedCourseIds: string[], isPrerequisiteAddonEnabled: boolean) => {
  return useQuery({
    queryKey: ['PrerequisiteCourses', excludedCourseIds],
    queryFn: () => getPrerequisiteCourses(excludedCourseIds).then((res) => res.data),
    enabled: isPrerequisiteAddonEnabled,
  });
};
