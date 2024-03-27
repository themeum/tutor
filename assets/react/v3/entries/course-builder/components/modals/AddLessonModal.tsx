import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import FormImageInput, { type Media } from '@Components/fields/FormImageInput';
import FormInput from '@Components/fields/FormInput';
import FormInputWithContent from '@Components/fields/FormInputWithContent';
import FormSwitch from '@Components/fields/FormSwitch';
import FormTextareaInput from '@Components/fields/FormTextareaInput';
import type { ModalProps } from '@Components/modals/Modal';
import ModalWrapper from '@Components/modals/ModalWrapper';
import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import React from 'react';
import { Controller } from 'react-hook-form';

interface AddLessonModalProps extends ModalProps {
  closeModal: (props?: { action: 'CONFIRM' | 'CLOSE' }) => void;
}

interface AddLessonForm {
  lesson_name: string;
  description: string;
  featured_image: Media | null;
  duration_hour: number;
  duration_min: number;
  duration_sec: number;
  lesson_preview: boolean;
}

const AddLessonModal = ({ closeModal, icon, title, subtitle }: AddLessonModalProps) => {
  const form = useFormWithGlobalError<AddLessonForm>();

  const onSubmit = (data: AddLessonForm) => {
    console.log(data);
    closeModal({ action: 'CONFIRM' });
  };

  return (
    <ModalWrapper
      onClose={() => closeModal({ action: 'CLOSE' })}
      icon={icon}
      title={title}
      subtitle={subtitle}
      actions={
        <>
          <Button variant="text" onClick={() => closeModal({ action: 'CLOSE' })}>
            Cancel
          </Button>
          <Button variant="primary" onClick={form.handleSubmit(onSubmit)}>
            Save
          </Button>
        </>
      }
    >
      <div css={{ width: '1472px', height: '100%' }}>
        <div css={styles.wrapper}>
          <div>
            <div css={styles.lessonInfo}>
              <Controller
                name="lesson_name"
                control={form.control}
                render={(controllerProps) => (
                  <FormInput
                    {...controllerProps}
                    label={__('Lesson Name', 'tutor')}
                    placeholder={__('Enter Lesson Name', 'tutor')}
                    maxLimit={245}
                    isClearable
                  />
                )}
              />
              <Controller
                name="description"
                control={form.control}
                render={(controllerProps) => (
                  <FormTextareaInput
                    {...controllerProps}
                    label={__('Description', 'tutor')}
                    placeholder={__('Enter Lesson Description', 'tutor')}
                  />
                )}
              />
            </div>
          </div>

          <div css={styles.rightPanel}>
            <Controller
              name="featured_image"
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
            {/* // @TODO: Need to add FormVideo component when its implemented*/}
            {/* <Controller
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
            /> */}
            <div css={styles.duration}>
              <Controller
                name="duration_hour"
                control={form.control}
                render={(controllerProps) => (
                  <FormInputWithContent
                    {...controllerProps}
                    type="number"
                    content={<span css={styles.durationContent}>{__('hour', 'tutor')}</span>}
                    contentPosition="right"
                    label={__('Duration', 'tutor')}
                    placeholder="0"
                    showVerticalBar={false}
                  />
                )}
              />
              <Controller
                name="duration_min"
                control={form.control}
                render={(controllerProps) => (
                  <FormInputWithContent
                    {...controllerProps}
                    type="number"
                    content={<span css={styles.durationContent}>{__('min', 'tutor')}</span>}
                    contentPosition="right"
                    placeholder="0"
                    showVerticalBar={false}
                  />
                )}
              />
              <Controller
                name="duration_sec"
                control={form.control}
                render={(controllerProps) => (
                  <FormInputWithContent
                    {...controllerProps}
                    type="number"
                    content={<span css={styles.durationContent}>{__('sec', 'tutor')}</span>}
                    contentPosition="right"
                    placeholder="0"
                    showVerticalBar={false}
                  />
                )}
              />
            </div>

            <div css={styles.uploadAttachment}>
              <span css={styles.uploadLabel}>Exercise Files</span>
              <Button
                icon={<SVGIcon name="attach" height={24} width={24} />}
                variant="secondary"
                buttonContentCss={css`
                  justify-content: center;
                `}
              >
                Upload Attachment
              </Button>
            </div>

            <div css={styles.lessonPreview}>
              <Controller
                name="lesson_preview"
                control={form.control}
                render={(controllerProps) => (
                  <FormSwitch
                    {...controllerProps}
                    label={__('Lesson preview', 'tutor')}
                    helpText={__('Show preview', 'tutor')}
                  />
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
    padding-inline: ${spacing[24]} ${spacing[64]};
    display: flex;
    flex-direction: column;
    gap: ${spacing[24]};
    position: sticky;
    top: 0;
  `,
  rightPanel: css`
    border-left: 1px solid ${colorTokens.stroke.divider};
    display: flex;
    flex-direction: column;
    gap: ${spacing[16]};
    padding-block: ${spacing[24]};
    padding-inline: ${spacing[64]} ${spacing[24]};
  `,
  duration: css`
    display: flex;
    align-items: flex-end;
    gap: ${spacing[8]};
  `,
  durationContent: css`
    ${typography.small()};
    color: ${colorTokens.text.hints};
  `,

  uploadAttachment: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[8]};
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
