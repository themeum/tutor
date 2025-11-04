import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import type { ReactNode, RefObject } from 'react';

import Button, { type ButtonVariant } from '@TutorShared/atoms/Button';

import { borderRadius, colorTokens, shadow, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import { AnimationType } from '@TutorShared/hooks/useAnimation';
import { type PopoverPlacement } from '@TutorShared/hooks/usePortalPopover';
import { styleUtils } from '@TutorShared/utils/style-utils';

import Popover from './Popover';

interface ConfirmationPopoverProps<TRef> {
  triggerRef: RefObject<TRef>;
  isOpen: boolean;
  title: string;
  message: string | ReactNode;
  onConfirmation: () => void;
  onCancel?: () => void;
  isLoading?: boolean;
  placement?: PopoverPlacement;
  gap?: number;
  maxWidth?: string;
  closePopover: () => void;
  animationType?: AnimationType;
  arrow?: boolean;
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
  placement,
  triggerRef,
  isOpen,
  title,
  message,
  onConfirmation,
  onCancel,
  isLoading = false,
  gap,
  maxWidth,
  closePopover,
  animationType = AnimationType.slideLeft,
  arrow = false,
  confirmButton,
  cancelButton,
  positionModifier,
}: ConfirmationPopoverProps<TRef>) => {
  return (
    <Popover
      triggerRef={triggerRef}
      isOpen={isOpen}
      arrow={arrow}
      placement={placement}
      closePopover={closePopover}
      animationType={animationType}
      maxWidth={maxWidth}
      positionModifier={positionModifier}
      gap={gap}
    >
      <div css={styles.content}>
        <div css={styles.body}>
          <div css={styles.title}>{title}</div>
          <p css={styles.description}>{message}</p>
        </div>
        <div css={styles.footer({ isDelete: confirmButton?.isDelete ?? false })}>
          <Button variant={cancelButton?.variant ?? 'text'} size="small" onClick={onCancel ?? closePopover}>
            {cancelButton?.text ?? __('Cancel', __TUTOR_TEXT_DOMAIN__)}
          </Button>
          <Button
            data-cy="confirm-button"
            variant={confirmButton?.variant ?? 'text'}
            onClick={() => {
              onConfirmation();
              closePopover();
            }}
            loading={isLoading}
            size="small"
          >
            {confirmButton?.text ?? __('Ok', __TUTOR_TEXT_DOMAIN__)}
          </Button>
        </div>
      </div>
    </Popover>
  );
};

export default ConfirmationPopover;

const styles = {
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
