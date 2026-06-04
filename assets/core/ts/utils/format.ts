import { __ } from '@wordpress/i18n';

export const formatBytes = (bytes: number, decimals = 2) => {
  if (!bytes || bytes <= 1) {
    return __('0 Bytes', 'tutor');
  }

  const kilobit = 1024;
  const decimal = Math.max(0, decimals);
  const sizes = [
    __('Bytes', 'tutor'),
    __('KB', 'tutor'),
    __('MB', 'tutor'),
    __('GB', 'tutor'),
    __('TB', 'tutor'),
    __('PB', 'tutor'),
    __('EB', 'tutor'),
    __('ZB', 'tutor'),
    __('YB', 'tutor'),
  ];

  const index = Math.floor(Math.log(bytes) / Math.log(kilobit));

  return `${Number.parseFloat((bytes / kilobit ** index).toFixed(decimal))} ${sizes[index]}`;
};
