import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { Controller } from 'react-hook-form';

import FormInput from '@Components/fields/FormInput';
import FormSelectInput from '@Components/fields/FormSelectInput';
import FormTextareaInput from '@Components/fields/FormTextareaInput';

import Button from '@Atoms/Button';

import { borderRadius, colorTokens, spacing, zIndex } from '@Config/styles';
import { typography } from '@Config/typography';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';

import ModalWrapper from '@Components/modals/ModalWrapper';
import type { ModalProps } from '@Components/modals/Modal';
import FormInputWithContent from '@Components/fields/FormInputWithContent';
import type { UploadedFile } from '@Components/fields/FormFileUploader';
import FormFileUploader from '@Components/fields/FormFileUploader';

interface AddLessonModalProps extends ModalProps {
  closeModal: (props?: { action: 'CONFIRM' | 'CLOSE' }) => void;
}

type TimeLimitUnit = 'weeks' | 'days' | 'hours';

interface AddLessonForm {
  assignment_title: string;
  summary: string;
  attachments: UploadedFile[] | null;
  time_limit: number;
  time_limit_unit: TimeLimitUnit;
  total_points: number;
  minimum_pass_points: number;
  fileupload_limit: number;
  maximum_filesize_limit: number;
}

const AddAssignmentModal = ({ closeModal, icon, title, subtitle }: AddLessonModalProps) => {
  const form = useFormWithGlobalError<AddLessonForm>({
    defaultValues: {
      assignment_title: '',
      summary: '',
      attachments: null,
      time_limit: 0,
      time_limit_unit: 'weeks',
      total_points: 0,
      minimum_pass_points: 0,
      fileupload_limit: 0,
      maximum_filesize_limit: 0,
    },
  });

  const timeLimitOptions: {
    label: string;
    value: TimeLimitUnit;
  }[] = [
    {
      label: __('Weeks', 'tutor'),
      value: 'weeks',
    },
    {
      label: __('Days', 'tutor'),
      value: 'days',
    },
    {
      label: __('Hours', 'tutor'),
      value: 'hours',
    },
  ];

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
          <div css={styles.assignmentInfo}>
            <Controller
              name="assignment_title"
              control={form.control}
              render={(controllerProps) => (
                <FormInput
                  {...controllerProps}
                  label={__('Assignment Title', 'tutor')}
                  placeholder={__('Enter Assignment Title', 'tutor')}
                  maxLimit={245}
                  isClearable
                />
              )}
            />

            <Controller
              name="summary"
              control={form.control}
              render={(controllerProps) => (
                <FormTextareaInput
                  {...controllerProps}
                  label={__('Summary', 'tutor')}
                  placeholder={__('Enter Assignment Summary', 'tutor')}
                />
              )}
            />
          </div>
        </div>

        <div css={styles.rightPanel}>
          <Controller
            name="attachments"
            control={form.control}
            render={(controllerProps) => (
              <FormFileUploader
                {...controllerProps}
                label={__('Attachments', 'tutor')}
                buttonText={__('Upload Attachment', 'tutor')}
                selectMultiple
              />
            )}
          />

          <div css={styles.timeLimit}>
            <Controller
              name="time_limit"
              control={form.control}
              render={(controllerProps) => (
                <FormInput
                  {...controllerProps}
                  type="number"
                  label={__('Time limit', 'tutor')}
                  placeholder="0"
                  helpText={__('Set the time limit for the course. Set 0 for unlimited time', 'tutor')}
                  removeBorder
                  dataAttribute="data-time-limit"
                />
              )}
            />

            <Controller
              name="time_limit_unit"
              control={form.control}
              render={(controllerProps) => (
                <FormSelectInput
                  {...controllerProps}
                  options={timeLimitOptions}
                  removeBorder
                  removeOptionsMinWidth
                  dataAttribute="data-time-limit-unit"
                />
              )}
            />
          </div>

          <Controller
            name="total_points"
            control={form.control}
            render={(controllerProps) => (
              <FormInput
                {...controllerProps}
                type="number"
                label={__('Total points', 'tutor')}
                placeholder="0"
                helpText={__('Set the total points for the assignment', 'tutor')}
              />
            )}
          />

          <Controller
            name="minimum_pass_points"
            control={form.control}
            render={(controllerProps) => (
              <FormInput
                {...controllerProps}
                type="number"
                label={__('Minimum pass points', 'tutor')}
                placeholder="0"
                helpText={__('Set the minimum pass points for the assignment', 'tutor')}
              />
            )}
          />

          <Controller
            name="fileupload_limit"
            control={form.control}
            render={(controllerProps) => (
              <FormInput
                {...controllerProps}
                placeholder="0"
                type="number"
                label={__('File upload Limit', 'tutor')}
                helpText={__('Set the file upload limit for the assignment', 'tutor')}
              />
            )}
          />

          <Controller
            name="maximum_filesize_limit"
            control={form.control}
            render={(controllerProps) => (
              <FormInputWithContent
                {...controllerProps}
                type="number"
                label={__('Maximum file size limit', 'tutor')}
                placeholder="0"
                content={__('MB', 'tutor')}
                contentPosition="right"
                helpText={__('Set the maximum file size limit for the assignment', 'tutor')}
              />
            )}
          />
        </div>
      </div>
    </ModalWrapper>
  );
};

export default AddAssignmentModal;

const styles = {
  wrapper: css`
    width: 1217px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: 1fr 395px;
    height: 100%;
    padding-inline: ${spacing[32]};
  `,
  assignmentInfo: css`
    padding-block: ${spacing[24]};
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
    padding-block: ${spacing[24]};
    padding-left: ${spacing[32]};
  `,
  timeLimit: css`
    display: grid;
    align-items: end;
    grid-template-columns: 1fr 100px;

    & input {
      border: 1px solid ${colorTokens.stroke.default};

      &[data-time-limit] {
        border-radius: ${borderRadius[6]} 0 0 ${borderRadius[6]};
        border-right: none;

        &:focus {
          border-right: 1px solid ${colorTokens.stroke.default};
          z-index: ${zIndex.positive};
        }
      }
      &[data-time-limit-unit] {
        border-radius: 0 ${borderRadius[6]} ${borderRadius[6]} 0;
      }
    }
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
};
