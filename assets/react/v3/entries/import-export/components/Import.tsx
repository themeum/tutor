import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';

import { useModal } from '@TutorShared/components/modals/Modal';
import { UploadButton } from '@TutorShared/molecules/FileUploader';

import ImportModal from '@ImportExport/components/modals/ImportModal';
import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { noop } from '@TutorShared/utils/util';

import importInitialImage from '@SharedImages/import-export/import-initial.webp';

const Import = () => {
  const { showModal, updateModal, closeModal } = useModal();

  const onImport = () => {
    updateModal<typeof ImportModal>('import-modal', {
      currentStep: 'error',
    });
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

  return (
    <div css={styles.wrapper}>
      <div css={styles.title}>{__('Import', 'tutor')}</div>

      <div css={styles.fileUpload}>
        <img css={styles.emptyStateImage} src={importInitialImage} alt="File Upload" width={100} height={100} />

        <UploadButton
          size="small"
          acceptedTypes={['.csv', '.json']}
          variant="secondary"
          onError={noop}
          onUpload={handleUpload}
        >
          {__('Choose a file', 'tutor')}
        </UploadButton>

        <div css={styles.description}>{__('Supported format: .CSV, .JSON', 'tutor')}</div>
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
