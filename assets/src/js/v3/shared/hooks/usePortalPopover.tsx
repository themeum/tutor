import { css } from '@emotion/react';
import { type ReactNode, type RefObject, useEffect, useMemo, useRef, useState } from 'react';
import { createPortal } from 'react-dom';

import FocusTrap from '@TutorShared/components/FocusTrap';
import { useModal } from '@TutorShared/components/modals/Modal';
import { isRTL } from '@TutorShared/config/constants';
import { zIndex } from '@TutorShared/config/styles';
import { AnimatedDiv, AnimationType, useAnimation } from '@TutorShared/hooks/useAnimation';
import { useScrollLock } from '@TutorShared/hooks/useScrollLock';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { noop } from '@TutorShared/utils/util';

const ARROW_CONFIG = {
  SAFE_MARGIN: 12,
  MAX_OFFSET_VERTICAL: 6,
  MAX_OFFSET_HORIZONTAL: 12,
  CENTER_OFFSET: 8,
} as const;

const POPOVER_BOUNDARY_MARGIN = 4;

export const POPOVER_PLACEMENTS = {
  TOP: 'top',
  TOP_LEFT: 'topLeft',
  TOP_RIGHT: 'topRight',
  RIGHT: 'right',
  RIGHT_TOP: 'rightTop',
  RIGHT_BOTTOM: 'rightBottom',
  BOTTOM: 'bottom',
  BOTTOM_LEFT: 'bottomLeft',
  BOTTOM_RIGHT: 'bottomRight',
  LEFT: 'left',
  LEFT_TOP: 'leftTop',
  LEFT_BOTTOM: 'leftBottom',
  MIDDLE: 'middle',
  ABSOLUTE_CENTER: 'absoluteCenter',
} as const;

export type PopoverPlacement = (typeof POPOVER_PLACEMENTS)[keyof typeof POPOVER_PLACEMENTS];

interface PopoverPosition {
  left: number;
  top: number;
  placement: PopoverPlacement;
  arrowLeft?: number;
  arrowTop?: number;
}

interface Dimensions {
  width: number;
  height: number;
}

interface PopoverHookArgs<T> {
  isOpen: boolean;
  triggerRef?: RefObject<T>;
  placement?: PopoverPlacement;
  arrow?: boolean;
  autoAdjustOverflow?: boolean;
  gap?: number;
  positionModifier?: { top: number; left: number };
  dependencies?: unknown[];
}

interface PortalProps {
  isOpen: boolean;
  children: ReactNode;
  onClickOutside?: () => void;
  onEscape?: () => void;
  animationType?: AnimationType;
}

export const getMirroredPlacement = (placement: PopoverPlacement): PopoverPlacement => {
  const mirrorMap: Record<PopoverPlacement, PopoverPlacement> = {
    [POPOVER_PLACEMENTS.TOP]: POPOVER_PLACEMENTS.TOP,
    [POPOVER_PLACEMENTS.TOP_LEFT]: POPOVER_PLACEMENTS.TOP_RIGHT,
    [POPOVER_PLACEMENTS.TOP_RIGHT]: POPOVER_PLACEMENTS.TOP_LEFT,
    [POPOVER_PLACEMENTS.RIGHT]: POPOVER_PLACEMENTS.LEFT,
    [POPOVER_PLACEMENTS.RIGHT_TOP]: POPOVER_PLACEMENTS.LEFT_TOP,
    [POPOVER_PLACEMENTS.RIGHT_BOTTOM]: POPOVER_PLACEMENTS.LEFT_BOTTOM,
    [POPOVER_PLACEMENTS.BOTTOM]: POPOVER_PLACEMENTS.BOTTOM,
    [POPOVER_PLACEMENTS.BOTTOM_LEFT]: POPOVER_PLACEMENTS.BOTTOM_RIGHT,
    [POPOVER_PLACEMENTS.BOTTOM_RIGHT]: POPOVER_PLACEMENTS.BOTTOM_LEFT,
    [POPOVER_PLACEMENTS.LEFT]: POPOVER_PLACEMENTS.RIGHT,
    [POPOVER_PLACEMENTS.LEFT_TOP]: POPOVER_PLACEMENTS.RIGHT_TOP,
    [POPOVER_PLACEMENTS.LEFT_BOTTOM]: POPOVER_PLACEMENTS.RIGHT_BOTTOM,
    [POPOVER_PLACEMENTS.MIDDLE]: POPOVER_PLACEMENTS.MIDDLE,
    [POPOVER_PLACEMENTS.ABSOLUTE_CENTER]: POPOVER_PLACEMENTS.ABSOLUTE_CENTER,
  };

  return mirrorMap[placement] || placement;
};

