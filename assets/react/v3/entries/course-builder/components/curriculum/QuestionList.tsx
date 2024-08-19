import {
  DndContext,
  DragOverlay,
  KeyboardSensor,
  PointerSensor,
  type UniqueIdentifier,
  closestCenter,
  useSensor,
  useSensors,
} from '@dnd-kit/core';
import { restrictToVerticalAxis, restrictToWindowEdges } from '@dnd-kit/modifiers';
import { SortableContext, sortableKeyboardCoordinates, verticalListSortingStrategy } from '@dnd-kit/sortable';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useMemo, useState } from 'react';
import { createPortal } from 'react-dom';
import { useFieldArray, useFormContext } from 'react-hook-form';

import SVGIcon from '@Atoms/SVGIcon';

import Question from '@CourseBuilderComponents/curriculum/Question';
import { useQuizModalContext } from '@CourseBuilderContexts/QuizModalContext';

import LoadingSpinner from '@Atoms/LoadingSpinner';
import { colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import For from '@Controls/For';
import Show from '@Controls/Show';
import type { ID } from '@CourseBuilderServices/curriculum';
import {
  type QuizForm,
  useCreateQuizQuestionMutation,
  useQuizQuestionSortingMutation,
} from '@CourseBuilderServices/quiz';
import { styleUtils } from '@Utils/style-utils';
import { moveTo } from '@Utils/util';

interface QuestionListProps {
  quizId?: ID;
}

const QuestionList = ({ quizId }: QuestionListProps) => {
  const [activeSortId, setActiveSortId] = useState<UniqueIdentifier | null>(null);

  const form = useFormContext<QuizForm>();
  const { setActiveQuestionId } = useQuizModalContext();
  const createQuizQuestion = useCreateQuizQuestionMutation();
  const quizQuestionSortingMutation = useQuizQuestionSortingMutation();

  const {
    remove: removeQuestion,
    move: moveQuestion,
    fields: questionFields,
  } = useFieldArray({
    control: form.control,
    name: 'questions',
  });

  const sensors = useSensors(
    useSensor(PointerSensor, {
      activationConstraint: {
        distance: 10,
      },
    }),
    useSensor(KeyboardSensor, { coordinateGetter: sortableKeyboardCoordinates }),
  );

  const activeSortItem = useMemo(() => {
    if (!activeSortId) {
      return null;
    }

    return questionFields.find((item) => item.question_id === activeSortId);
  }, [activeSortId, questionFields]);

  const handleAddQuestion = () => {
    if (quizId) {
      createQuizQuestion.mutate(quizId);
    }
  };

  if (!quizId) {
    return null;
  }

  return (
    <>
      <div css={styles.questionsLabel}>
        <span>{__('Questions', 'tutor')}</span>
        <Show when={!createQuizQuestion.isPending} fallback={<LoadingSpinner size={32} />}>
          <button disabled={createQuizQuestion.isPending} type="button" onClick={handleAddQuestion}>
            <SVGIcon name="plusSquareBrand" width={32} height={32} />
          </button>
        </Show>
      </div>

      <div css={styles.questionList}>
        <Show when={questionFields.length > 0} fallback={<div>{__('No questions added yet.', 'tutor')}</div>}>
          <DndContext
            sensors={sensors}
            collisionDetection={closestCenter}
            modifiers={[restrictToVerticalAxis, restrictToWindowEdges]}
            onDragStart={(event) => {
              setActiveSortId(event.active.id);
            }}
            onDragEnd={(event) => {
              const { active, over } = event;
              if (!over) {
                return;
              }

              if (active.id !== over.id) {
                const activeIndex = questionFields.findIndex((item) => item.question_id === active.id);
                const overIndex = questionFields.findIndex((item) => item.question_id === over.id);

                const updatedQuestionOrder = moveTo(form.watch('questions'), activeIndex, overIndex);
                quizQuestionSortingMutation.mutate({
                  quiz_id: quizId,
                  sorted_question_ids: updatedQuestionOrder.map((question) => question.question_id),
                });
                moveQuestion(activeIndex, overIndex);
              }

              setActiveSortId(null);
            }}
          >
            <SortableContext
              items={questionFields.map((item) => ({ ...item, id: item.question_id }))}
              strategy={verticalListSortingStrategy}
            >
              <For each={form.getValues('questions')}>
                {(question, index) => (
                  <Question
                    key={question.question_id}
                    question={question}
                    index={index}
                    onRemoveQuestion={() => {
                      removeQuestion(index);
                      setActiveQuestionId('');
                    }}
                  />
                )}
              </For>
            </SortableContext>

            {createPortal(
              <DragOverlay>
                <Show when={activeSortItem}>
                  {(item) => {
                    const index = questionFields.findIndex((question) => question.question_id === item.question_id);
                    return (
                      <Question
                        key={item.question_id}
                        question={item}
                        index={index}
                        onRemoveQuestion={() => {
                          removeQuestion(index);
                          setActiveQuestionId('');
                        }}
                      />
                    );
                  }}
                </Show>
              </DragOverlay>,
              document.body,
            )}
          </DndContext>
        </Show>
      </div>
    </>
  );
};

export default QuestionList;

const styles = {
  questionsLabel: css`
    display: flex;
    gap: ${spacing[4]};
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid ${colorTokens.stroke.divider};
    padding: ${spacing[16]} ${spacing[16]} ${spacing[16]} ${spacing[28]};

    ${typography.caption('medium')};
    color: ${colorTokens.text.subdued};

    button {
      ${styleUtils.resetButton};
      width: 32px;
      height: 32px;

      svg {
        color: ${colorTokens.action.primary.default};
        width: 100%;
        height: 100%;
      }
    }
  `,
  questionList: css`
    padding: ${spacing[8]} ${spacing[20]};
  `,
};
