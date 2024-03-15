import Button, { ButtonVariant } from '@Atoms/Button';
import { borderRadius, colorTokens, shadow, spacing, zIndex } from '@Config/styles';
import { typography } from '@Config/typography';
import { css } from '@emotion/react';
import { AnimationType } from '@Hooks/useAnimation';
import { arrowPosition, Portal, usePortalPopover } from '@Hooks/usePortalPopover';
import { styleUtils } from '@Utils/style-utils';
import { __ } from '@wordpress/i18n';
import { ReactNode, RefObject } from 'react';

interface ConfirmationPopoverProps<TRef> {
  triggerRef: RefObject<TRef>;
  isOpen: boolean;
  title: string;
  message: string | ReactNode;
  onConfirmation: () => void;
  isLoading?: boolean;
  arrow?: arrowPosition;
  gap?: number;
  maxWidth?: string;
  closePopover: () => void;
  animationType?: AnimationType;
  hideArrow?: boolean;
  positionModifier?: {
    top: number;
    left: number;
  };
  confirmButton?: {
    text: string;
    variant: ButtonVariant;
    isDelete?: boolean;
  };
  cancelButton?: {
    text: string;
    variant: ButtonVariant;
  };
}

const ConfirmationPopover = <TRef extends HTMLElement>({
  arrow,
  triggerRef,
  isOpen,
  title,
  message,
  onConfirmation,
  isLoading = false,
  gap,
  maxWidth,
  closePopover,
  animationType = AnimationType.slideLeft,
  hideArrow = false,
  confirmButton,
  cancelButton,
  positionModifier,
}: ConfirmationPopoverProps<TRef>) => {
  const { position, triggerWidth, popoverRef } = usePortalPopover<TRef, HTMLDivElement>({
    triggerRef,
    isOpen,
    arrow,
    gap,
    positionModifier,
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
        <div css={styles.content}>
          <div css={styles.body}>
            <div css={styles.title}>{title}</div>
            <p css={styles.description}>{message}</p>
          </div>
          <div css={styles.footer({ isDelete: confirmButton?.isDelete ?? false })}>
            <Button variant={cancelButton?.variant ?? 'text'} size="small" onClick={() => closePopover()}>
              {cancelButton?.text ?? __('Cancel', 'tutor')}
            </Button>
            <Button
              variant={confirmButton?.variant ?? 'text'}
              onClick={() => {
                onConfirmation();
                closePopover();
              }}
              loading={isLoading}
              size="small"
            >
              {confirmButton?.text ?? __('Ok', 'tutor')}
            </Button>
          </div>
        </div>
      </div>
    </Portal>
  );
};

export default ConfirmationPopover;

const styles = {
  wrapper: (arrow: arrowPosition | undefined, hideArrow: boolean) => css`
    position: absolute;
    width: 100%;
    z-index: ${zIndex.dropdown};

    &::before {
      ${arrow &&
      !hideArrow &&
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
  title: css`
    ${typography.small('medium')};
    color: ${colorTokens.text.primary};
  `,
  description: css`
    ${typography.small()};
    color: ${colorTokens.text.subdued};
  `,
  body: css`
    padding: ${spacing[16]} ${spacing[20]} ${spacing[12]};
    ${styleUtils.display.flex('column')};
    gap: ${spacing[8]};
  `,
  footer: ({ isDelete = false }) => css`
    ${styleUtils.display.flex()};
    padding: ${spacing[4]} ${spacing[16]} ${spacing[8]};
    justify-content: end;
    gap: ${spacing[10]};

    ${isDelete &&
    css`
      button:last-of-type {
        color: ${colorTokens.text.error};
      }
    `}
  `,
};
