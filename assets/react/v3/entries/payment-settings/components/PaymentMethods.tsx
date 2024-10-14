import {
  DndContext,
  type DragEndEvent,
  DragOverlay,
  KeyboardSensor,
  PointerSensor,
  type UniqueIdentifier,
  closestCenter,
  useSensor,
  useSensors,
} from '@dnd-kit/core';
import { restrictToParentElement, restrictToVerticalAxis } from '@dnd-kit/modifiers';
import { SortableContext, sortableKeyboardCoordinates, verticalListSortingStrategy } from '@dnd-kit/sortable';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useMemo, useState } from 'react';
import { createPortal } from 'react-dom';
import { useFormContext } from 'react-hook-form';

import { colorTokens, spacing } from '@/v3/shared/config/styles';
import { typography } from '@/v3/shared/config/typography';
import For from '@/v3/shared/controls/For';
import Show from '@/v3/shared/controls/Show';
import { moveTo } from '@/v3/shared/utils/util';

import type { PaymentSettings } from '../services/payment';
import PaymentItem from './PaymentItem';

const PaymentMethods = () => {
  const form = useFormContext<PaymentSettings>();
  const paymentMethods = form.watch('payment_methods') ?? [];

  const [activeSortId, setActiveSortId] = useState<UniqueIdentifier | null>(null);

  const sensors = useSensors(
    useSensor(PointerSensor, {
      activationConstraint: {
        distance: 10,
      },
    }),
    useSensor(KeyboardSensor, { coordinateGetter: sortableKeyboardCoordinates })
  );

  const activeSortItem = useMemo(() => {
    if (activeSortId === null) {
      return null;
    }

    return paymentMethods.find((method) => method.name === activeSortId);
  }, [activeSortId, paymentMethods]);

  const handleDragEnd = (event: DragEndEvent) => {
    const { active, over } = event;
    if (!over || active.id === over.id) {
      return;
    }

    const activeIndex = paymentMethods.findIndex((method) => method.name === active.id);
    const overIndex = paymentMethods.findIndex((method) => method.name === over.id);

    const newPaymentMethods = moveTo(paymentMethods, activeIndex, overIndex);
    form.setValue('payment_methods', newPaymentMethods, { shouldDirty: true });

    setActiveSortId(null);
  };

  return (
    <div css={styles.wrapper}>
      <div css={styles.title}>{__('Supported payment methods', 'tutor')}</div>
      <div css={styles.methodWrapper}>
        <DndContext
          sensors={sensors}
          collisionDetection={closestCenter}
          modifiers={[restrictToVerticalAxis, restrictToParentElement]}
          onDragStart={(event) => {
            setActiveSortId(event.active.id);
          }}
          onDragEnd={handleDragEnd}
        >
          <SortableContext
            items={paymentMethods.map((method) => ({
              ...method,
              id: method.name,
            }))}
            strategy={verticalListSortingStrategy}
          >
            <For each={paymentMethods}>
              {(method, index) => <PaymentItem key={method.name + index} data={method} paymentIndex={index} />}
            </For>
          </SortableContext>

          {createPortal(
            <DragOverlay>
              <Show when={activeSortItem}>
                {(item) => {
                  const index = paymentMethods.findIndex((method) => method.name === item.name);

                  return <PaymentItem data={item} paymentIndex={index} isOverlay />;
                }}
              </Show>
            </DragOverlay>,
            document.body
          )}
        </DndContext>
      </div>
    </div>
  );
};

export default PaymentMethods;

const styles = {
  wrapper: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[16]};
  `,
  title: css`
    ${typography.body('medium')};
    color: ${colorTokens.text.subdued};
  `,
  methodWrapper: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[8]};
  `,
};
