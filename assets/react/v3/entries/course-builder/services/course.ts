import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { __ } from '@wordpress/i18n';
import type { AxiosResponse } from 'axios';

import { useToast } from '@Atoms/Toast';
import type { Media } from '@Components/fields/FormImageInput';
import type { CourseVideo } from '@Components/fields/FormVideoInput';

import { tutorConfig } from '@Config/config';
import type { Tag } from '@Services/tags';
import type { InstructorListResponse, User } from '@Services/users';
import { authApiInstance, wpAjaxInstance } from '@Utils/api';
import endpoints from '@Utils/endpoints';
import type { ErrorResponse } from '@Utils/form';
import type { ID } from './curriculum';

const currentUser = tutorConfig.current_user.data;

type CourseLevel = 'all_levels' | 'beginner' | 'intermediate' | 'expert';

export type ContentDripType =
  | 'unlock_by_date'
  | 'specific_days'
  | 'unlock_sequentially'
  | 'after_finishing_prerequisites'
  | '';
export type PricingCategory = 'subscription' | 'regular';

export interface CourseFormData {
  post_date: string;
  post_title: string;
  post_name: string;
  post_content: string;
  post_status: 'publish' | 'private' | 'draft' | 'future';
  visibility: 'publish' | 'private' | 'password_protected';
  post_password: string;
  post_author: User | null;
  thumbnail: Media | null;
  video: CourseVideo;
  course_pricing_category: PricingCategory;
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
  course_attachments: Media[] | null;
  isContentDripEnabled: boolean;
  contentDripType: ContentDripType;
  course_product_id: string;
  course_product_name: string;
  preview_link: string;
  course_prerequisites: PrerequisiteCourses[];
  tutor_course_certificate_template: string;
  enable_tutor_bp: boolean;
  bp_attached_group_ids: string[];
}

export const courseDefaultData: CourseFormData = {
  post_date: '',
  post_name: '',
  post_title: '',
  post_content: '',
  post_status: 'publish',
  visibility: 'publish',
  post_password: '',
  post_author: {
    id: Number(currentUser.id),
    name: currentUser.display_name,
    email: currentUser.user_email,
    avatar_url: '',
  },
  thumbnail: null,
  video: {
    source: 'external_url',
    source_video_id: '',
    poster: '',
    poster_url: '',
    source_external_url: '',
    source_shortcode: '',
    source_youtube: '',
    source_vimeo: '',
    source_embedded: '',
  },
  course_pricing_category: 'regular',
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
  course_attachments: null,
  isContentDripEnabled: false,
  contentDripType: '',
  course_product_id: '',
  course_product_name: '',
  preview_link: '',
  course_prerequisites: [],
  tutor_course_certificate_template: '',
  enable_tutor_bp: false,
  bp_attached_group_ids: [],
};

export interface CoursePayload {
  course_id?: number;
  post_date: string;
  post_title: string;
  post_name: string;
  post_content: string;
  post_status: 'publish' | 'private' | 'draft' | 'future';
  post_password: string;
  post_author: number | null;
  thumbnail_id: number | null;
  video?: CourseVideo;
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
  tutor_course_certificate_template: string;
  tutor_attachments: Media[];
}

interface CourseDetailsPayload {
  action: string;
  course_id: number;
}
export type CourseBuilderSteps = 'basic' | 'curriculum' | 'additional';

export interface ZoomMeeting {
  ID: string;
  post_content: string;
  post_title: string;
  meeting_data: {
    id: number;
    topic: string;
    start_time: string;
    duration: number;
    timezone: string;
    password: string;
    start_url: string;
    duration_unit: 'min' | 'hr';
    settings: {
      auto_recording: 'none' | 'local' | 'cloud';
    };
  };
}

