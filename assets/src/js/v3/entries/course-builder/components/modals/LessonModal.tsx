import { css } from '@emotion/react';
import { useQueryClient } from '@tanstack/react-query';
import { __, sprintf } from '@wordpress/i18n';
import { useEffect } from 'react';
import { Controller } from 'react-hook-form';

import Button from '@TutorShared/atoms/Button';
import { LoadingOverlay } from '@TutorShared/atoms/LoadingSpinner';
import ProBadge from '@TutorShared/atoms/ProBadge';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import Tooltip from '@TutorShared/atoms/Tooltip';

import FormDateInput from '@TutorShared/components/fields/FormDateInput';
import FormFileUploader from '@TutorShared/components/fields/FormFileUploader';
import FormImageInput from '@TutorShared/components/fields/FormImageInput';
import FormInput from '@TutorShared/components/fields/FormInput';
import FormInputWithContent from '@TutorShared/components/fields/FormInputWithContent';
import FormSwitch from '@TutorShared/components/fields/FormSwitch';
import FormTopicPrerequisites from '@TutorShared/components/fields/FormTopicPrerequisites';
import FormVideoInput, { type CourseVideo } from '@TutorShared/components/fields/FormVideoInput';
import FormWPEditor from '@TutorShared/components/fields/FormWPEditor';
import { type ModalProps, useModal } from '@TutorShared/components/modals/Modal';
import ModalWrapper from '@TutorShared/components/modals/ModalWrapper';

import CourseBuilderInjectionSlot from '@CourseBuilderComponents/CourseBuilderSlot';
import { useCourseBuilderSlot } from '@CourseBuilderContexts/CourseBuilderSlotContext';
import { type ContentDripType } from '@CourseBuilderServices/course';
import {
  convertLessonDataToPayload,
  type CourseTopic,
  type Lesson,
  useLessonDetailsQuery,
  useSaveLessonMutation,
} from '@CourseBuilderServices/curriculum';
import { getCourseId } from '@CourseBuilderUtils/utils';
import H5PContentListModal from '@TutorShared/components/modals/H5PContentListModal';
import { tutorConfig } from '@TutorShared/config/config';
import { Addons, CURRENT_VIEWPORT, TutorRoles, VisibilityControlKeys } from '@TutorShared/config/constants';
import { borderRadius, Breakpoint, colorTokens, spacing, zIndex } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import Show from '@TutorShared/controls/Show';
import { useFormWithGlobalError } from '@TutorShared/hooks/useFormWithGlobalError';
import useVisibilityControl from '@TutorShared/hooks/useVisibilityControl';
import { type WPMedia } from '@TutorShared/hooks/useWpMedia';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { type H5PContent, type ID } from '@TutorShared/utils/types';
import { findSlotFields, formatBytes, isAddonEnabled, normalizeLineEndings } from '@TutorShared/utils/util';
import { maxLimitRule } from '@TutorShared/utils/validation';

interface LessonModalProps extends ModalProps {
  lessonId?: ID;
  topicId: ID;
  closeModal: (props?: { action: 'CONFIRM' | 'CLOSE' }) => void;
  contentDripType: ContentDripType;
}

export interface LessonForm {
  title: string;
  description: string;
  thumbnail: WPMedia | null;
  tutor_attachments: WPMedia[];
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
  const isAdmin = tutorConfig.current_user.roles?.includes(TutorRoles.ADMINISTRATOR);
  const isInstructor = tutorConfig.current_user.roles?.includes(TutorRoles.TUTOR_INSTRUCTOR);

  const isWpEditorVisible = isClassicEditorEnabled && (isAdmin || (isInstructor && hasWpAdminAccess));

  const getLessonDetailsQuery = useLessonDetailsQuery(lessonId, topicId);
  const saveLessonMutation = useSaveLessonMutation(courseId);
  const queryClient = useQueryClient();
  const { showModal } = useModal();

  const { data: lessonDetails, isLoading } = getLessonDetailsQuery;
  const topics = queryClient.getQueryData(['Topic', courseId]) as CourseTopic[];

  const { fields } = useCourseBuilderSlot();
  const isVideoPlaybackTimeVisible = useVisibilityControl(
    VisibilityControlKeys.COURSE_BUILDER.CURRICULUM.LESSON.VIDEO_PLAYBACK_TIME,
  );
  const isLessonPreviewVisible = useVisibilityControl(
    VisibilityControlKeys.COURSE_BUILDER.CURRICULUM.LESSON.LESSON_PREVIEW,
  );
  const isFeaturedImageVisible = useVisibilityControl(
    VisibilityControlKeys.COURSE_BUILDER.CURRICULUM.LESSON.FEATURED_IMAGE,
  );
  const isVideoVisible = useVisibilityControl(VisibilityControlKeys.COURSE_BUILDER.CURRICULUM.LESSON.VIDEO);
  const isExerciseFilesVisible = useVisibilityControl(
    VisibilityControlKeys.COURSE_BUILDER.CURRICULUM.LESSON.EXERCISE_FILES,
  );

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

