import { __ } from '@wordpress/i18n';

export const convertToErrorMessage = (error: unknown): string => {
  if (error instanceof Error) {
    return error.message || __('Something went wrong', 'tutor');
  }

  return __('Something went wrong', 'tutor');
};
