import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { __ } from '@wordpress/i18n';
import { format, isBefore, isValid, parseISO } from 'date-fns';

import { useToast } from '@TutorShared/atoms/Toast';
import type { UserOption } from '@TutorShared/components/fields/FormSelectUser';
import type { CourseVideo } from '@TutorShared/components/fields/FormVideoInput';

import { tutorConfig } from '@TutorShared/config/config';
import { Addons, DateFormats } from '@TutorShared/config/constants';
import { type WPMedia } from '@TutorShared/hooks/useWpMedia';
import { type Course } from '@TutorShared/services/course';
import type { Tag } from '@TutorShared/services/tags';
import type { InstructorListResponse, User } from '@TutorShared/services/users';
import { wpAjaxInstance } from '@TutorShared/utils/api';
import endpoints from '@TutorShared/utils/endpoints';
import type { ErrorResponse } from '@TutorShared/utils/form';
import {
  isDefined,
  type Certificate,
  type Editor,
  type ID,
  type Prettify,
  type TutorCategory,
  type TutorMutationResponse,
  type TutorSellingOption,
  type WPPostStatus,
} from '@TutorShared/utils/types';
import { convertGMTtoLocalDate, convertToErrorMessage, convertToGMT, isAddonEnabled } from '@TutorShared/utils/util';

const currentUser = tutorConfig.current_user.data;

type CourseLevel = 'all_levels' | 'beginner' | 'intermediate' | 'expert';

export type ContentDripType =
  | 'unlock_by_date'
  | 'specific_days'
  | 'unlock_sequentially'
  | 'after_finishing_prerequisites'
  | '';
export type PricingType = 'free' | 'paid';
export type CourseSellingOption = Prettify<TutorSellingOption | 'membership' | 'all'>;

export interface CourseFormData {
  post_date: string;
  post_title: string;
  post_name: string;
  post_content: string;
  post_status: WPPostStatus;
  visibility: 'publish' | 'private' | 'password_protected';
  post_password: string;
  post_author: User | null;
  thumbnail: WPMedia | null;
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
  course_attachments: WPMedia[] | null;
  isContentDripEnabled: boolean;
  contentDripType: ContentDripType;
  course_product_id: string;
  course_product_name: string;
  preview_link: string;
  course_prerequisites: Course[];
  tutor_course_certificate_template: string;
  enable_tutor_bp: boolean;
  bp_attached_group_ids: string[];
  editor_used: Editor;
  isScheduleEnabled: boolean;
  showScheduleForm: boolean;
  schedule_date: string;
  schedule_time: string;
  enable_coming_soon: boolean;
  coming_soon_thumbnail: WPMedia | null;
  enable_curriculum_preview: boolean; // Only when coming-soon is enabled
  course_enrollment_period: boolean;
  enrollment_starts_date: string;
  enrollment_starts_time: string;
  enrollment_ends_date: string;
  enrollment_ends_time: string;
  pause_enrollment: boolean;
  tax_on_single: boolean;
  tax_on_subscription: boolean;
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
    source_html5: '',
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
  enable_coming_soon: false,
  coming_soon_thumbnail: null,
  enable_curriculum_preview: false,
  course_enrollment_period: false,
  enrollment_starts_date: '',
  enrollment_starts_time: '',
  enrollment_ends_date: '',
  enrollment_ends_time: '',
  pause_enrollment: false,
  tax_on_single: true,
  tax_on_subscription: true,
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