const getMirroredModifier = (modifier: { top: number; left: number }): { top: number; left: number } => {
  return {
    top: modifier.top,
    left: -modifier.left,
  };
};

const checkOverflow = (
  position: { top: number; left: number },
  dimensions: Dimensions,
): { top: boolean; bottom: boolean; left: boolean; right: boolean } => {
  const { width, height } = dimensions;

  return {
    top: position.top < 0,
    bottom: position.top + height > window.innerHeight,
    left: position.left < 0,
    right: position.left + width > window.innerWidth,
  };
};

const willPlacementOverflow = (
  placement: PopoverPlacement,
  overflow: { top: boolean; bottom: boolean; left: boolean; right: boolean },
): boolean => {
  return (
    (placement.startsWith('top') && overflow.top) ||
    (placement.startsWith('bottom') && overflow.bottom) ||
    (placement.startsWith('left') && overflow.left) ||
    (placement.startsWith('right') && overflow.right)
  );
};

const calculatePositionForPlacement = (
  placement: PopoverPlacement,
  triggerRect: DOMRect,
  dimensions: Dimensions,
  gap: number,
  modifier: { top: number; left: number },
): { top: number; left: number } => {
  const { width, height } = dimensions;
  const { top: modTop, left: modLeft } = modifier;

  const centerX = triggerRect.left + triggerRect.width / 2 - width / 2;
  const centerY = triggerRect.top + triggerRect.height / 2 - height / 2;

  const positionMap: Record<PopoverPlacement, { top: number; left: number }> = {
    [POPOVER_PLACEMENTS.TOP]: {
      top: triggerRect.top - height - gap,
      left: centerX,
    },
    [POPOVER_PLACEMENTS.TOP_LEFT]: {
      top: triggerRect.top - height - gap,
      left: triggerRect.left,
    },
    [POPOVER_PLACEMENTS.TOP_RIGHT]: {
      top: triggerRect.top - height - gap,
      left: triggerRect.right - width,
    },

    [POPOVER_PLACEMENTS.BOTTOM]: {
      top: triggerRect.bottom + gap,
      left: centerX,
    },
    [POPOVER_PLACEMENTS.BOTTOM_LEFT]: {
      top: triggerRect.bottom + gap,
      left: triggerRect.left,
    },
    [POPOVER_PLACEMENTS.BOTTOM_RIGHT]: {
      top: triggerRect.bottom + gap,
      left: triggerRect.right - width,
    },

    [POPOVER_PLACEMENTS.LEFT]: {
      top: centerY,
      left: triggerRect.left - width - gap,
    },
    [POPOVER_PLACEMENTS.LEFT_TOP]: {
      top: triggerRect.top,
      left: triggerRect.left - width - gap,
    },
    [POPOVER_PLACEMENTS.LEFT_BOTTOM]: {
      top: triggerRect.bottom - height,
      left: triggerRect.left - width - gap,
    },

    [POPOVER_PLACEMENTS.RIGHT]: {
      top: centerY,
      left: triggerRect.right + gap,
    },
    [POPOVER_PLACEMENTS.RIGHT_TOP]: {
      top: triggerRect.top,
      left: triggerRect.right + gap,
    },
    [POPOVER_PLACEMENTS.RIGHT_BOTTOM]: {
      top: triggerRect.bottom - height,
      left: triggerRect.right + gap,
    },

    [POPOVER_PLACEMENTS.MIDDLE]: {
      top: centerY,
      left: centerX,
    },
    [POPOVER_PLACEMENTS.ABSOLUTE_CENTER]: {
      top: window.innerHeight / 2 - height / 2,
      left: window.innerWidth / 2 - width / 2,
    },
  };

  const position = positionMap[placement] || positionMap[POPOVER_PLACEMENTS.BOTTOM];
  return {
    top: position.top + modTop,
    left: position.left + modLeft,
  };
};

