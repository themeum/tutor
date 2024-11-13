import { css } from '@emotion/react';
import { __, sprintf } from '@wordpress/i18n';
import { useRef, useState } from 'react';
import { useFormContext } from 'react-hook-form';

import Button from '@Atoms/Button';
import ProBadge from '@Atoms/ProBadge';
import SVGIcon from '@Atoms/SVGIcon';
import { useToast } from '@Atoms/Toast';
import { useModal } from '@Components/modals/Modal';
import Show from '@Controls/Show';

import { useFileUploader } from '@Molecules/FileUploader';
import Popover from '@Molecules/Popover';
import ThreeDots from '@Molecules/ThreeDots';

import GoogleMeetForm from '@CourseBuilderComponents/additional/meeting/GoogleMeetForm';
import ZoomMeetingForm from '@CourseBuilderComponents/additional/meeting/ZoomMeetingForm';
import AssignmentModal from '@CourseBuilderComponents/modals/AssignmentModal';
import LessonModal from '@CourseBuilderComponents/modals/LessonModal';
import QuizModal from '@CourseBuilderComponents/modals/QuizModal';

import { tutorConfig } from '@Config/config';
import { Addons } from '@Config/constants';
import { colorTokens, spacing } from '@Config/styles';
import type { CourseTopicWithCollapse } from '@CourseBuilderPages/Curriculum';
import type { CourseDetailsResponse, CourseFormData } from '@CourseBuilderServices/course';
import { useImportQuizMutation } from '@CourseBuilderServices/quiz';

import { getCourseId, getIdWithoutPrefix, isAddonEnabled } from '@CourseBuilderUtils/utils';
import { styleUtils } from '@Utils/style-utils';
import { noop } from '@Utils/util';
import { useQueryClient } from '@tanstack/react-query';

interface TopicFooterProps {
  topic: CourseTopicWithCollapse;
}

const courseId = getCourseId();
const isTutorPro = !!tutorConfig.tutor_pro_url;
const hasLiveAddons =
  isAddonEnabled(Addons.TUTOR_GOOGLE_MEET_INTEGRATION) || isAddonEnabled(Addons.TUTOR_ZOOM_INTEGRATION);

const TopicFooter = ({ topic }: TopicFooterProps) => {
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
        <div css={[styleUtils.display.flex(), { gap: spacing[12] }]}>
          <Button
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
                  subtitle: sprintf(__('Topic: %s', 'tutor'), topic.title),
                },
              });
            }}
          >
            {__('Lesson', 'tutor')}
          </Button>
          <Button
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
        <div css={styles.footerButtons}>
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
              maxWidth={isTutorPro ? '220px' : '240px'}
              isInverse
              arrowPosition="auto"
              hideArrow
            >
              <Show when={isAddonEnabled(Addons.TUTOR_GOOGLE_MEET_INTEGRATION)}>
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
              <Show when={isAddonEnabled(Addons.TUTOR_ZOOM_INTEGRATION)}>
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

      <input
        css={styleUtils.display.none}
        type="file"
        ref={fileInputRef}
        onChange={handleChange}
        multiple={false}
        accept=".csv"
      />

      <Popover
        triggerRef={triggerGoogleMeetRef}
        isOpen={meetingType === 'tutor-google-meet'}
        closePopover={() => {
          setMeetingType(null);
          setIsThreeDotOpen(false);
        }}
        maxWidth="306px"
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
        closePopover={() => {
          setMeetingType(null);
          setIsThreeDotOpen(false);
        }}
        maxWidth="306px"
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

export default TopicFooter;

const styles = {
  contentButtons: css`
    ${styleUtils.display.flex()};
    justify-content: space-between;
  `,
  footerButtons: css`
    display: flex;
    align-items: center;
  `,
  threeDotButton: css`
    display: flex;
    align-items: center;
    gap: ${spacing[4]};
  `,
  contentButton: css`
    :hover:not(:disabled) {
      background-color: ${colorTokens.background.white};
      color: ${colorTokens.text.brand};
      box-shadow: inset 0 0 0 1px ${colorTokens.stroke.brand};
    }
  `,
};
