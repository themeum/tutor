import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import type { ModalProps } from '@Components/modals/Modal';
import ModalWrapper from '@Components/modals/ModalWrapper';
import { colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import For from '@Controls/For';
import Show from '@Controls/Show';
import { SubscriptionEmptyState } from '@CourseBuilderComponents/subscription/SubscriptionEmptyState';
import SubscriptionItem from '@CourseBuilderComponents/subscription/SubscriptionItem';
import { type Subscription, defaultSubscription } from '@CourseBuilderServices/subscription';
import { droppableMeasuringStrategy } from '@Utils/dndkit';
import { moveTo, noop } from '@Utils/util';
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
import { useEffect, useMemo, useState } from 'react';
import { createPortal } from 'react-dom';

interface SubscriptionModalProps extends ModalProps {
  closeModal: (props?: { action: 'CONFIRM' | 'CLOSE' }) => void;
  subscriptions: (Subscription & { isExpanded: boolean })[];
  courseId: number;
}

export default function SubscriptionModal({
  title,
  subtitle,
  icon,
  closeModal,
  subscriptions,
}: SubscriptionModalProps) {
  const [items, setItems] = useState(subscriptions);
  const [isExpandedAll, setIsExpandedAll] = useState(false);
  const [activeSortId, setActiveSortId] = useState<UniqueIdentifier | null>(null);

  useEffect(() => {
    setItems(subscriptions.map((item, index) => ({ ...item, isExpanded: index === 0 })));
  }, [subscriptions]);

  const sensors = useSensors(
    useSensor(PointerSensor, {
      activationConstraint: {
        distance: 10,
      },
    }),
    useSensor(KeyboardSensor, {
      coordinateGetter: sortableKeyboardCoordinates,
    }),
  );

  const activeSortItem = useMemo(() => {
    if (!activeSortId) {
      return null;
    }
    return items.find((item) => item.id === activeSortId);
  }, [activeSortId, items]);

  return (
    <ModalWrapper
      onClose={() => closeModal({ action: 'CLOSE' })}
      icon={icon}
      title={title}
      subtitle={subtitle}
      actions={
        <>
          <Button variant="text" size="small" onClick={() => closeModal()}>
            {__('Cancel', 'tutor')}
          </Button>
          <Button size="small" onClick={() => closeModal()}>
            {__('Done', 'tutor')}
          </Button>
        </>
      }
    >
      <div css={styles.wrapper}>
        <Show
          when={items.length}
          fallback={
            <SubscriptionEmptyState
              onCreateSubscription={() => {
                setItems([{ ...defaultSubscription, isExpanded: true }]);
              }}
            />
          }
        >
          <div css={styles.container}>
            <div css={styles.header}>
              <h6>{__('Subscriptions', 'tutor')}</h6>
              <Button
                variant="text"
                onClick={() => {
                  if (isExpandedAll) {
                    // All are expanded already, so collapse all
                    setItems((previous) => previous.map((data) => ({ ...data, isExpanded: false })));
                    setIsExpandedAll(false);
                    return;
                  }

                  setItems((previous) => previous.map((data) => ({ ...data, isExpanded: true })));
                  setIsExpandedAll(true);
                }}
              >
                {!isExpandedAll ? __('Expand All', 'tutor') : __('Collapse All', 'tutor')}
              </Button>
            </div>
            <div css={styles.content}>
              <DndContext
                sensors={sensors}
                collisionDetection={closestCenter}
                measuring={droppableMeasuringStrategy}
                modifiers={[restrictToVerticalAxis, restrictToWindowEdges]}
                onDragStart={(event) => {
                  setActiveSortId(event.active.id);
                }}
                onDragEnd={(event) => {
                  const { active, over } = event;

                  if (!over) {
                    setActiveSortId(null);
                    return;
                  }

                  if (active.id !== over.id) {
                    const activeIndex = items.findIndex((item) => item.id === active.id);
                    const overIndex = items.findIndex((item) => item.id === over.id);
                    const itemsAfterSort = moveTo(items, activeIndex, overIndex);
                    setItems(itemsAfterSort);
                  }

                  setActiveSortId(null);
                }}
              >
                <SortableContext items={items} strategy={verticalListSortingStrategy}>
                  <For each={items}>
                    {(subscription) => {
                      return (
                        <SubscriptionItem
                          key={subscription.id}
                          subscription={subscription}
                          toggleCollapse={(id) => {
                            setItems((previous) => {
                              return previous.map((item) => {
                                if (item.id === id) {
                                  return { ...item, isExpanded: !item.isExpanded };
                                }
                                return { ...item, isExpanded: false };
                              });
                            });
                          }}
                        />
                      );
                    }}
                  </For>
                </SortableContext>
                {createPortal(
                  <DragOverlay>
                    <Show when={activeSortItem}>
                      {(item) => {
                        return <SubscriptionItem subscription={item} toggleCollapse={noop} bgLight />;
                      }}
                    </Show>
                  </DragOverlay>,
                  document.body,
                )}
              </DndContext>
              <div>
                <Button
                  variant="secondary"
                  icon={<SVGIcon name="plusSquareBrand" width={24} height={24} />}
                  onClick={() => {
                    setItems((previous) => {
                      const newItems = previous.map((item) => ({ ...item, isExpanded: false }));
                      const subscriptionId = Math.max(...newItems.map((item) => item.id)) + 1;
                      return [...newItems, { ...defaultSubscription, id: subscriptionId, isExpanded: true }];
                    });
                  }}
                >
                  {__('Add Subscription', 'tutor')}
                </Button>
              </div>
            </div>
          </div>
        </Show>
      </div>
    </ModalWrapper>
  );
}

const styles = {
  wrapper: css`
		width: 1218px;
	`,
  container: css`
		max-width: 640px;
		width: 100%;
		margin: ${spacing[40]} auto;
		display: flex;
		flex-direction: column;
		gap: ${spacing[32]};
	`,
  header: css`
		display: flex;
		align-items: center;
    justify-content: space-between;

		h6 {
			${typography.heading6('medium')};
			color: ${colorTokens.text.primary};
		}
	`,
  content: css`
		display: flex;
		flex-direction: column;
		gap: ${spacing[16]};
	`,
};
