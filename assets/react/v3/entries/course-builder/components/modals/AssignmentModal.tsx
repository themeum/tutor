import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { Controller } from 'react-hook-form';

import Button from '@Atoms/Button';

import FormInput from '@Components/fields/FormInput';
import FormSelectInput from '@Components/fields/FormSelectInput';
import FormTextareaInput from '@Components/fields/FormTextareaInput';
import ModalWrapper from '@Components/modals/ModalWrapper';
import type { ModalProps } from '@Components/modals/Modal';
import FormInputWithContent from '@Components/fields/FormInputWithContent';
import FormFileUploader from '@Components/fields/FormFileUploader';
import type { Media } from '@Components/fields/FormImageInput';

import { borderRadius, colorTokens, spacing, zIndex } from '@Config/styles';
import { typography } from '@Config/typography';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';

import { useAssignmentDetailsQuery, useSaveAssignmentMutation, type ID } from '@CourseBuilderServices/curriculum';
import {
  usePrerequisiteCoursesQuery,
  type ContentDripType,
  type PrerequisiteCourses,
} from '@CourseBuilderServices/course';
import { convertAssignmentDataToPayload, getCourseId, isAddonEnabled } from '@CourseBuilderUtils/utils';
import { useEffect } from 'react';
import Show from '@Controls/Show';
import FormCoursePrerequisites from '@Components/fields/FormCoursePrerequisites';
import FormDateInput from '@Components/fields/FormDateInput';
import SVGIcon from '@Atoms/SVGIcon';

interface AssignmentModalProps extends ModalProps {
  assignmentId?: ID;
  topicId: ID;
  closeModal: (props?: { action: 'CONFIRM' | 'CLOSE' }) => void;
  contentDripType: ContentDripType;
}

type TimeLimitUnit = 'weeks' | 'days' | 'hours';

export interface AssignmentForm {
  title: string;
  summary: string;
  attachments: Media[];
  time_duration: {
    time: string;
    value: TimeLimitUnit;
  };
  total_mark: number;
  pass_mark: number;
  upload_files_limit: number;
  upload_file_size_limit: number;
  content_drip_settings: {
    unlock_date: string;
    after_xdays_of_enroll: string;
    prerequisites: PrerequisiteCourses[];
  };
}

const courseId = getCourseId();

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

