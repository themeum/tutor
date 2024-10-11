import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';

import Button from '@Atoms/Button';
import BasicModalWrapper from '@Components/modals/BasicModalWrapper';

import { typography } from '@/v3/shared/config/typography';
import { tutorConfig } from '@Config/config';
import { colorTokens, spacing } from '@Config/styles';
import { styleUtils } from '@Utils/style-utils';

interface ExitCourseBuilderModalProps {
  closeModal: (props?: { action: 'CONFIRM' | 'CLOSE' }) => void;
}

const ExitCourseBuilderModal = ({ closeModal }: ExitCourseBuilderModalProps) => {
  return (
    <BasicModalWrapper
      onClose={() => closeModal({ action: 'CLOSE' })}
      title={__('Do you want to exit without saving?', 'tutor')}
    >
      <div css={styles.wrapper}>
        <p css={styles.message}>
          {__('You are about to exit the course creation without saving any changes.', 'tutor')}
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
            {__('Continue Editing', 'tutor')}
          </Button>
          <Button
            size="small"
            onClick={() => {
              const isFormWpAdmin = window.location.href.includes('wp-admin');

              window.location.href = isFormWpAdmin
                ? tutorConfig.backend_course_list_url
                : tutorConfig.frontend_course_list_url;
            }}
          >
            {__('Yes, Exit without saving', 'tutor')}
          </Button>
        </div>
      </div>
    </BasicModalWrapper>
  );
};

export default ExitCourseBuilderModal;

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
