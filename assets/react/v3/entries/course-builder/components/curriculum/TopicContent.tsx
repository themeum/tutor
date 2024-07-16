import { type AnimateLayoutChanges, defaultAnimateLayoutChanges, useSortable } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useRef, useState } from 'react';
import { useFormContext } from 'react-hook-form';

import SVGIcon from '@Atoms/SVGIcon';
import Popover from '@Molecules/Popover';

import { useModal } from '@Components/modals/Modal';
import ZoomMeetingForm from '@CourseBuilderComponents/additional/meeting/ZoomMeetingForm';
import AssignmentModal from '@CourseBuilderComponents/modals/AssignmentModal';
import LessonModal from '@CourseBuilderComponents/modals/LessonModal';
import QuizModal from '@CourseBuilderComponents/modals/QuizModal';
import { useCourseDetails } from '@CourseBuilderContexts/CourseDetailsContext';
import type { CourseTopicWithCollapse } from '@CourseBuilderPages/Curriculum';
import { type ContentType, type ID, useDeleteLessonMutation } from '@CourseBuilderServices/curriculum';

import LoadingSpinner from '@Atoms/LoadingSpinner';
import { borderRadius, colorTokens, shadow, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import GoogleMeetForm from '@CourseBuilderComponents/additional/meeting/GoogleMeetForm';
import type { CourseFormData } from '@CourseBuilderServices/course';
import { useExportQuizMutation } from '@CourseBuilderServices/quiz';
import { styleUtils } from '@Utils/style-utils';
import type { IconCollection } from '@Utils/types';
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

const TopicContent = ({ type, topic, content, isDragging = false, onCopy, onDelete }: TopicContentProps) => {
  const courseDetails = useCourseDetails();
  const form = useFormContext<CourseFormData>();
  const [meetingType, setMeetingType] = useState<'tutor_zoom_meeting' | 'tutor-google-meet' | null>(null);

  const editButtonRef = useRef<HTMLButtonElement>(null);

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
  const deleteLessonMutation = useDeleteLessonMutation();
  const deleteGoogleMeetMutation = useDeleteLessonMutation();
  const deleteZoomMeetingMutation = useDeleteLessonMutation();
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
    if (type === 'lesson' || type === 'tutor_assignments' || type === 'tutor_quiz') {
      deleteLessonMutation.mutate(content.id);
    }

    if (type === 'tutor-google-meet') {
      deleteGoogleMeetMutation.mutate(content.id);
    }

    if (type === 'tutor_zoom_meeting') {
      deleteZoomMeetingMutation.mutate(content.id);
    }
  };

  return (
    <>
      <div
        {...attributes}
        css={styles.wrapper({ isDragging, isMeetingSelected: meetingType === type })}
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
          <p css={styles.title}>
            <span dangerouslySetInnerHTML={{ __html: content.title }} />
            <Show when={type === 'tutor_quiz' && !!content.total_question}>
              <span data-question-count>({content.total_question} Questions)</span>
            </Show>
          </p>
        </div>

        <div css={styles.actions} data-actions>
          <Show when={type === 'tutor_quiz'}>
            <button
              type="button"
              css={styles.actionButton}
              onClick={() => {
                exportQuizMutation.mutate(content.id);
              }}
            >
              <SVGIcon name="upload" width={24} height={24} />
            </button>
          </Show>
          <button ref={editButtonRef} type="button" css={styles.actionButton} onClick={handleShowModalOrPopover}>
            <SVGIcon name="edit" width={24} height={24} />
          </button>
          <button type="button" css={styles.actionButton} onClick={onCopy}>
            <SVGIcon name="copyPaste" width={24} height={24} />
          </button>
          <button type="button" css={styles.actionButton} onClick={handleDelete}>
            {deleteLessonMutation.isPending ? (
              <LoadingSpinner size={24} />
            ) : (
              <SVGIcon name="delete" width={24} height={24} />
            )}
          </button>
          <button
            type="button"
            css={styles.actionButton}
            onClick={() => {
              alert('@TODO: will be implemented later');
            }}
          >
            <SVGIcon name="threeDotsVertical" width={24} height={24} />
          </button>
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
    </>
  );
};

export default TopicContent;

const styles = {
  wrapper: ({
    isDragging = false,
    isMeetingSelected = false,
  }: {
    isDragging: boolean;
    isMeetingSelected: boolean;
  }) => css`
    width: 100%;
    padding: ${spacing[10]} ${spacing[8]};
    cursor: pointer;
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
    [data-question-count] {
      color: ${colorTokens.text.hints};
    }
  `,
  iconAndTitle: ({ isDragging = false }) => css`
    display: flex;
    align-items: center;
    gap: ${spacing[8]};
    cursor: grab;

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
    align-items: center;
    gap: ${spacing[8]};
    justify-content: end;
  `,
  actionButton: css`
    ${styleUtils.resetButton};
    color: ${colorTokens.icon.default};
    display: flex;
  `,
};
