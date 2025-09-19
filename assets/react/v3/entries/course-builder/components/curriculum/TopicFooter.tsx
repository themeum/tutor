import { css } from '@emotion/react';
import { useQueryClient } from '@tanstack/react-query';
import { __, sprintf } from '@wordpress/i18n';
import { memo, useRef, useState } from 'react';
import { useFormContext } from 'react-hook-form';

import Button from '@TutorShared/atoms/Button';
import ProBadge from '@TutorShared/atoms/ProBadge';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import { useToast } from '@TutorShared/atoms/Toast';
import { useModal } from '@TutorShared/components/modals/Modal';
import Show from '@TutorShared/controls/Show';

import { useFileUploader } from '@TutorShared/molecules/FileUploader';
import Popover from '@TutorShared/molecules/Popover';
import ThreeDots from '@TutorShared/molecules/ThreeDots';

import GoogleMeetForm from '@CourseBuilderComponents/additional/meeting/GoogleMeetForm';
import ZoomMeetingForm from '@CourseBuilderComponents/additional/meeting/ZoomMeetingForm';
import AssignmentModal from '@CourseBuilderComponents/modals/AssignmentModal';
import LessonModal from '@CourseBuilderComponents/modals/LessonModal';
import QuizModal from '@CourseBuilderComponents/modals/QuizModal';

import CollectionListModal from '@CourseBuilderComponents/modals/ContentBankContentSelectModal';
import type { CourseTopicWithCollapse } from '@CourseBuilderPages/Curriculum';
import type { CourseDetailsResponse, CourseFormData } from '@CourseBuilderServices/course';
import { useImportQuizMutation } from '@CourseBuilderServices/quiz';
import { getCourseId, getIdWithoutPrefix } from '@CourseBuilderUtils/utils';
import { tutorConfig } from '@TutorShared/config/config';
import { Addons, CURRENT_VIEWPORT } from '@TutorShared/config/constants';
import { Breakpoint, colorTokens, spacing } from '@TutorShared/config/styles';
import { POPOVER_PLACEMENTS } from '@TutorShared/hooks/usePortalPopover';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { isAddonEnabled, noop } from '@TutorShared/utils/util';

interface TopicFooterProps {
  topic: CourseTopicWithCollapse;
  nextContentOrder: number;
}

const courseId = getCourseId();
const isTutorPro = !!tutorConfig.tutor_pro_url;
const hasLiveAddons =
  isAddonEnabled(Addons.TUTOR_GOOGLE_MEET_INTEGRATION) || isAddonEnabled(Addons.TUTOR_ZOOM_INTEGRATION);