const adjustPositionForOverflow = (
  position: { top: number; left: number },
  placement: PopoverPlacement,
  dimensions: Dimensions,
  triggerRect: DOMRect,
  gap: number,
  modifier: { top: number; left: number },
): { position: { top: number; left: number }; placement: PopoverPlacement } => {
  const oppositeMapping = {
    [POPOVER_PLACEMENTS.TOP]: POPOVER_PLACEMENTS.BOTTOM,
    [POPOVER_PLACEMENTS.TOP_LEFT]: POPOVER_PLACEMENTS.BOTTOM_LEFT,
    [POPOVER_PLACEMENTS.TOP_RIGHT]: POPOVER_PLACEMENTS.BOTTOM_RIGHT,
    [POPOVER_PLACEMENTS.BOTTOM]: POPOVER_PLACEMENTS.TOP,
    [POPOVER_PLACEMENTS.BOTTOM_LEFT]: POPOVER_PLACEMENTS.TOP_LEFT,
    [POPOVER_PLACEMENTS.BOTTOM_RIGHT]: POPOVER_PLACEMENTS.TOP_RIGHT,
    [POPOVER_PLACEMENTS.LEFT]: POPOVER_PLACEMENTS.RIGHT,
    [POPOVER_PLACEMENTS.LEFT_TOP]: POPOVER_PLACEMENTS.RIGHT_TOP,
    [POPOVER_PLACEMENTS.LEFT_BOTTOM]: POPOVER_PLACEMENTS.RIGHT_BOTTOM,
    [POPOVER_PLACEMENTS.RIGHT]: POPOVER_PLACEMENTS.LEFT,
    [POPOVER_PLACEMENTS.RIGHT_TOP]: POPOVER_PLACEMENTS.LEFT_TOP,
    [POPOVER_PLACEMENTS.RIGHT_BOTTOM]: POPOVER_PLACEMENTS.LEFT_BOTTOM,
    [POPOVER_PLACEMENTS.MIDDLE]: POPOVER_PLACEMENTS.MIDDLE,
    [POPOVER_PLACEMENTS.ABSOLUTE_CENTER]: POPOVER_PLACEMENTS.ABSOLUTE_CENTER,
  };
  const originalOverflow = checkOverflow(position, dimensions);
  const originalWouldOverflow = willPlacementOverflow(placement, originalOverflow);

  if (!originalWouldOverflow) {
    return { position, placement };
  }

  // Try opposite placement
  const oppositePlacement = oppositeMapping[placement];
  const oppositePosition = calculatePositionForPlacement(oppositePlacement, triggerRect, dimensions, gap, modifier);
  const oppositeOverflow = checkOverflow(oppositePosition, dimensions);
  const oppositeWouldOverflow = willPlacementOverflow(oppositePlacement, oppositeOverflow);

  if (!oppositeWouldOverflow) {
    return { position: oppositePosition, placement: oppositePlacement };
  }

  return { position, placement };
};

const calculateArrowPosition = (
  placement: PopoverPlacement,
  triggerRect: DOMRect,
  popoverPosition: { top: number; left: number },
  dimensions: Dimensions,
): { arrowLeft?: number; arrowTop?: number } => {
  const { width, height } = dimensions;

  // Skip arrow for covered triggers or special placements
  const isSpecialPlacement = (
    [POPOVER_PLACEMENTS.MIDDLE, POPOVER_PLACEMENTS.ABSOLUTE_CENTER] as PopoverPlacement[]
  ).includes(placement);
  const isTriggerCovered =
    popoverPosition.left < triggerRect.left + ARROW_CONFIG.SAFE_MARGIN &&
    popoverPosition.left + width > triggerRect.right - ARROW_CONFIG.SAFE_MARGIN &&
    popoverPosition.top < triggerRect.top + ARROW_CONFIG.SAFE_MARGIN &&
    popoverPosition.top + height > triggerRect.bottom - ARROW_CONFIG.SAFE_MARGIN;

  if (isSpecialPlacement || isTriggerCovered) return {};

  const isVertical = placement.startsWith('top') || placement.startsWith('bottom');
  const isHorizontal = placement.startsWith('left') || placement.startsWith('right');

  if (isVertical) {
    const triggerCenter = triggerRect.left + triggerRect.width / 2;
    let arrowLeft =
      Math.max(
        ARROW_CONFIG.SAFE_MARGIN,
        Math.min(width - ARROW_CONFIG.MAX_OFFSET_VERTICAL, triggerCenter - popoverPosition.left),
      ) - ARROW_CONFIG.CENTER_OFFSET;

    if (isRTL) {
      arrowLeft = width - arrowLeft - ARROW_CONFIG.CENTER_OFFSET * 2;
    }

    return { arrowLeft };
  }

  if (isHorizontal) {
    const triggerCenter = triggerRect.top + triggerRect.height / 2;
    const arrowTop =
      Math.max(
        ARROW_CONFIG.SAFE_MARGIN,
        Math.min(height - ARROW_CONFIG.MAX_OFFSET_HORIZONTAL, triggerCenter - popoverPosition.top),
      ) - ARROW_CONFIG.CENTER_OFFSET;
    return { arrowTop };
  }

  return {};
};

