import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';

import Button, { type ButtonVariant } from '@TutorShared/atoms/Button';
import BasicModalWrapper from '@TutorShared/components/modals/BasicModalWrapper';
import type { ModalProps } from '@TutorShared/components/modals/Modal';
import { colorTokens, fontSize, lineHeight, shadow, spacing } from '@TutorShared/config/styles';

interface StaticConfirmationModalProps extends ModalProps {
  description?: React.ReactNode | string;
  confirmButtonText?: string;
  cancelButtonText?: string;
  confirmButtonVariant?: ButtonVariant;
  closeModal: (props?: { action: 'CONFIRM' | 'CLOSE' }) => void;
}

const StaticConfirmationModal = ({
  closeModal,
  title,
  description,
  confirmButtonText,
  cancelButtonText,
  confirmButtonVariant,
}: StaticConfirmationModalProps) => {
  return (
    <BasicModalWrapper onClose={() => closeModal({ action: 'CLOSE' })} title={title} maxWidth={460}>
      <div css={styles.content}>{description ?? __('Once you perform this action this can’t be undone.', 'tutor')}</div>
      <div css={styles.footerWrapper}>
        <Button variant="text" onClick={() => closeModal({ action: 'CLOSE' })} size="small">
          {cancelButtonText ?? __('Cancel', 'tutor')}
        </Button>
        <Button
          variant={confirmButtonVariant ?? 'danger'}
          size="small"
          onClick={() => {
            closeModal({ action: 'CONFIRM' });
          }}
        >
          {confirmButtonText ?? __('Delete', 'tutor')}
        </Button>
      </div>
    </BasicModalWrapper>
  );
};

export default StaticConfirmationModal;

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
