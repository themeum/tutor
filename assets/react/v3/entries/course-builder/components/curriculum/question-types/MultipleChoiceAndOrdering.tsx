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
import { restrictToWindowEdges } from '@dnd-kit/modifiers';
import { SortableContext, sortableKeyboardCoordinates, verticalListSortingStrategy } from '@dnd-kit/sortable';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useMemo, useState } from 'react';
import { createPortal } from 'react-dom';
import { Controller, useFieldArray, useFormContext, useWatch } from 'react-hook-form';

import Button from '@TutorShared/atoms/Button';
import SVGIcon from '@TutorShared/atoms/SVGIcon';

import { useQuizModalContext } from '@CourseBuilderContexts/QuizModalContext';
import FormMultipleChoiceAndOrdering from '@TutorShared/components/fields/quiz/questions/FormMultipleChoiceAndOrdering';

import { type QuizForm } from '@CourseBuilderServices/quiz';
import { colorTokens, spacing } from '@TutorShared/config/styles';
import For from '@TutorShared/controls/For';
import Show from '@TutorShared/controls/Show';
import { calculateQuizDataStatus } from '@TutorShared/utils/quiz';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { QuizDataStatus, type QuizQuestionOption } from '@TutorShared/utils/types';
import { nanoid, noop } from '@TutorShared/utils/util';

const MultipleChoiceAndOrdering = () => {
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

  const handleCheckCorrectAnswer = (index: number) => {
    const option = currentOptions[index];

    if (hasMultipleCorrectAnswer) {
      updateOption(index, {
        ...option,
        ...(calculateQuizDataStatus(option._data_status, QuizDataStatus.UPDATE) && {
          _data_status: calculateQuizDataStatus(option._data_status, QuizDataStatus.UPDATE) as QuizDataStatus,
        }),
        is_correct: option.is_correct === '1' ? '0' : '1',
      });
    } else {
      const updatedOptions = currentOptions.map((item) => ({
        ...item,
        ...(calculateQuizDataStatus(item._data_status, QuizDataStatus.UPDATE) && {
          _data_status: calculateQuizDataStatus(item._data_status, QuizDataStatus.UPDATE) as QuizDataStatus,
        }),
        is_correct: item.answer_id === option.answer_id ? '1' : '0',
      })) as QuizQuestionOption[];
      replaceOption(updatedOptions);
    }

    if (validationError?.type === 'correct_option') {
      setValidationError(null);
    }
  };

  const handleAddOption = () => {
    appendOption(
      {
        _data_status: QuizDataStatus.NEW,
        is_saved: false,
        answer_id: nanoid(),
        answer_title: '',
        is_correct: '0',
        belongs_question_id: activeQuestionId,
        belongs_question_type: currentQuestionType,
        answer_order: optionsFields.length,
        answer_two_gap_match: '',
        answer_view_format: 'text',
      },
      {
        shouldFocus: true,
        focusName: `questions.${activeQuestionIndex}.question_answers.${optionsFields.length}.answer_title`,
      },
    );

    if (validationError?.type === 'add_option') {
      setValidationError(null);
    }
  };

  const handleDuplicateOption = (index: number, data: QuizQuestionOption) => {
    const duplicateOption: QuizQuestionOption = {
      ...data,
      _data_status: QuizDataStatus.NEW,
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

    if (option._data_status !== QuizDataStatus.NEW) {
      form.setValue('deleted_answer_ids', [...form.getValues('deleted_answer_ids'), option.answer_id]);
    }
  };

  return (
    <div
      css={styles.optionWrapper({
        isOrdering: currentQuestionType === 'ordering',
      })}
    >
      <DndContext
        sensors={sensors}
        collisionDetection={closestCenter}
        modifiers={[restrictToWindowEdges]}
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
                    onCheckCorrectAnswer={() => handleCheckCorrectAnswer(index)}
                    index={index}
                    hasMultipleCorrectAnswer={hasMultipleCorrectAnswer}
                    questionType={currentQuestionType}
                    questionId={activeQuestionId}
                    validationError={validationError}
                    setValidationError={setValidationError}
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
                        isOverlay
                        hasMultipleCorrectAnswer={hasMultipleCorrectAnswer}
                        questionType={currentQuestionType}
                        questionId={activeQuestionId}
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

      <div css={styles.addOptionButtonWrapper({ isOrdering: currentQuestionType === 'ordering' })}>
        <Button
          variant="text"
          onClick={handleAddOption}
          buttonContentCss={styles.addOptionButton}
          icon={<SVGIcon name="plus" height={24} width={24} />}
        >
          {__('Add Option', 'tutor')}
        </Button>
      </div>
    </div>
  );
};

export default MultipleChoiceAndOrdering;

const styles = {
  optionWrapper: ({ isOrdering }: { isOrdering: boolean }) => css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[12]};

    ${isOrdering &&
    css`
      padding-left: ${spacing[40]};
    `}
  `,
  addOptionButtonWrapper: ({ isOrdering }: { isOrdering: boolean }) => css`
    margin-left: ${spacing[48]};

    ${isOrdering &&
    css`
      margin-left: ${spacing[8]};
    `}
  `,
  addOptionButton: css`
    color: ${colorTokens.text.brand};

    svg {
      color: ${colorTokens.icon.brand};
    }
  `,
};
