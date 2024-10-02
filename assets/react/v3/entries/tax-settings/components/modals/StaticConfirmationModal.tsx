import type { ModalProps } from '@/v3/shared/components/modals/Modal';
import ModalWrapper from '@/v3/shared/components/modals/ModalWrapper';
import Button, { type ButtonVariant } from '@Atoms/Button';
import { shadow, spacing } from '@Config/styles';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';

interface StaticConfirmationModalProps extends ModalProps {
  description?: string;
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
    <ModalWrapper onClose={() => closeModal({ action: 'CLOSE' })} title={title}>
      <div css={styles.contentWrapper}>
        <p css={styles.content}>{description ?? __('Once you perform this action this can’t be undone.', 'tutor')}</p>
        <div css={styles.footerWrapper}>
          <Button variant="secondary" onClick={() => closeModal({ action: 'CLOSE' })} size="small">
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
      </div>
    </ModalWrapper>
  );
};

export default StaticConfirmationModal;

const styles = {
  contentWrapper: css`
    width: 620px;
  `,
  content: css`
    padding: ${spacing[20]};
  `,
  footerWrapper: css`
    display: flex;
    justify-content: end;
    gap: ${spacing[8]};
    padding: ${spacing[16]};
    box-shadow: ${shadow.dividerTop};
  `,
};
