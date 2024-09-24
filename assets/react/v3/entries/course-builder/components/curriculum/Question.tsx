import { useSortable } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useEffect, useRef, useState } from 'react';
import { useFormContext } from 'react-hook-form';

import ProBadge from '@Atoms/ProBadge';
import SVGIcon from '@Atoms/SVGIcon';
import ThreeDots from '@Molecules/ThreeDots';

import { useQuizModalContext } from '@CourseBuilderContexts/QuizModalContext';
import type { QuizForm, QuizQuestion, QuizQuestionType } from '@CourseBuilderServices/quiz';

import { tutorConfig } from '@Config/config';
import { colorTokens, shadow, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import type { ID } from '@CourseBuilderServices/curriculum';
import { validateQuizQuestion } from '@CourseBuilderUtils/utils';
import { AnimationType } from '@Hooks/useAnimation';
import { animateLayoutChanges } from '@Utils/dndkit';
import { styleUtils } from '@Utils/style-utils';
import type { IconCollection } from '@Utils/types';

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
};

const isTutorPro = !!tutorConfig.tutor_pro_url;

const Question = ({ question, index, onDuplicateQuestion, onRemoveQuestion, isOverlay = false }: QuestionProps) => {
  const { activeQuestionIndex, activeQuestionId, setActiveQuestionId, setValidationError } = useQuizModalContext();
  const form = useFormContext<QuizForm>();
  const [selectedQuestionId, setSelectedQuestionId] = useState<ID>('');
  const ref = useRef<HTMLDivElement>(null);

  const { attributes, listeners, setNodeRef, transform, transition, isDragging } = useSortable({
    id: question.question_id,
    animateLayoutChanges,
  });

  const style = {
    transform: CSS.Transform.toString(transform),
    transition,
    opacity: isDragging ? 0.3 : undefined,
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
        isThreeDotsOpen: selectedQuestionId === question.question_id,
      })}
      ref={(element) => {
        setNodeRef(element);
        // @ts-expect-error
        ref.current = element;
      }}
      style={style}
      tabIndex={-1}
      onClick={() => {
        if (activeQuestionId === question.question_id) {
          return;
        }

        const validation = validateQuizQuestion(activeQuestionIndex, form);

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

          const validation = validateQuizQuestion(activeQuestionIndex, form);

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
        <SVGIcon
          name={
            questionTypeIconMap[question.question_type as Exclude<QuizQuestionType, 'single_choice' | 'image_matching'>]
          }
          width={24}
          height={24}
          data-question-icon
        />
        <button {...listeners} type="button" css={styleUtils.resetButton}>
          <SVGIcon name="dragVertical" data-drag-icon width={24} height={24} />
        </button>
        <span data-serial>{index + 1}</span>
      </div>
      <span
        css={styles.questionTitle({
          isActive: String(activeQuestionId) === String(question.question_id),
        })}
      >
        {question.question_title}
      </span>
      <ThreeDots
        isOpen={selectedQuestionId === question.question_id}
        onClick={(event) => {
          const validation = validateQuizQuestion(activeQuestionIndex, form);
          if (validation !== true) {
            event.stopPropagation();

            if (activeQuestionId === question.question_id) {
              setSelectedQuestionId(question.question_id);
            }

            setValidationError(validation);
            return;
          }
          setSelectedQuestionId(question.question_id);
        }}
        animationType={AnimationType.slideDown}
        closePopover={() => setSelectedQuestionId('')}
        dotsOrientation="vertical"
        maxWidth={isTutorPro ? '150px' : '160px'}
        isInverse
        arrowPosition="auto"
        size="small"
        hideArrow
        data-three-dots
      >
        {validateQuizQuestion(activeQuestionIndex, form) === true && (
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
              setSelectedQuestionId('');
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
            setValidationError(null);
            setSelectedQuestionId('');
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
    padding: ${spacing[10]} ${spacing[8]} ${spacing[10]}  ${spacing[28]};
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: ${spacing[12]};
    border-bottom: 1px solid ${colorTokens.stroke.divider};
    cursor: pointer;
    transition: border 0.3s ease-in-out, background-color 0.3s ease-in-out;

    [data-three-dots] {
      opacity: 0;
      background: transparent;
      svg {
        color: ${colorTokens.icon.default};
      }
    }

    ${
      isActive &&
      css`
        color: ${colorTokens.text.brand};
        background-color: ${colorTokens.background.white};
        [data-icon-serial] {
          border-top-right-radius: 3px;
          border-bottom-right-radius: 3px;
          border-color: transparent;
        }
      `
    }

    ${
      isThreeDotsOpen &&
      css`
        [data-three-dots] {
          opacity: 1;
        }
      `
    }

    :hover {
      background-color: ${colorTokens.background.hover};

      [data-question-icon] {
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

    ${
      isDragging &&
      css`
        box-shadow: ${shadow.drag};
        background-color: ${colorTokens.background.white};

        :hover {
          background-color: ${colorTokens.background.white};
        }
      `
    }
  `,
  iconAndSerial: ({
    isDragging = false,
  }: {
    isDragging: boolean;
  }) => css`
    display: flex;
    align-items: center;
    background-color: ${colorTokens.bg.white};
    border-radius: 3px 0 0 3px;
    width: 56px;
    padding: ${spacing[4]} ${spacing[8]} ${spacing[4]} ${spacing[4]};
    border-right: 1px solid ${colorTokens.stroke.divider};
    flex-shrink: 0;

    [data-drag-icon] {
      display: none;
      color: ${colorTokens.icon.hints};
      cursor: ${isDragging ? 'grabbing' : 'grab'};
    }

    svg {
      flex-shrink: 0;
    }

    [data-serial] {
      ${typography.caption('medium')}
      text-align: right;
      width: 100%;
    }
  `,
  questionTitle: ({
    isActive = false,
  }: {
    isActive: boolean;
  }) => css`
    ${typography.small(isActive ? 'medium' : 'regular')};
    color: ${isActive ? colorTokens.text.brand : colorTokens.text.subdued};
    flex-grow: 1;
  `,
  duplicate: css`
    display: flex;
    align-items: center;
    gap: ${spacing[4]};
  `,
};
