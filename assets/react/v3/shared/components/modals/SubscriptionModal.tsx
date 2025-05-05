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

import Button from '@TutorShared/atoms/Button';
import SVGIcon from '@TutorShared/atoms/SVGIcon';

import type { ModalProps } from '@TutorShared/components/modals/Modal';
import ModalWrapper from '@TutorShared/components/modals/ModalWrapper';
import { SubscriptionEmptyState } from '@TutorShared/components/subscription/SubscriptionEmptyState';
import SubscriptionItem from '@TutorShared/components/subscription/SubscriptionItem';

import { CURRENT_VIEWPORT } from '@TutorShared/config/constants';
import { Breakpoint, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import For from '@TutorShared/controls/For';
import Show from '@TutorShared/controls/Show';
import { useFormWithGlobalError } from '@TutorShared/hooks/useFormWithGlobalError';
import {
  type Subscription,
  type SubscriptionFormData,
  convertFormDataToSubscription,
  convertSubscriptionToFormData,
  defaultSubscriptionFormData,
  useSaveCourseSubscriptionMutation,
  useSortCourseSubscriptionsMutation,
} from '@TutorShared/services/subscription';
import { droppableMeasuringStrategy } from '@TutorShared/utils/dndkit';
import { isDefined } from '@TutorShared/utils/types';
import { moveTo, nanoid, noop } from '@TutorShared/utils/util';

interface SubscriptionModalProps extends ModalProps {
  courseId: number;
  isBundle?: boolean;
  closeModal: (props?: { action: 'CONFIRM' | 'CLOSE' }) => void;
  expandedSubscriptionId?: string;
  createEmptySubscriptionOnMount?: boolean;
}

export type SubscriptionFormDataWithSaved = SubscriptionFormData & { isSaved: boolean };

export default function SubscriptionModal({
  courseId,
  isBundle = false,
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
          plan_type: isBundle ? 'bundle' : 'course',
        });
        const response = await saveSubscriptionMutation.mutateAsync(payload);

        if (response.status_code === 200 || response.status_code === 201) {
          setExpandedSubscriptionId((previous) => (previous === payload.id ? '' : payload.id || ''));
        }
      }, 0);

      return () => {
        clearTimeout(timeoutId);
      };
    } catch {
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

  useEffect(() => {
    if (createEmptySubscriptionOnMount) {
      const newId = nanoid();
      appendSubscription({ ...defaultSubscriptionFormData, id: newId, isSaved: false });
      setExpandedSubscriptionId(newId);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  return (
    <FormProvider {...form}>
      <ModalWrapper
        onClose={() => closeModal({ action: 'CLOSE' })}
        icon={isFormDirty ? <SVGIcon name="warning" width={24} height={24} /> : icon}
        title={isFormDirty ? (CURRENT_VIEWPORT.isAboveMobile ? __('Unsaved Changes', 'tutor') : '') : title}
        subtitle={isFormDirty ? title?.toString() : subtitle}
        maxWidth={1218}
        actions={
          isFormDirty && (
            <>
              <Button
                variant="text"
                size="small"
                onClick={() => (activeSubscription ? form.reset() : closeModal({ action: 'CLOSE' }))}
              >
                {activeSubscription?.isSaved ? __('Discard Changes', 'tutor') : __('Cancel', 'tutor')}
              </Button>
              <Button
                data-cy="save-subscription"
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
                            courseId={courseId}
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
                              courseId={courseId}
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
    width: 100%;
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

    ${Breakpoint.smallMobile} {
      padding-block: ${spacing[24]};
      padding-inline: ${spacing[8]};
    }
  `,
  header: css`
    display: flex;
    align-items: center;
    justify-content: space-between;

    h6 {
      ${typography.heading6('medium')};
      color: ${colorTokens.text.primary};
      text-transform: none;
      letter-spacing: normal;
    }
  `,
  content: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[16]};
  `,
};
