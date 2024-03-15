import { isDefined } from '@Utils/types';
import type { ReactNode } from 'react';

export type Accessor<T> = () => T;

type ShowProps<T> = {
	when: T | undefined | null | false;
	children: ReactNode | ((item: NonNullable<T>) => ReactNode);
	fallback?: ReactNode;
};

const isConditionSatisfies = <T,>(value: T | undefined | null | false): value is NonNullable<T> => {
	return isDefined(value) && !!value;
};

const Show = <T,>({ when, children, fallback = null }: ShowProps<T>) => {
	const condition = isConditionSatisfies<T>(when);

	if (condition) {
		return typeof children === 'function' ? children(when) : children;
	}

	return fallback;
};

export default Show;
