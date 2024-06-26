import type { FormControllerProps } from '@Utils/form';
import { __ } from '@wordpress/i18n';
import FormFieldWrapper from './FormFieldWrapper';
import SVGIcon from '@Atoms/SVGIcon';
import Button from '@Atoms/Button';
import { css } from '@emotion/react';
import { borderRadius, colorTokens, spacing } from '@Config/styles';
import Show from '@Controls/Show';
import For from '@Controls/For';
import { typography } from '@Config/typography';
import { formatBytes } from '@Utils/util';
import { format } from 'date-fns';
import { DateFormats } from '@Config/constants';
import { styleUtils } from '@Utils/style-utils';

export type UploadedFile = {
  id: number;
  title: string;
  url: string;
  date: string;
  filesizeInBytes: number;
  subtype: string;
};

type FormFileUploaderProps = {
  label?: string;
  onChange?: () => void;
  helpText?: string;
  buttonText?: string;
} & FormControllerProps<UploadedFile[] | null>;

const FormFileUploader = ({
  field,
  fieldState,
  label,
  helpText,
  buttonText = __('Upload Media', 'tutor'),
}: FormFileUploaderProps) => {
  const wpMedia = window.wp.media();

  const fieldValue = field.value;

  const uploadHandler = () => {
    wpMedia.open();
  };

  wpMedia.on('select', () => {
    const attachment = wpMedia.state().get('selection').first().toJSON();
    console.log(attachment);
    const { id, url, title, filesizeInBytes, date, subtype } = attachment;

    field.onChange(
      fieldValue
        ? [...fieldValue, { id, url, title, filesizeInBytes, date, subtype }]
        : [{ id, url, title, filesizeInBytes, date, subtype }]
    );
  });

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
              <div css={styles.wrapper({ hasFiles: files.length > 0 })}>
                <For each={files}>
                  {(file) => (
                    <div key={file.id} css={styles.attachmentCard}>
                      <SVGIcon name="preview" height={40} width={40} />
                      <div css={styles.attachmentCardBody}>
                        <p css={styles.attachmentCardTitle}>
                          <span css={styleUtils.text.ellipsis(1)}>{file.title}jkgsjahfasjhfashgdfahdfas</span>{' '}
                          {file.subtype}
                        </p>
                        <p css={styles.attachmentCardSubtitle}>
                          <span>{`${__('Size', 'tutor')}: ${formatBytes(file.filesizeInBytes)}`}</span> .
                          {/* <SVGIcon name="dot" height={4} width={4} /> */}
                          <span>{format(new Date(file.date), DateFormats.monthDayYearHoursMinutes)}</span>
                        </p>
                      </div>
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
  attachmentCard: css`
    display: flex;
    align-items: center;
    gap: ${spacing[8]};
  `,
  attachmentCardBody: css`
    display: flex;
    flex-direction: column;
  `,
  attachmentCardTitle: css`
    ${typography.caption('medium')}
  `,
  attachmentCardSubtitle: css`
    ${typography.tiny('regular')}
  `,
  uploadButton: css`
    width: 100%;
  `,
};
