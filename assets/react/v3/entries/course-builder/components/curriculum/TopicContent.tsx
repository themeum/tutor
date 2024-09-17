import { type AnimateLayoutChanges, defaultAnimateLayoutChanges, useSortable } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import { css } from '@emotion/react';
import { __, sprintf } from '@wordpress/i18n';
import { useRef, useState } from 'react';
import { useFormContext } from 'react-hook-form';

import LoadingSpinner from '@Atoms/LoadingSpinner';
import ProBadge from '@Atoms/ProBadge';
import SVGIcon from '@Atoms/SVGIcon';
import Tooltip from '@Atoms/Tooltip';

import ConfirmationPopover from '@Molecules/ConfirmationPopover';
import Popover from '@Molecules/Popover';

import { useModal } from '@Components/modals/Modal';
import ZoomMeetingForm from '@CourseBuilderComponents/additional/meeting/ZoomMeetingForm';
import AssignmentModal from '@CourseBuilderComponents/modals/AssignmentModal';
import LessonModal from '@CourseBuilderComponents/modals/LessonModal';
import QuizModal from '@CourseBuilderComponents/modals/QuizModal';
import { useCourseDetails } from '@CourseBuilderContexts/CourseDetailsContext';
import {
  type ContentType,
  type ID,
  useDeleteContentMutation,
  useDuplicateContentMutation,
} from '@CourseBuilderServices/curriculum';

