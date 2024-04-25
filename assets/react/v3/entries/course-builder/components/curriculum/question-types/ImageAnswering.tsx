import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { Controller, useFieldArray, useFormContext, useWatch } from 'react-hook-form';

import SVGIcon from '@Atoms/SVGIcon';

import type { QuizForm } from '@CourseBuilderComponents/modals/QuizModal';
import For from '@Controls/For';
import FormImageAnswering from '@Components/fields/quiz/FormImageAnswering';

import { colorTokens, spacing } from '@Config/styles';
import { styleUtils } from '@Utils/style-utils';
import { nanoid } from '@Utils/util';
import { useEffect, useMemo, useState } from 'react';
import {
  KeyboardSensor,
  PointerSensor,
  type UniqueIdentifier,
  useSensor,
  useSensors,
  DndContext,
  closestCenter,
  DragOverlay,
} from '@dnd-kit/core';
import { SortableContext, sortableKeyboardCoordinates, verticalListSortingStrategy } from '@dnd-kit/sortable';
import { restrictToVerticalAxis, restrictToWindowEdges } from '@dnd-kit/modifiers';
import { createPortal } from 'react-dom';
import Show from '@Controls/Show';
import { useQuizModalContext } from '@CourseBuilderContexts/QuizModalContext';

const ImageAnswering = () => {
  const [activeSortId, setActiveSortId] = useState<UniqueIdentifier | null>(null);
  const form = useFormContext<QuizForm>();
  const { activeQuestionIndex } = useQuizModalContext();
  const {
    fields: optionsFields,
    append: appendOption,
    remove: removeOption,
    move: moveOption,
  } = useFieldArray({
    control: form.control,
    name: `questions.${activeQuestionIndex}.options`,
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
    name: `questions.${activeQuestionIndex}.options`,
    defaultValue: [],
  });

  const activeSortItem = useMemo(() => {
    if (!activeSortId) {
      return null;
    }

    return optionsFields.find((item) => item.ID === activeSortId);
  }, [activeSortId, optionsFields]);

  // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
  useEffect(() => {
    const changedOptions = currentOptions.filter((option) => {
      const index = optionsFields.findIndex((item) => item.ID === option.ID);
      const previousOption = optionsFields[index];
      return option.isCorrect !== previousOption.isCorrect;
    });

    if (changedOptions.length === 0) {
      return;
    }

    const changedOptionIndex = optionsFields.findIndex((item) => item.ID === changedOptions[0].ID);

    const updatedOptions = [...optionsFields];
    updatedOptions[changedOptionIndex] = Object.assign({}, updatedOptions[changedOptionIndex], { isCorrect: true });
    updatedOptions.forEach((_, index) => {
      if (index !== changedOptionIndex) {
        updatedOptions[index] = Object.assign({}, updatedOptions[index], { isCorrect: false });
      }
    });

    form.setValue(`questions.${activeQuestionIndex}.options`, updatedOptions);
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
            const activeIndex = optionsFields.findIndex((item) => item.ID === active.id);
            const overIndex = optionsFields.findIndex((item) => item.ID === over.id);

            moveOption(activeIndex, overIndex);
          }

          setActiveSortId(null);
        }}
      >
        <SortableContext
          items={optionsFields.map((item) => ({ ...item, id: item.ID }))}
          strategy={verticalListSortingStrategy}
        >
          <For each={optionsFields}>
            {(option, index) => (
              <Controller
                key={option.id}
                control={form.control}
                name={`questions.${activeQuestionIndex}.options.${index}` as 'questions.0.options.0'}
                render={({ field, fieldState }) => (
                  <FormImageAnswering
                    field={field}
                    fieldState={fieldState}
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
                const index = optionsFields.findIndex((option) => option.ID === item.ID);
                return (
                  <Controller
                    key={activeSortId}
                    control={form.control}
                    name={`questions.${activeQuestionIndex}.options.${index}` as 'questions.0.options.0'}
                    render={({ field, fieldState }) => (
                      <FormImageAnswering
                        field={field}
                        fieldState={fieldState}
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

      <button type="button" onClick={() => appendOption({ ID: nanoid(), title: '' })} css={styles.addOptionButton}>
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
