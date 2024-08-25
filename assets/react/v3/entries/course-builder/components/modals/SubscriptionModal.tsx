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
import {
  type Subscription,
  type SubscriptionFormData,
  convertSubscriptionToFormData,
  defaultSubscriptionFormData,
  useSortCourseSubscriptionsMutation,
} from '@CourseBuilderServices/subscription';
import { getCourseId } from '@CourseBuilderUtils/utils';
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
import { useQueryClient } from '@tanstack/react-query';
import { __ } from '@wordpress/i18n';
import { useEffect, useMemo, useState } from 'react';
import { createPortal } from 'react-dom';

interface SubscriptionModalProps extends ModalProps {
  closeModal: (props?: { action: 'CONFIRM' | 'CLOSE' }) => void;
}

const courseId = getCourseId();

export default function SubscriptionModal({ title, subtitle, icon, closeModal }: SubscriptionModalProps) {
  const queryClient = useQueryClient();

  const courseSubscriptions = queryClient.getQueryData(['SubscriptionsList', courseId]) as Subscription[];
  const sortSubscriptionMutation = useSortCourseSubscriptionsMutation(courseId);

  const [items, setItems] = useState<(SubscriptionFormData & { isExpanded: boolean })[]>([]);
  const [isExpandedAll, setIsExpandedAll] = useState(false);
  const [activeSortId, setActiveSortId] = useState<UniqueIdentifier | null>(null);

  useEffect(() => {
    if (!courseSubscriptions) {
      return;
    }

    setItems(courseSubscriptions.map((item) => ({ ...convertSubscriptionToFormData(item), isExpanded: false })));
  }, [courseSubscriptions]);

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
                setItems([{ ...defaultSubscriptionFormData, isExpanded: true }]);
              }}
            />
          }
        >
          <div css={styles.container}>
            <div css={styles.header}>
              <h6>{__('Subscription Plans', 'tutor')}</h6>
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

                    sortSubscriptionMutation.mutate(itemsAfterSort.map((item) => Number(item.id)));
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
                          onDiscard={
                            !subscription.id
                              ? () => {
                                  setItems((previous) => previous.filter((item) => item.id !== subscription.id));
                                }
                              : noop
                          }
                        />
                      );
                    }}
                  </For>
                </SortableContext>
                {createPortal(
                  <DragOverlay>
                    <Show when={activeSortItem}>
                      {(item) => {
                        return <SubscriptionItem subscription={item} toggleCollapse={noop} bgLight onDiscard={noop} />;
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
                    setItems((previous: (SubscriptionFormData & { isExpanded: boolean })[]) => {
                      const newItems = previous.map((item) => ({ ...item, isExpanded: false }));
                      return [...newItems, { ...defaultSubscriptionFormData, id: '', isExpanded: true }];
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
