import { useSortable } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import { css } from '@emotion/react';

import SVGIcon from '@TutorShared/atoms/SVGIcon';
import { borderRadius, Breakpoint, colorTokens, shadow, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import { animateLayoutChanges } from '@TutorShared/utils/dndkit';
import type { FormControllerProps } from '@TutorShared/utils/form';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { type ID, type QuizQuestionOption } from '@TutorShared/utils/types';
import { nanoid } from '@TutorShared/utils/util';

interface FormTrueFalseProps extends FormControllerProps<QuizQuestionOption> {
  index: number;
  onCheckCorrectAnswer: () => void;
  isOverlay?: boolean;
  questionId: ID;
}

const FormTrueFalse = ({ field, onCheckCorrectAnswer, isOverlay = false, questionId }: FormTrueFalseProps) => {
  const inputValue = field.value ?? {
    answer_id: nanoid(),
    answer_title: '',
    is_correct: '0',
    belongs_question_id: questionId,
    belongs_question_type: 'true_false',
  };

  const { attributes, listeners, setNodeRef, transform, transition, isDragging } = useSortable({
    id: inputValue.answer_id || 0,
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
      css={styles.option({ isSelected: !!Number(inputValue.is_correct), isOverlay })}
      tabIndex={-1}
      ref={setNodeRef}
      style={style}
    >
      <button data-check-button type="button" css={styleUtils.optionCheckButton} onClick={onCheckCorrectAnswer}>
        <SVGIcon name={Number(inputValue.is_correct) ? 'checkFilled' : 'check'} height={32} width={32} />
      </button>
      <div css={styles.optionLabel({ isSelected: !!Number(inputValue.is_correct), isDragging, isOverlay })}>
        <span>{inputValue.answer_title}</span>

        <button
          {...listeners}
          type="button"
          css={styleUtils.optionDragButton({
            isOverlay,
          })}
          data-visually-hidden
        >
          <SVGIcon name="dragVertical" height={24} width={24} />
        </button>
      </div>
    </div>
  );
};

export default FormTrueFalse;

const styles = {
  option: ({ isSelected, isOverlay }: { isSelected: boolean; isOverlay: boolean }) => css`
    ${styleUtils.display.flex()};
    ${typography.caption('medium')};
    align-items: center;
    color: ${colorTokens.text.subdued};
    gap: ${spacing[10]};
    height: 48px;
    align-items: center;

    [data-check-button] {
      color: ${colorTokens.icon.default};
      opacity: 0;
      fill: none;
      flex-shrink: 0;

      &:focus-visible {
        opacity: 1;
      }
    }

    &:focus-within {
      [data-check-button] {
        opacity: 1;
      }
    }

    &:hover {
      [data-check-button] {
        opacity: ${isOverlay ? 0 : 1};
      }
    }

    ${isSelected &&
    css`
      [data-check-button] {
        opacity: 1;
        color: ${colorTokens.bg.success};
      }
    `}
  `,
  optionLabel: ({
    isSelected,
    isDragging,
    isOverlay,
  }: {
    isSelected: boolean;
    isDragging: boolean;
    isOverlay: boolean;
  }) => css`
    display: grid;
    grid-template-columns: 1fr auto 1fr;
    align-items: center;
    width: 100%;
    border-radius: ${borderRadius.card};
    padding: ${spacing[12]} ${spacing[16]};
    background-color: ${colorTokens.background.white};
    text-transform: capitalize;

    [data-visually-hidden] {
      opacity: 0;
    }

    &:hover {
      outline: 1px solid ${colorTokens.stroke.hover};

      [data-visually-hidden] {
        opacity: 1;
      }
    }

    &:focus-within {
      [data-visually-hidden] {
        opacity: 1;
      }
    }

    ${isSelected &&
    css`
      background-color: ${colorTokens.background.success.fill40};
      color: ${colorTokens.text.primary};

      &:hover {
        outline: 1px solid ${colorTokens.stroke.success.fill70};
      }
    `}

    ${isDragging &&
    css`
      background-color: ${colorTokens.stroke.hover};
    `}

    ${isOverlay &&
    css`
      box-shadow: ${shadow.drag};
    `}

    ${Breakpoint.smallTablet} {
      [data-visually-hidden] {
        opacity: 1;
      }
    }
  `,
};
