import {
  closestCenter,
  DndContext,
  DragOverlay,
  KeyboardSensor,
  PointerSensor,
  type UniqueIdentifier,
  useSensor,
  useSensors,
} from '@dnd-kit/core';
import { restrictToWindowEdges } from '@dnd-kit/modifiers';
import { SortableContext, sortableKeyboardCoordinates, verticalListSortingStrategy } from '@dnd-kit/sortable';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useEffect, useState } from 'react';
import { createPortal } from 'react-dom';
import { useFieldArray } from 'react-hook-form';

import Button from '@TutorShared/atoms/Button';
import { LoadingSection } from '@TutorShared/atoms/LoadingSpinner';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import { useModal } from '@TutorShared/components/modals/Modal';
import SubscriptionModal, {
  type SubscriptionFormDataWithSaved,
} from '@TutorShared/components/modals/SubscriptionModal';
import { PreviewItem } from '@TutorShared/components/subscription/PreviewItem';

import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import For from '@TutorShared/controls/For';
import Show from '@TutorShared/controls/Show';
import { useFormWithGlobalError } from '@TutorShared/hooks/useFormWithGlobalError';
import {
  convertSubscriptionToFormData,
  defaultSubscriptionFormData,
  useCourseSubscriptionsQuery,
  useSortCourseSubscriptionsMutation,
} from '@TutorShared/services/subscription';
import { droppableMeasuringStrategy } from '@TutorShared/utils/dndkit';
import { moveTo } from '@TutorShared/utils/util';

interface SubscriptionPreviewProps {
  courseId: number;
  isBundle?: boolean;
}

const SubscriptionPreview = ({ courseId, isBundle = false }: SubscriptionPreviewProps) => {
  const courseSubscriptionsQuery = useCourseSubscriptionsQuery(courseId);
  const sortSubscriptionMutation = useSortCourseSubscriptionsMutation(courseId);
  const { showModal } = useModal();
  const [activeSortId, setActiveSortId] = useState<UniqueIdentifier | null>(null);

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
  const form = useFormWithGlobalError<{
    subscriptions: SubscriptionFormDataWithSaved[];
  }>({
    defaultValues: {
      subscriptions: [],
    },
    mode: 'onChange',
  });

  const { move: moveSubscription, fields: subscriptionFields } = useFieldArray({
    control: form.control,
    name: 'subscriptions',
    keyName: '_id',
  });

  const courseSubscriptions = courseSubscriptionsQuery.data;

  useEffect(() => {
    if (!courseSubscriptions) {
      return;
    }

    if (subscriptionFields.length === 0) {
      return form.reset({
        subscriptions: courseSubscriptions.map((subscription) => ({
          ...convertSubscriptionToFormData(subscription),
          isSaved: true,
        })),
      });
    }
    const subscriptions = courseSubscriptions.map((subscription) => {
      const existingItem = subscriptionFields.find((item) => item.id === subscription.id);
      if (existingItem) {
        return { ...existingItem, ...{ ...convertSubscriptionToFormData(subscription), isSaved: true } };
      }
      return { ...convertSubscriptionToFormData(subscription), isSaved: true };
    });

    form.reset({
      subscriptions: subscriptions,
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [courseSubscriptions, courseSubscriptionsQuery.isLoading]);

  if (courseSubscriptionsQuery.isLoading) {
    return <LoadingSection />;
  }

  if (!courseSubscriptionsQuery.data) {
    return null;
  }

  return (
    <div css={styles.outer}>
      <Show when={subscriptionFields.length > 0}>
        <div css={styles.header}>{__('Subscriptions', __TUTOR_TEXT_DOMAIN__)}</div>
      </Show>

      <div
        css={styles.inner({
          hasSubscriptions: subscriptionFields.length > 0,
        })}
      >
        <DndContext
          sensors={sensors}
          collisionDetection={closestCenter}
          measuring={droppableMeasuringStrategy}
          modifiers={[restrictToWindowEdges]}
          onDragStart={(event) => {
            setActiveSortId(event.active.id);
          }}
          onDragEnd={async (event) => {
            const { active, over } = event;

            if (!over) {
              setActiveSortId(null);
              return;
            }

            if (active.id !== over.id) {
              const activeIndex = subscriptionFields.findIndex((item) => item.id === active.id);
              const overIndex = subscriptionFields.findIndex((item) => item.id === over.id);
              const itemsAfterSort = moveTo(subscriptionFields, activeIndex, overIndex);
              moveSubscription(activeIndex, overIndex);

              sortSubscriptionMutation.mutateAsync(itemsAfterSort.map((item) => Number(item.id)));
            }

            setActiveSortId(null);
          }}
        >
          <SortableContext items={subscriptionFields} strategy={verticalListSortingStrategy}>
            <For each={subscriptionFields}>
              {(subscription) => (
                <PreviewItem
                  key={subscription.id}
                  subscription={subscription}
                  courseId={courseId}
                  isBundle={isBundle}
                />
              )}
            </For>
          </SortableContext>
          {createPortal(
            <DragOverlay>
              <Show when={activeSortId}>
                {(id) => {
                  const subscription = subscriptionFields.find((item) => item.id === id);
                  if (!subscription) {
                    return null;
                  }
                  return (
                    <PreviewItem
                      key={id}
                      subscription={subscription}
                      courseId={courseId}
                      isBundle={isBundle}
                      isOverlay
                    />
                  );
                }}
              </Show>
            </DragOverlay>,
            document.body,
          )}
        </DndContext>

        <div
          css={styles.emptyState({
            hasSubscriptions: subscriptionFields.length > 0,
          })}
        >
          <Button
            data-cy="add-subscription"
            variant="secondary"
            icon={<SVGIcon name="dollarRecurring" width={24} height={24} />}
            onClick={() => {
              showModal({
                component: SubscriptionModal,
                props: {
                  title: __('Manage Subscription Plans', __TUTOR_TEXT_DOMAIN__),
                  icon: <SVGIcon name="dollarRecurring" width={24} height={24} />,
                  subscription: {
                    ...defaultSubscriptionFormData,
                    plan_order: String(subscriptionFields.length + 1),
                    isSaved: false,
                  },
                  courseId,
                  isBundle,
                },
              });
            }}
          >
            {__('Add Subscription', __TUTOR_TEXT_DOMAIN__)}
          </Button>
        </div>
      </div>
    </div>
  );
};

export default SubscriptionPreview;
const styles = {
  outer: css`
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: ${spacing[8]};
  `,
  inner: ({ hasSubscriptions }: { hasSubscriptions: boolean }) => css`
    background: ${colorTokens.background.white};
    border: 1px solid ${colorTokens.stroke.default};
    border-radius: ${borderRadius.card};
    width: 100%;
    overflow: hidden;

    ${!hasSubscriptions &&
    css`
      border: none;
    `}
  `,
  header: css`
    display: flex;
    align-items: center;
    justify-content: space-between;
    ${typography.body()};
    color: ${colorTokens.text.title};
  `,
  emptyState: ({ hasSubscriptions }: { hasSubscriptions: boolean }) => css`
    padding: ${hasSubscriptions ? `${spacing[8]} ${spacing[12]}` : 0};
    width: 100%;

    & > button {
      width: 100%;
    }
  `,
};
