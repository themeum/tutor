import BasicModalWrapper from '@Components/modals/BasicModalWrapper';
import type { ModalProps } from '@Components/modals/Modal';
import Button, { type ButtonVariant } from '@Atoms/Button';
import { colorTokens, fontSize, lineHeight, shadow, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
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
    <BasicModalWrapper onClose={() => closeModal({ action: 'CLOSE' })} title={title}>
      <div css={styles.contentWrapper}>
        <p css={styles.content}>{description ?? __('Once you perform this action this can’t be undone.', 'tutor')}</p>
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
      </div>
    </BasicModalWrapper>
  );
};

export default StaticConfirmationModal;

const styles = {
  contentWrapper: css`
    width: 460px;
  `,
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
