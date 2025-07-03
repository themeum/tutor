import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useEffect, useState } from 'react';

import Button from '@TutorShared/atoms/Button';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import { useToast } from '@TutorShared/atoms/Toast';
import { UploadButton } from '@TutorShared/molecules/FileUploader';

import { tutorConfig } from '@TutorShared/config/config';
import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import Show from '@TutorShared/controls/Show';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { formatBytes } from '@TutorShared/utils/util';

interface ImportInitialStateProps {
  files: File[];
  currentStep: string;
  onClose: () => void;
  onImport: (file: File) => void;
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

const isTutorPro = !!tutorConfig.tutor_pro_url;

const ImportInitialState = ({ files: propsFiles, currentStep, onClose, onImport }: ImportInitialStateProps) => {
  const [files, setFiles] = useState<File[]>(propsFiles);
  const [isReadingFile, setIsReadingFile] = useState(false);
  const [isFileValid, setIsFileValid] = useState(true);
  const [hasSettings, setHasSettings] = useState(false);
  const { showToast } = useToast();

  useEffect(() => {
    if (files.length === 0) {
      return;
    }
    setIsReadingFile(true);
    readJsonFile(files[0])
      .then((data) => {
        const hasSettings = data?.data.find((item: { content_type: string }) => item.content_type === 'settings');

        setIsReadingFile(false);
        setHasSettings(hasSettings);
        setFiles(files);
        setIsFileValid(true);
      })
      .catch(() => {
        setIsReadingFile(false);
        setIsFileValid(false);
      })
      .finally(() => {
        setIsReadingFile(false);
      });
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

  if (files.length === 0) {
    return null;
  }

  const file = files[0];

  return (
    <>
      <div css={styles.selectedInfo}>
        <div css={styles.fileInfo}>
          <div css={styles.progressHeader}>
            <div css={typography.small()}>
              {isReadingFile ? __('Reading file...', 'tutor') : __('Selected', 'tutor')}
            </div>

            <Show
              when={isReadingFile}
              fallback={
                <Show when={isFileValid}>
                  <div css={styles.progressCount}>{__('Ready to import', 'tutor')}</div>
                </Show>
              }
            >
              <div css={styles.progressCount}>{__('Please wait...', 'tutor')}</div>
            </Show>
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

        <Show when={!isFileValid}>
          <div css={styles.alert}>
            <SVGIcon name="warning" width={40} height={40} />
            <p>
              {
                // prettier-ignore
                __('WARNING! Invalid file. Please upload a valid JSON file and try again.', 'tutor')
              }
            </p>
          </div>
        </Show>

        <Show when={isFileValid && hasSettings}>
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
            disabled={files.length === 0 || isReadingFile || !isFileValid || (!isTutorPro && !hasSettings)}
            variant="primary"
            size="small"
            loading={isReadingFile || currentStep === 'progress'}
            onClick={async () => onImport(files[0])}
          >
            {__('Import', 'tutor')}
          </Button>
        </div>
      </div>
    </>
  );
};

export default ImportInitialState;

const styles = {
  progressHeader: css`
    ${styleUtils.display.flex()};
    justify-content: space-between;
  `,
  progressCount: css`
    ${styleUtils.flexCenter()};
    ${typography.tiny('bold')};
    padding: ${spacing[2]} ${spacing[8]};
    background-color: ${colorTokens.background.status.success};
    color: ${colorTokens.text.success};
    border-radius: ${borderRadius[12]};
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
};
