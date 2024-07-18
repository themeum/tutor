import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { Controller, useFieldArray, useFormContext, useWatch } from 'react-hook-form';
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
import { useEffect, useMemo, useRef, useState } from 'react';
import { createPortal } from 'react-dom';

import SVGIcon from '@Atoms/SVGIcon';

import FormMultipleChoiceAndOrdering from '@Components/fields/quiz/FormMultipleChoiceAndOrdering';
import { useQuizModalContext } from '@CourseBuilderContexts/QuizModalContext';

import For from '@Controls/For';
import Show from '@Controls/Show';
import { colorTokens, spacing } from '@Config/styles';
import { styleUtils } from '@Utils/style-utils';
import { moveTo, nanoid } from '@Utils/util';
import {
  type QuizQuestionType,
  useQuizQuestionAnswerOrderingMutation,
  type QuizForm,
  type QuizQuestionOption,
} from '@CourseBuilderServices/quiz';

const MultipleChoiceAndOrdering = () => {
  const isInitialRenderRef = useRef(false);
  const [activeSortId, setActiveSortId] = useState<UniqueIdentifier | null>(null);
  const form = useFormContext<QuizForm>();
  const { activeQuestionIndex, activeQuestionId } = useQuizModalContext();
  const multipleCorrectAnswer = useWatch({
    control: form.control,
    name: `questions.${activeQuestionIndex}.multipleCorrectAnswer`,
    defaultValue: false,
  });

  const currentQuestionType = form.watch(`questions.${activeQuestionIndex}.question_type`);
  const filterByQuestionType = (currentQuestionType: QuizQuestionType) => {
    if (currentQuestionType === 'multiple_choice') {
      return multipleCorrectAnswer ? 'multiple_choice' : 'single_choice';
    }

    return 'ordering';
  };

  const quizQuestionAnswerOrderingMutation = useQuizQuestionAnswerOrderingMutation();

  const {
    fields: optionsFields,
    append: appendOption,
    insert: insertOption,
    remove: removeOption,
    move: moveOption,
  } = useFieldArray({
    control: form.control,
    name: `questions.${activeQuestionIndex}.question_answers`,
  });

  const filteredOptionsFields = optionsFields.reduce(
    (allOptions, option, index) => {
      if (option.belongs_question_type === filterByQuestionType(currentQuestionType)) {
        allOptions.push({
          ...option,
          index: index,
        });
      }
      return allOptions;
    },
    [] as Array<QuizQuestionOption & { index: number }>
  );

  const sensors = useSensors(
    useSensor(PointerSensor, {
      activationConstraint: {
        distance: 10,
      },
    }),
    useSensor(KeyboardSensor, { coordinateGetter: sortableKeyboardCoordinates })
  );

  const currentOptions = useWatch({
    control: form.control,
    name: `questions.${activeQuestionIndex}.question_answers`,
    defaultValue: [],
  });

  const activeSortItem = useMemo(() => {
    if (!activeSortId) {
      return null;
    }

    return filteredOptionsFields.find((item) => item.answer_id === activeSortId);
  }, [activeSortId, filteredOptionsFields]);

  useEffect(() => {
    isInitialRenderRef.current = true;
    return () => {
      isInitialRenderRef.current = false;
    };
  }, []);

  // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
  useEffect(() => {
    if (multipleCorrectAnswer) {
      return;
    }

    const changedOptions = currentOptions.filter((option) => {
      const index = optionsFields.findIndex((item) => item.answer_id === option.answer_id);
      const previousOption = optionsFields[index];
      return previousOption && option.is_correct !== previousOption.is_correct;
    });

    if (changedOptions.length === 0) {
      return;
    }

    const changedOptionIndex = currentOptions.findIndex((item) => item.answer_id === changedOptions[0].answer_id);

    const updatedOptions = [...currentOptions];
    updatedOptions[changedOptionIndex] = Object.assign({}, updatedOptions[changedOptionIndex], { is_correct: '1' });

    for (const [index, option] of updatedOptions.entries()) {
      if (index !== changedOptionIndex) {
        updatedOptions[index] = { ...option, is_correct: '0' };
      }
    }

    isInitialRenderRef.current = false;
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
            const activeIndex = filteredOptionsFields.findIndex((item) => item.answer_id === active.id);
            const overIndex = filteredOptionsFields.findIndex((item) => item.answer_id === over.id);

            const updatedOptionsOrder = moveTo(
              form.watch(`questions.${activeQuestionIndex}.question_answers`),
              activeIndex,
              overIndex
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
                key={`${option.answer_id}-${option.is_correct}`}
                control={form.control}
                name={
                  `questions.${activeQuestionIndex}.question_answers.${option.index}` as 'questions.0.question_answers.0'
                }
                render={(controllerProps) => (
                  <FormMultipleChoiceAndOrdering
                    {...controllerProps}
                    hasMultipleCorrectAnswers={multipleCorrectAnswer}
                    onDuplicateOption={() => {
                      const duplicateOption: QuizQuestionOption = {
                        ...option,
                        answer_id: '',
                        is_correct: '0',
                      };
                      const duplicateIndex = option.index + 1;
                      insertOption(duplicateIndex, duplicateOption);
                    }}
                    onRemoveOption={() => removeOption(option.index)}
                    index={index}
                  />
                )}
              />
            )}
          </For>
        </SortableContext>

        {createPortal(
          <DragOverlay>
            <Show when={activeSortItem}>
              {(item) => {
                const index = filteredOptionsFields.findIndex((option) => option.answer_id === item.answer_id);
                return (
                  <Controller
                    key={activeSortId}
                    control={form.control}
                    name={
                      `questions.${activeQuestionIndex}.question_answers.${index}` as 'questions.0.question_answers.0'
                    }
                    render={(controllerProps) => (
                      <FormMultipleChoiceAndOrdering
                        {...controllerProps}
                        hasMultipleCorrectAnswers={multipleCorrectAnswer}
                        onDuplicateOption={() => {
                          const duplicateOption: QuizQuestionOption = {
                            ...item,
                            answer_id: nanoid(),
                            is_correct: '0',
                          };
                          const duplicateIndex = index + 1;
                          insertOption(duplicateIndex, duplicateOption);
                        }}
                        onRemoveOption={() => removeOption(index)}
                        index={index}
                      />
                    )}
                  />
                );
              }}
            </Show>
          </DragOverlay>,
          document.body
        )}
      </DndContext>

      <button
        type="button"
        onClick={() =>
          appendOption(
            {
              answer_id: '',
              answer_title: '',
              is_correct: '0',
              belongs_question_id: activeQuestionId,
              belongs_question_type: filterByQuestionType(currentQuestionType),
              answer_order: optionsFields.length,
              answer_two_gap_match: '',
              answer_view_format: '',
            },
            {
              shouldFocus: true,
              focusName: `questions.${activeQuestionIndex}.question_answers.${filteredOptionsFields.length}.answer_title`,
            }
          )
        }
        css={styles.addOptionButton}
      >
        <SVGIcon name="plus" height={24} width={24} />
        {__('Add Option', 'tutor')}
      </button>
    </div>
  );
};

export default MultipleChoiceAndOrdering;

const styles = {
  optionWrapper: css`
      ${styleUtils.display.flex('column')};
      gap: ${spacing[12]};
    `,
  addOptionButton: css`
    ${styleUtils.resetButton}
    ${styleUtils.display.flex()}
    align-items: center;
    gap: ${spacing[8]};
    color: ${colorTokens.text.brand};
    margin-left: ${spacing[48]};
    margin-top: ${spacing[28]};

    svg {
      color: ${colorTokens.icon.brand};
    }
  `,
};
