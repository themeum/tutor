import { createPortal } from 'react-dom';
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
import { useFieldArray, useFormContext, useWatch } from 'react-hook-form';

import SVGIcon from '@Atoms/SVGIcon';

import type { QuizForm } from '@CourseBuilderComponents/modals/QuizModal';
import { useQuizModalContext } from '@CourseBuilderContexts/QuizModalContext';
import Question from '@CourseBuilderComponents/curriculum/Question';

import { colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import For from '@Controls/For';
import Show from '@Controls/Show';
import { styleUtils } from '@Utils/style-utils';
import { nanoid } from '@Utils/util';

const QuestionList = () => {
  const [activeSortId, setActiveSortId] = useState<UniqueIdentifier | null>(null);

  const form = useFormContext<QuizForm>();
  const { setActiveQuestionId } = useQuizModalContext();

  const quizTitle = useWatch({ control: form.control, name: 'quiz_title', defaultValue: '' });
  const quizDescription = useWatch({ control: form.control, name: 'quiz_description', defaultValue: '' });

  const {
    append: addQuestion,
    remove: removeQustion,
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
    useSensor(KeyboardSensor, { coordinateGetter: sortableKeyboardCoordinates })
  );

  const activeSortItem = useMemo(() => {
    if (!activeSortId) {
      return null;
    }

    return questionFields.find((item) => item.ID === activeSortId);
  }, [activeSortId, questionFields]);

  const handleAddQuestion = () => {
    const questionId = nanoid();
    addQuestion({
      ID: questionId,
      title: __('Write anything here..', 'tutor'),
      description: '',
      type: 'true-false',
      answerRequired: false,
      options: [
        {
          ID: nanoid(),
          title: __('True', 'tutor'),
        },
        {
          ID: nanoid(),
          title: __('False', 'tutor'),
        },
      ],
      questionMark: 1,
      randomizeQuestion: false,
      showQuestionMark: false,
      answerExplanation: '',
    });
    setActiveQuestionId(questionId);
  };

  if (!quizTitle || !quizDescription) {
    return null;
  }

  return (
    <>
      <div css={styles.questionsLabel}>
        <span>{__('Questions', 'tutor')}</span>
        <button type="button" onClick={handleAddQuestion}>
          <SVGIcon name="plusSquareBrand" />
        </button>
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
                const activeIndex = questionFields.findIndex((item) => item.ID === active.id);
                const overIndex = questionFields.findIndex((item) => item.ID === over.id);

                moveQuestion(activeIndex, overIndex);
              }

              setActiveSortId(null);
            }}
          >
            <SortableContext
              items={questionFields.map((item) => ({ ...item, id: item.ID }))}
              strategy={verticalListSortingStrategy}
            >
              <For each={form.getValues('questions')}>
                {(question, index) => (
                  <Question
                    key={question.ID}
                    question={question}
                    index={index}
                    onRemoveQuestion={() => {
                      removeQustion(index);
                      setActiveQuestionId(null);
                    }}
                  />
                )}
              </For>
            </SortableContext>

            {createPortal(
              <DragOverlay>
                <Show when={activeSortItem}>
                  {(item) => {
                    const index = questionFields.findIndex((question) => question.ID === item.ID);
                    return (
                      <Question
                        key={item.ID}
                        question={item}
                        index={index}
                        onRemoveQuestion={() => {
                          removeQustion(index);
                          setActiveQuestionId(null);
                        }}
                      />
                    );
                  }}
                </Show>
              </DragOverlay>,
              document.body
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
