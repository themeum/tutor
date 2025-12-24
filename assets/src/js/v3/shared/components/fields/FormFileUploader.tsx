import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';

import Button from '@TutorShared/atoms/Button';
import SVGIcon from '@TutorShared/atoms/SVGIcon';

import FormFieldWrapper from '@TutorShared/components/fields/FormFieldWrapper';

import { borderRadius, Breakpoint, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import For from '@TutorShared/controls/For';
import Show from '@TutorShared/controls/Show';
import { withVisibilityControl } from '@TutorShared/hoc/withVisibilityControl';
import useWPMedia, { type WPMedia } from '@TutorShared/hooks/useWpMedia';
import { type IconCollection } from '@TutorShared/icons/types';
import type { FormControllerProps } from '@TutorShared/utils/form';
import { styleUtils } from '@TutorShared/utils/style-utils';

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
  onChange?: (media: WPMedia[] | WPMedia | null) => void;
  helpText?: string;
  buttonText?: string;
  selectMultiple?: boolean;
  maxFiles?: number;
  maxFileSize?: number; // in bytes
} & FormControllerProps<WPMedia[] | WPMedia | null>;

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
  buttonText = __('Upload Media', __TUTOR_TEXT_DOMAIN__),
  selectMultiple = false,
  onChange,
  maxFileSize,
  maxFiles,
}: FormFileUploaderProps) => {
  const fieldValue = field.value;
  const { openMediaLibrary, resetFiles } = useWPMedia({
    options: { multiple: selectMultiple, maxFiles, maxFileSize },
    onChange: (files) => {
      field.onChange(files);
      if (onChange) {
        onChange(files);
      }
    },
    initialFiles: fieldValue ? (Array.isArray(fieldValue) ? fieldValue : [fieldValue]) : [],
  });

  const uploadHandler = () => {
    openMediaLibrary();
  };

  const clearHandler = (fileId: number) => {
    resetFiles();
    if (selectMultiple) {
      const newFiles = (Array.isArray(fieldValue) ? fieldValue : fieldValue ? [fieldValue] : []).filter(
        (file) => file.id !== fileId,
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
                              <span>{`${__('Size', __TUTOR_TEXT_DOMAIN__)}: ${file.size}`}</span>
                            </div>
                          </div>
                        </div>

                        <button
                          type="button"
                          css={styles.removeButton}
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
                    data-cy="upload-media"
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

export default withVisibilityControl(FormFileUploader);

const styles = {
  wrapper: ({ hasFiles }: { hasFiles: boolean }) => css`
    display: flex;
    flex-direction: column;
    position: relative;

    ${hasFiles &&
    css`
      background-color: ${colorTokens.background.white};
      padding: ${spacing[16]} 0 ${spacing[16]} ${spacing[16]};
      border: 1px solid ${colorTokens.stroke.default};
      border-radius: ${borderRadius.card};
      gap: ${spacing[8]};
    `}
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

    &:hover,
    &:focus-within {
      background: ${colorTokens.background.hover};

      button {
        opacity: 1;
      }
    }

    ${Breakpoint.smallTablet} {
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
  uploadButtonWrapper: ({ hasFiles }: { hasFiles: boolean }) => css`
    ${hasFiles &&
    css`
      margin-right: ${spacing[16]};
    `}
  `,
  uploadButton: css`
    width: 100%;
  `,
  fileIcon: css`
    flex-shrink: 0;
    color: ${colorTokens.icon.default};
  `,
  removeButton: css`
    ${styleUtils.crossButton};
    background: none;
    transition: none;
    flex-shrink: 0;
  `,
};
