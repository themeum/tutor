import { css } from '@emotion/react';
import { type ReactNode, type RefObject, useEffect, useMemo, useRef, useState } from 'react';
import { createPortal } from 'react-dom';

import FocusTrap from '@TutorShared/components/FocusTrap';
import { useModal } from '@TutorShared/components/modals/Modal';
import { zIndex } from '@TutorShared/config/styles';
import { AnimatedDiv, AnimationType, useAnimation } from '@TutorShared/hooks/useAnimation';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { noop } from '@TutorShared/utils/util';

const ARROW_SAFE_MARGIN = 12; // Minimum px from edge of popover for arrow
const ARROW_MAX_OFFSET_VERTICAL = 6; // Max px from right edge for vertical arrow
const ARROW_MAX_OFFSET_HORIZONTAL = 12; // Max px from bottom edge for horizontal arrow
const ARROW_CENTER_OFFSET = 8; // Half arrow size (for centering, assuming 16px arrow)
const POPOVER_BOUNDARY_MARGIN = 4;

export const PLACEMENTS = {
  // Top placements
  TOP: 'top',
  TOP_LEFT: 'topLeft',
  TOP_RIGHT: 'topRight',

  // Right placements
  RIGHT: 'right',
  RIGHT_TOP: 'rightTop',
  RIGHT_BOTTOM: 'rightBottom',

  // Bottom placements
  BOTTOM: 'bottom',
  BOTTOM_LEFT: 'bottomLeft',
  BOTTOM_RIGHT: 'bottomRight',

  // Left placements
  LEFT: 'left',
  LEFT_TOP: 'leftTop',
  LEFT_BOTTOM: 'leftBottom',

  // Center placements
  middle: 'middle',
  ABSOLUTE_CENTER: 'absoluteCenter',
} as const;

export type PopoverPlacement = (typeof PLACEMENTS)[keyof typeof PLACEMENTS];

interface PopoverPosition {
  left: number;
  top: number;
  placement: PopoverPlacement;
  arrowLeft?: number;
  arrowTop?: number;
}

interface PopoverHookArgs<T> {
  isOpen: boolean;
  triggerRef?: RefObject<T>;
  placement?: PopoverPlacement;
  arrow?: boolean;
  autoAdjustOverflow?: boolean;
  gap?: number;
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  dependencies?: any[];
}

