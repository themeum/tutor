import { css } from '@emotion/react';
import { __, sprintf } from '@wordpress/i18n';
import { useEffect, useState } from 'react';

import Button from '@TutorShared/atoms/Button';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import { useToast } from '@TutorShared/atoms/Toast';
import { UploadButton } from '@TutorShared/molecules/FileUploader';

import BasicModalWrapper from '@TutorShared/components/modals/BasicModalWrapper';
import { type ModalProps } from '@TutorShared/components/modals/Modal';

import { type ImportExportModalState } from '@ImportExport/services/import-export';
import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import Show from '@TutorShared/controls/Show';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { formatBytes } from '@TutorShared/utils/util';

import importErrorImage from '@SharedImages/import-export/import-error.webp';
import importInProgressImage from '@SharedImages/import-export/import-inprogress.webp';
import importSuccessImage from '@SharedImages/import-export/import-success.webp';

interface ImportModalProps extends Omit<ModalProps, 'title' | 'actions' | 'icon' | 'subtitle'> {
  files: File[];
  currentStep: ImportExportModalState;
  onClose: () => void;
  onImport: (data: string) => Promise<void>;
  progress?: number;
  message?: string;
}

// eslint-disable-next-line @typescript-eslint/no-explicit-any
const readJsonFile = (file: File): Promise<any> => {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();

    reader.onload = (event) => {
      try {
        const content = event.target?.result as string;
        const jsonData = JSON.parse(content);
        resolve(jsonData);
      } catch {
        reject(new Error(__('Invalid JSON file format', 'tutor')));
      }
    };

    reader.onerror = () => {
      reject(new Error(__('Failed to read file', 'tutor')));
    };

    reader.readAsText(file);
  });
};

