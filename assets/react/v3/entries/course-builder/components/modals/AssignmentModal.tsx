import { css } from '@emotion/react';
import { useQueryClient } from '@tanstack/react-query';
import { __ } from '@wordpress/i18n';
import { useEffect } from 'react';
import { Controller } from 'react-hook-form';

import Button from '@Atoms/Button';
import { LoadingOverlay } from '@Atoms/LoadingSpinner';
import SVGIcon from '@Atoms/SVGIcon';

import FormDateInput from '@Components/fields/FormDateInput';
import FormFileUploader from '@Components/fields/FormFileUploader';
import type { Media } from '@Components/fields/FormImageInput';
import FormInput from '@Components/fields/FormInput';
import FormInputWithContent from '@Components/fields/FormInputWithContent';
import FormSelectInput from '@Components/fields/FormSelectInput';
import FormTopicPrerequisites from '@Components/fields/FormTopicPrerequisites';
import FormWPEditor from '@Components/fields/FormWPEditor';
import type { ModalProps } from '@Components/modals/Modal';
import ModalWrapper from '@Components/modals/ModalWrapper';

import { Addons } from '@Config/constants';
import { borderRadius, colorTokens, spacing, zIndex } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import type { ContentDripType } from '@CourseBuilderServices/course';
import {
  type CourseTopic,
  type ID,
  useAssignmentDetailsQuery,
  useSaveAssignmentMutation,
} from '@CourseBuilderServices/curriculum';
import { convertAssignmentDataToPayload, getCourseId, isAddonEnabled } from '@CourseBuilderUtils/utils';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
import { maxLimitRule } from '@Utils/validation';

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
    value: string;
    time: TimeLimitUnit;
  };
  total_mark: number;
  pass_mark: number;
  upload_files_limit: number;
  upload_file_size_limit: number;
  content_drip_settings: {
    unlock_date: string;
    after_xdays_of_enroll: string;
    prerequisites: ID[];
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
  const getAssignmentDetailsQuery = useAssignmentDetailsQuery(assignmentId, topicId);
  const saveAssignmentMutation = useSaveAssignmentMutation(courseId);
  const queryClient = useQueryClient();

  const { data: assignmentDetails } = getAssignmentDetailsQuery;
  const topics = queryClient.getQueryData(['Topic', courseId]) as CourseTopic[];

  const form = useFormWithGlobalError<AssignmentForm>({
    defaultValues: {
      title: '',
      summary: '',
      attachments: [],
      time_duration: {
        value: '0',
        time: 'weeks',
      },
      total_mark: 10,
      pass_mark: 5,
      upload_files_limit: 1,
      upload_file_size_limit: 2,
      content_drip_settings: {
        unlock_date: '',
        after_xdays_of_enroll: '',
        prerequisites: [],
      },
    },
    shouldFocusError: true,
    mode: 'onChange',
  });

  // Need to do RND about WPEditor
  // const isFormDirty = false; /*form.formState.isDirty;*/

  // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
  useEffect(() => {
    if (assignmentDetails) {
      form.reset(
        {
          title: assignmentDetails.post_title || '',
          summary: assignmentDetails.post_content || '',
          attachments: assignmentDetails.attachments || [],
          time_duration: {
            value: assignmentDetails.assignment_option.time_duration.value || '0',
            time: (assignmentDetails.assignment_option.time_duration.time as TimeLimitUnit) || 'weeks',
          },
          total_mark: assignmentDetails.assignment_option.total_mark || 10,
          pass_mark: assignmentDetails.assignment_option.pass_mark || 5,
          upload_files_limit: assignmentDetails.assignment_option.upload_files_limit || 1,
          upload_file_size_limit: assignmentDetails.assignment_option.upload_file_size_limit || 2,
          content_drip_settings: {
            unlock_date: assignmentDetails.content_drip_settings.unlock_date || '',
            after_xdays_of_enroll: assignmentDetails.content_drip_settings.after_xdays_of_enroll || '',
            prerequisites: assignmentDetails.content_drip_settings.prerequisites || [],
          },
        },
        {
          keepDirty: false,
        },
      );
    }

    const timeoutId = setTimeout(() => {
      form.setFocus('title');
    }, 0);

    return () => {
      clearTimeout(timeoutId);
    };
  }, [assignmentDetails]);

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
          <Button
            variant="text"
            size="small"
            onClick={() => {
              closeModal({ action: 'CLOSE' });
            }}
          >
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
        <Show when={!getAssignmentDetailsQuery.isLoading} fallback={<LoadingOverlay />}>
          <div>
            <div css={styles.assignmentInfo}>
              <Controller
                name="title"
                control={form.control}
                rules={{
                  required: __('Assignment title is required', 'tutor'),
                  ...maxLimitRule(255),
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
                render={(controllerProps) => (
                  <FormWPEditor
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

            <Show when={isAddonEnabled(Addons.CONTENT_DRIP)}>
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
                      placeholder={__('Select Unlock Date', 'tutor')}
                      helpText={__(
                        'This lesson will be available from the given date. Leave empty to make it available immediately.',
                        'tutor',
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
                    <FormTopicPrerequisites
                      {...controllerProps}
                      label={
                        <div css={styles.contentDripLabel}>
                          <SVGIcon name="contentDrip" height={24} width={24} />
                          {__('Prerequisites', 'tutor')}
                        </div>
                      }
                      placeholder={__('Select Prerequisite', 'tutor')}
                      options={
                        topics.reduce((topics, topic) => {
                          if (topic.id === topicId) {
                            topics.push({
                              ...topic,
                              contents: topic.contents.filter((content) => content.ID !== assignmentId),
                            });
                          } else {
                            topics.push(topic);
                          }
                          return topics;
                        }, [] as CourseTopic[]) || []
                      }
                      isSearchable
                      helpText={__('Select items that should be complete before this item', 'tutor')}
                    />
                  )}
                />
              </Show>
            </Show>

            <div css={styles.timeLimit}>
              <Controller
                name="time_duration.value"
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
                name="time_duration.time"
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
              rules={{
                validate: (value) => {
                  if (value > form.getValues('total_mark')) {
                    return __('Pass mark cannot be greater than total mark', 'tutor');
                  }
                  return true;
                },
              }}
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
                    'tutor',
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
        </Show>
      </div>
    </ModalWrapper>
  );
};

export default AssignmentModal;

const styles = {
  wrapper: css`
    width: 1070px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: 1fr 338px;
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
    z-index: ${zIndex.positive}; // this is the hack to make the sticky work and not overlap with the editor
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
