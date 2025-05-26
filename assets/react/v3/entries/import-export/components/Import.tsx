import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useEffect } from 'react';

import { useModal } from '@TutorShared/components/modals/Modal';
import { UploadButton } from '@TutorShared/molecules/FileUploader';

import ImportModal from '@ImportExport/components/modals/ImportModal';
import { useImportContentsMutation } from '@ImportExport/services/import-export';
import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { convertToErrorMessage, noop } from '@TutorShared/utils/util';

import generateImportExportMessage from '@ImportExport/utils/utils';
import importInitialImage from '@SharedImages/import-export/import-initial.webp';
import { tutorConfig } from '@TutorShared/config/config';
import { useQueryClient } from '@tanstack/react-query';

const isTutorPro = !!tutorConfig.tutor_pro_url;

const Import = () => {
  const { showModal, updateModal, closeModal } = useModal();
  const queryClient = useQueryClient();
  const { data: importResponse, mutateAsync, isError, error, isPending } = useImportContentsMutation();

  const onImport = async (data: string): Promise<void> => {
    updateModal<typeof ImportModal>('import-modal', {
      currentStep: 'progress',
      progress: 0,
      message: __('Import in progress', 'tutor'),
    });

    try {
      await mutateAsync({
        data: data,
      });
    } catch {
      updateModal<typeof ImportModal>('import-modal', {
        currentStep: 'error',
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
    });
  };

  useEffect(() => {
    const progress = Number(importResponse?.job_progress);
    if (isError) {
      updateModal<typeof ImportModal>('import-modal', {
        currentStep: 'error',
        message: convertToErrorMessage(error),
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
        message: generateImportExportMessage(importResponse, 'import'),
      });
    }

    if (progress === 100) {
      updateModal<typeof ImportModal>('import-modal', {
        currentStep: 'success',
        progress: 100,
        onClose: () => {
          closeModal({ action: 'CLOSE' });
          if (!isTutorPro) {
            window.location.reload();
          }
        },
        completedContents: importResponse?.completed_contents,
        failedCourseIds: importResponse?.failed_course_ids,
        failedBundleIds: importResponse?.failed_bundle_ids,
      });
      queryClient.invalidateQueries({
        queryKey: ['ImportContents'],
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

        <UploadButton size="small" acceptedTypes={['.json']} variant="secondary" onError={noop} onUpload={handleUpload}>
          {__('Choose a File', 'tutor')}
        </UploadButton>

        <div css={styles.description}>{__('Supported format: .JSON', 'tutor')}</div>
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
