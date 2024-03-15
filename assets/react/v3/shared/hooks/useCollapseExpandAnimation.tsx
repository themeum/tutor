import { easings, useSpring } from '@react-spring/web';
import type { RefObject } from 'react';

export const useCollapseExpandAnimation = <T extends HTMLElement>({
	ref,
	isOpen,
	heightCalculator = 'scroll',
}: {
	ref: RefObject<T>;
	isOpen: boolean;
	heightCalculator?: 'scroll' | 'client';
}) => {
	const height = heightCalculator === 'scroll' ? ref.current?.scrollHeight : ref.current?.clientHeight;
	const heightAnimation = useSpring({
		height: isOpen ? height : 0,
		opacity: isOpen ? 1 : 0,
		overflow: 'hidden',
		config: {
			duration: 300,
			easing: (t) => t * (2 - t),
		},
	});

	return heightAnimation;
};