  const isFormDirty = form.formState.dirtyFields && Object.keys(form.formState.dirtyFields).length > 0;

  const hasRightSidebar = Boolean(
    contentDripType !== '' ||
    isVideoPlaybackTimeVisible ||
    isFeaturedImageVisible ||
    isVideoVisible ||
    isExerciseFilesVisible ||
    (isAddonEnabled(Addons.TUTOR_COURSE_PREVIEW) && isLessonPreviewVisible),
  );

  useEffect(() => {
    if (lessonDetails && !isLoading) {
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
        ...Object.fromEntries(
          findSlotFields({ fields: fields.Curriculum.Lesson }).map((key) => [key, lessonDetails[key as keyof Lesson]]),
        ),
      });
    }

    const addMediaButton = document.querySelector('.button.insert-media.add_media');
    const h5pButton = document.querySelector('.add-h5p-content-button');
    if (addMediaButton && h5pButton) {
      addMediaButton.after(h5pButton);
    }

    const timeoutId = setTimeout(() => {
      form.setFocus('title');
    }, 0);

    return () => {
      clearTimeout(timeoutId);
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [lessonDetails, isLoading]);

  const onSubmit = async (data: LessonForm) => {
    const payload = convertLessonDataToPayload(
      data,
      lessonId,
      topicId,
      contentDripType,
      findSlotFields({ fields: fields.Curriculum.Lesson }),
    );
    const response = await saveLessonMutation.mutateAsync(payload);

    if (response.data) {
      closeModal({ action: 'CONFIRM' });
    }
  };

