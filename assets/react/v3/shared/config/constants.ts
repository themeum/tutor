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
}

export enum Addons {
  COURSE_BUNDLE = 'Course Bundle',
  SUBSCRIPTION = 'Subscription',
  SOCIAL_LOGIN = 'Social Login',
  CONTENT_DRIP = 'Content Drip',
  TUTOR_MULTI_INSTRUCTORS = 'Tutor Multi Instructors',
  TUTOR_ASSIGNMENTS = 'Tutor Assignments',
  TUTOR_COURSE_PREVIEW = 'Tutor Course Preview',
  TUTOR_COURSE_ATTACHMENTS = 'Tutor Course Attachments',
  TUTOR_GOOGLE_MEET_INTEGRATION = 'Tutor Google Meet Integration',
  TUTOR_REPORT = 'Tutor Report',
  EMAIL = 'Email',
  CALENDAR = 'Calendar',
  NOTIFICATIONS = 'Notifications',
  GOOGLE_CLASSROOM_INTEGRATION = 'Google Classroom Integration',
  TUTOR_ZOOM_INTEGRATION = 'Tutor Zoom Integration',
  QUIZ_EXPORT_IMPORT = 'Quiz Export/Import',
  ENROLLMENT = 'Enrollment',
  TUTOR_CERTIFICATE = 'Tutor Certificate',
  GRADEBOOK = 'Gradebook',
  TUTOR_PREREQUISITES = 'Tutor Prerequisites',
  BUDDYPRESS = 'BuddyPress',
  WOOCOMMERCE_SUBSCRIPTIONS = 'WooCommerce Subscriptions',
  PAID_MEMBERSHIPS_PRO = 'Paid Memberships Pro',
  RESTRICT_CONTENT_PRO = 'Restrict Content Pro',
  WEGLOT = 'Weglot',
  WPML_MULTILINGUAL_CMS = 'WPML Multilingual CMS',
}
