import { borderRadius, colorPalate, shadow, spacing, zIndex } from '@Config/styles';
import { css } from '@emotion/react';
import { AnimationType } from '@Hooks/useAnimation';
import { Portal, usePortalPopover, arrowPosition } from '@Hooks/usePortalPopover';
import React, { RefObject } from 'react';

interface PopoverProps<T> {
  children: React.ReactNode;
  triggerRef: RefObject<T>;
  isOpen: boolean;
  arrow?: arrowPosition;
  gap?: number;
  maxWidth?: string;
  closePopover: () => void;
  animationType?: AnimationType;
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
          styles.wrapper(arrow ? position.arrowPlacement : undefined),
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
  wrapper: (arrow: arrowPosition | undefined) => css`
    position: absolute;
    width: 100%;

    &::before {
      ${arrow &&
      css`
        content: '';
        position: absolute;
        border: ${spacing[8]} solid transparent;

        ${arrow === 'left' && styles.arrowLeft}
        ${arrow === 'right' && styles.arrowRight}
        ${arrow === 'top' && styles.arrowTop}
        ${arrow === 'bottom' && styles.arrowBottom}
      `}
    }
  `,
  arrowLeft: css`
    border-right-color: ${colorPalate.surface.default};
    top: 50%;
    transform: translateY(-50%);
    left: -${spacing[16]};
  `,
  arrowRight: css`
    border-left-color: ${colorPalate.surface.default};
    top: 50%;
    transform: translateY(-50%);
    right: -${spacing[16]};
  `,
  arrowTop: css`
    border-bottom-color: ${colorPalate.surface.default};
    left: 50%;
    transform: translateX(-50%);
    top: -${spacing[16]};
  `,
  arrowBottom: css`
    border-top-color: ${colorPalate.surface.default};
    left: 50%;
    transform: translateX(-50%);
    bottom: -${spacing[16]};
  `,
  content: css`
    z-index: ${zIndex.dropdown};
    background-color: ${colorPalate.surface.default};
    box-shadow: ${shadow.popover};
    border-radius: ${borderRadius[6]};

    ::-webkit-scrollbar {
      background-color: ${colorPalate.basic.white};
      width: 10px;
    }

    ::-webkit-scrollbar-thumb {
      background-color: ${colorPalate.basic.secondary};
      border-radius: ${borderRadius[6]};
    }
  `,
};

export default Popover;
