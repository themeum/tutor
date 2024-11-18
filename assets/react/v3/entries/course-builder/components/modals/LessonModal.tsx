import { css } from '@emotion/react';
import { useQueryClient } from '@tanstack/react-query';
import { __, sprintf } from '@wordpress/i18n';
import { useEffect } from 'react';
import { Controller } from 'react-hook-form';

import Button from '@Atoms/Button';
import { LoadingOverlay } from '@Atoms/LoadingSpinner';
import ProBadge from '@Atoms/ProBadge';
import SVGIcon from '@Atoms/SVGIcon';

import Tooltip from '@/v3/shared/atoms/Tooltip';
import FormDateInput from '@Components/fields/FormDateInput';
import FormFileUploader from '@Components/fields/FormFileUploader';
import FormImageInput, { type Media } from '@Components/fields/FormImageInput';
import FormInput from '@Components/fields/FormInput';
import FormInputWithContent from '@Components/fields/FormInputWithContent';
import FormSwitch from '@Components/fields/FormSwitch';
import FormTopicPrerequisites from '@Components/fields/FormTopicPrerequisites';
import FormVideoInput, { type CourseVideo } from '@Components/fields/FormVideoInput';
import FormWPEditor from '@Components/fields/FormWPEditor';
import { type ModalProps, useModal } from '@Components/modals/Modal';
import ModalWrapper from '@Components/modals/ModalWrapper';
import { tutorConfig } from '@Config/config';
import { Addons, TutorRoles } from '@Config/constants';
import { borderRadius, colorTokens, spacing, zIndex } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import type { ContentDripType } from '@CourseBuilderServices/course';
import {
  type CourseTopic,
  type ID,
  convertLessonDataToPayload,
  useLessonDetailsQuery,
  useSaveLessonMutation,
} from '@CourseBuilderServices/curriculum';
import type { H5PContent } from '@CourseBuilderServices/quiz';
import { getCourseId, isAddonEnabled } from '@CourseBuilderUtils/utils';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
import { normalizeLineEndings } from '@Utils/util';
import { noop } from '@Utils/util';
import { maxLimitRule } from '@Utils/validation';
import H5PContentListModal from './H5PContentListModal';

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
  const isOpenAiEnabled = tutorConfig.settings?.chatgpt_enable === 'on';
  const isClassicEditorEnabled = tutorConfig.enable_lesson_classic_editor === '1';
  const hasWpAdminAccess = tutorConfig.settings?.hide_admin_bar_for_users === 'off';
  const isAdmin = tutorConfig.current_user.roles.includes(TutorRoles.ADMINISTRATOR);
  const isInstructor = tutorConfig.current_user.roles.includes(TutorRoles.TUTOR_INSTRUCTOR);

  const isWpEditorVisible = isClassicEditorEnabled && (isAdmin || (isInstructor && hasWpAdminAccess));

  const getLessonDetailsQuery = useLessonDetailsQuery(lessonId, topicId);
  const saveLessonMutation = useSaveLessonMutation(courseId);
  const queryClient = useQueryClient();
  const { showModal } = useModal();

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

  const isFormDirty = form.formState.isDirty;

  // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
  useEffect(() => {
    if (lessonDetails) {
      form.reset({
        title: lessonDetails.post_title || '',
        description: normalizeLineEndings(lessonDetails.post_content) || '',
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
          unlock_date: lessonDetails?.content_drip_settings?.unlock_date || '',
          after_xdays_of_enroll: lessonDetails?.content_drip_settings?.after_xdays_of_enroll || '',
          prerequisites: lessonDetails?.content_drip_settings?.prerequisites || [],
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
      icon={isFormDirty ? <SVGIcon name="warning" width={24} height={24} /> : icon}
      title={isFormDirty ? __('Unsaved Changes', 'tutor') : title}
      subtitle={subtitle}
      maxWidth={1070}
      actions={
        isFormDirty && (
          <>
            <Button
              variant="text"
              size="small"
              onClick={() => {
                lessonId ? form.reset() : closeModal({ action: 'CLOSE' });
              }}
            >
              {lessonId ? __('Discard Changes', 'tutor') : __('Cancel', 'tutor')}
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
        )
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
                  required: __('Lesson title is required.', 'tutor'),
                  ...maxLimitRule(255),
                }}
                render={(controllerProps) => (
                  <FormInput
                    {...controllerProps}
                    label={__('Name', 'tutor')}
                    placeholder={__('Enter Lesson Name', 'tutor')}
                    generateWithAi={!isTutorPro || isOpenAiEnabled}
                    selectOnFocus
                    isClearable
                  />
                )}
              />
              <div css={styles.description}>
                <Controller
                  name="description"
                  control={form.control}
                  render={(controllerProps) => (
                    <FormWPEditor
                      {...controllerProps}
                      label={
                        <div css={styles.descriptionLabel}>
                          {__('Content', 'tutor')}
                          <Show when={isWpEditorVisible}>
                            <Tooltip
                              content={__('Save the lesson first to use the WP Editor.', 'tutor')}
                              delay={200}
                              disabled={!!lessonId}
                            >
                              <Button
                                variant="text"
                                size="small"
                                onClick={() => {
                                  window.open(
                                    `${tutorConfig.home_url}/wp-admin/post-new.php?post_type=lesson`,
                                    '_blank',
                                    'noopener',
                                  );
                                }}
                                icon={<SVGIcon name="edit" width={24} height={24} />}
                                buttonCss={styles.wpEditorButton}
                                disabled={!lessonId}
                              >
                                {__('WP Editor', 'tutor')}
                              </Button>
                            </Tooltip>
                          </Show>
                        </div>
                      }
                      placeholder={__('Enter Lesson Description', 'tutor')}
                      generateWithAi={!isTutorPro || isOpenAiEnabled}
                    />
                  )}
                />

                <Show
                  when={isTutorPro && isAddonEnabled(Addons.H5P_INTEGRATION)}
                  fallback={
                    <Show when={!isTutorPro}>
                      <div css={styles.addH5PContentWrapper}>
                        <ProBadge>
                          <button css={styles.addH5PContentButton} type="button" disabled onClick={noop}>
                            {__('Add H5P Content', 'tutor')}
                          </button>
                        </ProBadge>
                      </div>
                    </Show>
                  }
                >
                  <button
                    css={styles.addH5PContentButton}
                    type="button"
                    onClick={() => {
                      showModal({
                        component: H5PContentListModal,
                        props: {
                          title: __('Select H5P Content', 'tutor'),
                          onAddContent: (contents) => {
                            const convertToH5PShortCode = (content: H5PContent) => {
                              return `[h5p id="${content.id}"]`;
                            };
                            const description = form.getValues('description');
                            const h5pContents = contents.map(convertToH5PShortCode);

                            form.setValue('description', `${description}\n${h5pContents.join('\n')}`, {
                              shouldDirty: true,
                            });
                          },
                          contentType: 'lesson',
                          addedContentIds: (() => {
                            const description = form.getValues('description');
                            const h5pShortCodes = description.match(/\[h5p id="(\d+)"\]/g) || [];
                            return h5pShortCodes.map((shortcode) => {
                              const id = shortcode.match(/\[h5p id="(\d+)"\]/)?.[1] || '';
                              return String(id);
                            });
                          })(),
                        },
                      });
                    }}
                  >
                    {__('Add H5P Content', 'tutor')}
                  </button>
                </Show>
              </div>
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
                  buttonText={__('Upload Image', 'tutor')}
                  infoText={sprintf(
                    __('JPEG, PNG, GIF, and WebP formats, up to %s', 'tutor'),
                    tutorConfig.max_upload_size,
                  )}
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
                  infoText={sprintf(__('MP4, and WebM formats, up to %s', 'tutor'), tutorConfig.max_upload_size)}
                  supportedFormats={['mp4', 'webm']}
                  onGetDuration={(duration) => {
                    form.setValue('duration.hour', duration.hours);
                    form.setValue('duration.minute', duration.minutes);
                    form.setValue('duration.second', duration.seconds);
                  }}
                />
              )}
            />
            <div css={styles.durationWrapper}>
              <span css={styles.additionLabel}>{__('Video Playback Time', 'tutor')}</span>
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
                        }, [] as CourseTopic[]) || []
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

            <Show when={!isTutorPro || isAddonEnabled(Addons.TUTOR_COURSE_PREVIEW)}>
              <div css={styles.lessonPreview}>
                <Controller
                  name="lesson_preview"
                  control={form.control}
                  render={(controllerProps) => (
                    <FormSwitch
                      {...controllerProps}
                      disabled={!isTutorPro}
                      label={
                        <div css={styles.previewLabel}>
                          {__('Lesson Preview', 'tutor')}
                          {!isTutorPro && <ProBadge size="small" content={__('Pro', 'tutor')} />}
                        </div>
                      }
                      helpText={__(
                        'If checked, any user/guest can view this lesson without enrolling in the course.',
                        'tutor',
                      )}
                    />
                  )}
                />
                <Show when={form.watch('lesson_preview')}>
                  <div css={styles.previewInfo}>
                    {__(
                      'This lesson is now available for preview. Users and guests can view it without enrolling in the course.',
                      'tutor',
                    )}
                  </div>
                </Show>
              </div>
            </Show>
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
    z-index: ${zIndex.positive}; // this is the hack to make the sticky work and not overlap with the editor
  `,
  description: css`
    position: relative;
  `,
  descriptionLabel: css`
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 32px;
  `,
  addH5PContentWrapper: css`
    position: absolute;
    top: 36px;
    left: 110px;
  `,
  addH5PContentButton: css`
    display: inline-block;
    text-decoration: none;
    font-size: 13px;
    line-height: 2.15384615;
    min-height: 30px;
    margin: 0;
    padding: 0 10px;
    cursor: pointer;
    border: 1px solid #3e64de;
    border-radius: 3px;
    white-space: nowrap;
    box-sizing: border-box;
    color: #3e64de;
    border-color: #3e64de;
    background: transparent;

    :hover:not(:disabled) {
      background: ${colorTokens.background.white};
    }

    :disabled {
      cursor: not-allowed;
      position: relative;
      top: auto;
      left: auto;
      opacity: 0.5;
    }
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
    gap: ${spacing[4]};
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
  wpEditorButton: css`
    margin-left: ${spacing[4]};
    color: ${colorTokens.text.brand};

    svg {
      color: ${colorTokens.icon.brand};
    }

    &:hover {
      text-decoration: underline;
      color: ${colorTokens.text.brand};
    }
  `,
};