export interface GoogleMeet {
  ID: string;
  post_content: string;
  post_title: string;
  meeting_data: {
    id: string;
    summary: string;
    start_datetime: string;
    end_datetime: string;
    attendees: 'Yes' | 'No';
    timezone: string;
    meet_link: string;
  };
}

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
  post_status: 'publish' | 'private' | 'draft' | 'future';
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
  thumbnail_id: ID;
  enable_qna: string;
  is_public_course: string;
  course_level: CourseLevel;
  video: CourseVideo;
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
    content_drip_type: ContentDripType;
    enable_content_drip: number;
    enrollment_expiry: number;
    enable_tutor_bp: 1 | 0;
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
  course_prerequisites: PrerequisiteCourses[];
  course_certificate_template: string;
  course_certificates_templates: Certificate[];
  course_attachments: Media[];
  zoom_users: {
    [key: string]: string;
  };
  zoom_timezones: {
    [key: string]: string;
  };
  zoom_meetings: ZoomMeeting[];
  google_meet_timezones: {
    [key: string]: string;
  };
  google_meet_meetings: GoogleMeet[];
  bp_attached_groups: string[];
}

export type MeetingType = 'zoom' | 'google_meet';

export interface ZoomMeetingFormData {
  meeting_name: string;
  meeting_summary: string;
  meeting_date: string;
  meeting_time: string;
  meeting_duration: string;
  meeting_duration_unit: 'min' | 'hr';
  meeting_timezone: string;
  auto_recording: 'none' | 'local' | 'cloud';
  meeting_password: string;
  meeting_host: string;
}

export interface GoogleMeetMeetingFormData {
  meeting_name: string;
  meeting_summary: string;
  meeting_start_date: string;
  meeting_start_time: string;
  meeting_end_date: string;
  meeting_end_time: string;
  meeting_enrolledAsAttendee: boolean;
  meeting_timezone: string;
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
  exclude_linked_products: boolean;
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

export interface Certificate {
  name: string;
  orientation: 'landscape' | 'portrait';
  edit_url?: string;
  url: string;
  preview_src: string;
  background_src: string;
  key: string;
  is_default?: boolean;
}

export interface ZoomMeetingPayload {
  meeting_id?: number; // only update
  topic_id?: number; // only when it will add as a lesson
  course_id: number;
  click_form: 'course_builder' | 'metabox'; // 'course_builder' for course lesson, 'metabox' for additional
  meeting_title: string;
  meeting_summary: string;
  meeting_date: string;
  meeting_time: string;
  meeting_duration: number;
  meeting_duration_unit: 'min' | 'hr';
  meeting_timezone: string;
  auto_recording: 'none' | 'local' | 'cloud';
  meeting_password: string;
  meeting_host: string;
}

export interface GoogleMeetMeetingPayload {
  'post-id'?: ID; //only update
  'event-id'?: ID; //only update
  attendees: 'Yes' | 'No';
  course_id: ID; // for course builder set topic id
  meeting_title: string;
  meeting_summary: string;
  meeting_start_date: string; // yyyy-mm-dd
  meeting_start_time: string; // hh:mm
  meeting_end_date: string;
  meeting_end_time: string;
  meeting_timezone: string;
  meeting_attendees_enroll_students: 'Yes' | 'No';
}

interface GoogleMeetMeetingDeletePayload {
  'post-id': string;
  'event-id': string;
}

const createCourse = (payload: CoursePayload) => {
  return wpAjaxInstance.post<CoursePayload, CourseResponse>(endpoints.CREATED_COURSE, payload);
};

export interface TutorMutationResponse<T> {
  data: T;
  message: string;
  status_code: number;
}

interface TutorDeleteResponse {
  data: {
    message: string;
    post_id: number;
  };
  success: boolean;
}

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
    queryFn: () =>
      getCourseDetails(courseId).then((res) => {
        return res.data;
      }),
    enabled: !!courseId,
  });
};

const getWcProducts = (courseId?: string) => {
  return authApiInstance.post<GetProductsPayload, AxiosResponse<WcProduct[]>>(endpoints.ADMIN_AJAX, {
    action: 'tutor_get_wc_products',
    exclude_linked_products: true,
    ...(courseId && { course_id: courseId }),
  });
};

export const useGetProductsQuery = (courseId?: string) => {
  return useQuery({
    queryKey: ['WcProducts'],
    queryFn: () => getWcProducts(courseId).then((res) => res.data),
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
    queryKey: ['WcProductDetails', productId, courseId],
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
    },
  );
};

