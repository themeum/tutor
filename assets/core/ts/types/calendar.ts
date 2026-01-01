import { __ } from '@wordpress/i18n';

export const CALENDAR_PRESETS = {
  'all-time': __('All Time', 'tutor'),
  yesterday: __('Yesterday', 'tutor'),
  'last-7': __('Last 7 days', 'tutor'),
  'last-14': __('Last 14 days', 'tutor'),
  'last-30': __('Last 30 days', 'tutor'),
  'this-month': __('This month', 'tutor'),
  'last-month': __('Last month', 'tutor'),
  'last-year': __('Last year', 'tutor'),
} as const;

export type Preset = keyof typeof CALENDAR_PRESETS;
