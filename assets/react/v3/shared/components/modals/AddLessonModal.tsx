import React from 'react';
import ModalWrapper from './ModalWrapper';
import { ModalProps } from './Modal';
import { css } from '@emotion/react';
import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { Controller } from 'react-hook-form';
import FormInput from '@Components/fields/FormInput';
import { __ } from '@wordpress/i18n';
import FormTextareaInput from '@Components/fields/FormTextareaInput';
import FormImageInput from '@Components/fields/FormImageInput';
import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import { typography } from '@Config/typography';
import FormSwitch from '@Components/fields/FormSwitch';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';

interface AddLessonModalProps extends ModalProps {
  closeModal: (props?: { action: 'CONFIRM' | 'CLOSE' }) => void;
}

const AddLessonModal = ({ closeModal, icon, title, subtitle, actions }: AddLessonModalProps) => {
  const form = useFormWithGlobalError();

  return (
    <ModalWrapper
      onClose={() => closeModal({ action: 'CLOSE' })}
      icon={icon}
      title={title}
      subtitle={subtitle}
      actions={actions}
    >
      <div css={{ width: '1472px', height: '100%' }}>
        <div css={styles.wrapper}>
          <div css={styles.lessonInfo}>
            <Controller
              name='lesson_name'
              control={form.control}
              render={(controllerProps) => (
                <FormInput
                  {...controllerProps}
                  label={__('Lesson Name', 'tutor')}
                  placeholder={__('Enter Lesson name', 'tutor')}
                  maxLimit={245}
                />
              )}
            />

            <Controller
              name='lesson_description'
              control={form.control}
              render={(controllerProps) => (
                <FormTextareaInput
                  {...controllerProps}
                  label={__('Description', 'tutor')}
                  placeholder={__('Enter Lesson name', 'tutor')}
                />
              )}
            />
          </div>
          <div css={styles.rightPanel}>
            <Controller
              name='thumbnail'
              control={form.control}
              render={(controllerProps) => (
                <FormImageInput
                  {...controllerProps}
                  label={__('Featured Image', 'tutor')}
                  buttonText={__('Upload Course Thumbnail', 'tutor')}
                  infoText={__('Size: 700x430 pixels', 'tutor')}
                />
              )}
            />

            <Controller
              name='thumbnail'
              control={form.control}
              render={(controllerProps) => (
                <FormImageInput
                  {...controllerProps}
                  label={__('Featured Image', 'tutor')}
                  buttonText={__('Upload Course Thumbnail', 'tutor')}
                  infoText={__('Size: 700x430 pixels', 'tutor')}
                />
              )}
            />

            <div css={styles.duration}>
              <Controller
                name='duration_hour'
                control={form.control}
                render={(controllerProps) => (
                  <FormInput {...controllerProps} label={__('Duration', 'tutor')} placeholder={__('hour', 'tutor')} />
                )}
              />
              <Controller
                name='duration_hour'
                control={form.control}
                render={(controllerProps) => <FormInput {...controllerProps} placeholder={__('min', 'tutor')} />}
              />
              <Controller
                name='duration_hour'
                control={form.control}
                render={(controllerProps) => <FormInput {...controllerProps} placeholder={__('sec', 'tutor')} />}
              />
            </div>
            <div css={styles.uploadAttachment}>
              <span css={styles.uploadLabel}>Exercise Files</span>
              <Button
                icon={<SVGIcon name='attach' />}
                variant='secondary'
                buttonContentCss={css`
                  justify-content: center;
                `}
              >
                Upload Attachment
              </Button>
            </div>

            <div css={styles.lessonPreview}>
              <Controller
                name='lesson_preview'
                control={form.control}
                render={(controllerProps) => (
                  <FormSwitch {...controllerProps} label={__('Lesson preview', 'tutor')} helpText='Show preview' />
                )}
              />
            </div>
          </div>
        </div>
      </div>
    </ModalWrapper>
  );
};

export default AddLessonModal;

const styles = {
  wrapper: css`
    width: 1035px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: 1fr 395px;
    height: 100%;
  `,
  lessonInfo: css`
    padding-block: ${spacing[24]};
    padding-left: ${spacing[24]};
    padding-right: ${spacing[64]};
    display: flex;
    flex-direction: column;
    row-gap: ${spacing[24]};
    align-self: start;
    position: sticky;
    top: 0;
  `,
  rightPanel: css`
    border-left: 1px solid ${colorTokens.stroke.divider};
    display: flex;
    flex-direction: column;
    row-gap: ${spacing[16]};
    padding-block: ${spacing[24]};
    padding-right: ${spacing[24]};
    padding-left: ${spacing[64]};
  `,
  duration: css`
    display: flex;
    align-items: flex-end;
    column-gap: ${spacing[8]};
    /* max-height: 62px; */
  `,
  uploadAttachment: css`
    display: flex;
    flex-direction: column;
    row-gap: ${spacing[8]};
  `,
  uploadLabel: css`
    ${typography.body()}
    color: ${colorTokens.text.title};
  `,
  lessonPreview: css`
    padding: ${spacing[12]};
    border: 1px solid ${colorTokens.stroke.default};
    border-radius: ${borderRadius[8]};
  `,
};
