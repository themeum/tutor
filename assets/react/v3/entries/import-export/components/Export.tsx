import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';

import {
  convertExportFormDataToPayload,
  useExportContentsMutation,
  type ExportFormData,
} from '@ImportExport/services/import-export';
import Button from '@TutorShared/atoms/Button';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import { useModal } from '@TutorShared/components/modals/Modal';
import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import { styleUtils } from '@TutorShared/utils/style-utils';
import ExportModal from './modals/ExportModal';

// @TODO: need to integrate with the API

const Export = () => {
  const { showModal, updateModal, closeModal } = useModal();

  const exportContentsMutation = useExportContentsMutation();

  const handleImport = (data: ExportFormData) => {
    const payload = convertExportFormDataToPayload(data);
    exportContentsMutation.mutate(payload);

    updateModal<typeof ExportModal>('export-modal', {
      currentStep: 'progress',
    });
  };

  return (
    <div css={styles.wrapper}>
      <div css={styles.title}>{__('Export', 'tutor')}</div>

      <div css={styles.export}>
        <div css={styles.exportHeader}>
          <div css={styles.exportTitle}>{__('Export Data', 'tutor')}</div>
          <div css={styles.exportSubtitle}>
            {__('Easily export your courses, lessons, quizzes, user data, and global settings.', 'tutor')}
          </div>
        </div>

        <div>
          <Button
            variant="primary"
            size="small"
            icon={<SVGIcon name="export" width={24} height={24} />}
            onClick={() =>
              showModal({
                id: 'export-modal',
                component: ExportModal,
                props: {
                  onClose: closeModal,
                  currentStep: 'initial',
                  onExport: handleImport,
                },
              })
            }
          >
            {__('Initiate Export', 'tutor')}
          </Button>
        </div>
      </div>
    </div>
  );
};

export default Export;

const styles = {
  wrapper: css`
    ${styleUtils.display.flex('column')}
    gap: ${spacing[12]};
  `,
  title: css`
    ${typography.body()}
    color: ${colorTokens.text.subdued};
  `,
  export: css`
    ${styleUtils.display.flex()}
    justify-content: space-between;
    align-items: center;
    padding: ${spacing[24]};
    border: 1px solid ${colorTokens.stroke.divider};
    border-radius: ${borderRadius[6]};
    background-color: ${colorTokens.background.white};
  `,
  exportHeader: css`
    ${styleUtils.display.flex('column')}
    gap: ${spacing[6]};
  `,
  exportTitle: css`
    ${typography.body('medium')}
    color: ${colorTokens.text.title};
  `,
  exportSubtitle: css`
    ${typography.small('regular')}
    color: ${colorTokens.text.subdued};
  `,
};