export const usePrerequisiteCoursesQuery = (excludedCourseIds: string[], isPrerequisiteAddonEnabled: boolean) => {
  return useQuery({
    queryKey: ['PrerequisiteCourses', excludedCourseIds],
    queryFn: () => getPrerequisiteCourses(excludedCourseIds).then((res) => res.data),
    enabled: isPrerequisiteAddonEnabled,
  });
};

const saveZoomMeeting = (payload: ZoomMeetingPayload) => {
  return authApiInstance.post<ZoomMeetingPayload, TutorMutationResponse<number>>(endpoints.ADMIN_AJAX, {
    action: 'tutor_zoom_save_meeting',
    ...payload,
  });
};

export const useSaveZoomMeetingMutation = (courseId: string) => {
  const { showToast } = useToast();
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: saveZoomMeeting,
    onSuccess: (response) => {
      showToast({ type: 'success', message: __(response.message, 'tutor') });

      queryClient.invalidateQueries({
        queryKey: ['CourseDetails', Number(courseId)],
      });

      queryClient.invalidateQueries({
        queryKey: ['Topic', Number(courseId)],
      });

      queryClient.invalidateQueries({
        queryKey: ['ZoomMeeting', response.data],
      });
    },
    onError: (error: ErrorResponse) => {
      showToast({ type: 'danger', message: error.response.data.message });
    },
  });
};

const deleteZoomMeeting = (meetingId: string) => {
  return authApiInstance.post<number, TutorDeleteResponse>(endpoints.ADMIN_AJAX, {
    action: 'tutor_zoom_delete_meeting',
    meeting_id: meetingId,
  });
};

export const useDeleteZoomMeetingMutation = (courseId: string) => {
  const { showToast } = useToast();
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: deleteZoomMeeting,
    onSuccess: (response) => {
      showToast({ type: 'success', message: __(response.data.message, 'tutor') });

      queryClient.invalidateQueries({
        queryKey: ['CourseDetails', Number(courseId)],
      });

      queryClient.invalidateQueries({
        queryKey: ['Topic', Number(courseId)],
      });
    },
    onError: (error: ErrorResponse) => {
      showToast({ type: 'danger', message: error.response.data.message });
    },
  });
};

const saveGoogleMeet = (payload: GoogleMeetMeetingPayload) => {
  return authApiInstance.post<GoogleMeetMeetingPayload, TutorMutationResponse<number>>(endpoints.ADMIN_AJAX, {
    action: 'tutor_google_meet_new_meeting',
    ...payload,
  });
};

export const useSaveGoogleMeetMutation = (courseId: string) => {
  const { showToast } = useToast();
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: saveGoogleMeet,
    onSuccess: (response) => {
      showToast({ type: 'success', message: __(response.message, 'tutor') });

      queryClient.invalidateQueries({
        queryKey: ['CourseDetails', Number(courseId)],
      });

      queryClient.invalidateQueries({
        queryKey: ['Topic', Number(courseId)],
      });
    },
    onError: (error: ErrorResponse) => {
      showToast({ type: 'danger', message: error.response.data.message });
    },
  });
};

const deleteGoogleMeet = (postId: string, eventId: string) => {
  return authApiInstance.post<GoogleMeetMeetingPayload, TutorMutationResponse<number>>(endpoints.ADMIN_AJAX, {
    action: 'tutor_google_meet_delete',
    'post-id': postId,
    'event-id': eventId,
  });
};

export const useDeleteGoogleMeetMutation = (courseId: string, payload: GoogleMeetMeetingDeletePayload) => {
  const { showToast } = useToast();
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: () => deleteGoogleMeet(payload['post-id'], payload['event-id']),
    onSuccess: (response) => {
      showToast({ type: 'success', message: __(response.message, 'tutor') });

      queryClient.invalidateQueries({
        queryKey: ['CourseDetails', Number(courseId)],
      });

      queryClient.invalidateQueries({
        queryKey: ['Topic', Number(courseId)],
      });
    },
    onError: (error: ErrorResponse) => {
      showToast({ type: 'danger', message: error.response.data.message });
    },
  });
};
