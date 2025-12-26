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