  // when course is scheduled
  enable_coming_soon?: '1' | '0';
  coming_soon_thumbnail_id?: number;
  enable_curriculum_preview?: '1' | '0';
  'course_settings[course_enrollment_period]'?: string;
  'course_settings[enrollment_starts_at]'?: string; // yyyy-mm-dd hh:mm:ss (24H)
  'course_settings[enrollment_ends_at]'?: string; // yyyy-mm-dd hh:mm:ss (24H)
  'course_settings[pause_enrollment]'?: string;
  tax_on_single?: '0' | '1';
  tax_on_subscription?: '0' | '1';
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
    host_id: string;
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
  ancestors: unknown[];
  page_template: string;
  post_category: unknown[];
  tags_input: unknown[];
  course_categories: TutorCategory[];
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
  course_settings: {
    maximum_students: number;
    content_drip_type: ContentDripType;
    enable_content_drip: number;
    enrollment_expiry: number;
    enable_tutor_bp: 1 | 0;
    course_enrollment_period: string;
    enrollment_starts_at: string;
    enrollment_ends_at: string;
    pause_enrollment: string;
  };
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
  course_attachments: WPMedia[];
  zoom_users: {
    [key: string]: string;
  };
  zoom_meetings: ZoomMeeting[];
  google_meet_meetings: GoogleMeet[];
  bp_attached_groups: string[];
  editor_used: Editor;
  editors: Editor[];
  total_enrolled_student: number;
  enable_coming_soon: '1' | '0';
  coming_soon_thumbnail: string;
  coming_soon_thumbnail_id: number;
  enable_curriculum_preview: '1' | '0';
  tax_collection?: {
    tax_on_single: '1' | '0';
    tax_on_subscription: '1' | '0';
  };
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

interface WcProductDetailsResponse {
  name: string;
  regular_price: string;
  sale_price: string;
}

interface PrerequisiteCourses {
  id: number;
  post_title: string;
  featured_image: string;
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

export const convertCourseDataToPayload = (data: CourseFormData, slot_fields: string[]): CoursePayload => {
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
    ...(data.course_product_id || data.course_price_type === 'free'
      ? {
          'pricing[product_id]': data.course_price_type === 'free' ? '-1' : data.course_product_id,
        }
      : {}),

    course_price: Number(data.course_price) || 0,
    course_sale_price: Number(data.course_sale_price) || 0,
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
      ? Object.fromEntries(
          Object.entries(data.video).map(([key, value]) => [
            `video[${key}]`,
            key === 'poster_url' && !data.video.poster ? '' : value,
          ]),
        )
      : {}),
    tutor_attachments: (data.course_attachments || []).map((item) => item.id) ?? [],
    bp_attached_group_ids: data.bp_attached_group_ids,
    ...(isBefore(new Date(), new Date(`${data.schedule_date} ${data.schedule_time}`)) && {
      enable_coming_soon: data.enable_coming_soon ? '1' : '0',
      coming_soon_thumbnail_id: data.coming_soon_thumbnail?.id ?? 0,
      enable_curriculum_preview: data.enable_curriculum_preview ? '1' : '0',
    }),
    'course_settings[course_enrollment_period]': data.course_enrollment_period ? 'yes' : 'no',
    'course_settings[enrollment_starts_at]': isValid(
      new Date(`${data.enrollment_starts_date} ${data.enrollment_starts_time}`),
    )
      ? convertToGMT(
          new Date(`${data.enrollment_starts_date} ${data.enrollment_starts_time}`),
          DateFormats.yearMonthDayHourMinuteSecond24H,
        )
      : '',
    'course_settings[enrollment_ends_at]': isValid(
      new Date(`${data.enrollment_ends_date} ${data.enrollment_ends_time}`),
    )
      ? convertToGMT(
          new Date(`${data.enrollment_ends_date} ${data.enrollment_ends_time}`),
          DateFormats.yearMonthDayHourMinuteSecond24H,
        )
      : '',
    'course_settings[pause_enrollment]': data.pause_enrollment ? 'yes' : 'no',
    ...Object.fromEntries(
      slot_fields.map((key) => {
        return [key, data[key as keyof CourseFormData]];
      }),
    ),
    ...(!!tutorConfig.settings?.enable_individual_tax_control && {
      tax_on_single: data.tax_on_single ? '1' : '0',
      tax_on_subscription: data.tax_on_subscription ? '1' : '0',
    }),
  };
};

