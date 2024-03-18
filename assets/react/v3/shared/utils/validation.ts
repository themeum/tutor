import { __ } from '@wordpress/i18n';
import { isValid } from 'date-fns';
import type { UseControllerProps } from 'react-hook-form';

import type { ProductDiscount } from './types';

type Rule = UseControllerProps['rules'];

export const requiredRule = (): Rule => ({
	required: { value: true, message: __('This field is required', 'tutor') },
});

export const maxValueRule = ({ maxValue, message }: { maxValue: number; message?: string }): Rule => ({
	maxLength: {
		value: maxValue,
		message: message || __(`Max. value should be ${maxValue}`),
	},
});

export const discountRule = (): Rule => ({
	validate: (value?: ProductDiscount) => {
		if (value?.amount === undefined) {
			return __('The field is required', 'tutor');
		}
		return undefined;
	},
});

export const invalidDateRule = (): Rule => ({
	validate: (value?: string) => {
		if (!isValid(new Date(value || ''))) {
			return 'Invalid date entered!';
		}

		return undefined;
	},
});

export const maxLimitRule = (maxLimit: number): Rule => ({
	validate: (value?: string) => {
		if (value && maxLimit < value.length) {
			return __(`Maximum ${maxLimit} character supported`, 'tutor');
		}
		return undefined;
	},
});

export const invalidTimeRule = (): Rule => ({
	validate: (value?: string) => {
		if (!value) {
			return undefined;
		}

		const message = 'Invalid time entered!';

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
	},
});
