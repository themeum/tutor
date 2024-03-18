import type { SerializedStyles } from '@emotion/react';
import { type EasingFunction, animated, easings, useSpring, useTransition } from '@react-spring/web';
import type { ReactNode } from 'react';
import useMeasure from 'react-use-measure';

export enum AnimationType {
	slideDown = 0,
	slideUp = 1,
	slideLeft = 2,
	slideRight = 3,
	collapseExpand = 4,
	zoomIn = 5,
	zoomOut = 6,
	fadeIn = 7,
	sidebar = 8,
}

interface AnimationProps<T> {
	data: T | T[];
	animationType?: AnimationType;
	slideThreshold?: number;
	animationDuration?: number;
	minOpacity?: number;
	maxOpacity?: number;
	easing?: EasingFunction;
	debounceMeasure?: boolean;
}

const MEASURE_DELAY_TIME = 100;

export const useAnimation = <T,>({
	data,
	animationType = AnimationType.collapseExpand,
	slideThreshold = 20,
	animationDuration = 150,
	minOpacity = 0,
	maxOpacity = 1,
	easing = easings.easeInOutQuad,
	debounceMeasure = false,
}: AnimationProps<T>) => {
	const isTriggered = Array.isArray(data) ? data.length > 0 : !!data;
	const [ref, position] = useMeasure({ debounce: debounceMeasure ? animationDuration + MEASURE_DELAY_TIME : 0 });

	const animationStyle = useSpring({
		from: {
			height: 0,
			opacity: minOpacity,
			y: 0,
		},
		to: {
			height: isTriggered ? position.height : 0,
			opacity: isTriggered ? maxOpacity : minOpacity,
			y: isTriggered ? 0 : slideThreshold * -1,
		},
		config: {
			duration: animationDuration,
			easing,
		},
	});

	const sidebarStyle = useSpring({
		from: {
			x: 0,
		},
		to: {
			x: isTriggered ? 0 : slideThreshold * -1,
		},
		config: {
			duration: animationDuration,
			easing,
		},
	});

	const coordinates = {
		x: 0,
		y: 0,
	};

	switch (animationType) {
		case AnimationType.slideDown:
			coordinates.y = slideThreshold * -1;
			coordinates.x = 0;
			break;
		case AnimationType.slideUp:
			coordinates.y = slideThreshold;
			coordinates.x = 0;
			break;
		case AnimationType.slideLeft:
			coordinates.x = slideThreshold;
			coordinates.y = 0;
			break;
		case AnimationType.slideRight:
			coordinates.x = slideThreshold * -1;
			coordinates.y = 0;
			break;
	}

	const transitions = useTransition(data, {
		from: {
			opacity: minOpacity,
			...coordinates,
			...(animationType === AnimationType.zoomIn && { transform: 'scale(0.8)' }),
			...(animationType === AnimationType.zoomOut && { transform: 'scale(1.2)' }),
			...(animationType === AnimationType.fadeIn && { opacity: 0 }),
		},
		enter: {
			opacity: maxOpacity,
			x: 0,
			y: 0,
			...(animationType === AnimationType.zoomIn && { transform: 'scale(1)' }),
			...(animationType === AnimationType.zoomOut && { transform: 'scale(1)' }),
			...(animationType === AnimationType.fadeIn && { opacity: 1 }),
		},
		leave: {
			opacity: minOpacity,
			...coordinates,
			...(animationType === AnimationType.zoomIn && { transform: 'scale(0.8)' }),
			...(animationType === AnimationType.zoomOut && { transform: 'scale(1.2)' }),
			...(animationType === AnimationType.fadeIn && { opacity: 0 }),
		},
		config: {
			duration: animationDuration,
			easing,
		},
	});

	return { animationStyle: animationType === AnimationType.sidebar ? sidebarStyle : animationStyle, ref, transitions };
};

export const AnimatedDiv = ({
	children,
	style,
	css,
	hideOnOverflow = true,
	...props
}: {
	children: ReactNode;
	style: Record<string, unknown>;
	css?: SerializedStyles;
	hideOnOverflow?: boolean;
}) => {
	return (
		<animated.div {...props} style={{ ...style, overflow: hideOnOverflow ? 'hidden' : 'initial' }}>
			{children}
		</animated.div>
	);
};
