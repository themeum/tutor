import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useEffect } from 'react';

import Button from '@TutorShared/atoms/Button';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import { useModal } from '@TutorShared/components/modals/Modal';

import ExportModal from '@ImportExport/components/modals/ExportModal';
import {
  convertExportFormDataToPayload,
  useExportContentsMutation,
  type ExportFormData,
} from '@ImportExport/services/import-export';
import { tutorConfig } from '@TutorShared/config/config';
import { borderRadius, colorTokens, spacing, zIndex } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import { type ExportableContent } from '@TutorShared/services/import-export';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { decodeParams } from '@TutorShared/utils/url';
import { convertToErrorMessage, formatBytes } from '@TutorShared/utils/util';

const CONTENT_BANK_PAGE = 'tutor-content-bank';
const isTutorPro = !!tutorConfig.tutor_pro_url;

const Export = () => {
  const { showModal, updateModal, closeModal } = useModal();
  const { data: exportContentResponse, mutateAsync, error, isError } = useExportContentsMutation();

  const handleExport = ({
    data,
    exportableContent,
  }: {
    data: ExportFormData;
    exportableContent: ExportableContent[];
  }) => {
    const payload = convertExportFormDataToPayload({ data, exportableContent });
    mutateAsync(payload);

    updateModal<typeof ExportModal>('export-modal', {
      currentStep: 'progress',
      progress: 0,
      message: __('Export in progress', 'tutor'),
    });
  };

  useEffect(() => {
    const urlParams = new URLSearchParams(window.location.search);
    const encodedData = urlParams.get('referrer');

    if (!encodedData || !isTutorPro) {
      return;
    }

    const decodedParams = decodeParams<{
      referrer: string;
      type: string;
      collection_id?: number;
      collection_title?: string;
      lesson_count?: number;
      assignment_count?: number;
      question_count?: number;
      total?: number;
    }>(encodedData);

    const { referrer, type, collection_id, collection_title, lesson_count, assignment_count, question_count, total } =
      decodedParams;

    const isForContentBank = referrer === CONTENT_BANK_PAGE && type === 'export';
    if (isForContentBank && collection_id) {
      showModal({
        id: 'export-modal',
        component: ExportModal,
        depthIndex: zIndex.highest,
        closeOnEscape: false,
        props: {
          onClose: closeModal,
          currentStep: 'initial',
          onExport: handleExport,
          progress: 0,
          collection: {
            ID: collection_id,
            post_title: collection_title || __('Selected Collection', 'tutor'),
            count_stats: {
              lesson: lesson_count || 0,
              assignment: assignment_count || 0,
              question: question_count || 0,
              total: total || 0,
            },
          },
        },
      });
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  useEffect(() => {
    const handleBeforeUnload = (e: BeforeUnloadEvent) => {
      if (exportContentResponse && exportContentResponse?.job_progress < 100) {
        e.preventDefault();
        return;
      }
    };
    window.addEventListener('beforeunload', handleBeforeUnload);

    return () => {
      window.removeEventListener('beforeunload', handleBeforeUnload);
    };
  }, [exportContentResponse]);

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
        message: exportContentResponse?.message || '',
      });
    }

    if (progress === 100 && exportContentResponse?.exported_data) {
      const url = exportContentResponse?.export_file?.url;
      updateModal<typeof ExportModal>('export-modal', {
        currentStep: 'success',
        progress: 100,
        fileName: isTutorPro ? exportContentResponse?.exported_data : '',
        fileSize: isTutorPro
          ? exportContentResponse?.export_file?.file_size
          : formatBytes(JSON.stringify(exportContentResponse?.exported_data).length),
        message: isTutorPro ? exportContentResponse?.message || '' : __('Settings', 'tutor'),
        completedContents: exportContentResponse?.completed_contents,
        onClose: () => {
          closeModal();
          const newUrl = new URL(url);
          newUrl.searchParams.set('download', 'false'); // this will delete the generated download link and file
          fetch(newUrl);
        },
        onDownload: (fileName: string) => {
          closeModal();

          if (isTutorPro) {
            const url = exportContentResponse?.export_file?.url;
            const a = document.createElement('a');
            a.href = url;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);

            return;
          }

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

    if (progress === 100 && !exportContentResponse?.exported_data) {
      updateModal<typeof ExportModal>('export-modal', {
        currentStep: 'error',
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
            {__('Easily export your courses, lessons, quizzes, assignments, global settings, etc.', 'tutor')}
          </div>
        </div>

        <Button
          variant="primary"
          size="small"
          icon={<SVGIcon name="export" width={24} height={24} />}
          onClick={() =>
            showModal({
              id: 'export-modal',
              component: ExportModal,
              depthIndex: zIndex.highest,
              closeOnEscape: false,
              props: {
                onClose: closeModal,
                currentStep: 'initial',
                onExport: handleExport,
                progress: Number(exportContentResponse?.job_progress) || 0,
              },
            })
          }
        >
          {__('Initiate Export', 'tutor')}
        </Button>
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

    button {
      flex-shrink: 0;
    }
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
