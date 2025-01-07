import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';

import Button from '@TutorShared/atoms/Button';
import BasicModalWrapper from '@TutorShared/components/modals/BasicModalWrapper';

import { colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import { styleUtils } from '@TutorShared/utils/style-utils';

interface LeaveWithoutSavingModalProps {
  closeModal: (props?: { action: 'CONFIRM' | 'CLOSE' }) => void;
  redirectUrl: string;
  message?: string;
}

const LeaveWithoutSavingModal = ({ closeModal, message, redirectUrl }: LeaveWithoutSavingModalProps) => {
  return (
    <BasicModalWrapper
      onClose={() => closeModal({ action: 'CLOSE' })}
      title={__('Do you want to exit without saving?', 'tutor')}
      maxWidth={445}
    >
      <div css={styles.wrapper}>
        <p css={styles.message}>
          {message || __('You have unsaved changes. Are you sure you want to exit without saving?', 'tutor')}
        </p>
        <div css={styles.formFooter}>
          <Button
            onClick={() =>
              closeModal({
                action: 'CLOSE',
              })
            }
            variant="text"
            size="small"
          >
            {__('Continue editing', 'tutor')}
          </Button>
          <Button
            variant="danger"
            size="small"
            onClick={() => {
              closeModal({ action: 'CONFIRM' });
              window.location.href = redirectUrl;
            }}
          >
            {__('Yes, exit without saving', 'tutor')}
          </Button>
        </div>
      </div>
    </BasicModalWrapper>
  );
};

export default LeaveWithoutSavingModal;

const styles = {
  wrapper: css`
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
