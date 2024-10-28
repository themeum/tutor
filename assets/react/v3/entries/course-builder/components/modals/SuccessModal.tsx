import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';

import Button from '@Atoms/Button';
import BasicModalWrapper from '@Components/modals/BasicModalWrapper';

import { typography } from '@/v3/shared/config/typography';
import { colorTokens, spacing } from '@Config/styles';
import { styleUtils } from '@Utils/style-utils';

interface SuccessModalProps {
  title: string;
  description?: string;
  closeModal: (props?: { action: 'CONFIRM' | 'CLOSE' }) => void;
  confirmAction?: () => void;
}

const SuccessModal = ({ title, description, closeModal }: SuccessModalProps) => {
  return (
    <BasicModalWrapper onClose={() => closeModal({ action: 'CLOSE' })} title={title}>
      <div css={styles.wrapper}>
        <p css={styles.message}>{description}</p>
        <div css={styles.formFooter}>
          <Button
            onClick={() =>
              closeModal({
                action: 'CLOSE',
              })
            }
            variant="secondary"
            size="small"
          >
            {__('Ok', 'tutor')}
          </Button>
        </div>
      </div>
    </BasicModalWrapper>
  );
};

export default SuccessModal;

const styles = {
  wrapper: css`
    width: 445px;
    ${styleUtils.display.flex('column')};
  `,
  message: css`
    ${typography.caption()};
    color: ${colorTokens.text.subdued};
    padding: ${spacing[20]};
  `,
  formWrapper: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[20]};
    padding: ${spacing[16]} ${spacing[16]} 0 ${spacing[16]};
  `,
  formFooter: css`
    ${styleUtils.display.flex()};
    justify-content: flex-end;
    gap: ${spacing[16]};
    border-top: 1px solid ${colorTokens.stroke.divider};
    padding: ${spacing[12]} ${spacing[16]};
  `,
};