const TopicFooter = ({ topic, nextContentOrder }: TopicFooterProps) => {
  const topicId = getIdWithoutPrefix('topic-', topic.id);

  const { showToast } = useToast();
  const { showModal } = useModal();
  const courseDetailsForm = useFormContext<CourseFormData>();

  const [meetingType, setMeetingType] = useState<'tutor-google-meet' | 'tutor_zoom_meeting' | null>(null);
  const [isThreeDotOpen, setIsThreeDotOpen] = useState(false);

  const triggerGoogleMeetRef = useRef<HTMLButtonElement>(null);
  const triggerZoomRef = useRef<HTMLButtonElement>(null);

  const importQuizMutation = useImportQuizMutation();

  const queryClient = useQueryClient();
  const courseDetails = queryClient.getQueryData(['CourseDetails', Number(courseId)]) as CourseDetailsResponse;
  const { fileInputRef, handleChange } = useFileUploader({
    acceptedTypes: ['.csv'],
    onUpload: async (files) => {
      await importQuizMutation.mutateAsync({
        topic_id: topicId,
        csv_file: files[0],
      });
      setIsThreeDotOpen(false);
    },
    onError: (errorMessages) => {
      for (const message of errorMessages) {
        showToast({
          message,
          type: 'danger',
        });
      }
      setIsThreeDotOpen(false);
    },
  });

  return (
    <>
      <div css={styles.contentButtons}>
        <div css={styles.leftButtons}>
          <Button
            data-cy="add-lesson"
            variant="tertiary"
            isOutlined
            size="small"
            icon={<SVGIcon name="plus" width={24} height={24} />}
            disabled={!topic.isSaved}
            buttonCss={styles.contentButton}
            onClick={() => {
              showModal({
                component: LessonModal,
                props: {
                  contentDripType: courseDetailsForm.watch('contentDripType'),
                  topicId: topicId,
                  title: __('Lesson', 'tutor'),
                  icon: <SVGIcon name="lesson" width={24} height={24} />,
                  /* translators: %s is the topic title */
                  subtitle: sprintf(__('Topic: %s', 'tutor'), topic.title),
                },
              });
            }}
          >
            {__('Lesson', 'tutor')}
          </Button>
          <Button
            data-cy="add-quiz"
            variant="tertiary"
            isOutlined
            size="small"
            icon={<SVGIcon name="plus" width={24} height={24} />}
            disabled={!topic.isSaved}
            buttonCss={styles.contentButton}
            onClick={() => {
              showModal({
                component: QuizModal,
                props: {
                  topicId: topicId,
                  contentDripType: courseDetailsForm.watch('contentDripType'),
                  title: __('Quiz', 'tutor'),
                  icon: <SVGIcon name="quiz" width={24} height={24} />,
                  /* translators: %s is the topic title */
                  subtitle: sprintf(__('Topic: %s', 'tutor'), topic.title),
                },
                closeOnEscape: false,
              });
            }}
          >
            {__('Quiz', 'tutor')}
          </Button>
          <Show
            when={!isTutorPro}
            fallback={
              <Show when={isAddonEnabled(Addons.H5P_INTEGRATION)}>
                <Button
                  data-cy="add-interactive-quiz"
                  variant="tertiary"
                  isOutlined
                  size="small"
                  icon={<SVGIcon name="plus" width={24} height={24} />}
                  disabled={!topic.isSaved}
                  buttonCss={styles.contentButton}
                  onClick={() => {
                    showModal({
                      component: QuizModal,
                      props: {
                        topicId: topicId,
                        contentDripType: courseDetailsForm.watch('contentDripType'),
                        title: __('Interactive Quiz', 'tutor'),
                        icon: <SVGIcon name="interactiveQuiz" width={24} height={24} />,
                        /* translators: %s is the topic title */
                        subtitle: sprintf(__('Topic: %s', 'tutor'), topic.title),
                        contentType: 'tutor_h5p_quiz',
                      },
                      closeOnEscape: false,
                    });
                  }}
                >
                  {__('Interactive Quiz', 'tutor')}
                </Button>
              </Show>
            }
          >
            <ProBadge>
              <Button
                variant="tertiary"
                isOutlined
                size="small"
                icon={<SVGIcon name="plus" width={24} height={24} />}
                disabled
                onClick={noop}
              >
                {__('Interactive Quiz', 'tutor')}
              </Button>
            </ProBadge>
          </Show>
          <Show
            when={!isTutorPro}
            fallback={
              <Show when={isAddonEnabled(Addons.TUTOR_ASSIGNMENTS)}>
                <Button
                  data-cy="add-assignment"
                  variant="tertiary"
                  isOutlined
                  size="small"
                  icon={<SVGIcon name="plus" width={24} height={24} />}
                  disabled={!topic.isSaved}
                  buttonCss={styles.contentButton}
                  onClick={() => {
                    showModal({
                      component: AssignmentModal,
                      props: {
                        topicId: topicId,
                        contentDripType: courseDetailsForm.watch('contentDripType'),
                        title: __('Assignment', 'tutor'),
                        icon: <SVGIcon name="assignment" width={24} height={24} />,
                        /* translators: %s is the topic title */
                        subtitle: sprintf(__('Topic: %s', 'tutor'), topic.title),
                      },
                    });
                  }}
                >
                  {__('Assignment', 'tutor')}
                </Button>
              </Show>
            }
          >
            <ProBadge>
              <Button
                variant="tertiary"
                isOutlined
                size="small"
                icon={<SVGIcon name="plus" width={24} height={24} />}
                disabled
                onClick={noop}
              >
                {__('Assignment', 'tutor')}
              </Button>
            </ProBadge>
          </Show>
        </div>
        <div css={styles.rightButtons}>
          <Show
            when={!isTutorPro}
            fallback={
              <Show when={isAddonEnabled(Addons.CONTENT_BANK)}>
                <Button
                  variant="tertiary"
                  isOutlined
                  size="small"
                  icon={<SVGIcon name="contentBank" width={24} height={24} />}
                  disabled={!topic.isSaved}
                  buttonCss={styles.contentButton}
                  data-cy="add-from-content-bank"
                  onClick={() => {
                    showModal({
                      id: 'content-bank-collection-list',
                      component: CollectionListModal,
                      props: {
                        type: 'lesson_assignment',
                        topicId: topicId,
                        nextContentOrder: nextContentOrder,
                      },
                    });
                  }}
                >
                  {__('Content Bank', 'tutor')}
                </Button>
              </Show>
            }
          >
            <ProBadge>
              <Button
                variant="tertiary"
                isOutlined
                size="small"
                icon={<SVGIcon name="contentBank" width={24} height={24} />}
                disabled
                onClick={noop}
              >
                {__('Content Bank', 'tutor')}
              </Button>
            </ProBadge>
          </Show>

          <Show
            when={!isTutorPro || hasLiveAddons}
            fallback={
              <Show
                when={isTutorPro}
                fallback={
                  <ProBadge>
                    <Button
                      variant="tertiary"
                      isOutlined
                      size="small"
                      icon={<SVGIcon name="import" width={24} height={24} />}
                      disabled
                      onClick={noop}
                    >
                      {__('Import Quiz', 'tutor')}
                    </Button>
                  </ProBadge>
                }
              >
                <Show when={isAddonEnabled(Addons.QUIZ_EXPORT_IMPORT)}>
                  <Button
                    variant="tertiary"
                    isOutlined
                    size="small"
                    icon={<SVGIcon name="import" width={24} height={24} />}
                    disabled={!topic.isSaved}
                    buttonCss={styles.contentButton}
                    onClick={() => {
                      fileInputRef?.current?.click();
                    }}
                  >
                    {__('Import Quiz', 'tutor')}
                  </Button>
                </Show>
              </Show>
            }
          >
            <ThreeDots
              isOpen={isThreeDotOpen}
              onClick={() => setIsThreeDotOpen(true)}
              closePopover={() => setIsThreeDotOpen(false)}
              disabled={!topic.isSaved}
              dotsOrientation="vertical"
              maxWidth={isTutorPro ? '220px' : '250px'}
              isInverse
              closeOnEscape={false}
              size={CURRENT_VIEWPORT.isAboveMobile ? 'medium' : 'small'}
            >
              <Show when={!isTutorPro || isAddonEnabled(Addons.TUTOR_GOOGLE_MEET_INTEGRATION)}>
                <ThreeDots.Option
                  text={
                    <span ref={triggerGoogleMeetRef} css={styles.threeDotButton}>
                      {__('Meet live lesson', 'tutor')}
                      <Show when={!isTutorPro}>
                        <ProBadge size="small" content={__('Pro', 'tutor')} />
                      </Show>
                    </span>
                  }
                  disabled={!isTutorPro}
                  icon={<SVGIcon width={24} height={24} name="googleMeetColorize" isColorIcon />}
                  onClick={() => setMeetingType('tutor-google-meet')}
                />
              </Show>
              <Show when={!isTutorPro || isAddonEnabled(Addons.TUTOR_ZOOM_INTEGRATION)}>
                <ThreeDots.Option
                  text={
                    <span ref={triggerZoomRef} css={styles.threeDotButton}>
                      {__('Zoom live lesson', 'tutor')}
                      <Show when={!isTutorPro}>
                        <ProBadge size="small" content={__('Pro', 'tutor')} />
                      </Show>
                    </span>
                  }
                  disabled={!isTutorPro}
                  icon={<SVGIcon width={24} height={24} name="zoomColorize" isColorIcon />}
                  onClick={() => setMeetingType('tutor_zoom_meeting')}
                />
              </Show>
              <Show when={!isTutorPro || isAddonEnabled(Addons.QUIZ_EXPORT_IMPORT)}>
                <ThreeDots.Option
                  text={
                    <span css={styles.threeDotButton}>
                      {__('Import Quiz', 'tutor')}
                      <Show when={!isTutorPro}>
                        <ProBadge size="small" content={__('Pro', 'tutor')} />
                      </Show>
                    </span>
                  }
                  disabled={!isTutorPro}
                  onClick={() => {
                    fileInputRef?.current?.click();
                  }}
                  icon={<SVGIcon name="importColorized" width={24} height={24} isColorIcon />}
                />
              </Show>
            </ThreeDots>
          </Show>
        </div>
      </div>

      <input css={styles.input} type="file" ref={fileInputRef} onChange={handleChange} multiple={false} accept=".csv" />

      <Popover
        triggerRef={triggerGoogleMeetRef}
        isOpen={meetingType === 'tutor-google-meet'}
        closePopover={noop}
        maxWidth="306px"
        closeOnEscape={false}
        placement={CURRENT_VIEWPORT.isAboveMobile ? POPOVER_PLACEMENTS.BOTTOM : POPOVER_PLACEMENTS.ABSOLUTE_CENTER}
      >
        <GoogleMeetForm
          topicId={topicId}
          data={null}
          onCancel={() => {
            setMeetingType(null);
            setIsThreeDotOpen(false);
          }}
        />
      </Popover>
      <Popover
        triggerRef={triggerZoomRef}
        isOpen={meetingType === 'tutor_zoom_meeting'}
        closePopover={noop}
        maxWidth="306px"
        closeOnEscape={false}
        placement={CURRENT_VIEWPORT.isAboveMobile ? POPOVER_PLACEMENTS.BOTTOM : POPOVER_PLACEMENTS.ABSOLUTE_CENTER}
      >
        <ZoomMeetingForm
          topicId={topicId}
          meetingHost={courseDetails?.zoom_users || {}}
          data={null}
          onCancel={() => {
            setMeetingType(null);
            setIsThreeDotOpen(false);
          }}
        />
      </Popover>
    </>
  );
};

export default memo(TopicFooter, (prev, next) => {
  return (
    prev.topic.id === next.topic.id &&
    prev.topic.isSaved === next.topic.isSaved &&
    prev.nextContentOrder === next.nextContentOrder
  );
});

const styles = {
  contentButtons: css`
    ${styleUtils.display.flex()};
    justify-content: space-between;
  `,
  leftButtons: css`
    ${styleUtils.display.flex()};
    gap: ${spacing[12]};

    ${Breakpoint.smallMobile} {
      flex-wrap: wrap;
    }
  `,
  rightButtons: css`
    ${styleUtils.display.flex()};
    align-items: center;
    gap: ${spacing[8]};
  `,
  threeDotButton: css`
    ${styleUtils.display.flex()};
    align-items: center;
    gap: ${spacing[4]};
  `,
  contentButton: css`
    :hover:not(:disabled) {
      background-color: ${colorTokens.background.white};
      color: ${colorTokens.text.brand};
      outline: 1px solid ${colorTokens.stroke.brand};
    }
  `,
  input: css`
    display: none !important;
  `,
};
