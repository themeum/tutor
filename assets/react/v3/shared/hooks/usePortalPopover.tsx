import { useModal } from '@Components/modals/Modal';
import { zIndex } from '@Config/styles';
import { styleUtils } from '@Utils/style-utils';
import { css } from '@emotion/react';
import { type ReactNode, type RefObject, useEffect, useMemo, useRef, useState } from 'react';
import { createPortal } from 'react-dom';

import { noop } from '@Utils/util';
import { AnimatedDiv, AnimationType, useAnimation } from './useAnimation';
import { usePrevious } from './usePrevious';

enum ArrowPosition {
	left = 'left',
	right = 'right',
	top = 'top',
	bottom = 'bottom',
	middle = 'middle',
	auto = 'auto',
}
export type arrowPosition = `${ArrowPosition}`;
interface PopoverHookArgs<T> {
	isOpen: boolean;
	triggerRef?: RefObject<T>;
	arrow?: arrowPosition;
	gap?: number;
	isDropdown?: boolean;
	positionModifier?: {
		top: number;
		left: number;
	};
}

interface PopoverPosition {
	left: number;
	top: number;
	arrowPlacement: arrowPosition;
}

export const usePortalPopover = <T extends HTMLElement, D extends HTMLElement>({
	isOpen,
	triggerRef: popoverTriggerRef,
	arrow = ArrowPosition.auto,
	gap = 10,
	isDropdown = false,
	positionModifier = {
		top: 0,
		left: 0,
	},
}: PopoverHookArgs<T>) => {
	const triggerRef = useMemo(() => {
		return popoverTriggerRef || { current: null };
	}, [popoverTriggerRef]);
	const popoverRef = useRef<D>(null);
	const previousPopoverRect = usePrevious(popoverRef.current?.getBoundingClientRect());
	const [triggerWidth, setTriggerWidth] = useState(0);
	const [position, setPosition] = useState<PopoverPosition>({ left: 0, top: 0, arrowPlacement: ArrowPosition.bottom });

	useEffect(() => {
		if (!triggerRef.current) return;

		const triggerRect = triggerRef.current.getBoundingClientRect();
		setTriggerWidth(triggerRect.width);
	}, [triggerRef]);

	// biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
	useEffect(() => {
		if (!isOpen || !triggerRef.current || !popoverRef.current) {
			return;
		}

		const triggerRect = triggerRef.current.getBoundingClientRect();
		const popoverRect = popoverRef.current.getBoundingClientRect();
		const { height: previousPopoverHeight, top: previousPopoverTop } = previousPopoverRect || {};
		const popoverWidth = popoverRect.width || triggerRect.width;
		const popoverHeight = popoverRect.height;

		if (previousPopoverHeight === popoverHeight && previousPopoverTop === popoverRect.top) {
			return;
		}

		let calculatedPosition: { top: number; left: number } = { top: 0, left: 0 };
		let arrowPlacement: PopoverPosition['arrowPlacement'] = ArrowPosition.bottom;

		const viewPortHeight = window.innerHeight || document.documentElement.clientHeight;
		const viewPortWidth = window.innerWidth || document.documentElement.clientWidth;
		const popoverHeightWithGap = popoverHeight + gap;
		const popoverWidthWithGap = popoverWidth + gap;
		const heightDifference = viewPortHeight - popoverHeight;

		const getLeftForTopBottom = () => {
			if (arrow === 'auto' && viewPortWidth > triggerRect.left + popoverWidth) {
				return Math.floor(triggerRect.left);
			}
			if (arrow === 'auto' && triggerRect.left > popoverWidth) {
				return Math.floor(triggerRect.right - popoverWidth);
			}

			return Math.floor(triggerRect.left - (popoverWidth - triggerWidth) / 2) + positionModifier.left;
		};
		const getTopForLeftRight = () =>
			Math.floor(triggerRect.top - popoverHeight / 2 + triggerRect.height / 2) + positionModifier.top;

		const positions = {
			top: {
				top: Math.floor(triggerRect.top - popoverHeight - gap + positionModifier.top),
				left: getLeftForTopBottom(),
			},
			bottom: {
				top: Math.floor(triggerRect.bottom + gap + positionModifier.top),
				left: getLeftForTopBottom(),
			},
			left: {
				top: getTopForLeftRight(),
				left: Math.floor(triggerRect.left - popoverWidth - gap + positionModifier.left),
			},
			right: {
				top: getTopForLeftRight(),
				left: Math.floor(triggerRect.right + gap + positionModifier.left),
			},
			middle: {
				top: heightDifference < 0 ? 0 : heightDifference / 2,
				left: Math.floor(triggerRect.left - popoverWidth / 2 + triggerRect.width / 2),
			},
		};

		const arrowPositions = {
			top: positions.bottom,
			bottom: positions.top,
			left: positions.right,
			right: positions.left,
			middle: positions.middle,
		};

		if (arrow !== ArrowPosition.auto) {
			calculatedPosition = arrowPositions[arrow];
			arrowPlacement = arrow;
		} else if (triggerRect.bottom + popoverHeightWithGap > viewPortHeight && triggerRect.top > popoverHeightWithGap) {
			calculatedPosition = positions.top;
			arrowPlacement = ArrowPosition.bottom;
		} else if (
			popoverWidthWithGap > triggerRect.left &&
			triggerRect.bottom + popoverHeightWithGap > viewPortHeight &&
			!isDropdown
		) {
			calculatedPosition = positions.right;
			arrowPlacement = ArrowPosition.left;
		} else if (
			popoverWidthWithGap < triggerRect.left &&
			triggerRect.bottom + popoverHeightWithGap > viewPortHeight &&
			!isDropdown
		) {
			calculatedPosition = positions.left;
			arrowPlacement = ArrowPosition.right;
		} else if (triggerRect.bottom + popoverHeightWithGap <= viewPortHeight) {
			calculatedPosition = positions.bottom;
			arrowPlacement = ArrowPosition.top;
		} else {
			calculatedPosition = positions.middle;
			arrowPlacement = ArrowPosition.middle;
		}

		setPosition({ ...calculatedPosition, arrowPlacement });
	}, [triggerRef, popoverRef, previousPopoverRect, triggerWidth, isOpen, gap, arrow, isDropdown]);

	return { position, triggerWidth, triggerRef, popoverRef };
};

