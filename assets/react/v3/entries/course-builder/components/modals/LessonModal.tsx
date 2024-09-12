import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useEffect } from 'react';
import { Controller } from 'react-hook-form';
import { useQueryClient } from '@tanstack/react-query';

import Button from '@Atoms/Button';
import { LoadingOverlay } from '@Atoms/LoadingSpinner';
import SVGIcon from '@Atoms/SVGIcon';

import FormDateInput from '@Components/fields/FormDateInput';
import FormFileUploader from '@Components/fields/FormFileUploader';
import FormImageInput, { type Media } from '@Components/fields/FormImageInput';
import FormInput from '@Components/fields/FormInput';
import FormInputWithContent from '@Components/fields/FormInputWithContent';
import FormSwitch from '@Components/fields/FormSwitch';
import FormVideoInput, { type CourseVideo } from '@Components/fields/FormVideoInput';
import FormWPEditor from '@Components/fields/FormWPEditor';
import type { ModalProps } from '@Components/modals/Modal';
import ModalWrapper from '@Components/modals/ModalWrapper';

import FormTopicPrerequisites from '@Components/fields/FormTopicPrerequisites';
import { tutorConfig } from '@Config/config';
import { Addons } from '@Config/constants';
import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import type { ContentDripType } from '@CourseBuilderServices/course';
import {
  type CourseTopic,
  type ID,
  useLessonDetailsQuery,
  useSaveLessonMutation,
} from '@CourseBuilderServices/curriculum';
import { convertLessonDataToPayload, getCourseId, isAddonEnabled } from '@CourseBuilderUtils/utils';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
import { maxLimitRule } from '@Utils/validation';

interface LessonModalProps extends ModalProps {
  lessonId?: ID;
  topicId: ID;
  closeModal: (props?: { action: 'CONFIRM' | 'CLOSE' }) => void;
  contentDripType: ContentDripType;
}

export interface LessonForm {
  title: string;
  description: string;
  thumbnail: Media | null;
  tutor_attachments: Media[];
  lesson_preview: boolean;
  video: CourseVideo | null;
  duration: {
    hour: number;
    minute: number;
    second: number;
  };
  content_drip_settings: {
    unlock_date: string;
    after_xdays_of_enroll: string;
    prerequisites: ID[];
  };
}

const courseId = getCourseId();

