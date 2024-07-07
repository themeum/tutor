import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';

import SVGIcon from '@Atoms/SVGIcon';
import Button from '@Atoms/Button';
import { useToast } from '@Atoms/Toast';

import FormFieldWrapper from '@Components/fields/FormFieldWrapper';
import type { Media } from '@Components/fields/FormImageInput';

import For from '@Controls/For';
import Show from '@Controls/Show';
import { borderRadius, colorTokens, spacing } from '@Config/styles';
import type { FormControllerProps } from '@Utils/form';
import { styleUtils } from '@Utils/style-utils';
import { typography } from '@Config/typography';
import type { IconCollection } from '@Utils/types';

export type WpMediaDetails = {
  id: number;
  url: string;
  title: string;
  date?: string;
  filesizeHumanReadable?: string;
  filesizeInBytes?: number;
  subtype?: string;
};

type FormFileUploaderProps = {
  label?: string;
  onChange?: (media: Media[] | Media | null) => void;
  helpText?: string;
  buttonText?: string;
  selectMultiple?: boolean;
  maxFiles?: number;
  maxFileSize?: number; // in bytes
} & FormControllerProps<Media[] | Media | null>;

const fileIcon = (fileExtension: string): IconCollection => {
  switch (fileExtension) {
    case 'iso':
      return 'iso';
    case 'dwg':
      return 'dwg';
    case 'pdf':
      return 'pdf';
    case 'doc':
    case 'docx':
      return 'doc';
    case 'csv':
      return 'csv';
    case 'xls':
    case 'xlsx':
      return 'xls';
    case 'ppt':
    case 'pptx':
      return 'ppt';
    case 'zip':
      return 'zip';
    case 'rar':
    case '7z':
    case 'tar':
    case 'gz':
      return 'archive';
    case 'txt':
      return 'txt';
    case 'rtf':
      return 'rtf';
    case 'log':
      return 'text';
    case 'jpg':
      return 'jpg';
    case 'png':
      return 'png';
    case 'jpeg':
    case 'gif':
      return 'image';
    case 'mp3':
      return 'mp3';
    case 'fla':
      return 'fla';
    case 'ogg':
    case 'wav':
    case 'wma':
      return 'audio';
    case 'mp4':
      return 'mp4';
    case 'avi':
      return 'avi';
    case 'ai':
      return 'ai';
    case 'mkv':
    case 'mpeg':
    case 'flv':
    case 'mov':
    case 'wmv':
      return 'videoFile';
    case 'svg':
      return 'svg';
    case 'css':
      return 'css';
    case 'js':
      return 'javascript';
    case 'xml':
      return 'xml';
    case 'html':
      return 'html';
    case 'exe':
      return 'exe';
    case 'psd':
      return 'psd';
    case 'json':
      return 'jsonFile';
    case 'dbf':
      return 'dbf';
    default:
      return 'file';
  }
};

