import { __ } from '@wordpress/i18n';
import { isValid } from 'date-fns';

import type { ProductDiscount } from './types';

export const requiredRule = (): object => ({
  required: { value: true, message: __('This field is required', __TUTOR_TEXT_DOMAIN__) },
});

export const maxValueRule = ({ maxValue, message }: { maxValue: number; message?: string }): object => ({
  maxLength: {
    value: maxValue,
    message: message || __(`Max. value should be ${maxValue}`, __TUTOR_TEXT_DOMAIN__),
  },
});

const EMAIL_PATTERN = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
const URL_PATTERN = /^https?:\/\//i;

export const emailRule = (): object => ({
  validate: (value?: string) => {
    const email = value?.trim();

    if (email && !EMAIL_PATTERN.test(email)) {
      return __('Invalid email address entered!', __TUTOR_TEXT_DOMAIN__);
    }

    return undefined;
  },
});

export const credentialRule = ({ allowEmail = false }: { allowEmail?: boolean } = {}): object => ({
  validate: (value?: string) => {
    const credential = value?.trim();

    if (!credential) {
      return undefined;
    }

    if (URL_PATTERN.test(credential)) {
      return __('This field should not be a URL.', __TUTOR_TEXT_DOMAIN__);
    }

    if (!allowEmail && EMAIL_PATTERN.test(credential)) {
      return __('This field should not be an email.', __TUTOR_TEXT_DOMAIN__);
    }

    return undefined;
  },
});

export const discountRule = (): object => ({
  validate: (value?: ProductDiscount) => {
    if (value?.amount === undefined) {
      return __('The field is required', __TUTOR_TEXT_DOMAIN__);
    }
    return undefined;
  },
});

export const invalidDateRule = (value?: string): string | undefined => {
  if (!isValid(new Date(value || ''))) {
    return __('Invalid date entered!', __TUTOR_TEXT_DOMAIN__);
  }

  return undefined;
};

export const maxLimitRule = (maxLimit: number): object => ({
  validate: (value?: string) => {
    if (value && maxLimit < value.length) {
      return __(`Maximum ${maxLimit} character supported`, __TUTOR_TEXT_DOMAIN__);
    }
    return undefined;
  },
});

export const invalidTimeRule = (value?: string): string | undefined => {
  if (!value) {
    return undefined;
  }

  const message = __('Invalid time entered!', __TUTOR_TEXT_DOMAIN__);

  const [hours, minutesAndMeridian] = value.split(':');

  if (!hours || !minutesAndMeridian) {
    return message;
  }

  const [minutes, meridian] = minutesAndMeridian.split(' ');

  if (!minutes || !meridian) {
    return message;
  }

  if (hours.length !== 2 || minutes.length !== 2) {
    return message;
  }

  if (Number(hours) < 1 || Number(hours) > 12) {
    return message;
  }

  if (Number(minutes) < 0 || Number(minutes) > 59) {
    return message;
  }

  if (!['am', 'pm'].includes(meridian.toLowerCase())) {
    return message;
  }

  return undefined;
};
