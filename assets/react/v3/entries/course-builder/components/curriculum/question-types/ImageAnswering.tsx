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
import { Controller, useFieldArray, useFormContext } from 'react-hook-form';

import SVGIcon from '@Atoms/SVGIcon';

import FormImageAnswering from '@Components/fields/quiz/FormImageAnswering';

import { colorTokens, spacing } from '@Config/styles';
import For from '@Controls/For';
import Show from '@Controls/Show';
import { styleUtils } from '@Utils/style-utils';
import { moveTo } from '@Utils/util';

import { useQuizModalContext } from '@CourseBuilderContexts/QuizModalContext';
import {
  type QuizForm,
  type QuizQuestionOption,
  useQuizQuestionAnswerOrderingMutation,
} from '@CourseBuilderServices/quiz';

const ImageAnswering = () => {
  const [activeSortId, setActiveSortId] = useState<UniqueIdentifier | null>(null);
  const form = useFormContext<QuizForm>();
  const { activeQuestionIndex, activeQuestionId, quizId } = useQuizModalContext();

  const quizQuestionAnswerOrderingMutation = useQuizQuestionAnswerOrderingMutation(quizId);

  const {
    fields: optionsFields,
    append: appendOption,
    insert: insertOption,
    remove: removeOption,
    move: moveOption,
  } = useFieldArray({
    control: form.control,
    name: `questions.${activeQuestionIndex}.question_answers` as 'questions.0.question_answers',
  });

  const filteredOptionsFields = optionsFields.reduce(
    (allOptions, option, index) => {
      if (option.belongs_question_type === 'image_answering') {
        allOptions.push({
          ...option,
          index: index,
        });
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

  const activeSortItem = useMemo(() => {
    if (!activeSortId) {
      return null;
    }

    return filteredOptionsFields.find((item) => item.answer_id === activeSortId);
  }, [activeSortId, filteredOptionsFields]);

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
                key={`${option.answer_id}-${option.index}`}
                control={form.control}
                name={
                  `questions.${activeQuestionIndex}.question_answers.${option.index}` as 'questions.0.question_answers.0'
                }
                render={(controllerProps) => (
                  <FormImageAnswering
                    {...controllerProps}
                    onDuplicateOption={(answerId) => {
                      const duplicateOption: QuizQuestionOption = {
                        ...option,
                        answer_id: answerId || '',
                        answer_title: `${option.answer_title} (Copy)`,
                        is_correct: '0',
                      };
                      const duplicateIndex = option.index - 1;
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
                      `questions.${activeQuestionIndex}.question_answers.${item.index}` as 'questions.0.question_answers.0'
                    }
                    render={(controllerProps) => (
                      <FormImageAnswering
                        {...controllerProps}
                        onDuplicateOption={(answerId) => {
                          const duplicateOption: QuizQuestionOption = {
                            ...item,
                            answer_id: answerId || '',
                            answer_title: `${item.answer_title} (Copy)`,
                            is_correct: '0',
                          };
                          const duplicateIndex = item.index - 1;
                          insertOption(duplicateIndex, duplicateOption);
                        }}
                        onRemoveOption={() => removeOption(item.index)}
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
              answer_id: '',
              answer_title: '',
              is_correct: '0',
              belongs_question_id: activeQuestionId,
              belongs_question_type: 'image_answering',
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
        css={styles.addOptionButton}
      >
        <SVGIcon name="plus" height={24} width={24} />
        {__('Add Option', 'tutor')}
      </button>
    </div>
  );
};

export default ImageAnswering;

const styles = {
  optionWrapper: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[12]};
    padding-left: ${spacing[40]};
  `,
  addOptionButton: css`
    ${styleUtils.resetButton}
    ${styleUtils.display.flex()}
    align-items: center;
    gap: ${spacing[8]};
    color: ${colorTokens.text.brand};
    margin-top: ${spacing[28]};
    margin-left: ${spacing[8]};

    svg {
      color: ${colorTokens.icon.brand};
    }
  `,
};
