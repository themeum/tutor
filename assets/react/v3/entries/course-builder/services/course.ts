import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { __ } from '@wordpress/i18n';
import type { AxiosResponse } from 'axios';
import { format, isBefore, parseISO } from 'date-fns';

import { useToast } from '@Atoms/Toast';
import type { Media } from '@Components/fields/FormImageInput';
import type { UserOption } from '@Components/fields/FormSelectUser';
import type { CourseVideo } from '@Components/fields/FormVideoInput';

import { tutorConfig } from '@Config/config';
import { Addons, DateFormats } from '@Config/constants';
import type { ID } from '@CourseBuilderServices/curriculum';
import { isAddonEnabled } from '@CourseBuilderUtils/utils';
import type { Tag } from '@Services/tags';
import type { InstructorListResponse, User } from '@Services/users';
import { authApiInstance, wpAjaxInstance } from '@Utils/api';
import endpoints from '@Utils/endpoints';
import type { ErrorResponse } from '@Utils/form';
import { convertToErrorMessage, convertToGMT } from '@Utils/util';

const currentUser = tutorConfig.current_user.data;

type CourseLevel = 'all_levels' | 'beginner' | 'intermediate' | 'expert';

export type ContentDripType =
  | 'unlock_by_date'
  | 'specific_days'
  | 'unlock_sequentially'
  | 'after_finishing_prerequisites'
  | '';
export type PricingType = 'free' | 'paid';
export type CourseSellingOption = 'subscription' | 'one_time' | 'both';
export type PostStatus = 'publish' | 'private' | 'draft' | 'future' | 'pending' | 'trash';

export interface CourseFormData {
  post_date: string;
  post_title: string;
  post_name: string;
  post_content: string;
  post_status: PostStatus;
  visibility: 'publish' | 'private' | 'password_protected';
  post_password: string;
  post_author: User | null;
  thumbnail: Media | null;
  video: CourseVideo;
  course_price_type: string;
  course_price: string;
  course_sale_price: string;
  course_selling_option: CourseSellingOption;
  course_categories: number[];
  course_tags: Tag[];
  course_instructors: UserOption[];
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
  editor_used: Editor;
  isScheduleEnabled: boolean;
  showScheduleForm: boolean;
  schedule_date: string;
  schedule_time: string;
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
    source: '',
    source_video_id: '',
    poster: '',
    poster_url: '',
    source_external_url: '',
    source_shortcode: '',
    source_youtube: '',
    source_vimeo: '',
    source_embedded: '',
  },
  course_price_type: 'free',
  course_price: '',
  course_sale_price: '',
  course_selling_option: 'one_time',
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
  editor_used: {
    name: 'classic',
    label: __('Classic Editor', 'tutor'),
    link: '',
  },
  isScheduleEnabled: false,
  showScheduleForm: false,
  schedule_date: '',
  schedule_time: '',
};

