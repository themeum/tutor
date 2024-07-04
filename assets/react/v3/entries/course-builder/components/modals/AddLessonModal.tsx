import { css } from '@emotion/react';
import { Controller } from 'react-hook-form';
import { __ } from '@wordpress/i18n';

import Button from '@Atoms/Button';

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
import FormFileUploader from '@Components/fields/FormFileUploader';
import FormVideoInput from '@Components/fields/FormVideoInput';
import { useEffect } from 'react';

interface AddLessonModalProps extends ModalProps {
  closeModal: (props?: { action: 'CONFIRM' | 'CLOSE' }) => void;
}

interface AddLessonForm {
  lesson_name: string;
  description: string;
  featured_image: Media | null;
  exercise_files: Media[];
  available_after_days: number;
  lesson_preview: boolean;
  video: Media | null;
}

const AddLessonModal = ({ closeModal, icon, title, subtitle }: AddLessonModalProps) => {
  const form = useFormWithGlobalError<AddLessonForm>();

  const onSubmit = (data: AddLessonForm) => {
    console.log(data);
    closeModal({ action: 'CONFIRM' });
  };

  // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
  useEffect(() => {
    const videoDuration = form.watch('video')?.duration;

    if (videoDuration) {
      form.setValue('video.duration.hour', videoDuration.hour);
      form.setValue('video.duration.minute', videoDuration.minute);
      form.setValue('video.duration.second', videoDuration.second);
    }
  }, [form.watch('video')]);

  return (
    <ModalWrapper
      onClose={() => closeModal({ action: 'CLOSE' })}
      icon={icon}
      title={title}
      subtitle={subtitle}
      actions={
        <>
          <Button variant="text" size="small" onClick={() => closeModal({ action: 'CLOSE' })}>
            {__('Cancel', 'tutor')}
          </Button>
          <Button variant="primary" size="small" onClick={form.handleSubmit(onSubmit)}>
            {__('Save', 'tutor')}
          </Button>
        </>
      }
    >
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
          <Controller
            name="video"
            control={form.control}
            render={(controllerProps) => (
              <FormVideoInput
                {...controllerProps}
                label={__('Video', 'tutor')}
                buttonText={__('Upload Video', 'tutor')}
                infoText={__('Supported file formats .mp4', 'tutor')}
                supportedFormats={['mp4']}
              />
            )}
          />
          <div css={styles.durationWrapper}>
            <span css={styles.additoinLabel}>{__('Video playback time', 'tutor')}</span>
            <div css={styles.duration}>
              <Controller
                name="video.duration.hour"
                control={form.control}
                render={(controllerProps) => (
                  <FormInputWithContent
                    {...controllerProps}
                    type="number"
                    content={<span css={styles.durationContent}>{__('hour', 'tutor')}</span>}
                    contentPosition="right"
                    placeholder="0"
                    showVerticalBar={false}
                  />
                )}
              />
              <Controller
                name="video.duration.minute"
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
                name="video.duration.second"
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
          </div>

          <Controller
            name="available_after_days"
            control={form.control}
            render={(controllerProps) => (
              <FormInput
                {...controllerProps}
                type="number"
                label={__('Available after days', 'tutor')}
                helpText={__('Set the number of days after which the lesson will be available', 'tutor')}
                placeholder="0"
              />
            )}
          />

          <Controller
            name="exercise_files"
            control={form.control}
            render={(controllerProps) => (
              <FormFileUploader
                {...controllerProps}
                label={__('Exercise Files', 'tutor')}
                buttonText={__('Upload Attachment', 'tutor')}
                selectMultiple
              />
            )}
          />

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
    </ModalWrapper>
  );
};

export default AddLessonModal;

const styles = {
  wrapper: css`
    width: 1217px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: 1fr 395px;
    height: 100%;
    padding-inline: ${spacing[32]};
  `,
  lessonInfo: css`
    padding-block: ${spacing[20]};
    padding-right: ${spacing[32]};
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
    padding-block: ${spacing[20]};
    padding-left: ${spacing[32]};
  `,
  durationWrapper: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[8]};
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
  additoinLabel: css`
    ${typography.body()}
    color: ${colorTokens.text.title};
  `,
  lessonPreview: css`
    padding: ${spacing[12]};
    border: 1px solid ${colorTokens.stroke.default};
    border-radius: ${borderRadius[8]};
  `,
};
