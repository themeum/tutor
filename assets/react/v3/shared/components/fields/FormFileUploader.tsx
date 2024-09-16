import { css } from '@emotion/react';
import { __, sprintf } from '@wordpress/i18n';

import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import { useToast } from '@Atoms/Toast';

import FormFieldWrapper from '@Components/fields/FormFieldWrapper';
import type { Media } from '@Components/fields/FormImageInput';

import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import For from '@Controls/For';
import Show from '@Controls/Show';
import type { FormControllerProps } from '@Utils/form';
import { styleUtils } from '@Utils/style-utils';
import type { IconCollection } from '@Utils/types';

export type WpMediaDetails = {
  id: number;
  url: string;
  title: string;
  filename: string;
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

const iconMapping: { [key: string]: string[] } = {
  iso: ['iso'],
  dwg: ['dwg'],
  pdf: ['pdf'],
  doc: ['doc', 'docx'],
  csv: ['csv'],
  xls: ['xls', 'xlsx'],
  ppt: ['ppt', 'pptx'],
  zip: ['zip'],
  archive: ['rar', '7z', 'tar', 'gz'],
  txt: ['txt'],
  rtf: ['rtf'],
  text: ['log'],
  jpg: ['jpg'],
  png: ['png'],
  image: ['jpeg', 'gif', 'webp', 'avif'],
  mp3: ['mp3'],
  fla: ['fla'],
  audio: ['ogg', 'wav', 'wma'],
  mp4: ['mp4'],
  avi: ['avi'],
  ai: ['ai'],
  videoFile: ['mkv', 'mpeg', 'flv', 'mov', 'wmv'],
  svg: ['svg'],
  css: ['css'],
  javascript: ['js'],
  xml: ['xml'],
  html: ['html'],
  exe: ['exe'],
  psd: ['psd'],
  jsonFile: ['json'],
  dbf: ['dbf'],
};

const fileIcon = (fileExtension: string): IconCollection => {
  for (const [icon, extensions] of Object.entries(iconMapping)) {
    if (extensions.includes(fileExtension)) {
      return icon as IconCollection;
    }
  }
  return 'file';
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

    const existingFileIds = new Set(
      Array.isArray(fieldValue) ? fieldValue.map((file) => file.id) : fieldValue ? [fieldValue.id] : [],
    );

    const newFiles = selected.reduce((allFiles: Media[], file: WpMediaDetails) => {
      if (maxFileSize && file.filesizeInBytes && file.filesizeInBytes > maxFileSize) {
        showToast({
          message: sprintf(__('%s size exceeds the limit', 'tutor'), file.title),
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
        ext: file.filename.split('.').pop(),
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
        message: sprintf(__('You can not upload more than %d files in total', 'tutor'), maxFiles),
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
        (file: Media) => file.id !== fileId,
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
                <div css={styles.attachmentsWrapper}>
                  <For each={Array.isArray(files) ? files : [files]}>
                    {(file) => (
                      <div key={file.id} css={styles.attachmentCardWrapper}>
                        <div css={styles.attachmentCard}>
                          <SVGIcon style={styles.fileIcon} name={fileIcon(file.ext || 'file')} height={40} width={40} />

                          <div css={styles.attachmentCardBody}>
                            <div css={styles.attachmentCardTitle}>
                              <div title={file.title} css={styleUtils.text.ellipsis(1)}>
                                {file.title}
                              </div>

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
                </div>

                <div
                  css={styles.uploadButtonWrapper({
                    hasFiles: Array.isArray(files) ? files.length > 0 : files !== null,
                  })}
                >
                  <Button
                    buttonCss={styles.uploadButton}
                    icon={<SVGIcon name="attach" height={24} width={24} />}
                    variant="secondary"
                    onClick={uploadHandler}
                  >
                    {buttonText}
                  </Button>
                </div>
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
    position: relative;
    
    ${
      hasFiles &&
      css`
        background-color: ${colorTokens.background.white};
        padding: ${spacing[16]} 0 ${spacing[16]} ${spacing[16]};
        border: 1px solid ${colorTokens.stroke.default};
        border-radius: ${borderRadius.card};
        gap: ${spacing[8]};
      `
    }
  `,
  attachmentsWrapper: css`
    max-height: 260px;
    padding-right: ${spacing[16]};
    ${styleUtils.overflowYAuto};
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
      background: ${colorTokens.background.hover};

      button {
        opacity: 1;
      }
    }
  `,
  attachmentCard: css`
    ${styleUtils.display.flex()};
    align-items: center;
    gap: ${spacing[8]};
    overflow: hidden;
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
  uploadButtonWrapper: ({
    hasFiles,
  }: {
    hasFiles: boolean;
  }) => css`
    ${
      hasFiles &&
      css`
      margin-right: ${spacing[16]};
    `
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
