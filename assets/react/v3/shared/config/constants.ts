import { __ } from '@wordpress/i18n';

import {
  DesktopBreakpoint,
  MobileBreakpoint,
  SmallMobileBreakpoint,
  TabletBreakpoint,
} from '@TutorShared/config/styles';

export const MAX_FILE_SIZE = 5 * 1024 * 1024;
export const VALID_IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/gif'];
export const ITEMS_PER_PAGE = 10;
export const TAG_ITEMS_PER_PAGE = 48;
export const MAX_MULTISELECT_CHIPS = 7;
export const MAX_NUMBER_OF_VARIANT_OPTIONS = 3;
export const ALIAS_PRODUCT_ROUTE_PREFIX = '/product';
export const ALIAS_CATEGORY_ROUTE_PREFIX = '/category';
export const ALIAS_TAG_ROUTE_PREFIX = '/tag';

export const isRTL = document.dir === 'rtl';
export const WP_ADMIN_BAR_HEIGHT = '32px';
export const WP_ADMIN_BAR_HEIGHT_MOBILE = '46px';
export const currentWindowWidth = window.innerWidth;
export const CURRENT_VIEWPORT = {
  isAboveDesktop: currentWindowWidth >= DesktopBreakpoint,
  isAboveTablet: currentWindowWidth >= TabletBreakpoint,
  isAboveMobile: currentWindowWidth >= MobileBreakpoint,
  isAboveSmallMobile: currentWindowWidth >= SmallMobileBreakpoint,
};

export const modal = {
  HEADER_HEIGHT: 56,
  MARGIN_TOP: 88,
  BASIC_MODAL_HEADER_HEIGHT: 50,
  BASIC_MODAL_MAX_WIDTH: 1218,
};

export const notebook = {
  MIN_NOTEBOOK_HEIGHT: 430,
  MIN_NOTEBOOK_WIDTH: 360,
  NOTEBOOK_HEADER: 50,
};

export const TutorRoles = {
  ADMINISTRATOR: 'administrator',
  TUTOR_INSTRUCTOR: 'tutor_instructor',
  SUBSCRIBER: 'subscriber',
};

export enum LocalStorageKeys {
  notebook = 'tutor_course_builder_notebook',
}

export enum DateFormats {
  day = 'dd',
  month = 'MMM',
  year = 'yyyy',
  yearMonthDay = 'yyyy-LL-dd',
  monthDayYear = 'MMM dd, yyyy',
  hoursMinutes = 'hh:mm a',
  yearMonthDayHourMinuteSecond = 'yyyy-MM-dd hh:mm:ss',
  yearMonthDayHourMinuteSecond24H = 'yyyy-MM-dd HH:mm:ss',
  monthDayYearHoursMinutes = 'MMM dd, yyyy, hh:mm a',
  localMonthDayYearHoursMinutes = 'PPp',
  activityDate = 'MMM dd, yyyy hh:mm aa',
  validityDate = 'dd MMMM yyyy',
  dayMonthYear = 'do MMMM, yyyy',
}

