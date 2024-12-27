import Button from '@/v3/shared/atoms/Button';
import SVGIcon from '@/v3/shared/atoms/SVGIcon';
import { borderRadius, colorTokens, spacing } from '@/v3/shared/config/styles';
import { typography } from '@/v3/shared/config/typography';
import For from '@/v3/shared/controls/For';
import Show from '@/v3/shared/controls/Show';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { featureIcons } from '../config/constants';
import { Controller, useFieldArray, useFormContext } from 'react-hook-form';
import FormFeatureItem from './fields/FormFeatureItem';
import { SortableContext, sortableKeyboardCoordinates, verticalListSortingStrategy } from '@dnd-kit/sortable';
import { DndContext, KeyboardSensor, PointerSensor, useSensor, useSensors } from '@dnd-kit/core';
import { nanoid } from '@/v3/shared/utils/util';
import { restrictToParentElement } from '@dnd-kit/modifiers';

export default function IconsAndFeatures() {
  const form = useFormContext();
  const { fields, append, remove, move } = useFieldArray({
    control: form.control,
    name: 'features',
  });

  const sensors = useSensors(
    useSensor(PointerSensor),
    useSensor(KeyboardSensor, {
      coordinateGetter: sortableKeyboardCoordinates,
    }),
  );

  return (
    <div css={styles.wrapper}>
      <div css={styles.header}>
        <label>{__('Icons & Features', 'tutor')}</label>
        <Button variant="text" onClick={() => append({ id: nanoid(), icon: featureIcons.tickCircleFill, content: '' })}>
          <SVGIcon name="plus" width={24} height={24} />
        </Button>
      </div>
      <Show when={fields.length > 0}>
        <div css={styles.features}>
          <DndContext
            sensors={sensors}
            modifiers={[restrictToParentElement]}
            onDragEnd={(event) => {
              const { active, over } = event;
              if (!over) {
                return;
              }

              if (active.id !== over.id) {
                const activeIndex = fields.findIndex((item) => item.id === active.id);
                const overIndex = fields.findIndex((item) => item.id === over.id);

                move(activeIndex, overIndex);
              }
            }}
          >
            <SortableContext items={fields} strategy={verticalListSortingStrategy}>
              <For each={fields}>
                {(item, index) => (
                  <Controller
                    key={item.id}
                    control={form.control}
                    name={`features.${index}` as 'features.0'}
                    rules={{
                      validate: (value) => !!value?.content || __('Content is required', 'tutor'),
                    }}
                    render={(controllerProps) => (
                      <FormFeatureItem id={item.id} {...controllerProps} handleDeleteClick={() => remove(index)} />
                    )}
                  />
                )}
              </For>
            </SortableContext>
          </DndContext>
        </div>
      </Show>
    </div>
  );
}

const styles = {
  wrapper: css`
    background-color: ${colorTokens.background.white};
    border: 1px solid ${colorTokens.stroke.divider};
    border-radius: ${borderRadius[6]};
    padding: ${spacing[12]} ${spacing[16]};
  `,
  header: css`
    display: flex;
    align-items: center;
    justify-content: space-between;

    label {
      ${typography.caption()};
      color: ${colorTokens.text.title};
    }

    button {
      color: ${colorTokens.icon.default};
      border: 1px solid ${colorTokens.stroke.default};
      border-radius: ${borderRadius[4]};
      padding: 3px;
    }
  `,
  features: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[8]};
    padding: ${spacing[12]} 0 ${spacing[8]};
  `,
};
