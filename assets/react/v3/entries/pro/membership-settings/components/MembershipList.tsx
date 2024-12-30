import Button from '@/v3/shared/atoms/Button';
import SVGIcon from '@/v3/shared/atoms/SVGIcon';
import For from '@/v3/shared/controls/For';
import { __ } from '@wordpress/i18n';
import { useFieldArray, useFormContext } from 'react-hook-form';
import MembershipItem from './MembershipItem';
import { type MembershipSettings } from '../services/memberships';
import { css } from '@emotion/react';
import { colorTokens, spacing } from '@/v3/shared/config/styles';
import { DndContext, KeyboardSensor, PointerSensor, useSensor, useSensors } from '@dnd-kit/core';
import { SortableContext, sortableKeyboardCoordinates, verticalListSortingStrategy } from '@dnd-kit/sortable';
import { restrictToParentElement } from '@dnd-kit/modifiers';

interface MembershipListProps {
  onNewMembershipClick: () => void;
}

export default function MembershipList({ onNewMembershipClick }: MembershipListProps) {
  const form = useFormContext<MembershipSettings>();

  const { fields, move } = useFieldArray({
    control: form.control,
    name: 'plans',
    keyName: '_id',
  });

  console.log(fields);

  const sensors = useSensors(
    useSensor(PointerSensor),
    useSensor(KeyboardSensor, {
      coordinateGetter: sortableKeyboardCoordinates,
    }),
  );

  return (
    <div css={styles.wrapper}>
      <div>
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
          <div css={styles.membershipList}>
            <SortableContext items={fields} strategy={verticalListSortingStrategy}>
              <For each={fields}>{(item, idx) => <MembershipItem key={item.id} data={item} index={idx} />}</For>
            </SortableContext>
          </div>
        </DndContext>
      </div>

      <div>
        <Button
          variant="primary"
          isOutlined
          size="large"
          onClick={onNewMembershipClick}
          icon={<SVGIcon name="plus" width={24} height={24} />}
        >
          {__('New Membership Level', 'tutor')}
        </Button>
      </div>
    </div>
  );
}

const styles = {
  wrapper: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[16]};
  `,
  membershipList: css`
    background-color: ${colorTokens.background.white};
    border: 1px solid ${colorTokens.stroke.divider};
    border-radius: ${spacing[6]};
  `,
};
