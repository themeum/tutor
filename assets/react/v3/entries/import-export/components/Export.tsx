import { css } from '@emotion/react';
import { __, sprintf } from '@wordpress/i18n';
import { useEffect } from 'react';

import {
  convertExportFormDataToPayload,
  useExportContentsMutation,
  type ExportContentResponse,
  type ExportFormData,
} from '@ImportExport/services/import-export';
import Button from '@TutorShared/atoms/Button';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import { useModal } from '@TutorShared/components/modals/Modal';
import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { convertToErrorMessage } from '@TutorShared/utils/util';
import ExportModal from './modals/ExportModal';

const Export = () => {
  const { showModal, updateModal, closeModal } = useModal();

  const { data: exportContentResponse, mutateAsync, error, isPending, isError } = useExportContentsMutation();

  const handleImport = (data: ExportFormData) => {
    const payload = convertExportFormDataToPayload(data);
    mutateAsync(payload);

    updateModal<typeof ExportModal>('export-modal', {
      currentStep: 'progress',
      progress: 0,
    });
  };

  const generateExportMessage = (exportStatus: ExportContentResponse | undefined): string => {
    // Early return for missing data
    if (!exportStatus) {
      return __('Export in progress...', 'tutor');
    }

    const { completed_contents: completedContents, failed_course_ids = [], failed_bundle_ids = [] } = exportStatus;

    // Handle case with only failures
    if (!completedContents || Object.keys(completedContents).length === 0) {
      if (failed_course_ids.length || failed_bundle_ids.length) {
        const failedMessages = [];

        if (failed_course_ids.length) {
          failedMessages.push(
            failed_course_ids.length === 1
              ? sprintf(__('%d Course failed', 'tutor'), failed_course_ids.length)
              : sprintf(__('%d Courses failed', 'tutor'), failed_course_ids.length),
          );
        }

        if (failed_bundle_ids.length) {
          failedMessages.push(
            failed_bundle_ids.length === 1
              ? sprintf(__('%d Bundle failed', 'tutor'), failed_bundle_ids.length)
              : sprintf(__('%d Bundles failed', 'tutor'), failed_bundle_ids.length),
          );
        }

        return failedMessages.join(', ');
      }

      return __('Export in progress...', 'tutor');
    }

    const { courses, 'course-bundle': bundles, settings } = completedContents;
    const successItems = [];
    const failedItems = [];

    // Handle successful exports
    if (courses?.length) {
      successItems.push(
        courses.length === 1
          ? sprintf(__('%d Course', 'tutor'), courses.length)
          : sprintf(__('%d Courses', 'tutor'), courses.length),
      );
    }

    if (bundles?.length) {
      successItems.push(
        bundles.length === 1
          ? sprintf(__('%d Bundle', 'tutor'), bundles.length)
          : sprintf(__('%d Bundles', 'tutor'), bundles.length),
      );
    }

    if (settings) {
      successItems.push(__('Settings', 'tutor'));
    }

    // Handle failed exports
    if (failed_course_ids.length) {
      failedItems.push(
        failed_course_ids.length === 1
          ? sprintf(__('%d Course failed', 'tutor'), failed_course_ids.length)
          : sprintf(__('%d Courses failed', 'tutor'), failed_course_ids.length),
      );
    }

    if (failed_bundle_ids.length) {
      failedItems.push(
        failed_bundle_ids.length === 1
          ? sprintf(__('%d Bundle failed', 'tutor'), failed_bundle_ids.length)
          : sprintf(__('%d Bundles failed', 'tutor'), failed_bundle_ids.length),
      );
    }

    // Early return if nothing to report
    if (successItems.length === 0 && failedItems.length === 0) {
      return __('Export in progress...', 'tutor');
    }

    let message = '';

    if (successItems.length > 0) {
      message = `${successItems.join(', ')} ${__('Exported', 'tutor')}`;
    }

    if (failedItems.length > 0) {
      if (message) {
        message += `. ${failedItems.join(', ')}.`;
      } else {
        message = `${failedItems.join(', ')}.`;
      }
    }

    return message;
  };

  useEffect(() => {
    const handleBeforeUnload = (e: BeforeUnloadEvent) => {
      if (isPending) {
        e.preventDefault();
        return;
      }
    };
    window.addEventListener('beforeunload', handleBeforeUnload);

    return () => {
      window.removeEventListener('beforeunload', handleBeforeUnload);
    };
  }, [isPending]);

  useEffect(() => {
    const progress = Number(exportContentResponse?.job_progress);
    if (isError) {
      updateModal<typeof ExportModal>('export-modal', {
        currentStep: 'error',
        progress: 0,
        message: convertToErrorMessage(error),
      });
    }

    if (progress < 100) {
      mutateAsync({
        job_id: exportContentResponse?.job_id,
      });
    }

    if (progress > 0 && progress < 100) {
      updateModal<typeof ExportModal>('export-modal', {
        currentStep: 'progress',
        progress,
        message: generateExportMessage(exportContentResponse),
      });
    }

    if (progress === 100) {
      updateModal<typeof ExportModal>('export-modal', {
        currentStep: 'success',
        progress: 100,
        fileSize: JSON.stringify(exportContentResponse?.exported_data).length,
        onDownload: (fileName) => {
          const jsonFile = new Blob([JSON.stringify(exportContentResponse?.exported_data)], {
            type: 'application/json',
          });
          const url = URL.createObjectURL(jsonFile);
          const a = document.createElement('a');
          a.href = url;
          a.download = fileName;
          document.body.appendChild(a);
          a.click();
          document.body.removeChild(a);
          URL.revokeObjectURL(url);
        },
      });
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [exportContentResponse, error, isError]);

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
                  progress: Number(exportContentResponse?.job_progress) || 0,
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
    gap: ${spacing[8]};
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