export const convertCourseDataToFormData = (
  courseDetails: CourseDetailsResponse,
  slotFields: string[],
): CourseFormData => {
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
    video: {
      ...courseDetails.video,
      source: Object.values(tutorConfig.supported_video_sources).find(
        (item) => item.value === courseDetails.video.source,
      )
        ? courseDetails.video.source
        : '',
    },
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
    course_prerequisites: (courseDetails.course_prerequisites ?? []).map((course) => ({
      id: course.id,
      title: course.post_title,
      image: course.featured_image,
      is_purchasable: false,
      regular_price: '',
      sale_price: '',
    })),
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
    enable_coming_soon: courseDetails.enable_coming_soon === '1',
    coming_soon_thumbnail: {
      id: Number(courseDetails.coming_soon_thumbnail_id),
      title: '',
      url: courseDetails.coming_soon_thumbnail,
    },
    enable_curriculum_preview: courseDetails.enable_curriculum_preview === '1',
    course_enrollment_period: courseDetails.course_settings.course_enrollment_period === 'yes',
    enrollment_starts_date: isValid(new Date(courseDetails.course_settings.enrollment_starts_at))
      ? format(convertGMTtoLocalDate(courseDetails.course_settings.enrollment_starts_at), DateFormats.yearMonthDay)
      : '',
    enrollment_starts_time: isValid(new Date(courseDetails.course_settings.enrollment_starts_at))
      ? format(convertGMTtoLocalDate(courseDetails.course_settings.enrollment_starts_at), DateFormats.hoursMinutes)
      : '',
    enrollment_ends_date: isValid(new Date(courseDetails.course_settings.enrollment_ends_at))
      ? format(convertGMTtoLocalDate(courseDetails.course_settings.enrollment_ends_at), DateFormats.yearMonthDay)
      : '',
    enrollment_ends_time: isValid(new Date(courseDetails.course_settings.enrollment_ends_at))
      ? format(convertGMTtoLocalDate(courseDetails.course_settings.enrollment_ends_at), DateFormats.hoursMinutes)
      : '',
    pause_enrollment: courseDetails.course_settings.pause_enrollment === 'yes',
    ...Object.fromEntries(
      slotFields.map((key) => {
        return [key, courseDetails[key as keyof CourseDetailsResponse]];
      }),
    ),
    tax_on_single: isDefined(courseDetails?.tax_collection?.tax_on_single)
      ? courseDetails.tax_collection.tax_on_single === '1'
      : true,
    tax_on_subscription: isDefined(courseDetails?.tax_collection?.tax_on_subscription)
      ? courseDetails.tax_collection.tax_on_subscription === '1'
      : true,
  };
};

const createCourse = (payload: CoursePayload) => {
  return wpAjaxInstance.post<CoursePayload, CourseResponse>(endpoints.CREATED_COURSE, payload);
};

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
  return wpAjaxInstance.post<CoursePayload, CourseResponse>(endpoints.UPDATE_COURSE, payload);
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

        queryClient.invalidateQueries({
          queryKey: ['WcProducts'],
        });
      }
    },
    onError: (error: ErrorResponse) => {
      showToast({ type: 'danger', message: convertToErrorMessage(error) });
    },
  });
};

const getCourseDetails = (courseId: number) => {
  return wpAjaxInstance.get<CourseDetailsResponse>(endpoints.GET_COURSE_DETAILS, {
    params: {
      course_id: courseId,
    },
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
  return wpAjaxInstance.get<WcProduct[]>(endpoints.GET_WC_PRODUCTS, {
    params: {
      exclude_linked_products: true,
      ...(courseId && { course_id: courseId }),
    },
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
  return wpAjaxInstance.get<WcProductDetailsResponse>(endpoints.GET_WC_PRODUCT_DETAILS, {
    params: { product_id: productId, course_id: courseId },
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

const saveZoomMeeting = (payload: ZoomMeetingPayload) => {
  return wpAjaxInstance.post<ZoomMeetingPayload, TutorMutationResponse<number>>(endpoints.SAVE_ZOOM_MEETING, payload);
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
    },
    onError: (error: ErrorResponse) => {
      showToast({ type: 'danger', message: convertToErrorMessage(error) });
    },
  });
};

const deleteZoomMeeting = (meetingId: string) => {
  return wpAjaxInstance.post<number, TutorDeleteResponse>(endpoints.DELETE_ZOOM_MEETING, {
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
  return wpAjaxInstance.post<GoogleMeetMeetingPayload, TutorMutationResponse<number>>(
    endpoints.SAVE_GOOGLE_MEET,
    convertedPayload,
  );
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
  return wpAjaxInstance.post<GoogleMeetMeetingPayload, TutorMutationResponse<number>>(endpoints.DELETE_GOOGLE_MEET, {
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
