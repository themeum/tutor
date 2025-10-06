import { css } from '@emotion/react';
import type React from 'react';
import type { RefObject } from 'react';

import { isRTL } from '@TutorShared/config/constants';
import { borderRadius, colorTokens, shadow, zIndex } from '@TutorShared/config/styles';
import { AnimationType } from '@TutorShared/hooks/useAnimation';
import {
  getMirroredPlacement,
  POPOVER_PLACEMENTS,
  Portal,
  usePortalPopover,
  type PopoverPlacement,
} from '@TutorShared/hooks/usePortalPopover';

interface PopoverProps<T> {
  children: React.ReactNode;
  triggerRef: RefObject<T>;
  isOpen: boolean;
  placement?: PopoverPlacement;
  gap?: number;
  maxWidth?: string;
  closePopover: () => void;
  closeOnEscape?: boolean;
  animationType?: AnimationType;
  arrow?: boolean;
  autoAdjustOverflow?: boolean;
  positionModifier?: {
    top: number;
    left: number;
  };
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  dependencies?: any[];
}

const Popover = <T extends HTMLElement>({
  children,
  placement = POPOVER_PLACEMENTS.BOTTOM,
  triggerRef,
  isOpen,
  gap,
  maxWidth,
  closePopover,
  closeOnEscape = true,
  animationType = AnimationType.slideLeft,
  arrow = false,
  autoAdjustOverflow = true,
  positionModifier = {
    top: 0,
    left: 0,
  },
  dependencies = [],
}: PopoverProps<T>) => {
  const { position, triggerWidth, popoverRef } = usePortalPopover<T, HTMLDivElement>({
    triggerRef,
    isOpen,
    autoAdjustOverflow,
    placement,
    arrow,
    gap,
    positionModifier,
    dependencies,
  });

  return (
    <Portal
      isOpen={isOpen}
      onClickOutside={closePopover}
      animationType={animationType}
      onEscape={closeOnEscape ? closePopover : undefined}
    >
      <div
        css={styles.wrapper({
          placement: isRTL ? getMirroredPlacement(position.placement) : position.placement,
          hideArrow: !arrow || (position.arrowLeft === undefined && position.arrowTop === undefined),
          arrowLeft: position.arrowLeft,
          arrowTop: position.arrowTop,
        })}
        style={{
          left: position.left,
          top: position.top,
          maxWidth: maxWidth ?? triggerWidth,
        }}
        ref={popoverRef}
      >
        <div css={styles.content}>{children}</div>
      </div>
    </Portal>
  );
};

const styles = {
  wrapper: ({
    placement,
    hideArrow,
    arrowLeft,
    arrowTop,
  }: {
    placement: PopoverPlacement | undefined;
    hideArrow: boolean | undefined;
    arrowLeft?: number;
    arrowTop?: number;
  }) => css`
    position: absolute;
    width: 100%;
    z-index: ${zIndex.dropdown};

    &::before {
      ${placement && !hideArrow
        ? css`
            content: '';
            position: absolute;
            width: 0;
            height: 0;
            border-color: transparent;
            border-style: solid;
            ${placement.startsWith('top') &&
            css`
              border-left: 8px solid transparent;
              border-right: 8px solid transparent;
              border-top: 8px solid ${colorTokens.stroke.white};
              border-bottom: none;
              left: ${arrowLeft !== undefined ? `${arrowLeft}px` : '50%'};
              bottom: -8px;
              transform: ${arrowLeft === undefined ? 'translateX(-50%)' : 'none'};
            `}
            ${placement.startsWith('bottom') &&
            css`
              border-left: 8px solid transparent;
              border-right: 8px solid transparent;
              border-bottom: 8px solid ${colorTokens.stroke.white};
              border-top: none;
              left: ${arrowLeft !== undefined ? `${arrowLeft}px` : '50%'};
              top: -8px;
              transform: ${arrowLeft === undefined ? 'translateX(-50%)' : 'none'};
            `}
            ${placement.startsWith('left') &&
            css`
              border-top: 8px solid transparent;
              border-bottom: 8px solid transparent;
              border-left: 8px solid ${colorTokens.stroke.white};
              border-right: none;
              right: -8px;
              top: ${arrowTop !== undefined ? `${arrowTop}px` : '50%'};
              transform: ${arrowTop === undefined ? 'translateY(-50%)' : 'none'};
            `}
            ${placement.startsWith('right') &&
            css`
              border-top: 8px solid transparent;
              border-bottom: 8px solid transparent;
              border-right: 8px solid ${colorTokens.stroke.white};
              border-left: none;
              left: -8px;
              top: ${arrowTop !== undefined ? `${arrowTop}px` : '50%'};
              transform: ${arrowTop === undefined ? 'translateY(-50%)' : 'none'};
            `}
          `
        : ''}
    }
  `,
  content: css`
    background-color: ${colorTokens.background.white};
    box-shadow: ${shadow.popover};
    border-radius: ${borderRadius[6]};
    ::-webkit-scrollbar {
      background-color: ${colorTokens.background.white};
      width: 10px;
    }
    ::-webkit-scrollbar-thumb {
      background-color: ${colorTokens.action.secondary.default};
      border-radius: ${borderRadius[6]};
    }
  `,
};

export default Popover;
