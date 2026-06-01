/**
 * Date format constants compatible with dayjs
 * These formats replace date-fns formats for dayjs compatibility
 */
export enum DateFormats {
  day = 'DD',
  month = 'MMM',
  year = 'YYYY',
  yearMonthDay = 'YYYY-MM-DD',
  monthDayYear = 'MMM DD, YYYY',
  hoursMinutes = 'hh:mm A',
  yearMonthDayHourMinuteSecond = 'YYYY-MM-DD hh:mm:ss',
  yearMonthDayHourMinuteSecond24H = 'YYYY-MM-DD HH:mm:ss',
  monthDayYearHoursMinutes = 'MMM DD, YYYY, hh:mm A',
  localMonthDayYearHoursMinutes = 'MMM DD, YYYY hh:mm A',
  activityDate = 'MMM DD, YYYY at hh:mm A',
  validityDate = 'DD MMMM YYYY',
  dayMonthYear = 'MMMM D, YYYY',
}