import { tutorConfig } from '@Config/config';
import { Addons } from '@Config/constants';
import { borderRadius, colorTokens, shadow, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import GoogleMeetForm from '@CourseBuilderComponents/additional/meeting/GoogleMeetForm';
import type { CourseTopicWithCollapse } from '@CourseBuilderPages/Curriculum';
import type { CourseFormData } from '@CourseBuilderServices/course';
import { useDeleteQuizMutation, useExportQuizMutation } from '@CourseBuilderServices/quiz';
import { getCourseId, isAddonEnabled } from '@CourseBuilderUtils/utils';
import { AnimationType } from '@Hooks/useAnimation';
import { styleUtils } from '@Utils/style-utils';
import type { IconCollection } from '@Utils/types';
import { noop } from '@Utils/util';

interface TopicContentProps {
  type: ContentType;
  topic: CourseTopicWithCollapse;
  content: { id: ID; title: string; total_question: number };
  isDragging?: boolean;
  onDelete?: () => void;
  onCopy?: () => void;
}

const icons = {
  lesson: {
    name: 'lesson',
    color: colorTokens.icon.default,
  },
  tutor_quiz: {
    name: 'quiz',
    color: colorTokens.design.warning,
  },
  tutor_assignments: {
    name: 'assignment',
    color: colorTokens.icon.processing,
  },
  tutor_zoom_meeting: {
    name: 'zoomColorize',
    color: '',
  },
  'tutor-google-meet': {
    name: 'googleMeetColorize',
    color: '',
  },
} as const;

const modalComponent: {
  // biome-ignore lint/suspicious/noExplicitAny: <explanation>
  [key in Exclude<ContentType, 'tutor_zoom_meeting' | 'tutor-google-meet'>]: React.FunctionComponent<any>;
} = {
  lesson: LessonModal,
  tutor_quiz: QuizModal,
  tutor_assignments: AssignmentModal,
} as const;

const modalTitle: {
  [key in Exclude<ContentType, 'tutor_zoom_meeting' | 'tutor-google-meet'>]: string;
} = {
  lesson: __('Lesson', 'tutor'),
  tutor_quiz: __('Quiz', 'tutor'),
  tutor_assignments: __('Assignment', 'tutor'),
} as const;

const modalIcon: {
  [key in Exclude<ContentType, 'tutor_zoom_meeting' | 'tutor-google-meet'>]: IconCollection;
} = {
  lesson: 'lesson',
  tutor_quiz: 'quiz',
  tutor_assignments: 'assignment',
} as const;

const animateLayoutChanges: AnimateLayoutChanges = (args) =>
  defaultAnimateLayoutChanges({ ...args, wasDragging: true });

const isTutorPro = !!tutorConfig.tutor_pro_url;
const courseId = getCourseId();

const TopicContent = ({ type, topic, content, isDragging = false, onCopy, onDelete }: TopicContentProps) => {
  const courseDetails = useCourseDetails();
  const form = useFormContext<CourseFormData>();
  const [meetingType, setMeetingType] = useState<'tutor_zoom_meeting' | 'tutor-google-meet' | null>(null);
  const [isDeletePopoverOpen, setIsDeletePopoverOpen] = useState(false);

  const editButtonRef = useRef<HTMLButtonElement>(null);
  const deleteRef = useRef<HTMLButtonElement>(null);

  const icon = icons[type];
  const { attributes, listeners, setNodeRef, transform, transition } = useSortable({
    id: content.id,
    animateLayoutChanges,
  });

  const style = {
    transform: CSS.Transform.toString(transform),
    transition,
  };
  const { showModal } = useModal();
  const duplicateContentMutation = useDuplicateContentMutation();
  const deleteContentMutation = useDeleteContentMutation();
  const deleteQuizMutation = useDeleteQuizMutation();
  const deleteGoogleMeetMutation = useDeleteContentMutation();
  const deleteZoomMeetingMutation = useDeleteContentMutation();
  const exportQuizMutation = useExportQuizMutation();

  const handleShowModalOrPopover = () => {
    const isContentType = type as keyof typeof modalComponent;
    if (modalComponent[isContentType]) {
      showModal({
        component: modalComponent[isContentType],
        props: {
          contentDripType: form.watch('contentDripType'),
          topicId: topic.id,
          lessonId: content.id,
          assignmentId: content.id,
          quizId: content.id,
          title: modalTitle[isContentType],
          subtitle: `${__('Topic')}: ${topic.title}`,
          icon: <SVGIcon name={modalIcon[isContentType]} height={24} width={24} />,
        },
      });
    }
    if (type === 'tutor_zoom_meeting') {
      setMeetingType('tutor_zoom_meeting');
    }

    if (type === 'tutor-google-meet') {
      setMeetingType('tutor-google-meet');
    }
  };

  const handleDelete = () => {
    if (['lesson', 'tutor_assignments'].includes(type)) {
      deleteContentMutation.mutateAsync(content.id);
    } else if (type === 'tutor_quiz') {
      deleteQuizMutation.mutateAsync(content.id);
    } else if (type === 'tutor-google-meet') {
      deleteGoogleMeetMutation.mutateAsync(content.id);
    } else if (type === 'tutor_zoom_meeting') {
      deleteZoomMeetingMutation.mutateAsync(content.id);
    }
    onDelete?.();
  };

  const handleDuplicate = () => {
    const convertedContentType: {
      [key in Exclude<ContentType, 'tutor_zoom_meeting' | 'tutor-google-meet'>]:
        | 'lesson'
        | 'assignment'
        | 'answer'
        | 'question'
        | 'quiz'
        | 'topic';
    } = {
      lesson: 'lesson',
      tutor_assignments: 'assignment',
      tutor_quiz: 'quiz',
    } as const;

    duplicateContentMutation.mutateAsync({
      course_id: courseId,
      content_id: content.id,
      content_type: convertedContentType[type as Exclude<ContentType, 'tutor_zoom_meeting' | 'tutor-google-meet'>],
    });
    onCopy?.();
  };

  return (
    <>
      <div
        {...attributes}
        css={styles.wrapper({ isDragging, isActive: meetingType === type || isDeletePopoverOpen })}
        ref={setNodeRef}
        style={style}
      >
        <div css={styles.iconAndTitle({ isDragging })} {...listeners}>
          <div data-content-icon>
            <SVGIcon
              name={icon.name as IconCollection}
              width={24}
              height={24}
              style={css`
								color: ${icon.color};
							`}
            />
          </div>
          <div data-bar-icon>
            <SVGIcon name="bars" width={24} height={24} />
          </div>
          <p css={styles.title} onClick={handleShowModalOrPopover} onKeyDown={noop}>
            <span dangerouslySetInnerHTML={{ __html: content.title }} />
            <Show when={type === 'tutor_quiz' && !!content.total_question}>
              <span data-question-count>({content.total_question} Questions)</span>
            </Show>
          </p>
        </div>

        <div css={styles.actions} data-actions>
          <Show when={type === 'tutor_quiz'}>
            <Tooltip content={__('Export Quiz', 'tutor')} delay={200}>
              <Show
                when={!isTutorPro}
                fallback={
                  <Show when={isAddonEnabled(Addons.QUIZ_EXPORT_IMPORT)}>
                    <button
                      type="button"
                      css={styles.actionButton}
                      onClick={() => {
                        exportQuizMutation.mutate(content.id);
                      }}
                    >
                      <SVGIcon name="export" width={24} height={24} />
                    </button>
                  </Show>
                }
              >
                <ProBadge size="tiny">
                  <button type="button" css={styles.actionButton} disabled onClick={noop}>
                    <SVGIcon name="export" width={24} height={24} />
                  </button>
                </ProBadge>
              </Show>
            </Tooltip>
          </Show>
          <Tooltip content={__('Edit', 'tutor')} delay={200}>
            <button ref={editButtonRef} type="button" css={styles.actionButton} onClick={handleShowModalOrPopover}>
              <SVGIcon name="edit" width={24} height={24} />
            </button>
          </Tooltip>
          <Show when={!['tutor_zoom_meeting', 'tutor_zoom_meeting'].includes(type)}>
            <Show when={!duplicateContentMutation.isPending} fallback={<LoadingSpinner size={24} />}>
              <Tooltip content={__('Duplicate', 'tutor')} delay={200}>
                <Show
                  when={!isTutorPro}
                  fallback={
                    <button type="button" css={styles.actionButton} onClick={handleDuplicate}>
                      <SVGIcon name="copyPaste" width={24} height={24} />
                    </button>
                  }
                >
                  <ProBadge size="tiny">
                    <button disabled type="button" css={styles.actionButton} onClick={noop}>
                      <SVGIcon name="copyPaste" width={24} height={24} />
                    </button>
                  </ProBadge>
                </Show>
              </Tooltip>
            </Show>
          </Show>
          <Tooltip content={__('Delete', 'tutor')} delay={200}>
            <button
              ref={deleteRef}
              type="button"
              css={styles.actionButton}
              onClick={() => {
                setIsDeletePopoverOpen(true);
              }}
            >
              <SVGIcon name="delete" width={24} height={24} />
            </button>
          </Tooltip>
        </div>
      </div>
      <Popover
        triggerRef={editButtonRef}
        isOpen={meetingType !== null}
        closePopover={() => setMeetingType(null)}
        maxWidth="306px"
      >
        <Show when={meetingType === 'tutor_zoom_meeting'}>
          <ZoomMeetingForm
            data={null}
            topicId={topic.id}
            meetingHost={courseDetails?.zoom_users || {}}
            onCancel={() => setMeetingType(null)}
            meetingId={content.id}
          />
        </Show>
        <Show when={meetingType === 'tutor-google-meet'}>
          <GoogleMeetForm data={null} topicId={topic.id} onCancel={() => setMeetingType(null)} meetingId={content.id} />
        </Show>
      </Popover>
      <ConfirmationPopover
        isOpen={isDeletePopoverOpen}
        triggerRef={deleteRef}
        closePopover={() => setIsDeletePopoverOpen(false)}
        maxWidth="258px"
        title={sprintf(__('Delete "%s"', 'tutor'), content.title)}
        message={__('Are you sure you want to delete this content from your course? This cannot be undone.', 'tutor')}
        animationType={AnimationType.slideUp}
        arrow="auto"
        hideArrow
        confirmButton={{
          text: __('Delete', 'tutor'),
          variant: 'text',
          isDelete: true,
        }}
        cancelButton={{
          text: __('Cancel', 'tutor'),
          variant: 'text',
        }}
        onConfirmation={handleDelete}
      />
    </>
  );
};

export default TopicContent;

const styles = {
  wrapper: ({
    isDragging = false,
    isActive: isMeetingSelected = false,
  }: {
    isDragging: boolean;
    isActive: boolean;
  }) => css`
    width: 100%;
    padding: ${spacing[10]} ${spacing[8]};
    border: 1px solid transparent;
    border-radius: ${borderRadius[6]};
    display: flex;
    justify-content: space-between;
    align-items: center;

    [data-content-icon],
    [data-bar-icon] {
      display: flex;
      height: 24px;
    }

    :hover {
      border-color: ${colorTokens.stroke.border};
      background-color: ${colorTokens.background.white};

      [data-content-icon] {
        display: none;
      }
      [data-bar-icon] {
        display: block;
      }

      [data-actions] {
        opacity: 1;
      }
    }

    ${
      isMeetingSelected &&
      css`
        border-color: ${colorTokens.stroke.border};
        background-color: ${colorTokens.background.white};
        [data-content-icon] {
          display: flex;
        }
        [data-bar-icon] {
          display: none;
        }
        [data-actions] {
          opacity: 1;
        }
      `
    }

    ${
      isDragging &&
      css`
      box-shadow: ${shadow.drag};
      border-color: ${colorTokens.stroke.border};
      background-color: ${colorTokens.background.white};

      [data-actions] {
        opacity: 1;
      }
    `
    }
  `,
  title: css`
    ${typography.caption()};
    color: ${colorTokens.text.title};
    display: flex;
    align-items: center;
    gap: ${spacing[4]};
    cursor: pointer;
    [data-question-count] {
      color: ${colorTokens.text.hints};
    }
  `,
  iconAndTitle: ({ isDragging = false }) => css`
    display: flex;
    align-items: center;
    gap: ${spacing[8]};
    cursor: grab;
    flex-grow: 1;

    [data-bar-icon] {
      display: none;
    }
    ${
      isDragging &&
      css`
      [data-content-icon] {
        display: none;
      }
      [data-bar-icon] {
        display: block;
      }
      cursor: grabbing;
    `
    }
  `,
  actions: css`
    display: flex;
    opacity: 0;
    align-items: start;
    gap: ${spacing[8]};
    justify-content: end;
  `,
  actionButton: css`
    ${styleUtils.resetButton};
    color: ${colorTokens.icon.default};
    display: flex;

    :disabled {
      color: ${colorTokens.icon.disable.background};
      cursor: not-allowed;
    }
  `,
};
