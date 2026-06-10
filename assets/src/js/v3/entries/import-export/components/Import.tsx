import { css } from '@emotion/react';
import { __, sprintf } from '@wordpress/i18n';
import { useEffect } from 'react';
import { type ErrorResponse } from 'react-router-dom';

import { useToast } from '@TutorShared/atoms/Toast';
import { UploadButton } from '@TutorShared/molecules/FileUploader';

import ImportModal from '@ImportExport/components/modals/ImportModal';
import { useImportContentsMutation } from '@ImportExport/services/import-export';
import { useModal } from '@TutorShared/components/modals/Modal';
import { tutorConfig } from '@TutorShared/config/config';
import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { convertToErrorMessage } from '@TutorShared/utils/util';

import importInitialImage from '@SharedImages/import-export/import-initial.webp';

const isTutorPro = !!tutorConfig.tutor_pro_url;

const Import = () => {
  const { showModal, updateModal, closeModal } = useModal();
  const { data: importResponse, mutateAsync, isError, error, isPending } = useImportContentsMutation();
  const { showToast } = useToast();

  const onImport = async ({ file, collectionId }: { file: File; collectionId?: number }): Promise<void> => {
    updateModal<typeof ImportModal>('import-modal', {
      currentStep: 'progress',
      progress: 0,
      message: __('Import in progress', 'tutor'),
    });

    try {
      await mutateAsync({
        ...(collectionId ? { collection_id: collectionId } : {}),
        data: file,
      });
    } catch (error) {
      updateModal<typeof ImportModal>('import-modal', {
        currentStep: 'error',
        message: error
          ? convertToErrorMessage(error as ErrorResponse)
          : __('Something went wrong during import. Please try again!', 'tutor'),
      });
      return;
    }
  };

  const handleUpload = async (files: File[]) => {
    const file = files[0];
    // Early return if file is invalid
    if (!file || !(file instanceof File)) {
      return;
    }

    showModal({
      component: ImportModal,
      id: 'import-modal',
      props: {
        files: files,
        currentStep: 'initial',
        onClose: closeModal,
        onImport: onImport,
      },
      closeOnEscape: false,
    });
  };

  useEffect(() => {
    const progress = Number(importResponse?.job_progress);
    if (isError) {
      updateModal<typeof ImportModal>('import-modal', {
        currentStep: 'error',
        message: error
          ? convertToErrorMessage(error)
          : __('Something went wrong during import. Please try again!', 'tutor'),
      });
    }

    if (progress < 100) {
      mutateAsync({
        job_id: importResponse?.job_id,
      });
    }

    if (progress > 0 && progress < 100) {
      updateModal<typeof ImportModal>('import-modal', {
        currentStep: 'progress',
        progress,
        message: importResponse?.message || __('Import in progress...', 'tutor'),
      });
    }

    if (progress === 100) {
      updateModal<typeof ImportModal>('import-modal', {
        currentStep: 'success',
        message: importResponse?.message || '',
        failedMessage: importResponse?.failed_message || '',
        progress: 100,
        onClose: () => {
          closeModal({ action: 'CLOSE' });
        },
        completedContents: importResponse?.completed_contents,
        importErrors: importResponse?.errors,
      });
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [importResponse, isPending, error]);

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

  return (
    <div css={styles.wrapper}>
      <div css={styles.title}>{__('Import', 'tutor')}</div>

      <div css={styles.fileUpload}>
        <img css={styles.emptyStateImage} src={importInitialImage} alt="File Upload" width={100} height={100} />

        <UploadButton
          size="small"
          acceptedTypes={isTutorPro ? ['.json', '.zip'] : ['.json']}
          variant="secondary"
          onError={(errors) => {
            showToast({
              type: 'danger',
              message: errors.join(', '),
            });
          }}
          onUpload={handleUpload}
        >
          {__('Choose a File', 'tutor')}
        </UploadButton>

        <div css={styles.description}>
          {sprintf(
            // translators: %s is the file extension
            __('Supported format: %s', 'tutor'),
            isTutorPro ? '.JSON, .ZIP' : '.JSON',
          )}
        </div>
      </div>
    </div>
  );
};

export default Import;

const styles = {
  wrapper: css`
    ${styleUtils.display.flex('column')}
    gap: ${spacing[12]};
  `,
  title: css`
    ${typography.body()}
    color: ${colorTokens.text.subdued};
  `,
  fileUpload: css`
    ${styleUtils.display.flex('column')}
    align-items: center;
    gap: ${spacing[8]};
    padding: ${spacing[16]} ${spacing[24]};
    padding-block: ${spacing[48]};
    background-color: ${colorTokens.background.white};
    position: relative;
    border-radius: ${borderRadius.card};

    ::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      border-radius: ${borderRadius.card};
      background-image:
        linear-gradient(to right, ${colorTokens.stroke.border} 50%, rgba(255, 255, 255, 0) 0%),
        linear-gradient(${colorTokens.stroke.border} 50%, rgba(255, 255, 255, 0) 0%),
        linear-gradient(to right, ${colorTokens.stroke.border} 50%, rgba(255, 255, 255, 0) 0%),
        linear-gradient(${colorTokens.stroke.border} 50%, rgba(255, 255, 255, 0) 0%);
      background-size:
        10px 1px,
        1px 10px;
      background-position: top, right, bottom, left;
      background-repeat: repeat-x, repeat-y;
    }
  `,
  emptyStateImage: css`
    width: 52px;
    height: auto;
    ${styleUtils.objectFit()}
    margin-bottom: ${spacing[20]};
  `,
  description: css`
    ${typography.tiny()}
    color: ${colorTokens.text.subdued};
  `,
};