const LessonModal = ({
  lessonId = '',
  topicId,
  closeModal,
  icon,
  title,
  subtitle,
  contentDripType,
}: LessonModalProps) => {
  const isTutorPro = !!tutorConfig.tutor_pro_url;
  const getLessonDetailsQuery = useLessonDetailsQuery(lessonId, topicId);
  const saveLessonMutation = useSaveLessonMutation(courseId);
  const queryClient = useQueryClient();

  const { data: lessonDetails } = getLessonDetailsQuery;
  const topics = queryClient.getQueryData(['Topic', courseId]) as CourseTopic[];

  const form = useFormWithGlobalError<LessonForm>({
    defaultValues: {
      title: '',
      description: '',
      thumbnail: null,
      tutor_attachments: [],
      lesson_preview: false,
      video: null,
      duration: {
        hour: 0,
        minute: 0,
        second: 0,
      },
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
    if (lessonDetails) {
      form.reset({
        title: lessonDetails.post_title || '',
        description: lessonDetails.post_content || '',
        thumbnail: {
          id: lessonDetails.thumbnail_id ? Number(lessonDetails.thumbnail_id) : 0,
          title: '',
          url: lessonDetails.thumbnail || '',
        },
        tutor_attachments: lessonDetails.attachments || [],
        lesson_preview: lessonDetails.is_preview || false,
        video: lessonDetails.video || null,
        duration: {
          hour: lessonDetails.video.runtime?.hours || 0,
          minute: lessonDetails.video.runtime?.minutes || 0,
          second: lessonDetails.video.runtime?.seconds || 0,
        },
        content_drip_settings: {
          unlock_date: lessonDetails.content_drip_settings?.unlock_date || '',
          after_xdays_of_enroll: lessonDetails.content_drip_settings?.after_xdays_of_enroll || '',
          prerequisites: lessonDetails.content_drip_settings?.prerequisites || [],
        },
      });
    }

    const timeoutId = setTimeout(() => {
      form.setFocus('title');
    }, 0);

    return () => {
      clearTimeout(timeoutId);
    };
  }, [lessonDetails]);

  const onSubmit = async (data: LessonForm) => {
    const payload = convertLessonDataToPayload(data, lessonId, topicId, contentDripType);
    const response = await saveLessonMutation.mutateAsync(payload);

    if (response.data) {
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
            loading={saveLessonMutation.isPending}
            variant="primary"
            size="small"
            onClick={form.handleSubmit(onSubmit)}
          >
            {lessonId ? __('Update', 'tutor') : __('Save', 'tutor')}
          </Button>
        </>
      }
    >
      <div css={styles.wrapper}>
        <Show when={!getLessonDetailsQuery.isLoading} fallback={<LoadingOverlay />}>
          <div>
            <div css={styles.lessonInfo}>
              <Controller
                name="title"
                control={form.control}
                rules={{
                  required: __('Lesson Name is required', 'tutor'),
                  ...maxLimitRule(255),
                }}
                render={(controllerProps) => (
                  <FormInput
                    {...controllerProps}
                    label={__('Lesson Name', 'tutor')}
                    placeholder={__('Enter Lesson Name', 'tutor')}
                    helpText={__('Lesson titles are displayed publicly wherever required.', 'tutor')}
                    maxLimit={245}
                    selectOnFocus
                    isClearable
                  />
                )}
              />
              <Controller
                name="description"
                control={form.control}
                rules={{
                  required: __('Description is required', 'tutor'),
                }}
                render={(controllerProps) => (
                  <FormWPEditor
                    {...controllerProps}
                    label={__('Description', 'tutor')}
                    placeholder={__('Enter Lesson Description', 'tutor')}
                    helpText={__(
                      'The idea of a summary is a short text to prepare students for the activities within the topic or week. The text is shown on the course page under the topic name.',
                      'tutor',
                    )}
                  />
                )}
              />
            </div>
          </div>

          <div css={styles.rightPanel}>
            <Controller
              name="thumbnail"
              control={form.control}
              render={(controllerProps) => (
                <FormImageInput
                  {...controllerProps}
                  label={__('Featured Image', 'tutor')}
                  buttonText={__('Upload Featured Image', 'tutor')}
                  infoText={__('Standard Size: 800x450 pixels', 'tutor')}
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
                  onGetDuration={(duration) => {
                    form.setValue('duration.hour', duration.hours);
                    form.setValue('duration.minute', duration.minutes);
                    form.setValue('duration.second', duration.seconds);
                  }}
                />
              )}
            />
            <div css={styles.durationWrapper}>
              <span css={styles.additionLabel}>{__('Video playback time', 'tutor')}</span>
              <div css={styles.duration}>
                <Controller
                  name="duration.hour"
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
                  name="duration.minute"
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
                  name="duration.second"
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
                              contents: topic.contents.filter((content) => content.ID !== lessonId),
                            });
                          } else {
                            topics.push(topic);
                          }
                          return topics;
                        }, [] as CourseTopic[]) ||
                        [] ||
                        []
                      }
                      isSearchable
                      helpText={__('Select items that should be complete before this item', 'tutor')}
                    />
                  )}
                />
              </Show>
            </Show>

            <Controller
              name="tutor_attachments"
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
                    disabled={!isTutorPro || !isAddonEnabled(Addons.TUTOR_COURSE_PREVIEW)}
                    label={
                      <div css={styles.previewLabel}>
                        {__('Lesson Preview', 'tutor')}
                        {!isTutorPro && <SVGIcon name="crown" width={24} height={24} />}
                      </div>
                    }
                    helpText={
                      isTutorPro && isAddonEnabled(Addons.TUTOR_COURSE_PREVIEW)
                        ? __('If checked, any users/guest can view this lesson without enroll course', 'tutor')
                        : ''
                    }
                  />
                )}
              />
              <Show when={form.watch('lesson_preview')}>
                <div css={styles.previewInfo}>
                  {__(
                    'Course preview is on, from now on any users/guest can view this lesson without enrolling the course.',
                    'tutor',
                  )}
                </div>
              </Show>
            </div>
          </div>
        </Show>
      </div>
    </ModalWrapper>
  );
};

export default LessonModal;

const styles = {
  wrapper: css`
    width: 1070px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: 1fr 338px;
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
  additionLabel: css`
    ${typography.body()}
    color: ${colorTokens.text.title};
  `,
  lessonPreview: css`
    background-color: ${colorTokens.background.white};
    padding: ${spacing[12]};
    border: 1px solid ${colorTokens.stroke.default};
    border-radius: ${borderRadius[8]};
  `,
  previewLabel: css`
    display: flex;
    align-items: center;
  `,
  contentDripLabel: css`
    display: flex;
    align-items: center;

    svg {
      margin-right: ${spacing[4]};
      color: ${colorTokens.icon.success};
    }
  `,
  previewInfo: css`
    ${typography.small()};
    text-align: center;
    color: ${colorTokens.text.title};
    padding: ${spacing[8]} ${spacing[24]};
    background: ${colorTokens.background.status.success};
    border-radius: ${borderRadius[4]};
    margin-top: ${spacing[12]};
  `,
};