interface PortalProps {
	isOpen: boolean;
	children: ReactNode;
	onClickOutside?: () => void;
	animationType?: AnimationType;
}

export const Portal = ({ isOpen, children, onClickOutside, animationType = AnimationType.slideDown }: PortalProps) => {
	// We can determine if there any modal opens by using this `hasModalOnStack`
	// If there any modal opens then we will not set the overflow value to initial.
	const { hasModalOnStack } = useModal();

	useEffect(() => {
		if (isOpen) {
			document.body.style.overflow = 'hidden';
		}

		return () => {
			if (!hasModalOnStack) {
				document.body.style.overflow = 'initial';
			}
		};
	}, [isOpen, hasModalOnStack]);

	const { transitions } = useAnimation({
		data: isOpen,
		animationType,
	});

	return transitions((style, openState) => {
		if (openState) {
			return createPortal(
				<AnimatedDiv css={styles.wrapper} style={style}>
					<div role="presentation">
						<div
							css={styles.backdrop}
							onKeyUp={noop}
							onClick={() => {
								onClickOutside?.();
							}}
						/>
						{children}
					</div>
				</AnimatedDiv>,
				document.body
			);
		}
	});
};

const styles = {
	wrapper: css`
    position: fixed;
    z-index: ${zIndex.highest};
    inset: 0;
  `,
	backdrop: css`
    ${styleUtils.centeredFlex};
    position: fixed;
    inset: 0;
    z-index: ${zIndex.negative};
  `,
};
