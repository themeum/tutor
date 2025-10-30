import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';

import Button, { type ButtonVariant } from '@TutorShared/atoms/Button';
import BasicModalWrapper from '@TutorShared/components/modals/BasicModalWrapper';
import type { ModalProps } from '@TutorShared/components/modals/Modal';
import { colorTokens, fontSize, lineHeight, shadow, spacing } from '@TutorShared/config/styles';

interface ConfirmationModalProps extends Omit<ModalProps, 'actions'> {
  description?: React.ReactNode | string;
  confirmButtonText?: string;
  cancelButtonText?: string;
  confirmButtonVariant?: ButtonVariant;
  closeModal: (props?: { action: 'CONFIRM' | 'CLOSE' }) => void;
  onConfirm?: () => void;
  isLoading?: boolean;
  maxWidth?: number;
}

const ConfirmationModal = ({
  title,
  description,
  confirmButtonText,
  cancelButtonText,
  confirmButtonVariant,
  closeModal,
  onConfirm,
  isLoading = false,
  icon,
  maxWidth = 460,
}: ConfirmationModalProps) => {
  return (
    <BasicModalWrapper icon={icon} onClose={() => closeModal({ action: 'CLOSE' })} title={title} maxWidth={maxWidth}>
      <div css={styles.content}>
        {description ?? __('Once you perform this action this can’t be undone.', __TUTOR_TEXT_DOMAIN__)}
      </div>
      <div css={styles.footerWrapper}>
        <Button variant="text" onClick={() => closeModal({ action: 'CLOSE' })} size="small">
          {cancelButtonText ?? __('Cancel', __TUTOR_TEXT_DOMAIN__)}
        </Button>
        <Button
          variant={confirmButtonVariant ?? 'danger'}
          size="small"
          loading={isLoading}
          onClick={() => {
            if (onConfirm) {
              onConfirm();
            } else {
              closeModal({ action: 'CONFIRM' });
            }
          }}
        >
          {confirmButtonText ?? __('Delete', __TUTOR_TEXT_DOMAIN__)}
        </Button>
      </div>
    </BasicModalWrapper>
  );
};

export default ConfirmationModal;

const styles = {
  content: css`
    font-size: ${fontSize[14]};
    line-height: ${lineHeight[20]};
    color: ${colorTokens.text.subdued};
    padding: ${spacing[20]};
  `,
  footerWrapper: css`
    display: flex;
    justify-content: end;
    gap: ${spacing[8]};
    padding: ${spacing[12]} ${spacing[16]};
    box-shadow: ${shadow.dividerTop};
  `,
};