const FormFileUploader = ({
  field,
  fieldState,
  label,
  helpText,
  buttonText = __('Upload Media', 'tutor'),
  selectMultiple = false,
  onChange,
  maxFileSize,
  maxFiles,
}: FormFileUploaderProps) => {
  const { showToast } = useToast();

  const wpMedia = window.wp.media({
    multiple: selectMultiple ? 'add' : false,
  });

  const fieldValue = field.value;

  const uploadHandler = () => {
    wpMedia.open();
  };

  wpMedia.on('open', () => {
    const selection = wpMedia.state().get('selection');
    const ids = Array.isArray(fieldValue) ? fieldValue.map((file) => file.id) : fieldValue ? [fieldValue.id] : [];
    if (ids.length > 0) {
      for (const id of ids) {
        const attachment = window.wp.media.attachment(id);
        selection.add(attachment ? [attachment] : []);
      }
    }
  });

  wpMedia.on('select', () => {
    const selected = wpMedia.state().get('selection').toJSON();

    console.log(selected[0]);

    const existingFileIds = new Set(
      Array.isArray(fieldValue) ? fieldValue.map((file) => file.id) : fieldValue ? [fieldValue.id] : []
    );

    const newFiles = selected.reduce((allFiles: Media[], file: WpMediaDetails) => {
      if (maxFileSize && file.filesizeInBytes && file.filesizeInBytes > maxFileSize) {
        showToast({
          message: `${file.title} ${__(' size exceeds the limit', 'tutor')}`,
          type: 'danger',
        });
        return allFiles;
      }

      if (existingFileIds.has(file.id)) {
        return allFiles;
      }

      const newFile: Media = {
        id: file.id,
        title: file.title,
        url: file.url,
        name: file.title,
        size: file.filesizeHumanReadable,
        size_bytes: file.filesizeInBytes,
        ext: file.subtype,
      };

      if (!selectMultiple) {
        return [newFile];
      }

      allFiles.push(newFile);
      return allFiles;
    }, []);

    const totalFiles = fieldValue ? (Array.isArray(fieldValue) ? fieldValue.length : 1) : 0 + newFiles.length;

    if (maxFiles && totalFiles > maxFiles) {
      showToast({
        message: __(`You can not upload more than ${maxFiles} files in total`, 'tutor'),
        type: 'danger',
      });
      return;
    }

    if (selectMultiple) {
      const updatedValue = Array.isArray(fieldValue)
        ? [...fieldValue, ...newFiles]
        : fieldValue
          ? [fieldValue, ...newFiles]
          : newFiles;
      field.onChange(updatedValue);

      if (onChange) {
        onChange(updatedValue);
      }
    } else {
      field.onChange(newFiles[0] || null);

      if (onChange) {
        onChange(newFiles[0] || null);
      }
    }
  });

  const clearHandler = (fileId: number) => {
    if (selectMultiple) {
      const newFiles = (Array.isArray(fieldValue) ? fieldValue : fieldValue ? [fieldValue] : []).filter(
        (file: Media) => file.id !== fileId
      );
      field.onChange(newFiles.length > 0 ? newFiles : null);

      if (onChange) {
        onChange(newFiles.length > 0 ? newFiles : null);
      }
    } else {
      field.onChange(null);

      if (onChange) {
        onChange(null);
      }
    }
  };

  return (
    <FormFieldWrapper label={label} field={field} fieldState={fieldState} helpText={helpText}>
      {() => {
        return (
          <Show
            when={fieldValue}
            fallback={
              <Button
                buttonCss={styles.uploadButton}
                icon={<SVGIcon name="attach" height={24} width={24} />}
                variant="secondary"
                onClick={uploadHandler}
              >
                {buttonText}
              </Button>
            }
          >
            {(files) => (
              <div css={styles.wrapper({ hasFiles: Array.isArray(files) ? files.length > 0 : files !== null })}>
                <For each={Array.isArray(files) ? files : [files]}>
                  {(file) => (
                    <div key={file.id} css={styles.attachmentCardWrapper}>
                      <div css={styles.attachmentCard}>
                        <SVGIcon style={styles.fileIcon} name={fileIcon(file.ext || 'file')} height={40} width={40} />

                        <div css={styles.attachmentCardBody}>
                          <div css={styles.attachmentCardTitle}>
                            <div css={styleUtils.text.ellipsis(1)}>{file.title}</div>

                            <div css={styles.fileExtension}>{`.${file.ext}`}</div>
                          </div>

                          <div css={styles.attachmentCardSubtitle}>
                            <span>{`${__('Size', 'tutor')}: ${file.size}`}</span>
                          </div>
                        </div>
                      </div>

                      <button
                        type="button"
                        css={styleUtils.resetButton}
                        onClick={() => {
                          clearHandler(file.id);
                        }}
                      >
                        <SVGIcon name="cross" height={24} width={24} />
                      </button>
                    </div>
                  )}
                </For>

                <Button
                  buttonCss={styles.uploadButton}
                  icon={<SVGIcon name="attach" height={24} width={24} />}
                  variant="secondary"
                  onClick={uploadHandler}
                >
                  {buttonText}
                </Button>
              </div>
            )}
          </Show>
        );
      }}
    </FormFieldWrapper>
  );
};

export default FormFileUploader;

const styles = {
  wrapper: ({
    hasFiles,
  }: {
    hasFiles: boolean;
  }) => css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[8]};

    ${
      hasFiles &&
      css`
        padding: ${spacing[16]};
        border: 1px solid ${colorTokens.stroke.default};
        border-radius: ${borderRadius.card};
      `
    }
  `,
  attachmentCardWrapper: css`
    ${styleUtils.display.flex()};
    justify-content: space-between;
    align-items: center;
    gap: ${spacing[20]};
    padding: ${spacing[4]} ${spacing[12]} ${spacing[4]} 0;
    border-radius: ${borderRadius[6]};

    button {
      opacity: 0;
    }

    &:hover {
      background: ${colorTokens.background.white};

      button {
        opacity: 1;
      }
    }
  `,
  attachmentCard: css`
    ${styleUtils.display.flex()};
    align-items: center;
    gap: ${spacing[8]};
  `,
  attachmentCardBody: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[4]};
  `,
  attachmentCardTitle: css`
    ${styleUtils.display.flex()};
    ${typography.caption('medium')}
    word-break: break-all;
  `,
  fileExtension: css`
    flex-shrink: 0;
  `,
  attachmentCardSubtitle: css`
    ${typography.tiny('regular')}
    ${styleUtils.display.flex()};
    align-items: center;
    gap: ${spacing[8]};
    color: ${colorTokens.text.hints};

    svg {
      color: ${colorTokens.icon.default};
    }
  `,
  uploadButton: css`
    width: 100%;
  `,
  fileIcon: css`
    flex-shrink: 0;
    color: ${colorTokens.icon.default};
  `,
};
