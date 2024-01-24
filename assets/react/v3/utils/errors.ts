import { hasOwnProperty } from '@Utils/types';

const errorToMessage = {
  error_unexpected: 'msg_error_unexpected',
  error_verify_already_verified: 'msg_error_verify_already_verified',
  error_already_exists: 'msg_error_already_exists',
  error_login_bad_credentials: 'msg_error_login_bad_credentials',
  error_invalid_phone_format: 'msg_error_invalid_phone_format',
  error_anonymous_requester_info_required: 'msg_error_anonymous_requester_info_required',
  error_login_user_email_not_verified: 'msg_error_login_user_email_not_verified',
  error_password_is_incorrect: 'msg_error_password_is_incorrect',
  error_user_is_inactive: 'msg_error_user_is_inactive',
  error_new_email_already_taken: 'msg_error_email_is_already_taken',
} as const;

export const translateBeErrorMessage = (key: string): string => {
  if (hasOwnProperty(errorToMessage, key)) {
    return errorToMessage[key];
  }

  console.error(`Missing BE error translations: ${key}`);
  return key;
};