const ImportModal = ({ files: propsFiles, currentStep, onClose, onImport, message, progress }: ImportModalProps) => {
  const [files, setFiles] = useState<File[]>(propsFiles);
  const [isReadingFile, setIsReadingFile] = useState(false);
  const [hasSettings, setHasSettings] = useState(false);
  const { showToast } = useToast();

  useEffect(() => {
    if (propsFiles.length === 0) {
      return;
    }
    setIsReadingFile(true);
    readJsonFile(files[0]).then((data) => {
      const hasSettings =
        data?.data.filter((item: { content_type: string }) => item.content_type === 'settings').length > 0;

      setIsReadingFile(false);
      setHasSettings(hasSettings);
      setFiles(files);
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [files]);

  const handleUpload = (uploadedFiles: File[]) => {
    if (uploadedFiles.length) {
      setFiles(uploadedFiles);
    }
  };

  const handleUploadError = (errorMessages: string[]) => {
    showToast({
      message: errorMessages.join(', '),
      type: 'danger',
    });
  };

  const renderHeader = {
    initial: __('Import File', 'tutor'),
    progress: __('Importing...', 'tutor'),
    success: __('Imported Successfully!', 'tutor'),
    error: __('Import Failed!', 'tutor'),
  };

  const renderInitialState = (file: File) => {
    return (
      <>
        <div css={styles.selectedInfo}>
          <div css={styles.fileInfo}>
            <div css={styles.progressHeader}>
              <div css={typography.small()}>
                {isReadingFile ? __('Reading file...', 'tutor') : __('Selected', 'tutor')}
              </div>

              <div css={styles.progressCount}>
                {isReadingFile ? __('Please wait...', 'tutor') : __('Ready to import', 'tutor')}
              </div>
            </div>

            <div css={styles.file}>
              <div css={styles.fileIcon}>
                <SVGIcon name="attachmentLine" width={24} height={24} />
              </div>
              <div css={styles.fileRight}>
                <div css={styles.fileDetails}>
                  <div css={styles.fileName}>{file.name}</div>
                  <div css={styles.fileSize}>{formatBytes(file.size)}</div>
                </div>

                <div>
                  <UploadButton
                    data-cy="replace-file"
                    variant="tertiary"
                    size="small"
                    onUpload={handleUpload}
                    onError={handleUploadError}
                    acceptedTypes={['.csv', '.json']}
                  >
                    {__('Replace', 'tutor')}
                  </UploadButton>
                </div>
              </div>
            </div>
          </div>

          <Show when={hasSettings}>
            <div css={styles.alert}>
              <SVGIcon name="infoFill" width={40} height={40} />
              <p>
                {
                  // prettier-ignore
                  __('WARNING! This will overwrite all existing settings, please proceed with caution.', 'tutor')
                }
              </p>
            </div>
          </Show>
        </div>
        <div css={styles.footer}>
          <div css={styles.actionButtons}>
            <Button variant="text" size="small" onClick={onClose}>
              {__('Cancel', 'tutor')}
            </Button>
            <Button
              data-cy="import-csv"
              disabled={files.length === 0}
              variant="primary"
              size="small"
              loading={isReadingFile || currentStep === 'progress'}
              onClick={async () => onImport(await readJsonFile(files[0]))}
            >
              {__('Import', 'tutor')}
            </Button>
          </div>
        </div>
      </>
    );
  };

  const renderProgressState = (file: File) => {
    return (
      <div css={styles.progress}>
        <img src={importInProgressImage} alt={__('Importing...', 'tutor')} />
        <div css={styles.progressHeader}>
          <div css={typography.caption()}>{renderHeader[currentStep]}</div>
          <div css={styles.progressCount}>{progress}%</div>
        </div>
        <div css={styles.progressBar({ progress })} />
        <div css={styles.progressInfo}>{message || file.name}</div>
      </div>
    );
  };

  const renderCompletedState = (file: File, state: ImportExportModalState) => {
    const imageSrc = {
      success: importSuccessImage,
      error: importErrorImage,
    };
    const subtitle = {
      success: sprintf(__('You have successfully imported a “%s"', 'tutor'), file.name),
      error: message || sprintf(__('Failed to import “%s".', 'tutor'), file.name),
    };

    return (
      <div css={styles.completed}>
        <img src={imageSrc[state as keyof typeof imageSrc]} alt={renderHeader[state]} />
        <div css={styles.title}>{renderHeader[state]}</div>
        <div css={styles.subtitle}>{subtitle[state as keyof typeof subtitle]}</div>

        <Button variant="primary" size="small" onClick={onClose}>
          {__('Okay', 'tutor')}
        </Button>
      </div>
    );
  };

  const modalBody = {
    initial: renderInitialState(files[0]),
    progress: renderProgressState(files[0]),
    success: renderCompletedState(files[0], 'success'),
    error: renderCompletedState(files[0], 'error'),
  };

  return (
    <BasicModalWrapper
      onClose={onClose}
      entireHeader={currentStep === 'initial' ? undefined : <>&nbsp;</>}
      maxWidth={500}
      title={currentStep === 'initial' ? renderHeader[currentStep] : undefined}
      isCloseAble={currentStep !== 'progress'}
    >
      <div css={styles.wrapper({ state: currentStep })}>{modalBody[currentStep]}</div>
    </BasicModalWrapper>
  );
};

export default ImportModal;

const styles = {
  wrapper: ({ state = 'initial' }: { state?: ImportExportModalState }) => css`
    max-height: 340px;
    transition: max-height 0.3s ease-in-out;

    ${state === 'progress' &&
    css`
      max-height: 294px;

      img {
        height: 94px;
      }
    `}

    ${state === 'success' &&
    css`
      max-height: 443px;

      img {
        height: 110px;
      }
    `}

    ${state === 'error' &&
    css`
      max-height: 336px;

      img {
        height: 110px;
      }
    `}
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
  progress: css`
    width: 100%;
    ${styleUtils.display.flex('column')};
    gap: ${spacing[4]};
    padding: ${spacing[32]} 91px ${spacing[48]} 91px;

    img {
      align-self: center;
      width: 83px;
      height: 'auto';
      object-fit: contain;
      object-position: center;
      margin-bottom: ${spacing[36]};
    }
  `,
  progressHeader: css`
    ${styleUtils.display.flex()};
    justify-content: space-between;
  `,
  progressCount: css`
    ${styleUtils.flexCenter()};
    ${typography.tiny('bold')};
    padding: ${spacing[2]} ${spacing[4]};
    background-color: ${colorTokens.background.status.success};
    color: ${colorTokens.text.success};
    border-radius: ${borderRadius[12]};
  `,
  progressBar: ({ progress = 0 }) => css`
    position: relative;
    width: 100%;
    height: 6px;
    background-color: ${colorTokens.color.black[10]};
    border-radius: ${borderRadius[50]};
    overflow: hidden;

    &::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      height: 100%;
      border-radius: ${borderRadius[50]};
      transition: ${progress === 0 ? 'none' : 'width 0.3s ease-in-out'};
      width: ${progress === 0 ? 100 : progress}%;
      background-color: ${colorTokens.bg.success};

      ${progress === 0 &&
      css`
        background-image: linear-gradient(
          45deg,
          rgba(255, 255, 255, 0.15) 25%,
          transparent 25%,
          transparent 50%,
          rgba(255, 255, 255, 0.15) 50%,
          rgba(255, 255, 255, 0.15) 75%,
          transparent 75%,
          transparent
        );
        background-size: 1rem 1rem;
        animation: progress-stripes 1s linear infinite;

        @keyframes progress-stripes {
          from {
            background-position: 1rem 0;
          }
          to {
            background-position: 0 0;
          }
        }
      `}
    }
  `,
  progressInfo: css`
    ${typography.small()};
    color: ${colorTokens.text.subdued};
  `,
  selectedInfo: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[20]};
    padding: ${spacing[20]} ${spacing[16]};
    border-bottom: 1px solid ${colorTokens.stroke.divider};
  `,
  fileInfo: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[10]};
  `,
  file: css`
    ${styleUtils.display.flex()};
    height: 64px;
    border: 1px solid ${colorTokens.stroke.divider};
    overflow: hidden;
    border-radius: ${borderRadius[6]};
    width: 100%;
  `,
  fileIcon: css`
    background-color: #f7f7f7;
    ${styleUtils.flexCenter()};
    width: 64px;
    height: 100%;
    border-right: 1px solid ${colorTokens.stroke.divider};
    flex-shrink: 0;

    svg {
      color: ${colorTokens.icon.disable.background};
    }
  `,
  fileRight: css`
    flex-grow: 1;
    ${styleUtils.display.flex()};
    justify-content: space-between;
    align-items: center;
    padding: ${spacing[10]} ${spacing[16]} ${spacing[10]} ${spacing[20]};
  `,
  fileDetails: css`
    flex-grow: 1;
    ${styleUtils.display.flex('column')};
    gap: ${spacing[4]};
  `,
  fileName: css`
    ${typography.small('medium')};
    color: ${colorTokens.text.subdued};
    ${styleUtils.text.ellipsis(1)};
  `,
  fileSize: css`
    ${typography.tiny()};
    color: ${colorTokens.text.hints};
  `,
  alert: css`
    ${styleUtils.display.flex()};
    align-items: flex-start;
    gap: ${spacing[8]};
    padding: ${spacing[20]};
    border-radius: ${borderRadius[6]};
    background-color: ${colorTokens.background.status.warning};

    svg {
      color: ${colorTokens.icon.warning};
      flex-shrink: 0;
    }

    p {
      ${typography.caption()};
      color: ${colorTokens.text.warning};
    }
  `,
  footer: css`
    ${styleUtils.display.flex()};
    align-items: center;
    justify-content: flex-end;
    padding: ${spacing[12]} ${spacing[16]};
  `,
  actionButtons: css`
    ${styleUtils.display.flex()};
    align-items: center;
    gap: ${spacing[8]};
  `,
  completed: css`
    ${styleUtils.display.flex('column')};
    align-items: center;
    gap: ${spacing[8]};
    padding-block: ${spacing[40]} ${spacing[32]};
  `,
  completedHeader: css`
    ${styleUtils.display.flex('column')};
    align-items: center;
    gap: ${spacing[8]};
  `,
};
