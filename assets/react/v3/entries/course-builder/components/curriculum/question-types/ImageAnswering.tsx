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
import { Controller, useFieldArray, useFormContext } from 'react-hook-form';

import SVGIcon from '@TutorShared/atoms/SVGIcon';

import FormImageAnswering from '@TutorShared/components/fields/quiz/questions/FormImageAnswering';

import { Breakpoint, colorTokens, spacing } from '@TutorShared/config/styles';
import For from '@TutorShared/controls/For';
import Show from '@TutorShared/controls/Show';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { nanoid, noop } from '@TutorShared/utils/util';

import { useQuizModalContext } from '@CourseBuilderContexts/QuizModalContext';
import { type QuizForm } from '@CourseBuilderServices/quiz';
import Button from '@TutorShared/atoms/Button';
import { QuizDataStatus, type QuizQuestionOption } from '@TutorShared/utils/types';

const ImageAnswering = () => {
  const [activeSortId, setActiveSortId] = useState<UniqueIdentifier | null>(null);
  const form = useFormContext<QuizForm>();
  const { activeQuestionIndex, activeQuestionId, validationError, setValidationError } = useQuizModalContext();

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

    return optionsFields.find((item) => item.answer_id === activeSortId);
  }, [activeSortId, optionsFields]);

  const handleAddOption = () => {
    appendOption(
      {
        _data_status: QuizDataStatus.NEW,
        is_saved: false,
        answer_id: nanoid(),
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
    <div css={styles.optionWrapper}>
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
                key={`${option.answer_id}-${index}`}
                control={form.control}
                name={`questions.${activeQuestionIndex}.question_answers.${index}` as 'questions.0.question_answers.0'}
                render={(controllerProps) => (
                  <FormImageAnswering
                    {...controllerProps}
                    onDuplicateOption={(data) => handleDuplicateOption(index, data)}
                    onRemoveOption={() => handleDeleteOption(index, option)}
                    index={index}
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
                const index = optionsFields.findIndex((option) => option.answer_id === item.answer_id);
                return (
                  <Controller
                    key={activeSortId}
                    control={form.control}
                    name={
                      `questions.${activeQuestionIndex}.question_answers.${index}` as 'questions.0.question_answers.0'
                    }
                    render={(controllerProps) => (
                      <FormImageAnswering
                        {...controllerProps}
                        onDuplicateOption={noop}
                        onRemoveOption={noop}
                        index={index}
                        isOverlay
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

      <div>
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

export default ImageAnswering;

const styles = {
  optionWrapper: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[12]};
    padding-left: ${spacing[40]};

    ${Breakpoint.smallMobile} {
      padding-left: ${spacing[8]};
    }
  `,
  addOptionButton: css`
    color: ${colorTokens.text.brand};

    svg {
      color: ${colorTokens.icon.brand};
    }
  `,
};
