import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';

import BasicModalWrapper from '@TutorShared/components/modals/BasicModalWrapper';
import { type ModalProps } from '@TutorShared/components/modals/Modal';

import {
  type ImportExportContentResponseBase,
  type ImportExportModalState,
} from '@ImportExport/services/import-export';
import { colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';

import ImportExportCompletedState from './import-export-states/ImportExportCompletedState';
import ImportExportProgressState from './import-export-states/ImportExportProgressState';
import ImportInitialState from './import-export-states/ImportInitialState';

interface ImportModalProps extends Omit<ModalProps, 'title' | 'actions' | 'icon' | 'subtitle'> {
  files: File[];
  currentStep: ImportExportModalState;
  onClose: () => void;
  onImport: ({
    file,
    collectionId,
  }: {
    file: File;
    collectionId?: number; // Optional for content bank import
  }) => void;
  progress?: number;
  message?: string;
  completedContents?: ImportExportContentResponseBase['completed_contents'];
}

const ImportModal = ({
  files,
  currentStep,
  onClose,
  onImport,
  message,
  progress,
  completedContents,
}: ImportModalProps) => {
  const renderCompletedState = (file: File, state: ImportExportModalState) => {
    return (
      <ImportExportCompletedState
        onClose={onClose}
        state={state}
        fileSize={file.size}
        completedContents={completedContents}
        type="import"
        importFileName={file.name}
        message={message || ''}
      />
    );
  };

  const modalContent = {
    initial: <ImportInitialState files={files} currentStep={currentStep} onClose={onClose} onImport={onImport} />,
    progress: <ImportExportProgressState progress={progress || 0} message={message || files[0].name} type="import" />,
    success: renderCompletedState(files[0], 'success'),
    error: renderCompletedState(files[0], 'error'),
  };

  return (
    <BasicModalWrapper
      onClose={onClose}
      entireHeader={currentStep === 'initial' ? undefined : <>&nbsp;</>}
      maxWidth={500}
      title={currentStep === 'initial' ? __('Import File', 'tutor') : undefined}
      isCloseAble={currentStep !== 'progress'}
    >
      <div css={styles.wrapper}>{modalContent[currentStep]}</div>
    </BasicModalWrapper>
  );
};

export default ImportModal;

const styles = {
  wrapper: css`
    max-height: 840px;
    transition: max-height 0.3s ease-in-out;
  `,
  title: css`
    ${typography.heading6('medium')};
    text-align: center;
    margin-top: ${spacing[16]};
  `,
  subtitle: css`
    ${typography.caption()};
    color: ${colorTokens.text.subdued};
    text-align: center;
    margin-bottom: ${spacing[8]};
  `,
};