const AssignmentModal = ({
  assignmentId = '',
  topicId,
  closeModal,
  icon,
  title,
  subtitle,
  contentDripType,
}: AssignmentModalProps) => {
  const isPrerequisiteAddonEnabled = isAddonEnabled('Tutor Prerequisites');
  const getAssignmentDetailsQuery = useAssignmentDetailsQuery(topicId, assignmentId);
  const saveAssignmentMutation = useSaveAssignmentMutation({ courseId, topicId, assignmentId });

  const { data: assignmentDetails } = getAssignmentDetailsQuery;

  const form = useFormWithGlobalError<AssignmentForm>({
    defaultValues: {
      title: '',
      summary: '',
      attachments: [],
      time_duration: {
        time: '0',
        value: 'weeks',
      },
      total_mark: 0,
      pass_mark: 0,
      upload_files_limit: 0,
      upload_file_size_limit: 0,
      content_drip_settings: {
        unlock_date: '',
        after_xdays_of_enroll: '',
        prerequisites: [],
      },
    },
    shouldFocusError: true,
  });

  const prerequisiteCourses = assignmentDetails?.content_drip_settings?.course_prerequisites
    ? assignmentDetails?.content_drip_settings?.course_prerequisites.map((item) => String(item.id))
    : [];

  const prerequisiteCoursesQuery = usePrerequisiteCoursesQuery(
    String(courseId) ? [String(courseId), ...prerequisiteCourses] : prerequisiteCourses,
    isPrerequisiteAddonEnabled && contentDripType === 'after_finishing_prerequisites'
  );

  // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
  useEffect(() => {
    if (assignmentDetails) {
      form.reset({
        title: assignmentDetails.post_title || '',
        summary: assignmentDetails.post_content || '',
        attachments: assignmentDetails.attachments || [],
        time_duration: {
          time: assignmentDetails.assignment_option.time_duration.time,
          value: (assignmentDetails.assignment_option.time_duration.value as TimeLimitUnit) || 'weeks',
        },
        total_mark: assignmentDetails.assignment_option.total_mark,
        pass_mark: assignmentDetails.assignment_option.pass_mark,
        upload_files_limit: assignmentDetails.assignment_option.upload_files_limit,
        upload_file_size_limit: assignmentDetails.assignment_option.upload_file_size_limit,
        content_drip_settings: {
          unlock_date: assignmentDetails.content_drip_settings.unlock_date || '',
          after_xdays_of_enroll: assignmentDetails.content_drip_settings.after_xdays_of_enroll || '',
          prerequisites: assignmentDetails.content_drip_settings.course_prerequisites || [],
        },
      });
    }
  }, [assignmentDetails]);

  useEffect(() => {
    form.setFocus('title');
  }, [form]);

  const onSubmit = async (data: AssignmentForm) => {
    const payload = convertAssignmentDataToPayload(data, assignmentId, topicId, contentDripType);
    const response = await saveAssignmentMutation.mutateAsync(payload);

    if (response.status_code === 200 || response.status_code === 201) {
      closeModal({ action: 'CONFIRM' });
    }
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
          <Button
            loading={saveAssignmentMutation.isPending}
            variant="primary"
            size="small"
            onClick={form.handleSubmit(onSubmit)}
          >
            {assignmentId ? __('Update', 'tutor') : __('Save', 'tutor')}
          </Button>
        </>
      }
    >
      <div css={styles.wrapper}>
        <div>
          <div css={styles.assignmentInfo}>
            <Controller
              name="title"
              control={form.control}
              rules={{
                required: __('Assignment title is required', 'tutor'),
              }}
              render={(controllerProps) => (
                <FormInput
                  {...controllerProps}
                  label={__('Assignment Title', 'tutor')}
                  placeholder={__('Enter Assignment Title', 'tutor')}
                  maxLimit={245}
                  isClearable
                  selectOnFocus
                />
              )}
            />

            <Controller
              name="summary"
              control={form.control}
              rules={{
                required: __('Assignment summary is required', 'tutor'),
              }}
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

          <Show when={isAddonEnabled('Content Drip')}>
            <Show when={contentDripType === 'specific_days'}>
              <Controller
                name="content_drip_settings.after_xdays_of_enroll"
                control={form.control}
                render={(controllerProps) => (
                  <FormInput
                    {...controllerProps}
                    type="number"
                    label={
                      <div css={styles.contentDripLabel}>
                        <SVGIcon name="contentDrip" height={24} width={24} />
                        {__('Available after days', 'tutor')}
                      </div>
                    }
                    helpText={__('This lesson will be available after the given number of days.', 'tutor')}
                    placeholder="0"
                    selectOnFocus
                  />
                )}
              />
            </Show>

            <Show when={contentDripType === 'unlock_by_date'}>
              <Controller
                name="content_drip_settings.unlock_date"
                control={form.control}
                render={(controllerProps) => (
                  <FormDateInput
                    {...controllerProps}
                    label={
                      <div css={styles.contentDripLabel}>
                        <SVGIcon name="contentDrip" height={24} width={24} />
                        {__('Unlock Date', 'tutor')}
                      </div>
                    }
                    helpText={__(
                      'This lesson will be available from the given date. Leave empty to make it available immediately.',
                      'tutor'
                    )}
                  />
                )}
              />
            </Show>

            <Show when={contentDripType === 'after_finishing_prerequisites'}>
              <Controller
                name="content_drip_settings.prerequisites"
                control={form.control}
                render={(controllerProps) => (
                  <FormCoursePrerequisites
                    {...controllerProps}
                    label={
                      <div css={styles.contentDripLabel}>
                        <SVGIcon name="contentDrip" height={24} width={24} />
                        {__('Prerequisites', 'tutor')}
                      </div>
                    }
                    placeholder={__('Select Prerequisite', 'tutor')}
                    options={prerequisiteCoursesQuery.data || []}
                    helpText={__('Select items that should be complete before this item', 'tutor')}
                  />
                )}
              />
            </Show>
          </Show>

          <div css={styles.timeLimit}>
            <Controller
              name="time_duration.time"
              control={form.control}
              render={(controllerProps) => (
                <FormInput
                  {...controllerProps}
                  type="number"
                  label={__('Time limit', 'tutor')}
                  placeholder="0"
                  dataAttribute="data-time-limit"
                  selectOnFocus
                />
              )}
            />

            <Controller
              name="time_duration.value"
              control={form.control}
              render={(controllerProps) => (
                <FormSelectInput
                  {...controllerProps}
                  options={timeLimitOptions}
                  removeOptionsMinWidth
                  dataAttribute="data-time-limit-unit"
                />
              )}
            />
          </div>

          <Controller
            name="total_mark"
            control={form.control}
            render={(controllerProps) => (
              <FormInput
                {...controllerProps}
                type="number"
                label={__('Total points', 'tutor')}
                placeholder="0"
                helpText={__('Maximum points a student can score', 'tutor')}
                selectOnFocus
              />
            )}
          />

          <Controller
            name="pass_mark"
            control={form.control}
            render={(controllerProps) => (
              <FormInput
                {...controllerProps}
                type="number"
                label={__('Minimum pass points', 'tutor')}
                placeholder="0"
                helpText={__('Minimum points required for the student to pass this assignment', 'tutor')}
                selectOnFocus
              />
            )}
          />

          <Controller
            name="upload_files_limit"
            control={form.control}
            render={(controllerProps) => (
              <FormInput
                {...controllerProps}
                placeholder="0"
                type="number"
                label={__('File upload Limit', 'tutor')}
                helpText={__(
                  'Define the number of files that a student can upload in this assignment. Input 0 to disable the option to upload.',
                  'tutor'
                )}
                selectOnFocus
              />
            )}
          />

          <Controller
            name="upload_file_size_limit"
            control={form.control}
            render={(controllerProps) => (
              <FormInputWithContent
                {...controllerProps}
                type="number"
                label={__('Maximum file size limit', 'tutor')}
                placeholder="0"
                content={__('MB', 'tutor')}
                contentPosition="right"
                helpText={__('Define maximum file size attachment in MB', 'tutor')}
              />
            )}
          />
        </div>
      </div>
    </ModalWrapper>
  );
};

export default AssignmentModal;

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
  contentDripLabel: css`
    display: flex;
    align-items: center;

    svg {
      margin-right: ${spacing[4]};
      color: ${colorTokens.icon.success};
    }
  `,
};