export const useEnhancedPortalPopover = <T extends HTMLElement, D extends HTMLElement>({
  isOpen,
  triggerRef: popoverTriggerRef,
  placement = PLACEMENTS.BOTTOM,
  arrow = false,
  gap = 10,
  autoAdjustOverflow = true,
  dependencies = [],
}: PopoverHookArgs<T>) => {
  const triggerRef = useMemo(() => {
    return popoverTriggerRef || { current: null };
  }, [popoverTriggerRef]);
  const popoverRef = useRef<D>(null);
  const [triggerWidth, setTriggerWidth] = useState(0);
  const [position, setPosition] = useState<PopoverPosition>({
    left: 0,
    top: 0,
    placement: PLACEMENTS.BOTTOM,
  });

  useEffect(() => {
    if (!triggerRef.current) return;

    const triggerRect = triggerRef.current.getBoundingClientRect();
    setTriggerWidth(triggerRect.width);
  }, [triggerRef]);

  useEffect(() => {
    if (!isOpen || !triggerRef.current || !popoverRef.current) {
      return;
    }

    const triggerRect = triggerRef.current.getBoundingClientRect();
    const popoverRect = popoverRef.current.getBoundingClientRect();
    const popoverWidth = popoverRect.width || triggerRect.width;
    const popoverHeight = popoverRect.height;

    const container = document.body;
    const containerRect = container.getBoundingClientRect();

    let calculatedPosition: { top: number; left: number } = { top: 0, left: 0 };
    let finalPlacement: PopoverPosition['placement'] = placement;
    let arrowLeft: number | undefined;
    let arrowTop: number | undefined;

    const positions = {
      // top placements
      top: {
        top: triggerRect.top - popoverHeight - gap,
        left: triggerRect.left + triggerRect.width / 2 - popoverWidth / 2,
      },
      topLeft: {
        top: triggerRect.top - popoverHeight - gap,
        left: triggerRect.left,
      },
      topRight: {
        top: triggerRect.top - popoverHeight - gap,
        left: triggerRect.right - popoverWidth,
      },

      // right placements
      right: {
        top: triggerRect.top + triggerRect.height / 2 - popoverHeight / 2,
        left: triggerRect.right + gap,
      },
      rightTop: {
        top: triggerRect.top,
        left: triggerRect.right + gap,
      },
      rightBottom: {
        top: triggerRect.bottom - popoverHeight,
        left: triggerRect.right + gap,
      },

      // Bottom placements
      bottom: {
        top: triggerRect.bottom + gap,
        left: triggerRect.left + triggerRect.width / 2 - popoverWidth / 2,
      },
      bottomLeft: {
        top: triggerRect.bottom + gap,
        left: triggerRect.left,
      },
      bottomRight: {
        top: triggerRect.bottom + gap,
        left: triggerRect.right - popoverWidth,
      },

      // left placements
      left: {
        top: triggerRect.top + triggerRect.height / 2 - popoverHeight / 2,
        left: triggerRect.left - popoverWidth - gap,
      },
      leftTop: {
        top: triggerRect.top,
        left: triggerRect.left - popoverWidth - gap,
      },
      leftBottom: {
        top: triggerRect.bottom - popoverHeight,
        left: triggerRect.left - popoverWidth - gap,
      },

      // center placements
      middle: {
        top: triggerRect.top + triggerRect.height / 2 - popoverHeight / 2,
        left: triggerRect.left + triggerRect.width / 2 - popoverWidth / 2,
      },
      absoluteCenter: {
        top: window.innerHeight / 2 - popoverHeight / 2,
        left: window.innerWidth / 2 - popoverWidth / 2,
      },
    };

    calculatedPosition = positions[finalPlacement as keyof typeof positions] || positions.bottom;

    if (autoAdjustOverflow) {
      const wouldOverflow = {
        top: calculatedPosition.top < containerRect.top,
        bottom: calculatedPosition.top + popoverHeight > containerRect.top + containerRect.height,
        left: calculatedPosition.left < containerRect.left,
        right: calculatedPosition.left + popoverWidth > containerRect.left + containerRect.width,
      };

      const oppositePlacements = {
        top: 'bottom',
        bottom: 'top',
        left: 'right',
        right: 'left',
        topLeft: 'bottomLeft',
        bottomLeft: 'topLeft',
        topRight: 'bottomRight',
        bottomRight: 'topRight',
        leftTop: 'rightTop',
        rightTop: 'leftTop',
        leftBottom: 'rightBottom',
        rightBottom: 'leftBottom',
        middle: 'middle',
        absoluteCenter: 'absoluteCenter',
      } as const satisfies Record<PopoverPlacement, PopoverPlacement>;

      if (finalPlacement.startsWith('top') && wouldOverflow.top) {
        finalPlacement = oppositePlacements[finalPlacement];
        calculatedPosition = positions[finalPlacement as keyof typeof positions];
      } else if (finalPlacement.startsWith('bottom') && wouldOverflow.bottom) {
        finalPlacement = oppositePlacements[finalPlacement];
        calculatedPosition = positions[finalPlacement as keyof typeof positions];
      } else if (finalPlacement.startsWith('left') && wouldOverflow.left) {
        finalPlacement = oppositePlacements[finalPlacement];
        calculatedPosition = positions[finalPlacement as keyof typeof positions];
      } else if (finalPlacement.startsWith('right') && wouldOverflow.right) {
        finalPlacement = oppositePlacements[finalPlacement];
        calculatedPosition = positions[finalPlacement as keyof typeof positions];
      }

      // Fine-tune position to stay within bounds
      if (calculatedPosition.left < containerRect.left) {
        calculatedPosition.left = containerRect.left + POPOVER_BOUNDARY_MARGIN;
      } else if (calculatedPosition.left + popoverWidth > containerRect.left + containerRect.width) {
        calculatedPosition.left = containerRect.left + containerRect.width - popoverWidth - POPOVER_BOUNDARY_MARGIN;
      }

      if (calculatedPosition.top < containerRect.top) {
        calculatedPosition.top = containerRect.top + POPOVER_BOUNDARY_MARGIN;
      } else if (calculatedPosition.top + popoverHeight > containerRect.top + containerRect.height) {
        calculatedPosition.top = containerRect.top + containerRect.height - popoverHeight - POPOVER_BOUNDARY_MARGIN;
      }
    }

    // Calculate arrow position if arrow is enabled
    if (arrow) {
      const isVerticalPlacement = finalPlacement.startsWith('top') || finalPlacement.startsWith('bottom');
      const isHorizontalPlacement = finalPlacement.startsWith('left') || finalPlacement.startsWith('right');

      if (isVerticalPlacement) {
        // Arrow points up/down, positioned horizontally
        const triggerCenter = triggerRect.left + triggerRect.width / 2;
        const popoverLeft = calculatedPosition.left;
        arrowLeft =
          Math.max(ARROW_SAFE_MARGIN, Math.min(popoverWidth - ARROW_MAX_OFFSET_VERTICAL, triggerCenter - popoverLeft)) -
          ARROW_CENTER_OFFSET;
      } else if (isHorizontalPlacement) {
        // Arrow points left/right, positioned vertically
        const triggerCenter = triggerRect.top + triggerRect.height / 2;
        const popoverTop = calculatedPosition.top;
        arrowTop =
          Math.max(
            ARROW_SAFE_MARGIN,
            Math.min(popoverHeight - ARROW_MAX_OFFSET_HORIZONTAL, triggerCenter - popoverTop),
          ) - ARROW_CENTER_OFFSET;
      }
    }

    setPosition({
      ...calculatedPosition,
      placement: finalPlacement,
      arrowLeft,
      arrowTop,
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [triggerRef, popoverRef, isOpen, placement, gap, arrow, autoAdjustOverflow, ...dependencies]);

  return { position, triggerWidth, triggerRef, popoverRef };
};

interface PortalProps {
  isOpen: boolean;
  children: ReactNode;
  onClickOutside?: () => void;
  onEscape?: () => void;
  animationType?: AnimationType;
}

let portalCount = 0;

export const Portal = ({
  isOpen,
  children,
  onClickOutside,
  onEscape,
  animationType = AnimationType.slideDown,
}: PortalProps) => {
  const { hasModalOnStack } = useModal();

  useEffect(() => {
    const handleKeyDown = (event: KeyboardEvent) => {
      if (event.key === 'Escape') {
        onEscape?.();
      }
    };
    if (isOpen) {
      portalCount++;
      document.body.style.overflow = 'hidden';
      document.addEventListener('keydown', handleKeyDown, true);
    }

    return () => {
      if (isOpen) {
        portalCount--;
      }

      if (!hasModalOnStack && portalCount === 0) {
        document.body.style.overflow = 'initial';
      }

      document.removeEventListener('keydown', handleKeyDown, true);
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [isOpen, hasModalOnStack]);

  const { transitions } = useAnimation({
    data: isOpen,
    animationType,
  });

  return transitions((style, openState) => {
    if (openState) {
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
