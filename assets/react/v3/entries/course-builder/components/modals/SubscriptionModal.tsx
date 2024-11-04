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
import { useIsFetching, useQueryClient } from '@tanstack/react-query';
import { __ } from '@wordpress/i18n';
import { useEffect, useState } from 'react';
import { createPortal } from 'react-dom';
import { FormProvider, useFieldArray } from 'react-hook-form';

import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';

import type { ModalProps } from '@Components/modals/Modal';
import ModalWrapper from '@Components/modals/ModalWrapper';
import { SubscriptionEmptyState } from '@CourseBuilderComponents/subscription/SubscriptionEmptyState';
import SubscriptionItem from '@CourseBuilderComponents/subscription/SubscriptionItem';

import { colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import For from '@Controls/For';
import Show from '@Controls/Show';
import {
  type Subscription,
  type SubscriptionFormData,
  convertFormDataToSubscription,
  convertSubscriptionToFormData,
  defaultSubscriptionFormData,
  useSaveCourseSubscriptionMutation,
  useSortCourseSubscriptionsMutation,
} from '@CourseBuilderServices/subscription';
import { getCourseId } from '@CourseBuilderUtils/utils';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
import { droppableMeasuringStrategy } from '@Utils/dndkit';
import { isDefined } from '@Utils/types';
import { moveTo, nanoid, noop } from '@Utils/util';

interface SubscriptionModalProps extends ModalProps {
  closeModal: (props?: { action: 'CONFIRM' | 'CLOSE' }) => void;
  expandedSubscriptionId?: string;
  createEmptySubscriptionOnMount?: boolean;
}

export type SubscriptionFormDataWithSaved = SubscriptionFormData & { isSaved: boolean };

const courseId = getCourseId();

export default function SubscriptionModal({
  title,
  subtitle,
  icon,
  closeModal,
  expandedSubscriptionId,
  createEmptySubscriptionOnMount,
}: SubscriptionModalProps) {
  const queryClient = useQueryClient();
  const form = useFormWithGlobalError<{
    subscriptions: SubscriptionFormDataWithSaved[];
  }>({
    defaultValues: {
      subscriptions: [],
    },
    mode: 'onChange',
  });

  const {
    append: appendSubscription,
    remove: removeSubscription,
    move: moveSubscription,
    fields: subscriptionFields,
  } = useFieldArray({
    control: form.control,
    name: 'subscriptions',
    keyName: '_id',
  });

  const [expendedSubscriptionId, setExpandedSubscriptionId] = useState<string>(expandedSubscriptionId || '');
  const [activeSortId, setActiveSortId] = useState<UniqueIdentifier | null>(null);

  const isSubscriptionListLoading = !!useIsFetching({
    queryKey: ['SubscriptionsList', courseId],
  });
  const courseSubscriptions = queryClient.getQueryData(['SubscriptionsList', courseId]) as Subscription[];
  const sortSubscriptionMutation = useSortCourseSubscriptionsMutation(courseId);
  const saveSubscriptionMutation = useSaveCourseSubscriptionMutation(courseId);

  const isFormDirty = form.formState.isDirty;

  const activeSubscription = form.getValues().subscriptions.find((item) => item.id === expendedSubscriptionId);

  const dirtySubscriptionIndex =
    subscriptionFields.findIndex((item) => !item.isSaved) !== -1
      ? subscriptionFields.findIndex((item) => !item.isSaved)
      : form.formState.dirtyFields.subscriptions?.findIndex((item) => isDefined(item));

  // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
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
  }, [courseSubscriptions, isSubscriptionListLoading]);

  const handleSaveSubscription = async (values: SubscriptionFormDataWithSaved) => {
    try {
      form.trigger();
      const timeoutId = setTimeout(async () => {
        const subscriptionErrors = form.formState.errors.subscriptions || [];

        if (subscriptionErrors.length) {
          return;
        }

        const payload = convertFormDataToSubscription({
          ...values,
          id: values.isSaved ? values.id : '0',
          assign_id: String(courseId),
        });
        const response = await saveSubscriptionMutation.mutateAsync(payload);

        if (response.status_code === 200 || response.status_code === 201) {
          setExpandedSubscriptionId((previous) => (previous === payload.id ? '' : payload.id || ''));
        }
      }, 0);

      return () => {
        clearTimeout(timeoutId);
      };
    } catch (error) {
      form.reset();
    }
  };

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

  // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
  useEffect(() => {
    if (createEmptySubscriptionOnMount) {
      const newId = nanoid();
      appendSubscription({ ...defaultSubscriptionFormData, id: newId, isSaved: false });
      setExpandedSubscriptionId(newId);
    }
  }, []);

  return (
    <FormProvider {...form}>
      <ModalWrapper
        onClose={() => closeModal({ action: 'CLOSE' })}
        icon={isFormDirty ? <SVGIcon name="warning" width={24} height={24} /> : icon}
        title={isFormDirty ? __('Unsaved Changes', 'tutor') : title}
        subtitle={isFormDirty ? title?.toString() : subtitle}
        actions={
          isFormDirty && (
            <>
              <Button
                variant="text"
                size="small"
                onClick={() => {
                  activeSubscription ? form.reset() : closeModal({ action: 'CLOSE' });
                }}
              >
                {activeSubscription?.isSaved ? __('Discard Changes', 'tutor') : __('Cancel', 'tutor')}
              </Button>
              <Button
                loading={saveSubscriptionMutation.isPending}
                variant="primary"
                size="small"
                onClick={() => {
                  if (dirtySubscriptionIndex !== -1 && activeSubscription) {
                    handleSaveSubscription(activeSubscription);
                  }
                }}
              >
                {activeSubscription?.isSaved ? __('Update', 'tutor') : __('Save', 'tutor')}
              </Button>
            </>
          )
        }
      >
        <div css={styles.wrapper}>
          <Show
            when={subscriptionFields.length}
            fallback={
              <SubscriptionEmptyState
                onCreateSubscription={() => {
                  const newId = nanoid();
                  appendSubscription({ ...defaultSubscriptionFormData, id: newId, isSaved: false });
                  setExpandedSubscriptionId(newId);
                }}
              />
            }
          >
            <div css={styles.container}>
              <div css={styles.header}>
                <h6>{__('Subscription Plans', 'tutor')}</h6>
              </div>
              <div css={styles.content}>
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
                      {(subscription, index) => {
                        return (
                          <SubscriptionItem
                            key={subscription.id}
                            id={subscription.id}
                            toggleCollapse={(id) => {
                              setExpandedSubscriptionId((previous) => (previous === id ? '' : id));
                            }}
                            onDiscard={
                              !subscription.id
                                ? () => {
                                    removeSubscription(index);
                                  }
                                : noop
                            }
                            isExpanded={activeSortId ? false : expendedSubscriptionId === subscription.id}
                          />
                        );
                      }}
                    </For>
                  </SortableContext>
                  {createPortal(
                    <DragOverlay>
                      <Show when={activeSortId}>
                        {(id) => {
                          return (
                            <SubscriptionItem
                              id={id}
                              toggleCollapse={noop}
                              bgLight
                              onDiscard={noop}
                              isExpanded={false}
                              isOverlay
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
                    variant="secondary"
                    icon={<SVGIcon name="plusSquareBrand" width={24} height={24} />}
                    disabled={isFormDirty}
                    onClick={() => {
                      const newId = nanoid();
                      appendSubscription({ ...defaultSubscriptionFormData, id: newId, isSaved: false });
                      setExpandedSubscriptionId(newId);
                    }}
                  >
                    {__('Add New Plan', 'tutor')}
                  </Button>
                </div>
              </div>
            </div>
          </Show>
        </div>
      </ModalWrapper>
    </FormProvider>
  );
}

const styles = {
  wrapper: css`
		width: 1218px;
    height: 100%;
	`,
  container: css`
		max-width: 640px;
		width: 100%;
		padding-block: ${spacing[40]};
    margin-inline: auto;
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
