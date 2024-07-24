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
import { useEffect, useMemo, useState } from 'react';
import { createPortal } from 'react-dom';
import { Controller, useFieldArray, useFormContext, useWatch } from 'react-hook-form';

import FormTrueFalse from '@Components/fields/quiz/FormTrueFalse';
import { useQuizModalContext } from '@CourseBuilderContexts/QuizModalContext';

import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import For from '@Controls/For';
import Show from '@Controls/Show';
import {
  type QuizForm,
  type QuizQuestionOption,
  useQuizQuestionAnswerOrderingMutation,
} from '@CourseBuilderServices/quiz';
import { styleUtils } from '@Utils/style-utils';
import { moveTo } from '@Utils/util';

const TrueFalse = () => {
  const [activeSortId, setActiveSortId] = useState<UniqueIdentifier | null>(null);
  const form = useFormContext<QuizForm>();
  const { activeQuestionId, activeQuestionIndex, quizId } = useQuizModalContext();

  const quizQuestionAnswerOrderingMutation = useQuizQuestionAnswerOrderingMutation(quizId);

  const { fields: optionsFields, move: moveOption } = useFieldArray({
    control: form.control,
    name: `questions.${activeQuestionIndex}.question_answers` as 'questions.0.question_answers',
  });

  const filteredOptionsFields = optionsFields.reduce(
    (allOptions, option, index) => {
      if (option.belongs_question_type === 'true_false') {
        allOptions.push({ ...option, index: index });
      }

      return allOptions;
    },
    [] as Array<QuizQuestionOption & { index: number }>,
  );

  const sensors = useSensors(
    useSensor(PointerSensor, {
      activationConstraint: {
        distance: 10,
      },
    }),
    useSensor(KeyboardSensor, { coordinateGetter: sortableKeyboardCoordinates }),
  );

  const currentOptions = useWatch({
    control: form.control,
    name: `questions.${activeQuestionIndex}.question_answers` as 'questions.0.question_answers',
    defaultValue: [],
  });

  const activeSortItem = useMemo(() => {
    if (!activeSortId) {
      return null;
    }

    return filteredOptionsFields.find((item) => item.answer_id === activeSortId);
  }, [activeSortId, filteredOptionsFields]);

  // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
  useEffect(() => {
    const changedOptions = currentOptions.filter((option) => {
      const index = optionsFields.findIndex((item) => item.answer_id === option.answer_id);
      const previousOption = optionsFields[index] || {};
      return option.is_correct !== previousOption.is_correct;
    });

    if (changedOptions.length === 0) {
      return;
    }

    const changedOptionIndex = optionsFields.findIndex((item) => item.answer_id === changedOptions[0].answer_id);

    const updatedOptions = [...optionsFields];
    updatedOptions[changedOptionIndex] = Object.assign({}, updatedOptions[changedOptionIndex], { is_correct: '1' });

    for (const [index, option] of updatedOptions.entries()) {
      if (index !== changedOptionIndex) {
        updatedOptions[index] = { ...option, is_correct: '0' };
      }
    }

    form.setValue(`questions.${activeQuestionIndex}.question_answers`, updatedOptions);
  }, [currentOptions]);

  return (
    <div css={styles.optionWrapper}>
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
            const activeIndex = optionsFields.findIndex((item) => item.answer_id === active.id);
            const overIndex = optionsFields.findIndex((item) => item.answer_id === over.id);

            const updatedOptionsOrder = moveTo(
              form.watch(`questions.${activeQuestionIndex}.question_answers`),
              activeIndex,
              overIndex,
            );

            quizQuestionAnswerOrderingMutation.mutate({
              question_id: activeQuestionId,
              sorted_answer_ids: updatedOptionsOrder.map((option) => option.answer_id),
            });

            moveOption(activeIndex, overIndex);
          }

          setActiveSortId(null);
        }}
      >
        <SortableContext
          items={filteredOptionsFields.map((item) => ({ ...item, id: item.answer_id }))}
          strategy={verticalListSortingStrategy}
        >
          <For each={filteredOptionsFields}>
            {(option, index) => (
              <Controller
                key={option.answer_id}
                control={form.control}
                name={
                  `questions.${activeQuestionIndex}.question_answers.${option.index}` as 'questions.0.question_answers.0'
                }
                render={(controllerProps) => <FormTrueFalse {...controllerProps} index={index} />}
              />
            )}
          </For>
        </SortableContext>

        {createPortal(
          <DragOverlay>
            <Show when={activeSortItem}>
              {(item) => {
                const index = optionsFields.findIndex((option) => option.answer_id === item.answer_id);
                return (
                  <Controller
                    key={activeSortId}
                    control={form.control}
                    name={
                      `questions.${activeQuestionIndex}.question_answers.${index}` as 'questions.0.question_answers.0'
                    }
                    render={(controllerProps) => <FormTrueFalse {...controllerProps} index={index} />}
                  />
                );
              }}
            </Show>
          </DragOverlay>,
          document.body,
        )}
      </DndContext>
    </div>
  );
};

export default TrueFalse;

const styles = {
  optionWrapper: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[12]};
  `,
  option: ({
    isSelected,
  }: {
    isSelected: boolean;
  }) => css`
    ${styleUtils.display.flex()};
    ${typography.caption('medium')};
    align-items: center;
    color: ${colorTokens.text.subdued};
    gap: ${spacing[10]};
    height: 48px;
    align-items: center;

    [data-check-icon] {
      opacity: 0;
      transition: opacity 0.15s ease-in-out;
      fill: none;
      flex-shrink: 0;
    }

    &:hover {
      [data-check-icon] {
        opacity: 1;
      }
    }


    ${
      isSelected &&
      css`
        [data-check-icon] {
          opacity: 1;
          color: ${colorTokens.bg.success};
        }
      `
    }
  `,
  optionLabel: ({
    isSelected,
  }: {
    isSelected: boolean;
  }) => css`
    display: grid;
    grid-template-columns: 1fr auto 1fr;
    align-items: center;
    width: 100%;
    border-radius: ${borderRadius.card};
    padding: ${spacing[12]} ${spacing[16]};
    background-color: ${colorTokens.background.white};
    cursor: pointer;

    [data-visually-hidden] {
      opacity: 0;
    }

    &:hover {
      box-shadow: 0 0 0 1px ${colorTokens.stroke.hover};

      [data-visually-hidden] {
        opacity: 1;
      }
    }

    ${
      isSelected &&
      css`
        background-color: ${colorTokens.background.success.fill40};
        color: ${colorTokens.text.primary};

        &:hover {
          box-shadow: 0 0 0 1px ${colorTokens.stroke.success.fill70};
        }
      `
    }
  `,
  optionDragButton: css`
    ${styleUtils.resetButton}
    ${styleUtils.flexCenter()}
    transform: rotate(90deg);
    color: ${colorTokens.icon.default};
    cursor: grab;
    place-self: center center;
  `,
};
