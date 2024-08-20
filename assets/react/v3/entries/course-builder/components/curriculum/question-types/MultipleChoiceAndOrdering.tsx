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
import { useEffect, useMemo, useRef, useState } from 'react';
import { createPortal } from 'react-dom';
import { Controller, useFieldArray, useFormContext, useWatch } from 'react-hook-form';

import SVGIcon from '@Atoms/SVGIcon';

import FormMultipleChoiceAndOrdering from '@Components/fields/quiz/FormMultipleChoiceAndOrdering';
import { useQuizModalContext } from '@CourseBuilderContexts/QuizModalContext';

import { colorTokens, spacing } from '@Config/styles';
import For from '@Controls/For';
import Show from '@Controls/Show';
import type { QuizDataStatus, QuizForm, QuizQuestionOption, QuizQuestionType } from '@CourseBuilderServices/quiz';
import { styleUtils } from '@Utils/style-utils';
import { nanoid } from '@Utils/util';

const MultipleChoiceAndOrdering = () => {
  const isInitialRenderRef = useRef(false);
  const [activeSortId, setActiveSortId] = useState<UniqueIdentifier | null>(null);
  const form = useFormContext<QuizForm>();
  const { activeQuestionIndex, activeQuestionId, quizId } = useQuizModalContext();
  const hasMultipleCorrectAnswer = useWatch({
    control: form.control,
    name: `questions.${activeQuestionIndex}.has_multiple_correct_answer` as 'questions.0.has_multiple_correct_answer',
    defaultValue: false,
  });

  const currentQuestionType = form.watch(`questions.${activeQuestionIndex}.question_type`);
  const filterByQuestionType = (currentQuestionType: QuizQuestionType) => {
    if (currentQuestionType === 'multiple_choice') {
      return hasMultipleCorrectAnswer ? 'multiple_choice' : 'single_choice';
    }

    return 'ordering';
  };

  // const quizQuestionAnswerOrderingMutation = useQuizQuestionAnswerOrderingMutation(quizId);

  const {
    fields: optionsFields,
    append: appendOption,
    insert: insertOption,
    remove: removeOption,
    replace: replaceOption,
    move: moveOption,
  } = useFieldArray({
    control: form.control,
    name: `questions.${activeQuestionIndex}.question_answers`,
  });

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
    name: `questions.${activeQuestionIndex}.question_answers`,
    defaultValue: [],
  });

  const activeSortItem = useMemo(() => {
    if (!activeSortId) {
      return null;
    }

    return optionsFields.find((item) => item.answer_id === activeSortId);
  }, [activeSortId, optionsFields]);

  useEffect(() => {
    isInitialRenderRef.current = true;
    return () => {
      isInitialRenderRef.current = false;
    };
  }, []);

  // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
  useEffect(() => {
    if (!hasMultipleCorrectAnswer && !isInitialRenderRef.current) {
      const resetOptions = optionsFields.map((option) => ({
        ...option,
        is_correct: '0' as '0' | '1',
        _data_status: 'update' as QuizDataStatus,
      }));
      form.setValue(`questions.${activeQuestionIndex}.question_answers`, resetOptions);
    }
    isInitialRenderRef.current = false;
  }, [hasMultipleCorrectAnswer]);

  // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
  useEffect(() => {
    if (hasMultipleCorrectAnswer) {
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

    replaceOption(updatedOptions);
    isInitialRenderRef.current = false;
  }, [currentOptions]);

  return (
    <div
      css={styles.optionWrapper({
        currentQuestionType: currentQuestionType as 'multiple_choice' | 'ordering',
      })}
    >
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

            // const updatedOptionsOrder = moveTo(
            //   form.watch(`questions.${activeQuestionIndex}.question_answers`),
            //   activeIndex,
            //   overIndex,
            // );

            // quizQuestionAnswerOrderingMutation.mutate({
            //   question_id: activeQuestionId,
            //   sorted_answer_ids: updatedOptionsOrder.map((option) => option.answer_id),
            // });

            moveOption(activeIndex, overIndex);
          }

          setActiveSortId(null);
        }}
      >
        <SortableContext
          items={optionsFields.map((item) => ({ ...item, id: item.answer_id }))}
          strategy={verticalListSortingStrategy}
        >
          <For each={optionsFields}>
            {(option, index) => (
              <Controller
                key={JSON.stringify(option)}
                control={form.control}
                name={`questions.${activeQuestionIndex}.question_answers.${index}` as 'questions.0.question_answers.0'}
                render={(controllerProps) => (
                  <FormMultipleChoiceAndOrdering
                    {...controllerProps}
                    onDuplicateOption={(data) => {
                      const duplicateOption: QuizQuestionOption = {
                        ...data,
                        _data_status: 'new',
                        is_saved: true,
                        answer_id: nanoid(),
                        answer_title: `${data.answer_title} (copy)`,
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
                    render={(controllerProps) => (
                      <FormMultipleChoiceAndOrdering
                        {...controllerProps}
                        onDuplicateOption={() => {
                          const duplicateOption: QuizQuestionOption = {
                            ...item,
                            _data_status: 'new',
                            is_saved: true,
                            answer_id: nanoid(),
                            answer_title: `${item.answer_title} (copy)`,
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
          document.body,
        )}
      </DndContext>

      <button
        type="button"
        onClick={() =>
          appendOption(
            {
              _data_status: 'new',
              is_saved: false,
              answer_id: nanoid(),
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
              focusName: `questions.${activeQuestionIndex}.question_answers.${optionsFields.length}.answer_title`,
            },
          )
        }
        css={styles.addOptionButton({
          currentQuestionType: currentQuestionType as 'multiple_choice' | 'ordering',
        })}
      >
        <SVGIcon name="plus" height={24} width={24} />
        {__('Add Option', 'tutor')}
      </button>
    </div>
  );
};

export default MultipleChoiceAndOrdering;

const styles = {
  optionWrapper: ({
    currentQuestionType,
  }: {
    currentQuestionType: 'multiple_choice' | 'ordering';
  }) => css`
      ${styleUtils.display.flex('column')};
      gap: ${spacing[12]};
      
      ${
        currentQuestionType === 'ordering' &&
        css`
        padding-left: ${spacing[40]};
      `
      }
    `,
  addOptionButton: ({
    currentQuestionType,
  }: {
    currentQuestionType: 'multiple_choice' | 'ordering';
  }) => css`
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

    ${
      currentQuestionType === 'ordering' &&
      css`
        margin-left: ${spacing[8]};
      `
    }
  `,
};