export enum Addons {
  COURSE_BUNDLE = 'course-bundle',
  SUBSCRIPTION = 'subscription',
  SOCIAL_LOGIN = 'social-login',
  CONTENT_DRIP = 'content-drip',
  TUTOR_MULTI_INSTRUCTORS = 'tutor-multi-instructors',
  TUTOR_ASSIGNMENTS = 'tutor-assignments',
  TUTOR_COURSE_PREVIEW = 'tutor-course-preview',
  TUTOR_COURSE_ATTACHMENTS = 'tutor-course-attachments',
  TUTOR_GOOGLE_MEET_INTEGRATION = 'google-meet',
  TUTOR_REPORT = 'tutor-report',
  EMAIL = 'tutor-email',
  CALENDAR = 'calendar',
  NOTIFICATIONS = 'tutor-notifications',
  GOOGLE_CLASSROOM_INTEGRATION = 'google-classroom',
  TUTOR_ZOOM_INTEGRATION = 'tutor-zoom',
  QUIZ_EXPORT_IMPORT = 'quiz-import-export',
  ENROLLMENT = 'enrollments',
  TUTOR_CERTIFICATE = 'tutor-certificate',
  GRADEBOOK = 'gradebook',
  TUTOR_PREREQUISITES = 'tutor-prerequisites',
  BUDDYPRESS = 'buddypress',
  WOOCOMMERCE_SUBSCRIPTIONS = 'wc-subscriptions',
  PAID_MEMBERSHIPS_PRO = 'pmpro',
  RESTRICT_CONTENT_PRO = 'restrict-content-pro',
  WEGLOT = 'tutor-weglot',
  WPML_MULTILINGUAL_CMS = 'tutor-wpml',
  H5P_INTEGRATION = 'h5p',
  CONTENT_BANK = 'content-bank',
}

export const VideoRegex = {
  YOUTUBE: /^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#&?]*).*/,
  VIMEO: /^.*(vimeo\.com\/)((channels\/[A-z]+\/)|(groups\/[A-z]+\/videos\/))?([0-9]+)/,
  // eslint-disable-next-line no-useless-escape
  EXTERNAL_URL: /(http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/,
  SHORTCODE: /^\[.*\]$/,
};

export const visibilityStatusOptions = [
  {
    label: __('Public', 'tutor'),
    value: 'publish',
  },
  {
    label: __('Password Protected', 'tutor'),
    value: 'password_protected',
  },
  {
    label: __('Private', 'tutor'),
    value: 'private',
  },
];

export const VisibilityControlKeys = {
  COURSE_BUILDER: {
    BASICS: {
      FEATURED_IMAGE: 'course_builder.basics_featured_image',
      INTRO_VIDEO: 'course_builder.basics_intro_video',
      SCHEDULING_OPTIONS: 'course_builder.basics_scheduling_options',
      PRICING_OPTIONS: 'course_builder.basics_pricing_options',
      CATEGORIES: 'course_builder.basics_categories',
      TAGS: 'course_builder.basics_tags',
      AUTHOR: 'course_builder.basics_author',
      INSTRUCTORS: 'course_builder.basics_instructors',
      OPTIONS: {
        GENERAL: 'course_builder.basics_options_general',
        CONTENT_DRIP: 'course_builder.basics_options_content_drip',
        ENROLLMENT: 'course_builder.basics_options_enrollment',
      },
    },
    CURRICULUM: {
      LESSON: {
        FEATURED_IMAGE: 'course_builder.curriculum_lesson_featured_image',
        VIDEO: 'course_builder.curriculum_lesson_video',
        VIDEO_PLAYBACK_TIME: 'course_builder.curriculum_lesson_video_playback_time',
        EXERCISE_FILES: 'course_builder.curriculum_lesson_exercise_files',
        LESSON_PREVIEW: 'course_builder.curriculum_lesson_lesson_preview',
      },
    },
    ADDITIONAL: {
      COURSE_BENEFITS: 'course_builder.additional_course_benefits',
      COURSE_TARGET_AUDIENCE: 'course_builder.additional_course_target_audience',
      TOTAL_COURSE_DURATION: 'course_builder.additional_total_course_duration',
      COURSE_MATERIALS_INCLUDES: 'course_builder.additional_course_material_includes',
      COURSE_REQUIREMENTS: 'course_builder.additional_course_requirements',
      CERTIFICATES: 'course_builder.additional_certificate',
      ATTACHMENTS: 'course_builder.additional_attachments',
      SCHEDULE_LIVE_CLASS: 'course_builder.additional_schedule_live_class',
    },
  },
} as const;

export const QuizDataStatus = {
  NEW: 'new',
  UPDATE: 'update',
  NO_CHANGE: 'no_change',
} as const;
