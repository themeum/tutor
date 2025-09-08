import { useSortable } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useEffect, useRef, useState } from 'react';
import { useFormContext } from 'react-hook-form';

import ProBadge from '@TutorShared/atoms/ProBadge';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import ThreeDots from '@TutorShared/molecules/ThreeDots';

import { useQuizModalContext } from '@CourseBuilderContexts/QuizModalContext';

import { type QuizForm } from '@CourseBuilderServices/quiz';
import { tutorConfig } from '@TutorShared/config/config';
import { borderRadius, Breakpoint, colorTokens, shadow, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import { AnimationType } from '@TutorShared/hooks/useAnimation';
import { type IconCollection } from '@TutorShared/icons/types';
import { animateLayoutChanges } from '@TutorShared/utils/dndkit';
import { validateQuizQuestion } from '@TutorShared/utils/quiz';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { type QuizQuestion, type QuizQuestionType } from '@TutorShared/utils/types';

interface QuestionProps {
  question: QuizQuestion;
  index: number;
  onDuplicateQuestion: (question: QuizQuestion) => void;
  onRemoveQuestion: () => void;
  isOverlay?: boolean;
}

const questionTypeIconMap: Record<Exclude<QuizQuestionType, 'single_choice' | 'image_matching'>, IconCollection> = {
  true_false: 'quizTrueFalse',
  multiple_choice: 'quizMultiChoice',
  open_ended: 'quizEssay',
  fill_in_the_blank: 'quizFillInTheBlanks',
  short_answer: 'quizShortAnswer',
  matching: 'quizImageMatching',
  image_answering: 'quizImageAnswer',
  ordering: 'quizOrdering',
  h5p: 'quizH5p',
};

const isTutorPro = !!tutorConfig.tutor_pro_url;

const Question = ({ question, index, onDuplicateQuestion, onRemoveQuestion, isOverlay = false }: QuestionProps) => {
  const {
    activeQuestionIndex,
    activeQuestionId,
    validationError,
    setActiveQuestionId,
    setValidationError,
    contentType,
  } = useQuizModalContext();
  const form = useFormContext<QuizForm>();
  const [isThreeDotOpen, setIsThreeDotOpen] = useState(false);
  const ref = useRef<HTMLDivElement>(null);

  const activeQuestion = form.getValues(`questions.${activeQuestionIndex}` as 'questions.0');

  const { attributes, listeners, setNodeRef, transform, transition, isDragging } = useSortable({
    id: question.question_id,
    animateLayoutChanges,
  });

  const style = {
    transform: CSS.Transform.toString(transform),
    transition,
    opacity: isDragging ? 0.3 : undefined,
    background: isDragging ? colorTokens.stroke.hover : undefined,
  };

  useEffect(() => {
    if (activeQuestionId === question.question_id) {
      ref.current?.scrollIntoView({
        behavior: 'smooth',
        block: 'center',
        inline: 'center',
      });
    }
  }, [activeQuestionId, question.question_id]);

  return (
    <div
      {...attributes}
      key={question.question_id}
      css={styles.questionItem({
        isActive: String(activeQuestionId) === String(question.question_id),
        isDragging: isOverlay,
        isThreeDotsOpen: isThreeDotOpen,
      })}
      ref={(element) => {
        setNodeRef(element);
        // @ts-expect-error
        ref.current = element;
      }}
      style={style}
      onClick={() => {
        if (activeQuestionId === question.question_id) {
          return;
        }

        const validation = validateQuizQuestion(activeQuestion);

        if (validation !== true) {
          setValidationError(validation);
          return;
        }

        setValidationError(null);
        setActiveQuestionId(question.question_id);
      }}
      onKeyDown={(event) => {
        if (event.key === 'Enter' || event.key === ' ') {
          if (activeQuestionId === question.question_id) {
            return;
          }

          const validation = validateQuizQuestion(activeQuestion);

          if (validation !== true) {
            setValidationError(validation);
            return;
          }

          setValidationError(null);
          setActiveQuestionId(question.question_id);
        }
      }}
    >
      <div css={styles.iconAndSerial({ isDragging: isOverlay })} data-icon-serial>
        <span data-serial>{index + 1}</span>
        <button data-drag-icon {...listeners} type="button" css={styles.dragButton}>
          <SVGIcon data-drag-icon name="dragVertical" width={24} height={24} />
        </button>
        <SVGIcon
          name={
            questionTypeIconMap[question.question_type as Exclude<QuizQuestionType, 'single_choice' | 'image_matching'>]
          }
          width={24}
          height={24}
          data-question-icon
        />
      </div>
      <span
        css={styles.questionTitle({
          isActive: String(activeQuestionId) === String(question.question_id),
        })}
      >
        {question.question_title}
      </span>
      <ThreeDots
        isOpen={isThreeDotOpen}
        onClick={(event) => {
          event.stopPropagation();
          const validation = validateQuizQuestion(activeQuestion);
          setIsThreeDotOpen(true);
          if (validation !== true) {
            setValidationError(validation);
          }
        }}
        animationType={AnimationType.slideDown}
        closePopover={() => setIsThreeDotOpen(false)}
        dotsOrientation="vertical"
        maxWidth={isTutorPro ? '150px' : '160px'}
        isInverse
        size="small"
        data-three-dots
      >
        {!validationError && contentType !== 'tutor_h5p_quiz' && (
          <ThreeDots.Option
            text={
              <div css={styles.duplicate}>
                {__('Duplicate', 'tutor')}
                {!isTutorPro && <ProBadge size="small" content={__('Pro', 'tutor')} />}
              </div>
            }
            icon={<SVGIcon name="duplicate" width={24} height={24} />}
            disabled={!isTutorPro}
            onClick={(event) => {
              event.stopPropagation();
              onDuplicateQuestion(question);
              setIsThreeDotOpen(false);
            }}
          />
        )}
        <ThreeDots.Option
          isTrash
          text={__('Delete', 'tutor')}
          icon={<SVGIcon name="delete" width={24} height={24} />}
          onClick={(event) => {
            event.stopPropagation();
            onRemoveQuestion();
            setIsThreeDotOpen(false);
          }}
        />
      </ThreeDots>
    </div>
  );
};

export default Question;

const styles = {
  questionItem: ({
    isActive = false,
    isDragging = false,
    isThreeDotsOpen = false,
  }: {
    isActive: boolean;
    isDragging: boolean;
    isThreeDotsOpen: boolean;
  }) => css`
    padding: ${spacing[10]} ${spacing[8]} ${spacing[10]} ${spacing[28]};
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: ${spacing[12]};
    border-bottom: 1px solid ${colorTokens.stroke.divider};
    cursor: pointer;
    transition:
      border 0.3s ease-in-out,
      background-color 0.3s ease-in-out;

    [data-three-dots] {
      opacity: 0;
      background: transparent;
      svg {
        color: ${colorTokens.icon.default};
      }

      :focus-visible {
        opacity: 1;
      }
    }

    ${isActive &&
    css`
      color: ${colorTokens.text.brand};
      background-color: ${colorTokens.background.white};
      [data-icon-serial] {
        border-top-right-radius: 3px;
        border-bottom-right-radius: 3px;
        border-color: transparent;
      }
    `}

    ${isThreeDotsOpen &&
    css`
      [data-three-dots] {
        opacity: 1;
      }
    `}

    :hover {
      background-color: ${colorTokens.background.hover};

      [data-serial] {
        display: none;
      }

      [data-drag-icon] {
        display: block;
      }

      [data-icon-serial] {
        border-top-right-radius: 3px;
        border-bottom-right-radius: 3px;
        border-color: transparent;
      }

      [data-three-dots] {
        opacity: 1;
      }
    }

    :focus-visible {
      outline: 2px solid ${colorTokens.stroke.brand};
      outline-offset: -2px;
      border-radius: ${borderRadius.card};

      [data-three-dots] {
        opacity: 1;
      }
    }

    ${isDragging &&
    css`
      box-shadow: ${shadow.drag};
      background-color: ${colorTokens.background.white};
      border-radius: ${borderRadius.card};

      :hover {
        background-color: ${colorTokens.background.white};
      }
    `}

    ${Breakpoint.smallMobile} {
      padding: ${spacing[8]} ${spacing[8]} ${spacing[8]} ${spacing[8]};

      [data-three-dots] {
        opacity: 1;
      }
    }
  `,
  iconAndSerial: ({ isDragging = false }: { isDragging: boolean }) => css`
    display: grid;
    grid-template-columns: 1fr 1fr;
    align-items: center;
    border-radius: 3px 0 0 3px;
    width: 64px;
    padding: ${spacing[4]} ${spacing[8]} ${spacing[4]} ${spacing[4]};
    flex-shrink: 0;
    column-gap: ${spacing[12]};
    place-items: center center;

    [data-drag-icon] {
      display: none;
      color: ${colorTokens.icon.hints};
      cursor: ${isDragging ? 'grabbing' : 'grab'};
    }

    [data-question-icon] {
      flex-shrink: 0;
    }

    svg {
      flex-shrink: 0;
    }

    [data-serial] {
      width: 24px;
      display: block;
      ${typography.caption('medium')}
      text-align: center;
      flex-grow: 1;
    }
  `,
  questionTitle: ({ isActive = false }: { isActive: boolean }) => css`
    ${typography.small(isActive ? 'medium' : 'regular')};
    color: ${isActive ? colorTokens.text.brand : colorTokens.text.subdued};
    flex-grow: 1;
  `,
  duplicate: css`
    display: flex;
    align-items: center;
    gap: ${spacing[4]};
  `,
  dragButton: css`
    ${styleUtils.resetButton};

    &:focus,
    &:active,
    &:hover {
      background: none;
    }
  `,
};
