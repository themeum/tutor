import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';

import { typography } from '@Config/typography';
import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { styleUtils } from '@Utils/style-utils';
import { Controller, useFieldArray, useFormContext, useWatch } from 'react-hook-form';
import { useEffect, useMemo, useState } from 'react';
import { nanoid } from '@Utils/util';
import { useQuizModalContext } from '@CourseBuilderContexts/QuizModalContext';
import type { QuizForm, QuizQuestionOption } from '@CourseBuilderServices/quiz';
import {
  closestCenter,
  DndContext,
  DragOverlay,
  KeyboardSensor,
  PointerSensor,
  useSensor,
  useSensors,
  type UniqueIdentifier,
} from '@dnd-kit/core';
import { SortableContext, sortableKeyboardCoordinates, verticalListSortingStrategy } from '@dnd-kit/sortable';
import { restrictToVerticalAxis, restrictToWindowEdges } from '@dnd-kit/modifiers';
import { createPortal } from 'react-dom';
import Show from '@Controls/Show';
import FormTrueFalse from '@Components/fields/quiz/FormTrueFalse';

const TrueFalse = () => {
  const [activeSortId, setActiveSortId] = useState<UniqueIdentifier | null>(null);
  const form = useFormContext<QuizForm>();
  const { activeQuestionId, activeQuestionIndex } = useQuizModalContext();

  const { fields: optionsFields, move: moveOption } = useFieldArray({
    control: form.control,
    name: `questions.${activeQuestionIndex}.question_answers` as 'questions.0.question_answers',
  });

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
    name: `questions.${activeQuestionIndex}.question_answers` as 'questions.0.question_answers',
    defaultValue: [],
  });

  const activeSortItem = useMemo(() => {
    if (!activeSortId) {
      return null;
    }

    return optionsFields.find((item) => item.answer_id === activeSortId);
  }, [activeSortId, optionsFields]);

  // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
  useEffect(() => {
    if (
      optionsFields.length === 2 &&
      optionsFields.every((option: QuizQuestionOption) => option.belongs_question_type === 'true_false')
    ) {
      return;
    }

    form.setValue(`questions.${activeQuestionIndex}.question_answers`, [
      {
        answer_id: nanoid(),
        answer_title: __('True', 'tutor'),
        is_correct: '0',
        belongs_question_id: activeQuestionId,
        belongs_question_type: 'true_false',
        answer_order: 0,
        answer_two_gap_match: '',
        answer_view_format: '',
      },
      {
        answer_id: nanoid(),
        answer_title: __('False', 'tutor'),
        is_correct: '0',
        belongs_question_id: activeQuestionId,
        belongs_question_type: 'true_false',
        answer_order: 1,
        answer_two_gap_match: '',
        answer_view_format: '',
      },
    ]);
  }, [optionsFields.length]);

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

            moveOption(activeIndex, overIndex);
          }

          setActiveSortId(null);
        }}
      >
        <SortableContext
          items={optionsFields.map((item) => ({ ...item, id: item.answer_id }))}
          strategy={verticalListSortingStrategy}
        >
          {optionsFields.map((option, index) => (
            <Controller
              key={option.id}
              control={form.control}
              name={`questions.${activeQuestionIndex}.question_answers.${index}` as 'questions.0.question_answers.0'}
              render={(controllerProps) => <FormTrueFalse {...controllerProps} index={index} />}
            />
          ))}
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
          document.body
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
