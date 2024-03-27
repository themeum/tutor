import { borderRadius, colorTokens, shadow, spacing, zIndex } from '@Config/styles';
import { AnimationType } from '@Hooks/useAnimation';
import { Portal, type arrowPosition, usePortalPopover } from '@Hooks/usePortalPopover';
import { css } from '@emotion/react';
import type React from 'react';
import type { RefObject } from 'react';

interface PopoverProps<T> {
  children: React.ReactNode;
  triggerRef: RefObject<T>;
  isOpen: boolean;
  arrow?: arrowPosition;
  gap?: number;
  maxWidth?: string;
  closePopover: () => void;
  animationType?: AnimationType;
  hideArrow?: boolean;
}

const Popover = <T extends HTMLElement>({
  children,
  arrow,
  triggerRef,
  isOpen,
  gap,
  maxWidth,
  closePopover,
  animationType = AnimationType.slideLeft,
  hideArrow,
}: PopoverProps<T>) => {
  const { position, triggerWidth, popoverRef } = usePortalPopover<T, HTMLDivElement>({
    triggerRef,
    isOpen,
    arrow,
    gap,
  });

  return (
    <Portal isOpen={isOpen} onClickOutside={closePopover} animationType={animationType}>
      <div
        css={[
          styles.wrapper(arrow ? position.arrowPlacement : undefined, hideArrow),
          { left: position.left, top: position.top, maxWidth: maxWidth ?? triggerWidth },
        ]}
        ref={popoverRef}
      >
        <div css={styles.content}>{children}</div>
      </div>
    </Portal>
  );
};

const styles = {
  wrapper: (arrow: arrowPosition | undefined, hideArrow: boolean | undefined) => css`
    position: absolute;
    width: 100%;
    z-index: ${zIndex.dropdown};

    &::before {
      ${
        arrow &&
        !hideArrow &&
        css`
        content: '';
        position: absolute;
        border: ${spacing[8]} solid transparent;

        ${arrow === 'left' && styles.arrowLeft}
        ${arrow === 'right' && styles.arrowRight}
        ${arrow === 'top' && styles.arrowTop}
        ${arrow === 'bottom' && styles.arrowBottom}
      `
      }
    }
  `,
  arrowLeft: css`
    border-right-color: ${colorTokens.surface.tutor};
    top: 50%;
    transform: translateY(-50%);
    left: -${spacing[16]};
  `,
  arrowRight: css`
    border-left-color: ${colorTokens.surface.tutor};
    top: 50%;
    transform: translateY(-50%);
    right: -${spacing[16]};
  `,
  arrowTop: css`
    border-bottom-color: ${colorTokens.surface.tutor};
    left: 50%;
    transform: translateX(-50%);
    top: -${spacing[16]};
  `,
  arrowBottom: css`
    border-top-color: ${colorTokens.surface.tutor};
    left: 50%;
    transform: translateX(-50%);
    bottom: -${spacing[16]};
  `,
  content: css`
    background-color: ${colorTokens.surface.tutor};
    box-shadow: ${shadow.popover};
    border-radius: ${borderRadius[6]};

    ::-webkit-scrollbar {
      background-color: ${colorTokens.surface.tutor};
      width: 10px;
    }

    ::-webkit-scrollbar-thumb {
      background-color: ${colorTokens.action.secondary.default};
      border-radius: ${borderRadius[6]};
    }
  `,
};

export default Popover;