export interface CoursePayload {
  course_id?: number;
  post_date?: string;
  post_date_gmt?: string;
  post_title: string;
  post_name: string;
  post_content?: string;
  post_status: string;
  post_password: string;
  post_author: number | null;
  'pricing[type]': string;
  'pricing[product_id]'?: string;
  course_price: number;
  course_sale_price: number;
  course_selling_option: CourseSellingOption;
  course_categories: number[];
  course_tags: number[];
  thumbnail_id: number | null;
  enable_qna: string;
  is_public_course: string;
  course_level: string;
  'course_settings[maximum_students]': number;
  'course_settings[enrollment_expiry]': number;
  'course_settings[enable_content_drip]': number;
  'course_settings[content_drip_type]': string;
  'course_settings[enable_tutor_bp]': number;
  'additional_content[course_benefits]': string;
  'additional_content[course_target_audience]': string;
  'additional_content[course_duration][hours]': number;
  'additional_content[course_duration][minutes]': number;
  'additional_content[course_material_includes]': string;
  'additional_content[course_requirements]': string;
  preview_link: string;
  course_instructor_ids?: number[];
  _tutor_prerequisites_main_edit?: boolean;
  _tutor_course_prerequisites_ids?: number[];
  tutor_course_certificate_template: string;
  _tutor_course_additional_data_edit?: boolean;
  _tutor_attachments_main_edit?: boolean;
  'video[source]'?: string;
  'video[source_video_id]'?: string;
  'video[poster]'?: string;
  'video[source_external_url]'?: string;
  'video[source_shortcode]'?: string;
  'video[source_youtube]'?: string;
  'video[source_vimeo]'?: string;
  'video[source_embedded]'?: string;
  tutor_attachments: number[];
  bp_attached_group_ids: string[];
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
  meeting_starts_at: string;
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

export interface Editor {
  label: string;
  link: string;
  name: string;
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
    type: PricingType;
  };
  course_selling_option: CourseSellingOption;
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
  editor_used: Editor;
  editors: Editor[];
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

export interface WcProduct {
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
  course_id: ID; // for additional
  topic_id?: ID; // for course builder
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

export const convertCourseDataToPayload = (data: CourseFormData): CoursePayload => {
  return {
    ...(data.isScheduleEnabled && {
      post_date: format(
        new Date(`${data.schedule_date} ${data.schedule_time}`),
        DateFormats.yearMonthDayHourMinuteSecond24H,
      ),
      post_date_gmt: convertToGMT(new Date(`${data.schedule_date} ${data.schedule_time}`)),
    }),
    post_title: data.post_title,
    post_name: data.post_name,
    ...(data.editor_used.name === 'classic' && {
      post_content: data.post_content,
    }),
    post_status: data.post_status,
    post_password: data.visibility === 'password_protected' ? data.post_password : '',
    post_author: data.post_author?.id ?? null,
    'pricing[type]': data.course_price_type,
    ...(data.course_price_type === 'paid' &&
      data.course_product_id && {
        'pricing[product_id]': data.course_product_id,
      }),

    course_price: Number(data.course_price) ?? 0,
    course_sale_price: Number(data.course_sale_price) ?? 0,
    course_selling_option: data.course_selling_option,

    course_categories: data.course_categories ?? [],
    course_tags: data.course_tags.map((item) => item.id) ?? [],
    thumbnail_id: data.thumbnail?.id ?? null,
    enable_qna: data.enable_qna ? 'yes' : 'no',
    is_public_course: data.is_public_course ? 'yes' : 'no',
    course_level: data.course_level,
    'course_settings[maximum_students]': data.maximum_students ?? 0,
    'course_settings[enrollment_expiry]': data.enrollment_expiry ?? '',
    'course_settings[enable_content_drip]': data.contentDripType ? 1 : 0,
    'course_settings[content_drip_type]': data.contentDripType,
    'course_settings[enable_tutor_bp]': data.enable_tutor_bp ? 1 : 0,

    'additional_content[course_benefits]': data.course_benefits ?? '',
    'additional_content[course_target_audience]': data.course_target_audience ?? '',
    'additional_content[course_duration][hours]': data.course_duration_hours ?? 0,
    'additional_content[course_duration][minutes]': data.course_duration_minutes ?? 0,
    'additional_content[course_material_includes]': data.course_material_includes ?? '',
    'additional_content[course_requirements]': data.course_requirements ?? '',
    preview_link: data.preview_link,

    ...(isAddonEnabled(Addons.TUTOR_MULTI_INSTRUCTORS) && {
      course_instructor_ids: [...data.course_instructors.map((item) => item.id), Number(data.post_author?.id)],
    }),

    ...(isAddonEnabled(Addons.TUTOR_PREREQUISITES) && {
      _tutor_prerequisites_main_edit: true,
      _tutor_course_prerequisites_ids: data.course_prerequisites?.map((item) => item.id) ?? [],
    }),
    tutor_course_certificate_template: data.tutor_course_certificate_template,

    _tutor_course_additional_data_edit: true,
    _tutor_attachments_main_edit: true,
    ...(data.video.source
      ? Object.fromEntries(Object.entries(data.video).map(([key, value]) => [`video[${key}]`, value]))
      : {}),
    tutor_attachments: (data.course_attachments || []).map((item) => item.id) ?? [],
    bp_attached_group_ids: data.bp_attached_group_ids,
  };
};

export const convertCourseDataToFormData = (courseDetails: CourseDetailsResponse): CourseFormData => {
  return {
    post_date: courseDetails.post_date,
    post_title: courseDetails.post_title,
    post_name: courseDetails.post_name,
    post_content: courseDetails.post_content,
    post_status: courseDetails.post_status,
    visibility: (() => {
      if (courseDetails.post_password.length) {
        return 'password_protected';
      }
      if (courseDetails.post_status === 'private') {
        return 'private';
      }
      return 'publish';
    })(),
    post_password: courseDetails.post_password,
    post_author: {
      id: Number(courseDetails.post_author.ID),
      name: courseDetails.post_author.display_name,
      email: courseDetails.post_author.user_email,
      avatar_url: courseDetails.post_author.tutor_profile_photo_url,
    },
    thumbnail: {
      id: courseDetails.thumbnail_id ? Number(courseDetails.thumbnail_id) : 0,
      title: '',
      url: courseDetails.thumbnail,
    },
    video: courseDetails.video,
    course_product_name: courseDetails.course_pricing.product_name,
    course_price_type: !courseDetails.course_pricing.type ? 'free' : courseDetails.course_pricing.type,
    course_price: courseDetails.course_pricing.price,
    course_sale_price: courseDetails.course_pricing.sale_price,
    course_selling_option: courseDetails.course_selling_option || 'one_time',
    course_categories: courseDetails.course_categories.map((item) => item.term_id),
    course_tags: courseDetails.course_tags.map((item) => {
      return {
        id: item.term_id,
        name: item.name,
      };
    }),
    enable_qna: courseDetails.enable_qna === 'yes',
    is_public_course: courseDetails.is_public_course === 'yes',
    course_level: courseDetails.course_level || 'intermediate',
    maximum_students: courseDetails.course_settings.maximum_students,
    enrollment_expiry: courseDetails.course_settings.enrollment_expiry,
    course_benefits: courseDetails.course_benefits,
    course_duration_hours: courseDetails.course_duration.hours,
    course_duration_minutes: courseDetails.course_duration.minutes,
    course_material_includes: courseDetails.course_material_includes,
    course_requirements: courseDetails.course_requirements,
    course_target_audience: courseDetails.course_target_audience,
    isContentDripEnabled: courseDetails.course_settings.enable_content_drip === 1,
    contentDripType: isAddonEnabled(Addons.CONTENT_DRIP)
      ? ['unlock_by_date', 'specific_days', 'unlock_sequentially', 'after_finishing_prerequisites'].includes(
          courseDetails.course_settings.content_drip_type,
        )
        ? courseDetails.course_settings.content_drip_type
        : ''
      : '',
    course_product_id:
      String(courseDetails.course_pricing.product_id) === '0' ? '' : String(courseDetails.course_pricing.product_id),
    course_instructors:
      courseDetails.course_instructors?.reduce((instructors, item) => {
        if (String(item.id) !== String(courseDetails.post_author.ID)) {
          instructors.push({
            id: item.id,
            name: item.display_name,
            email: item.user_email,
            avatar_url: item.avatar_url,
            isRemoveAble: false,
          });
        }
        return instructors;
      }, [] as UserOption[]) ?? [],
    preview_link: courseDetails.preview_link ?? '',
    course_prerequisites: courseDetails.course_prerequisites ?? [],
    tutor_course_certificate_template: courseDetails.course_certificate_template ?? '',
    course_attachments: courseDetails.course_attachments ?? [],
    enable_tutor_bp: !!(isAddonEnabled(Addons.BUDDYPRESS) && courseDetails.course_settings.enable_tutor_bp === 1),
    bp_attached_group_ids: courseDetails.bp_attached_groups ?? [],
    editor_used: courseDetails.editor_used,
    isScheduleEnabled: isBefore(new Date(), new Date(courseDetails.post_date)),
    showScheduleForm: !isBefore(new Date(), new Date(courseDetails.post_date)),
    schedule_date: !isBefore(parseISO(courseDetails.post_date), new Date())
      ? format(parseISO(courseDetails.post_date), DateFormats.yearMonthDay)
      : '',
    schedule_time: !isBefore(parseISO(courseDetails.post_date), new Date())
      ? format(parseISO(courseDetails.post_date), DateFormats.hoursMinutes)
      : '',
  };
};

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
      showToast({ type: 'danger', message: convertToErrorMessage(error) });
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
      if (response.data) {
        showToast({ type: 'success', message: __(response.message, 'tutor') });

        queryClient.invalidateQueries({
          queryKey: ['CourseDetails', response.data],
        });

        queryClient.invalidateQueries({
          queryKey: ['InstructorList', String(response.data)],
        });
      }
    },
    onError: (error: ErrorResponse) => {
      showToast({ type: 'danger', message: convertToErrorMessage(error) });
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

export const useGetWcProductsQuery = (monetizeBy: 'tutor' | 'wc' | 'edd' | undefined, courseId?: string) => {
  return useQuery({
    queryKey: ['WcProducts'],
    queryFn: () => getWcProducts(courseId).then((res) => res.data),
    enabled: monetizeBy === 'wc',
  });
};

const getProductDetails = (productId: string, courseId: string) => {
  return authApiInstance.post<WcProductDetailsPayload, AxiosResponse<WcProductDetailsResponse>>(endpoints.ADMIN_AJAX, {
    action: 'tutor_get_wc_product',
    product_id: productId,
    course_id: courseId,
  });
};

export const useWcProductDetailsQuery = (
  productId: string,
  courseId: string,
  coursePriceType: string,
  monetizedBy: 'tutor' | 'wc' | 'edd' | undefined,
) => {
  return useQuery({
    queryKey: ['WcProductDetails', productId, courseId],
    queryFn: () => getProductDetails(productId, courseId).then((res) => res.data),
    enabled: !!productId && coursePriceType === 'paid' && monetizedBy === 'wc',
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

export const usePrerequisiteCoursesQuery = ({
  excludedIds,
  isEnabled,
}: {
  excludedIds: string[];
  isEnabled: boolean;
}) => {
  return useQuery({
    queryKey: ['PrerequisiteCourses', excludedIds],
    queryFn: () => getPrerequisiteCourses(excludedIds).then((res) => res.data),
    enabled: isEnabled,
  });
};

const saveZoomMeeting = (payload: ZoomMeetingPayload) => {
  return authApiInstance.post<ZoomMeetingPayload, TutorMutationResponse<number>>(endpoints.ADMIN_AJAX, {
    action: 'tutor_zoom_save_meeting',
    ...payload,
  });
};

export const useSaveZoomMeetingMutation = () => {
  const { showToast } = useToast();
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: saveZoomMeeting,
    onSuccess: (response, payload) => {
      if (response.data) {
        showToast({ type: 'success', message: __(response.message, 'tutor') });

        if (payload.click_form === 'course_builder') {
          queryClient.invalidateQueries({
            queryKey: ['Topic', payload.course_id],
          });
        }

        if (payload.click_form === 'metabox') {
          queryClient.invalidateQueries({
            queryKey: ['CourseDetails', Number(payload.course_id)],
          });
        }
      }

      queryClient.invalidateQueries({
        queryKey: ['ZoomMeeting', response.data],
      });
    },
    onError: (error: ErrorResponse) => {
      showToast({ type: 'danger', message: convertToErrorMessage(error) });
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
      if (response.data) {
        showToast({ type: 'success', message: __(response.data.message, 'tutor') });

        queryClient.invalidateQueries({
          queryKey: ['CourseDetails', Number(courseId)],
        });

        queryClient.invalidateQueries({
          queryKey: ['Topic', courseId],
        });
      }
    },
    onError: (error: ErrorResponse) => {
      showToast({ type: 'danger', message: convertToErrorMessage(error) });
    },
  });
};

const saveGoogleMeet = (payload: GoogleMeetMeetingPayload) => {
  const convertedPayload: GoogleMeetMeetingPayload = {
    ...(payload['post-id'] &&
      payload['event-id'] && {
        'post-id': payload['post-id'],
        'event-id': payload['event-id'],
      }),
    course_id: payload.topic_id ? payload.topic_id : payload.course_id,
    meeting_summary: payload.meeting_summary,
    meeting_title: payload.meeting_title,
    meeting_start_date: payload.meeting_start_date,
    meeting_start_time: payload.meeting_start_time,
    meeting_end_date: payload.meeting_end_date,
    meeting_end_time: payload.meeting_end_time,
    meeting_timezone: payload.meeting_timezone,
    meeting_attendees_enroll_students: payload.meeting_attendees_enroll_students,
    attendees: payload.attendees,
  };
  return authApiInstance.post<GoogleMeetMeetingPayload, TutorMutationResponse<number>>(endpoints.ADMIN_AJAX, {
    action: 'tutor_google_meet_new_meeting',
    ...convertedPayload,
  });
};

export const useSaveGoogleMeetMutation = () => {
  const { showToast } = useToast();
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: saveGoogleMeet,
    onSuccess: (response, payload) => {
      showToast({ type: 'success', message: __(response.message, 'tutor') });

      if (payload.topic_id) {
        queryClient.invalidateQueries({
          queryKey: ['Topic', payload.course_id],
        });
      } else {
        queryClient.invalidateQueries({
          queryKey: ['CourseDetails', payload.course_id],
        });
      }
    },
    onError: (error: ErrorResponse) => {
      showToast({ type: 'danger', message: convertToErrorMessage(error) });
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

export const useDeleteGoogleMeetMutation = (courseId: ID, payload: GoogleMeetMeetingDeletePayload) => {
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
      showToast({ type: 'danger', message: convertToErrorMessage(error) });
    },
  });
};

const saveOpenAiSettingsKey = (payload: { chatgpt_api_key: string; chatgpt_enable: 1 | 0 }) => {
  return wpAjaxInstance.post<
    {
      chatgpt_api_key: string;
      chatgpt_enable: 'on' | 'off';
    },
    TutorMutationResponse<null>
  >(endpoints.OPEN_AI_SAVE_SETTINGS, {
    ...payload,
  });
};

export const useSaveOpenAiSettingsMutation = () => {
  const { showToast } = useToast();

  return useMutation({
    mutationFn: saveOpenAiSettingsKey,
    onSuccess: (response) => {
      showToast({ type: 'success', message: __(response.message, 'tutor') });
    },
    onError: (error: ErrorResponse) => {
      showToast({ type: 'danger', message: convertToErrorMessage(error) });
    },
  });
};

const getYouTubeVideoDuration = (videoId: string) => {
  return wpAjaxInstance.post<
    { videoId: string },
    TutorMutationResponse<{
      duration: string;
    }>
  >(endpoints.TUTOR_YOUTUBE_VIDEO_DURATION, {
    video_id: videoId,
  });
};

export const useGetYouTubeVideoDuration = () => {
  return useMutation({
    mutationFn: getYouTubeVideoDuration,
  });
};

interface UnlinkPageBuilderPayload {
  courseId: number;
  builder: string;
}

const unlinkPageBuilder = ({ courseId, builder }: UnlinkPageBuilderPayload) => {
  return wpAjaxInstance.post<UnlinkPageBuilderPayload, TutorMutationResponse<null>>(
    endpoints.TUTOR_UNLINK_PAGE_BUILDER,
    {
      course_id: courseId,
      builder: builder,
    },
  );
};

export const useUnlinkPageBuilder = () => {
  return useMutation({
    mutationFn: unlinkPageBuilder,
  });
};
