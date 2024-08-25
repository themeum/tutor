import { useSortable } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useState } from 'react';
import { useFormContext } from 'react-hook-form';

import SVGIcon from '@Atoms/SVGIcon';
import ThreeDots from '@Molecules/ThreeDots';

import { useQuizModalContext } from '@CourseBuilderContexts/QuizModalContext';
import type { QuizForm, QuizQuestion, QuizQuestionType } from '@CourseBuilderServices/quiz';

import { useToast } from '@Atoms/Toast';
import { borderRadius, colorTokens, shadow, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import type { ID } from '@CourseBuilderServices/curriculum';
import { animateLayoutChanges } from '@Utils/dndkit';
import { styleUtils } from '@Utils/style-utils';
import type { IconCollection } from '@Utils/types';

interface QuestionProps {
  question: QuizQuestion;
  index: number;
  onDuplicateQuestion: (question: QuizQuestion) => void;
  onRemoveQuestion: () => void;
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

const Question = ({ question, index, onDuplicateQuestion, onRemoveQuestion }: QuestionProps) => {
  const { activeQuestionIndex, activeQuestionId, setActiveQuestionId } = useQuizModalContext();
  const form = useFormContext<QuizForm>();
  const [selectedQuestionId, setSelectedQuestionId] = useState<ID>('');
  const { showToast } = useToast();

  const { attributes, listeners, setNodeRef, transform, transition, isDragging } = useSortable({
    id: question.question_id,
    animateLayoutChanges,
  });

  const style = {
    transform: CSS.Transform.toString(transform),
    transition,
    opacity: isDragging ? 0.3 : undefined,
  };

  return (
    <div
      {...attributes}
      key={question.question_id}
      css={styles.questionItem({ isActive: String(activeQuestionId) === String(question.question_id), isDragging })}
      ref={setNodeRef}
      style={style}
      tabIndex={-1}
      onClick={() => {
        const activeQuestionType = form.watch(`questions.${activeQuestionIndex}.question_type`);
        const answers = form.watch(`questions.${activeQuestionIndex}.question_answers`);

        if (['multiple_choice', 'true_false'].includes(activeQuestionType)) {
          if (answers.length === 0) {
            showToast({
              message: __('Please add option', 'tutor'),
              type: 'danger',
            });
            return;
          }

          const hasCorrectAnswer = answers.some((answer) => answer.is_correct === '1');

          if (!hasCorrectAnswer) {
            showToast({
              message: __('Please select a correct answer', 'tutor'),
              type: 'danger',
            });
            return;
          }
        }

        setActiveQuestionId(question.question_id);
      }}
      onKeyDown={(event) => {
        if (event.key === 'Enter' || event.key === ' ') {
          setActiveQuestionId(question.question_id);
        }
      }}
    >
      <div css={styles.iconAndSerial({ isDragging })} data-icon-serial>
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
      <span css={styles.questionTitle}>{question.question_title}</span>
      <ThreeDots
        isOpen={selectedQuestionId === question.question_id}
        onClick={() => setSelectedQuestionId(question.question_id)}
        closePopover={() => setSelectedQuestionId('')}
        dotsOrientation="vertical"
        maxWidth="150px"
        isInverse
        arrowPosition="auto"
        size="small"
        hideArrow
        data-three-dots
      >
        <ThreeDots.Option
          text={__('Duplicate', 'tutor')}
          icon={<SVGIcon name="duplicate" width={24} height={24} />}
          onClick={(event) => {
            event.stopPropagation();
            onDuplicateQuestion(question);
            setSelectedQuestionId('');
          }}
        />
        <ThreeDots.Option
          isTrash
          text={__('Delete', 'tutor')}
          icon={<SVGIcon name="delete" width={24} height={24} />}
          onClick={(event) => {
            event.stopPropagation();
            onRemoveQuestion();
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
  }: {
    isActive: boolean;
    isDragging: boolean;
  }) => css`
    margin-right: ${spacing[20]};
    padding: ${spacing[10]} ${spacing[8]};
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: ${spacing[12]};
    border: 1px solid transparent;
    border-radius: ${borderRadius.min};
    cursor: pointer;
    transition: border 0.3s ease-in-out, background-color 0.3s ease-in-out;

    [data-three-dots] {
      opacity: 0;
      svg {
        color: ${colorTokens.icon.default};
      }
    }

    ${
      isActive &&
      css`
      border-color: ${colorTokens.stroke.brand};
      background-color: ${colorTokens.background.active};
      [data-icon-serial] {
        border-top-right-radius: 3px;
        border-bottom-right-radius: 3px;
        border-color: transparent;
      }
    `
    }
    :hover {
      background-color: ${colorTokens.background.white};

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
  questionTitle: css`
    ${typography.small()};
    color: ${colorTokens.text.subdued};
    max-width: 170px;
    width: 100%;
  `,
};
