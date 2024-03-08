import { useToast } from '@Atoms/Toast';
import { Media } from '@Components/fields/FormImageInput';
import { tutorConfig } from '@Config/config';
import { Tag } from '@Services/tags';
import { User } from '@Services/users';
import { authApiInstance } from '@Utils/api';
import endpoints from '@Utils/endpoints';
import { useMutation, useQuery } from '@tanstack/react-query';
import { AxiosResponse } from 'axios';

const currentUser = tutorConfig.current_user.data;

export const courseDefaultData: CourseFormData = {
  post_date: '',
  post_name: '',
  post_title: '',
  post_content: '',
  post_status: 'publish',
  post_password: '',
  post_author: {
    id: Number(currentUser.ID),
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
  enrollment_expiration: '',
};

export interface CourseFormData {
  post_date: string;
  post_title: string;
  post_name: string;
  post_content: string;
  post_status: string;
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
  course_level: string;
  maximum_students: number | null;
  enrollment_expiration: string;
}

export interface CoursePayload {
  course_id?: number;
  post_date: string;
  post_title: string;
  post_name: string;
  post_content: string;
  post_status: string;
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
  course_level: string;
  _tutor_course_settings: {
    maximum_students: number;
    enable_content_drip?: number;
    content_drip_type?: string;
  };
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
  ancestors: any[];
  page_template: string;
  post_category: any[];
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
  course_level: string;
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
  course_requirements: string[];
  course_target_audience: string;
  course_material_includes: string;
  course_price_type: string;
  course_price: string;
  course_sale_price: string;
  course_settings: {
    maximum_students: number;
    content_drip_type: string;
    enable_content_drip: number;
  };
  step_completion_status: Record<CourseBuilderSteps, boolean>;
}

interface CourseResponse {
  data: number;
  message: string;
  status_code: number;
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
    onSuccess: response => {
      showToast({ type: 'success', message: response.message });
    },
    onError: (error: any) => {
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

  return useMutation({
    mutationFn: updateCourse,
    onSuccess: response => {
      showToast({ type: 'success', message: response.message });
    },
    onError: (error: any) => {
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
    queryFn: () => getCourseDetails(courseId).then(res => res.data),
    enabled: !!courseId,
  });
};
