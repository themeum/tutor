import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';

import { useModal } from '@TutorShared/components/modals/Modal';
import { UploadButton } from '@TutorShared/molecules/FileUploader';

import ImportModal from '@ImportExport/components/modals/ImportModal';
import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { noop } from '@TutorShared/utils/util';

import { useImportContentsMutation } from '@ImportExport/services/import-export';
import importInitialImage from '@SharedImages/import-export/import-initial.webp';
import { useEffect } from 'react';

// @TODO: need to integrate with the API
const readJsonFile = (file: File): Promise<any> => {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();

    reader.onload = (event) => {
      try {
        const content = event.target?.result as string;
        const jsonData = JSON.parse(content);
        resolve(jsonData);
      } catch (error) {
        reject(new Error(__('Invalid JSON file format', 'tutor')));
      }
    };

    reader.onerror = () => {
      reject(new Error(__('Failed to read file', 'tutor')));
    };

    reader.readAsText(file);
  });
};

const Import = () => {
  const { showModal, updateModal, closeModal } = useModal();
  const { data: importResponse, mutateAsync, isPending, isError } = useImportContentsMutation();

  const onImport = async (file: File): Promise<void> => {
    // Early return if file is invalid
    if (!file || !(file instanceof File)) {
      return;
    }
    const jsonData = await readJsonFile(file);

    try {
      await mutateAsync({
        data: jsonData,
      });
    } catch {
      updateModal<typeof ImportModal>('import-modal', {
        currentStep: 'error',
      });
      return;
    }
  };

  const handleUpload = (files: File[]) => {
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

  // useEffect(() => {

  // }, [importResponse]);

  return (
    <div css={styles.wrapper}>
      <div css={styles.title}>{__('Import', 'tutor')}</div>

      <div css={styles.fileUpload}>
        <img css={styles.emptyStateImage} src={importInitialImage} alt="File Upload" width={100} height={100} />

        <UploadButton size="small" acceptedTypes={['.json']} variant="secondary" onError={noop} onUpload={handleUpload}>
          {__('Choose a file', 'tutor')}
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
