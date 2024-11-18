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

export const modal = {
  HEADER_HEIGHT: 56,
  MARGIN_TOP: 88,
  BASIC_MODAL_HEADER_HEIGHT: 50,
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
}

export const VideoRegex = {
  YOUTUBE: /^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#&?]*).*/,
  VIMEO: /^.*(vimeo\.com\/)((channels\/[A-z]+\/)|(groups\/[A-z]+\/videos\/))?([0-9]+)/,
  EXTERNAL_URL: /(http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/,
  SHORTCODE: /^\[.*\]$/,
};
