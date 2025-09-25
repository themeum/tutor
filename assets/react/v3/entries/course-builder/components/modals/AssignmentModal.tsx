import { css } from '@emotion/react';
import { useQueryClient } from '@tanstack/react-query';
import { __ } from '@wordpress/i18n';
import { useEffect } from 'react';
import { Controller } from 'react-hook-form';

import Button from '@TutorShared/atoms/Button';
import { LoadingOverlay } from '@TutorShared/atoms/LoadingSpinner';
import SVGIcon from '@TutorShared/atoms/SVGIcon';

import FormDateInput from '@TutorShared/components/fields/FormDateInput';
import FormFileUploader from '@TutorShared/components/fields/FormFileUploader';
import FormInput from '@TutorShared/components/fields/FormInput';
import FormInputWithContent from '@TutorShared/components/fields/FormInputWithContent';
import FormSelectInput from '@TutorShared/components/fields/FormSelectInput';
import FormTopicPrerequisites from '@TutorShared/components/fields/FormTopicPrerequisites';
import FormWPEditor from '@TutorShared/components/fields/FormWPEditor';
import type { ModalProps } from '@TutorShared/components/modals/Modal';
import ModalWrapper from '@TutorShared/components/modals/ModalWrapper';

import CourseBuilderInjectionSlot from '@CourseBuilderComponents/CourseBuilderSlot';
import { useCourseBuilderSlot } from '@CourseBuilderContexts/CourseBuilderSlotContext';
import { type ContentDripType } from '@CourseBuilderServices/course';
import {
  convertAssignmentDataToPayload,
  useAssignmentDetailsQuery,
  useSaveAssignmentMutation,
  type Assignment,
  type CourseTopic,
} from '@CourseBuilderServices/curriculum';
import { getCourseId } from '@CourseBuilderUtils/utils';
import FormSwitch from '@TutorShared/components/fields/FormSwitch';
import { tutorConfig } from '@TutorShared/config/config';
import { Addons, CURRENT_VIEWPORT } from '@TutorShared/config/constants';
import { borderRadius, Breakpoint, colorTokens, spacing, zIndex } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import Show from '@TutorShared/controls/Show';
import { useFormWithGlobalError } from '@TutorShared/hooks/useFormWithGlobalError';
import { type WPMedia } from '@TutorShared/hooks/useWpMedia';
import { type ID } from '@TutorShared/utils/types';
import { findSlotFields, isAddonEnabled, normalizeLineEndings } from '@TutorShared/utils/util';
import { maxLimitRule, requiredRule } from '@TutorShared/utils/validation';

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
  attachments: WPMedia[];
  time_duration: {
    value: string;
    time: TimeLimitUnit;
  };
  deadline_from_start: boolean;
  total_mark: number;
  pass_mark: number;
  upload_files_limit: number;
  upload_file_size_limit: number;
  is_retry_allowed: boolean;
  attempts_allowed: number;
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
  const { fields } = useCourseBuilderSlot();
  const isTutorPro = !!tutorConfig.tutor_pro_url;
  const isOpenAiEnabled = tutorConfig.settings?.chatgpt_enable === 'on';
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
      deadline_from_start: false,
      total_mark: 10,
      pass_mark: 5,
      upload_files_limit: 1,
      upload_file_size_limit: 2,
      is_retry_allowed: true,
      attempts_allowed: 5,
      content_drip_settings: {
        unlock_date: '',
        after_xdays_of_enroll: '',
        prerequisites: [],
      },
    },
    shouldFocusError: true,
    mode: 'onChange',
  });

  const isFormDirty = form.formState.dirtyFields && Object.keys(form.formState.dirtyFields).length > 0;
  const isRetryAllowed = form.watch('is_retry_allowed');

  useEffect(() => {
    if (assignmentDetails) {
      form.reset(
        {
          title: assignmentDetails.post_title || '',
          summary: normalizeLineEndings(assignmentDetails.post_content) || '',
          attachments: assignmentDetails.attachments || [],
          time_duration: {
            value: assignmentDetails.assignment_option.time_duration.value || '0',
            time: (assignmentDetails.assignment_option.time_duration.time as TimeLimitUnit) || 'weeks',
          },
          deadline_from_start: assignmentDetails.assignment_option.deadline_from_start === '1' ? true : false,
          total_mark: assignmentDetails.assignment_option.total_mark || 10,
          pass_mark: assignmentDetails.assignment_option.pass_mark || 5,
          upload_files_limit: assignmentDetails.assignment_option.upload_files_limit || 1,
          upload_file_size_limit: assignmentDetails.assignment_option.upload_file_size_limit || 2,
          is_retry_allowed: assignmentDetails.assignment_option.is_retry_allowed === '1' ? true : false,
          attempts_allowed: assignmentDetails.assignment_option.attempts_allowed || 10,
          content_drip_settings: {
            unlock_date: assignmentDetails?.content_drip_settings?.unlock_date || '',
            after_xdays_of_enroll: assignmentDetails?.content_drip_settings?.after_xdays_of_enroll || '',
            prerequisites: assignmentDetails?.content_drip_settings?.prerequisites || [],
          },
          ...Object.fromEntries(
            findSlotFields({ fields: fields.Curriculum.Lesson }).map((key) => [
              key,
              assignmentDetails[key as keyof Assignment],
            ]),
          ),
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
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [assignmentDetails]);

  const onSubmit = async (data: AssignmentForm) => {
    const payload = convertAssignmentDataToPayload(
      data,
      assignmentId,
      topicId,
      contentDripType,
      findSlotFields({ fields: fields.Curriculum.Assignment }),
    );
    const response = await saveAssignmentMutation.mutateAsync(payload);

    if (response.status_code === 200 || response.status_code === 201) {
      closeModal({ action: 'CONFIRM' });
    }
  };

  return (
    <ModalWrapper
      onClose={() => closeModal({ action: 'CLOSE' })}
      icon={isFormDirty ? <SVGIcon name="warning" width={24} height={24} /> : icon}
      title={isFormDirty ? (CURRENT_VIEWPORT.isAboveDesktop ? __('Unsaved Changes', 'tutor') : '') : title}
      subtitle={CURRENT_VIEWPORT.isAboveSmallMobile ? subtitle : ''}
      maxWidth={1070}
      actions={
        isFormDirty && (
          <>
            <Button
              variant="text"
              size="small"
              onClick={() => {
                if (assignmentId) {
                  form.reset();
                } else {
                  closeModal({ action: 'CLOSE' });
                }
              }}
            >
              {assignmentId ? __('Discard Changes', 'tutor') : __('Cancel', 'tutor')}
            </Button>
            <Button
              data-cy="save-assignment"
              loading={saveAssignmentMutation.isPending}
              variant="primary"
              size="small"
              onClick={form.handleSubmit(onSubmit)}
            >
              {assignmentId ? __('Update', 'tutor') : __('Save', 'tutor')}
            </Button>
          </>
        )
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
                    label={__('Title', 'tutor')}
                    placeholder={__('Enter Assignment Title', 'tutor')}
                    generateWithAi={!isTutorPro || isOpenAiEnabled}
                    isClearable
                  />
                )}
              />

              <Controller
                name="summary"
                control={form.control}
                render={(controllerProps) => (
                  <FormWPEditor
                    {...controllerProps}
                    label={__('Content', 'tutor')}
                    placeholder={__('Enter Assignment Content', 'tutor')}
                    generateWithAi={!isTutorPro || isOpenAiEnabled}
                  />
                )}
              />

              <CourseBuilderInjectionSlot section="Curriculum.Assignment.after_description" form={form} />
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
                      helpText={__('This assignment will be available after the given number of days.', 'tutor')}
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
                      helpText={
                        // prettier-ignore
                        __('This assignment will be available from the given date. Leave empty to make it available immediately.', 'tutor')
                      }
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
                          if (String(topic.id) === String(topicId)) {
                            topics.push({
                              ...topic,
                              contents: topic.contents.filter((content) => String(content.ID) !== String(assignmentId)),
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
                    label={__('Time Limit', 'tutor')}
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
              name="deadline_from_start"
              control={form.control}
              render={(controllerProps) => (
                <FormSwitch
                  {...controllerProps}
                  label={__('Set Deadline From Assignment Start Time', 'tutor')}
                  helpText={__(
                    'Each student will get their own deadline based on when they start the assignment.',
                    'tutor',
                  )}
                />
              )}
            />

            <Controller
              name="total_mark"
              control={form.control}
              render={(controllerProps) => (
                <FormInput
                  {...controllerProps}
                  type="number"
                  label={__('Total Points', 'tutor')}
                  placeholder="0"
                  selectOnFocus
                />
              )}
            />

            <Controller
              name="pass_mark"
              control={form.control}
              rules={{
                validate: (value) => {
                  if (Number(value) > Number(form.getValues('total_mark'))) {
                    return __('Pass mark cannot be greater than total mark', 'tutor');
                  }
                  return true;
                },
              }}
              render={(controllerProps) => (
                <FormInput
                  {...controllerProps}
                  type="number"
                  label={__('Minimum Pass Points', 'tutor')}
                  placeholder="0"
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
                  label={__('File Upload Limit', 'tutor')}
                  helpText={
                    // prettier-ignore
                    __('Define the number of files that a student can upload in this assignment. Input 0 to disable the option to upload.','tutor')
                  }
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
                  label={__('Maximum File Size Limit', 'tutor')}
                  placeholder="0"
                  content={__('MB', 'tutor')}
                  contentPosition="right"
                />
              )}
            />

            <Controller
              name="is_retry_allowed"
              control={form.control}
              render={(controllerProps) => (
                <FormSwitch {...controllerProps} label={__('Allow Assignment Resubmission', 'tutor')} />
              )}
            />

            <Show when={isRetryAllowed}>
              <Controller
                name="attempts_allowed"
                control={form.control}
                rules={{
                  ...requiredRule(),
                  validate: (value) => {
                    if (value >= 1 && value <= 20) {
                      return true;
                    }
                    return __('Allowed attempts must be between 1 and 20', 'tutor');
                  },
                }}
                render={(controllerProps) => (
                  <FormInput
                    {...controllerProps}
                    type="number"
                    label={__('Maximum Resubmission Attempts', 'tutor')}
                    helpText={__('Set how many times students can resubmit even after the deadline.', 'tutor')}
                    selectOnFocus
                  />
                )}
              />
            </Show>

            <CourseBuilderInjectionSlot section="Curriculum.Assignment.bottom_of_sidebar" form={form} />
          </div>
        </Show>
      </div>
    </ModalWrapper>
  );
};

export default AssignmentModal;

const styles = {
  wrapper: css`
    margin: 0 auto;
    display: grid;
    grid-template-columns: 1fr 338px;
    width: 100%;
    height: 100%;
    padding-inline: ${spacing[32]};

    ${Breakpoint.smallTablet} {
      grid-template-columns: 1fr;
      padding-inline: ${spacing[24]};
    }

    ${Breakpoint.mobile} {
      padding-inline: ${spacing[16]};
    }
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

    ${Breakpoint.smallTablet} {
      position: unset;
      padding-right: 0;
    }
  `,
  rightPanel: css`
    border-left: 1px solid ${colorTokens.stroke.divider};
    display: flex;
    flex-direction: column;
    gap: ${spacing[16]};
    padding-block: ${spacing[24]};
    padding-left: ${spacing[32]};

    ${Breakpoint.smallTablet} {
      border-left: none;
      padding-left: 0;
    }
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
