import type { ErrorResponse } from '@TutorShared/utils/form';
import { __ } from '@wordpress/i18n';

export const convertToErrorMessage = (error: ErrorResponse) => {
  const errorData = error.response?.data ?? error.data;

  if (!errorData) {
    return __('Something went wrong', 'tutor');
  }

  let errorMessage = errorData.message;
  if (errorData.status_code === 422 && errorData.data) {
    errorMessage = errorData.data[Object.keys(errorData.data)[0]];
  }

  return errorMessage || __('Something went wrong', 'tutor');
};
