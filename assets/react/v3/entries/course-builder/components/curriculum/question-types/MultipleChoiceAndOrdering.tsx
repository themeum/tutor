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
import {
  type QuizDataStatus,
  type QuizForm,
  type QuizQuestionOption,
  calculateQuizDataStatus,
} from '@CourseBuilderServices/quiz';
import { styleUtils } from '@Utils/style-utils';
import { nanoid, noop } from '@Utils/util';

const MultipleChoiceAndOrdering = () => {
  const isInitialRenderRef = useRef(false);
  const [activeSortId, setActiveSortId] = useState<UniqueIdentifier | null>(null);
  const form = useFormContext<QuizForm>();
  const { activeQuestionIndex, activeQuestionId, validationError, setValidationError } = useQuizModalContext();
  const hasMultipleCorrectAnswer = useWatch({
    control: form.control,
    name: `questions.${activeQuestionIndex}.question_settings.has_multiple_correct_answer` as 'questions.0.question_settings.has_multiple_correct_answer',
    defaultValue: false,
  });

  const currentQuestionType = form.watch(`questions.${activeQuestionIndex}.question_type`);

  const {
    fields: optionsFields,
    append: appendOption,
    insert: insertOption,
    remove: removeOption,
    update: updateOption,
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

  const handleCheckCorrectAnswer = (index: number, option: QuizQuestionOption) => {
    if (hasMultipleCorrectAnswer) {
      updateOption(index, {
        ...option,
        ...(calculateQuizDataStatus(option._data_status, 'update') && {
          _data_status: calculateQuizDataStatus(option._data_status, 'update') as QuizDataStatus,
        }),
        is_correct: option.is_correct === '1' ? '0' : '1',
      });
    } else {
      const updatedOptions = currentOptions.map((item) => ({
        ...item,
        ...(calculateQuizDataStatus(item._data_status, 'update') && {
          _data_status: calculateQuizDataStatus(item._data_status, 'update') as QuizDataStatus,
        }),
        is_correct: item.answer_id === option.answer_id ? '1' : '0',
      })) as QuizQuestionOption[];
      replaceOption(updatedOptions);
    }

    if (validationError?.type === 'correct_answer') {
      setValidationError(null);
    }
  };

  const handleDuplicateOption = (index: number, data: QuizQuestionOption) => {
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
  };
  const handleDeleteOption = (index: number, option: QuizQuestionOption) => {
    removeOption(index);

    if (option._data_status !== 'new') {
      form.setValue('deleted_answer_ids', [...form.getValues('deleted_answer_ids'), option.answer_id]);
    }
  };

  useEffect(() => {
    isInitialRenderRef.current = true;
    return () => {
      isInitialRenderRef.current = false;
    };
  }, []);

  // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
  useEffect(() => {
    if (!hasMultipleCorrectAnswer && !isInitialRenderRef.current) {
      const resetOptions = currentOptions.map((option) => ({
        ...option,
        ...(calculateQuizDataStatus(option._data_status, 'update') && {
          _data_status: calculateQuizDataStatus(option._data_status, 'update') as QuizDataStatus,
        }),
        is_correct: '0' as '0' | '1',
        is_saved: true,
      }));
      replaceOption(resetOptions);
    }
    isInitialRenderRef.current = false;
  }, [hasMultipleCorrectAnswer]);

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
                key={`${option.answer_id}-${option.is_correct}`}
                control={form.control}
                name={`questions.${activeQuestionIndex}.question_answers.${index}` as 'questions.0.question_answers.0'}
                render={(controllerProps) => (
                  <FormMultipleChoiceAndOrdering
                    {...controllerProps}
                    onDuplicateOption={(data) => handleDuplicateOption(index, data)}
                    onRemoveOption={() => handleDeleteOption(index, option)}
                    onCheckCorrectAnswer={() => handleCheckCorrectAnswer(index, option)}
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
                const index = currentOptions.findIndex((option) => option.answer_id === item.answer_id);
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
                        onDuplicateOption={noop}
                        onRemoveOption={noop}
                        onCheckCorrectAnswer={noop}
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
        onClick={() => {
          appendOption(
            {
              _data_status: 'new',
              is_saved: false,
              answer_id: nanoid(),
              answer_title: '',
              is_correct: '0',
              belongs_question_id: activeQuestionId,
              belongs_question_type: currentQuestionType,
              answer_order: optionsFields.length,
              answer_two_gap_match: '',
              answer_view_format: '',
            },
            {
              shouldFocus: true,
              focusName: `questions.${activeQuestionIndex}.question_answers.${optionsFields.length}.answer_title`,
            },
          );

          if (validationError?.type === 'option') {
            setValidationError(null);
          }
        }}
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