const clampPositionToBoundaries = (
  position: { top: number; left: number },
  dimensions: Dimensions,
  margin: number = POPOVER_BOUNDARY_MARGIN,
): { top: number; left: number } => {
  const { width, height } = dimensions;

  return {
    left: Math.max(margin, Math.min(window.innerWidth - width - margin, position.left)),
    top: Math.max(margin, Math.min(window.innerHeight - height - margin, position.top)),
  };
};

export const usePortalPopover = <T extends HTMLElement, D extends HTMLElement>({
  isOpen,
  triggerRef: popoverTriggerRef,
  placement = POPOVER_PLACEMENTS.BOTTOM,
  arrow = false,
  gap = 10,
  autoAdjustOverflow = true,
  positionModifier = { top: 0, left: 0 },
  dependencies = [],
}: PopoverHookArgs<T>) => {
  const triggerRef = useMemo(() => popoverTriggerRef || { current: null }, [popoverTriggerRef]);
  const popoverRef = useRef<D>(null);
  const [triggerWidth, setTriggerWidth] = useState(0);
  const [position, setPosition] = useState<PopoverPosition>({
    left: 0,
    top: 0,
    placement: POPOVER_PLACEMENTS.BOTTOM,
  });

  const effectivePlacement = useMemo(() => {
    return isRTL ? getMirroredPlacement(placement) : placement;
  }, [placement]);

  const effectiveModifier = useMemo(() => {
    return isRTL ? getMirroredModifier(positionModifier) : positionModifier;
  }, [positionModifier]);

  useEffect(() => {
    if (!triggerRef.current) return;
    setTriggerWidth(triggerRef.current.getBoundingClientRect().width);
  }, [triggerRef]);

  useEffect(() => {
    if (!isOpen || !triggerRef.current || !popoverRef.current) return;

    const triggerRect = triggerRef.current.getBoundingClientRect();
    const popoverRect = popoverRef.current.getBoundingClientRect();
    const dimensions = {
      width: popoverRect.width || triggerRect.width,
      height: popoverRect.height,
    };

    let calculatedPosition = calculatePositionForPlacement(
      effectivePlacement,
      triggerRect,
      dimensions,
      gap,
      effectiveModifier,
    );
    let finalPlacement = effectivePlacement;

    if (autoAdjustOverflow) {
      const adjusted = adjustPositionForOverflow(
        calculatedPosition,
        effectivePlacement,
        dimensions,
        triggerRect,
        gap,
        effectiveModifier,
      );
      calculatedPosition = adjusted.position;
      finalPlacement = adjusted.placement;
    }

    calculatedPosition = clampPositionToBoundaries(calculatedPosition, dimensions);

    const arrowPosition = arrow
      ? calculateArrowPosition(finalPlacement, triggerRect, calculatedPosition, dimensions)
      : {};

    setPosition({
      ...calculatedPosition,
      placement: finalPlacement,
      ...arrowPosition,
    });
  }, [
    triggerRef,
    popoverRef,
    isOpen,
    effectivePlacement,
    effectiveModifier,
    gap,
    arrow,
    autoAdjustOverflow,
    // eslint-disable-next-line react-hooks/exhaustive-deps
    ...dependencies,
  ]);

  return { position, triggerWidth, triggerRef, popoverRef };
};

export const Portal = ({
  isOpen,
  children,
  onClickOutside,
  onEscape,
  animationType = AnimationType.slideDown,
}: PortalProps) => {
  const { hasModalOnStack } = useModal();
  useScrollLock(isOpen);

  useEffect(() => {
    const handleKeyDown = (event: KeyboardEvent) => {
      if (event.key === 'Escape') {
        onEscape?.();
      }
    };

    if (!isOpen) return;

    document.addEventListener('keydown', handleKeyDown, true);

    return () => {
      document.removeEventListener('keydown', handleKeyDown, true);
    };
  }, [isOpen, hasModalOnStack, onEscape]);

  const { transitions } = useAnimation({
    data: isOpen,
    animationType,
  });

  return transitions((style, openState) => {
    if (!openState) {
      return null;
    }

    return createPortal(
      <AnimatedDiv css={styles.wrapper} style={style}>
        <FocusTrap>
          <div className="tutor-portal-popover" role="presentation">
            <div
              css={styles.backdrop}
              onKeyUp={noop}
              onClick={(event) => {
                event.stopPropagation();
                onClickOutside?.();
              }}
            />
            {children}
          </div>
        </FocusTrap>
      </AnimatedDiv>,
      document.body,
    );
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
