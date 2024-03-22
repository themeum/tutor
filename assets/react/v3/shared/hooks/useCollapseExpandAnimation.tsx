import { easings, update, useSpring } from '@react-spring/web';
import { useEffect, type RefObject, useState } from 'react';

export const useCollapseExpandAnimation = <T extends HTMLElement>({
	ref,
	isOpen,
	heightCalculator = 'scroll',
}: {
	ref: RefObject<T>;
	isOpen: boolean;
	heightCalculator?: 'scroll' | 'client';
}) => {
	const [height, setHeight] = useState<number | undefined>(
		heightCalculator === 'scroll' ? ref.current?.scrollHeight : ref.current?.clientHeight
	);

	useEffect(() => {
		if (ref.current) {
			const updatedHeight = heightCalculator === 'scroll' ? ref.current.scrollHeight : ref.current.clientHeight;
			setHeight(updatedHeight);
		}
	}, [ref.current, heightCalculator]);

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
