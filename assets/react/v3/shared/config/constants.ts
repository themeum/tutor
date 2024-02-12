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

export const TutorRoles = {
  ADMINISTRATOR: 'administrator',
  TUTOR_INSTRUCTOR: 'tutor_instructor',
  SUBSCRIBER: 'subscriber',
};

export enum LocalStorage {
  clientId = 'eCommerceClientId',
}

export enum DateFormats {
  yearMonthDay = 'yyyy-LL-dd',
  monthDayYear = 'MMM dd, yyyy',
  hoursMinutes = 'hh:mm a',
  yearMonthDayHourMinuteSecond = 'yyyy-MM-dd hh:mm:ss',
  monthDayYearHoursMinutes = 'MMM dd, yyyy, hh:mm a',
  localMonthDayYearHoursMinutes = 'PPp',
}
