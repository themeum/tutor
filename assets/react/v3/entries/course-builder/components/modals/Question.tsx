import SVGIcon from '@Atoms/SVGIcon';
import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { QuizQuestion, QuizQuestionType } from '@CourseBuilderServices/quiz';
import ThreeDots from '@Molecules/ThreeDots';
import { IconCollection, isDefined } from '@Utils/types';
import { css } from '@emotion/react';
import { useCallback, useEffect, useRef, useState } from 'react';
import { animateLayoutChanges } from '@Utils/dndkit';
import { useSortable } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';

interface QuestionProps {
  question: QuizQuestion;
  index: number;
  selectedQuestionId: number | null;
  setSelectedQuestionId: (id: number | null) => void;
}

const questionTypeIconMap: Record<QuizQuestionType, IconCollection> = {
  'true-false': 'quizTrueFalse',
  'single-choice': 'quizSingleChoice',
  'multiple-choice': 'quizMultiChoice',
  'open-ended': 'quizEssay',
  'fill-in-the-blanks': 'quizFillInTheBlanks',
  'short-answer': 'quizShortAnswer',
  matching: 'quizMatching',
  'image-matching': 'quizImageMatching',
  'image-answering': 'quizImageAnswer',
  ordering: 'quizOrdering',
};

export const Question = ({ question, index, selectedQuestionId, setSelectedQuestionId }: QuestionProps) => {
  const [isActive, setIsActive] = useState<boolean>(index === 0);
  const { attributes, listeners, setNodeRef, transform, transition, isDragging } = useSortable({
    id: question.ID,
    animateLayoutChanges,
  });

  const wrapperRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    const handleOutsideClick = (event: MouseEvent) => {
      if (isDefined(wrapperRef.current) && !wrapperRef.current.contains(event.target as HTMLDivElement)) {
        setIsActive(false);
      }
    };

    document.addEventListener('click', handleOutsideClick);

    return () => document.removeEventListener('click', handleOutsideClick);
  }, []);

  const combinedRef = useCallback(
    (node: HTMLDivElement) => {
      if (node) {
        setNodeRef(node);
        (wrapperRef as any).current = node;
      }
    },
    [setNodeRef, wrapperRef]
  );

  const style = {
    transform: CSS.Transform.toString(transform),
    transition,
    opacity: isDragging ? 0.3 : undefined,
  };

  return (
    <div {...attributes} key={question.ID} css={styles.questionItem({ isActive })} ref={combinedRef} style={style} onClick={() => setIsActive(true)}>
      <div css={styles.iconAndSerial} data-icon-serial>
        <SVGIcon name={questionTypeIconMap[question.type]} width={24} height={24} data-question-icon />
        <div {...listeners} role="button">
          <SVGIcon name="dragVertical" data-drag-icon width={24} height={24} />
        </div>
        <span data-serial>{index + 1}</span>
      </div>
      <span css={styles.questionTitle}>{question.title}</span>
      <ThreeDots
        isOpen={selectedQuestionId === question.ID}
        onClick={() => setSelectedQuestionId(question.ID)}
        closePopover={() => setSelectedQuestionId(null)}
        dotsOrientation="vertical"
        maxWidth="220px"
        isInverse
        arrowPosition="auto"
        hideArrow
        data-three-dots
      >
        <ThreeDots.Option text="Duplicate" icon={<SVGIcon name="duplicate" width={24} height={24} />} />
        <ThreeDots.Option text="Delete" icon={<SVGIcon name="delete" width={24} height={24} />} />
      </ThreeDots>
    </div>
  );
};

const styles = {
  questionItem: ({ isActive = false }) => css`
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

    ${isActive &&
    css`
      border-color: ${colorTokens.stroke.brand};
      background-color: ${colorTokens.background.active};
      [data-icon-serial] {
        border-top-right-radius: 3px;
        border-bottom-right-radius: 3px;
        border-color: transparent;
      }
    `}
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
  `,
  iconAndSerial: css`
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
      cursor: grab;
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
    max-width: 170px;
    width: 100%;
  `,
};