  const onAddH5PContentButtonClick = () => {
    const description = form.getValues('description');
    const h5pShortCodes = description.match(/\[h5p id="(\d+)"\]/g) || [];
    const addedContentIds = h5pShortCodes.map((shortcode) => {
      const id = shortcode.match(/\[h5p id="(\d+)"\]/)?.[1] || '';
      return String(id);
    });

    const onAddContent = (contents: H5PContent[]) => {
      const convertToH5PShortCode = (content: H5PContent) => {
        return `[h5p id="${content.id}"]`;
      };
      const h5pContents = contents.map(convertToH5PShortCode);

      form.setValue('description', `${description}\n${h5pContents.join('\n')}`, {
        shouldDirty: true,
      });
    };

    showModal({
      component: H5PContentListModal,
      props: {
        title: __('Select H5P Content', 'tutor'),
        onAddContent: onAddContent,
        contentType: 'lesson',
        addedContentIds: addedContentIds,
      },
    });
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
                if (lessonId) {
                  form.reset();
                } else {
                  closeModal({ action: 'CLOSE' });
                }
              }}
            >
              {lessonId ? __('Discard Changes', 'tutor') : __('Cancel', 'tutor')}
            </Button>
            <Button
              data-cy="save-lesson"
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
      <div css={styles.wrapper({ hasRightSidebar })}>
        <Show when={!getLessonDetailsQuery.isLoading} fallback={<LoadingOverlay />}>
          {/* This div is required to make the sticky work */}
          <div>
            <div css={styles.lessonInfo({ hasRightSidebar })}>
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
                                    `${tutorConfig.site_url}/wp-admin/post.php?post=${lessonId}&action=edit`,
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
                      <button
                        css={styles.addH5PContentButton({ isPro: false })}
                        type="reset"
                        className="add-h5p-content-button"
                        disabled
                      >
                        <ProBadge>
                          <div data-button-text>{__('Add H5P Content', 'tutor')}</div>
                        </ProBadge>
                      </button>
                    </Show>
                  }
                >
                  <button
                    className="add-h5p-content-button"
                    css={styles.addH5PContentButton}
                    type="button"
                    onClick={onAddH5PContentButtonClick}
                  >
                    {__('Add H5P Content', 'tutor')}
                  </button>
                </Show>
              </div>

              <CourseBuilderInjectionSlot section="Curriculum.Lesson.after_description" form={form} />
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
                  infoText={
                    /* translators: %s is the maximum allowed upload file size (e.g., "2MB") */
                    sprintf(
                      __('JPEG, PNG, GIF, and WebP formats, up to %s', 'tutor'),
                      formatBytes(Number(tutorConfig?.max_upload_size || 0)),
                    )
                  }
                  visibilityKey={VisibilityControlKeys.COURSE_BUILDER.CURRICULUM.LESSON.FEATURED_IMAGE}
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
                  infoText={
                    // translators: %s is the maximum allowed file size
                    sprintf(
                      __('MP4, and WebM formats, up to %s', 'tutor'),
                      formatBytes(Number(tutorConfig?.max_upload_size || 0)),
                    )
                  }
                  onGetDuration={(duration) => {
                    form.setValue('duration.hour', duration.hours);
                    form.setValue('duration.minute', duration.minutes);
                    form.setValue('duration.second', duration.seconds);
                  }}
                  visibilityKey={VisibilityControlKeys.COURSE_BUILDER.CURRICULUM.LESSON.VIDEO}
                />
              )}
            />
            <Show when={isVideoPlaybackTimeVisible}>
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
                        visibilityKey={VisibilityControlKeys.COURSE_BUILDER.CURRICULUM.LESSON.VIDEO_PLAYBACK_TIME}
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
                        visibilityKey={VisibilityControlKeys.COURSE_BUILDER.CURRICULUM.LESSON.VIDEO_PLAYBACK_TIME}
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
                        visibilityKey={VisibilityControlKeys.COURSE_BUILDER.CURRICULUM.LESSON.VIDEO_PLAYBACK_TIME}
                      />
                    )}
                  />
                </div>
              </div>
            </Show>

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
                      helpText={
                        // prettier-ignore
                        __('This lesson will be available from the given date. Leave empty to make it available immediately.', 'tutor')
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
                              contents: topic.contents.filter((content) => String(content.ID) !== String(lessonId)),
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
                  visibilityKey={VisibilityControlKeys.COURSE_BUILDER.CURRICULUM.LESSON.EXERCISE_FILES}
                />
              )}
            />

            <Show when={!isTutorPro || (isLessonPreviewVisible && isAddonEnabled(Addons.TUTOR_COURSE_PREVIEW))}>
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
                      helpText={
                        // prettier-ignore
                        __( 'If checked, any user/guest can view this lesson without enrolling in the course.', 'tutor')
                      }
                      visibilityKey={VisibilityControlKeys.COURSE_BUILDER.CURRICULUM.LESSON.LESSON_PREVIEW}
                    />
                  )}
                />
                <Show when={form.watch('lesson_preview')}>
                  <div css={styles.previewInfo}>
                    {
                      // prettier-ignore
                      __('This lesson is now available for preview. Users and guests can view it without enrolling in the course.', 'tutor')
                    }
                  </div>
                </Show>
              </div>
            </Show>

            <CourseBuilderInjectionSlot section="Curriculum.Lesson.bottom_of_sidebar" form={form} />
          </div>
        </Show>
      </div>
    </ModalWrapper>
  );
};

export default LessonModal;

const styles = {
  wrapper: ({ hasRightSidebar = true }) => css`
    margin: 0 auto;
    display: grid;
    grid-template-columns: ${hasRightSidebar ? '1fr 338px' : '1fr'};
    height: 100%;
    width: 100%;
    padding-inline: ${spacing[32]};

    ${Breakpoint.smallTablet} {
      grid-template-columns: 1fr;
      padding-inline: ${spacing[24]};
    }

    ${Breakpoint.mobile} {
      padding-inline: ${spacing[16]};
    }
  `,
  lessonInfo: ({ hasRightSidebar = true }) => css`
    padding-block: ${spacing[20]};
    padding-right: ${hasRightSidebar && spacing[32]};
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
  description: css`
    position: relative;
  `,
  descriptionLabel: css`
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 32px;
  `,
  addH5PContentButton: ({ isPro = true }) => css`
    text-decoration: none;
    font-size: 13px;
    line-height: 2.15384615;
    min-height: 30px;
    margin: 0;
    padding: ${!isPro ? '0px' : '0 10px'};
    cursor: pointer;
    border: 1px solid #3e64de;
    border-radius: 3px;
    white-space: nowrap;
    box-sizing: border-box;
    color: #3e64de;
    border-color: #3e64de;
    background: transparent;

    [data-button-text] {
      ${styleUtils.flexCenter()};
      padding: 0 10px;
    }

    :hover:not(:disabled) {
      background: ${colorTokens.background.white};
      color: #3e64de;
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

    ${Breakpoint.smallTablet} {
      border-left: none;
      padding-left: 0;
    }
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

    &:hover:not(:disabled) {
      text-decoration: underline;
    }
  `,
};
