import { useSortable } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import { css } from '@emotion/react';
import { __, sprintf } from '@wordpress/i18n';
import { useState } from 'react';

import LoadingSpinner from '@TutorShared/atoms/LoadingSpinner';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import Switch from '@TutorShared/atoms/Switch';
import { TutorBadge } from '@TutorShared/atoms/TutorBadge';
import { useModal } from '@TutorShared/components/modals/Modal';
import SubscriptionModal from '@TutorShared/components/modals/SubscriptionModal';

import ConfirmationModal from '@TutorShared/components/modals/ConfirmationModal';
import { borderRadius, colorTokens, fontSize, shadow, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import Show from '@TutorShared/controls/Show';
import ThreeDots from '@TutorShared/molecules/ThreeDots';
import {
  convertFormDataToSubscription,
  useDeleteCourseSubscriptionMutation,
  useDuplicateCourseSubscriptionMutation,
  useSaveCourseSubscriptionMutation,
  type SubscriptionFormData,
} from '@TutorShared/services/subscription';
import { animateLayoutChanges } from '@TutorShared/utils/dndkit';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { type DurationUnit } from '@TutorShared/utils/types';

interface PreviewItemProps {
  courseId: number;
  subscription: SubscriptionFormData;
  isBundle?: boolean;
  isOverlay?: boolean;
}

export function formatRepeatUnit(unit: Omit<DurationUnit, 'hour'>, value: number) {
  switch (unit) {
    case 'hour':
      return value > 1 ? __('Hours', 'tutor') : __('Hour', 'tutor');
    case 'day':
      return value > 1 ? __('Days', 'tutor') : __('Day', 'tutor');
    case 'week':
      return value > 1 ? __('Weeks', 'tutor') : __('Week', 'tutor');
    case 'month':
      return value > 1 ? __('Months', 'tutor') : __('Month', 'tutor');
    case 'year':
      return value > 1 ? __('Years', 'tutor') : __('Year', 'tutor');
    case 'until_cancellation':
      return __('Until Cancellation', 'tutor');
  }
}

export function PreviewItem({ subscription, courseId, isBundle, isOverlay }: PreviewItemProps) {
  const [isThreeDotOpen, setIsThreeDotOpen] = useState(false);
  const { showModal, updateModal, closeModal } = useModal();
  const updateSubscriptionMutation = useSaveCourseSubscriptionMutation(courseId);
  const deleteSubscriptionMutation = useDeleteCourseSubscriptionMutation(courseId);
  const duplicateSubscriptionMutation = useDuplicateCourseSubscriptionMutation(courseId);

  const { attributes, listeners, setNodeRef, transform, transition, isDragging } = useSortable({
    id: subscription.id || '',
    animateLayoutChanges,
  });

  const style = {
    transform: CSS.Transform.toString(transform),
    transition,
    opacity: isDragging ? 0.3 : undefined,
    background: isDragging ? colorTokens.stroke.hover : undefined,
  };

  const handleToggleSubscription = (isEnabled: boolean) => {
    const payload = convertFormDataToSubscription(subscription);
    updateSubscriptionMutation.mutate({
      ...payload,
      is_enabled: isEnabled ? '1' : '0',
    });
  };

  const handleEditSubscription = () => {
    const subscriptionWithSaved = {
      ...subscription,
      isSaved: true,
    };
    showModal({
      component: SubscriptionModal,
      props: {
        icon: <SVGIcon name="dollarRecurring" width={24} height={24} />,
        subscription: subscriptionWithSaved,
        courseId,
        isBundle,
      },
    });
    setIsThreeDotOpen(false);
  };

  const handleDeleteSubscription = async () => {
    updateModal<typeof ConfirmationModal>('subscription-delete-modal', {
      isLoading: true,
    });

    const response = await deleteSubscriptionMutation.mutateAsync(Number(subscription.id));

    if (response.status_code === 200) {
      closeModal();
    }
  };

  const handleDuplicateSubscription = async () => {
    const response = await duplicateSubscriptionMutation.mutateAsync(Number(subscription.id));

    if (response.status_code === 200) {
      setIsThreeDotOpen(false);
    }
  };

  return (
    <div
      {...attributes}
      data-cy="subscription-preview-item"
      css={styles.wrapper({ isActionButtonVisible: isThreeDotOpen || updateSubscriptionMutation.isPending, isOverlay })}
      style={style}
      ref={setNodeRef}
    >
      <div css={styles.item}>
        <p
          css={styles.title}
          {...listeners}
          onClick={handleEditSubscription}
          onKeyDown={(event) => {
            if (event.key === 'Enter' || event.key === ' ') {
              handleEditSubscription();
            }
          }}
        >
          <SVGIcon data-grabber name="threeDotsVerticalDouble" width={20} height={20} />
          {subscription.plan_name}
          <Show when={subscription.is_featured}>
            <SVGIcon style={styles.featuredIcon} name="star" height={20} width={20} />
          </Show>
          <Show when={!subscription.is_enabled}>
            <TutorBadge css={styles.badge} variant="secondary" title={__('Inactive', 'tutor')}>
              {__('Inactive', 'tutor')}
            </TutorBadge>
          </Show>
        </p>
        <p css={styles.information}>
          <Show when={subscription.payment_type === 'recurring'} fallback={<span>{__('Lifetime', 'tutor')}</span>}>
            <span>
              {
                /* translators: %1$s is the number and the %2$s is the repeat unit (e.g., day, week, month) */
                sprintf(
                  __('Renew every %1$s %2$s', 'tutor'),
                  subscription.recurring_value.toString().padStart(2, '0'),
                  formatRepeatUnit(subscription.recurring_interval, Number(subscription.recurring_value)),
                )
              }
            </span>
          </Show>

          {/* @TODO: will be updated after confirmation */}
          {/* <Show when={subscription.enable_free_trial}>
          <span>•</span>
          <span>
            {sprintf(
              __('%s %s trial', 'tutor'),
              subscription.trial_value.toString().padStart(2, '0'),
              formatRepeatUnit(subscription.trial_interval, Number(subscription.trial_value)),
            )}
          </span>
        </Show> */}

          <Show when={subscription.payment_type !== 'onetime'}>
            <Show
              when={subscription.recurring_limit === __('Until cancelled', 'tutor')}
              fallback={
                <>
                  <span>•</span>
                  <span>
                    {subscription.recurring_limit.toString().padStart(2, '0')} {__('Billing Cycles', 'tutor')}
                  </span>
                </>
              }
            >
              <span>•</span>
              <span>{__('Until Cancellation', 'tutor')}</span>
            </Show>
          </Show>
        </p>
      </div>
      <div css={styles.actionButtons} data-action-buttons>
        <Switch
          checked={subscription.is_enabled}
          onChange={handleToggleSubscription}
          loading={updateSubscriptionMutation.isPending}
          size="small"
        />

        <ThreeDots
          isOpen={isThreeDotOpen}
          closePopover={() => setIsThreeDotOpen(false)}
          onClick={() => setIsThreeDotOpen(!isThreeDotOpen)}
          dotsOrientation="vertical"
          size="small"
        >
          <ThreeDots.Option
            icon={<SVGIcon name="edit" width={16} height={16} />}
            text={__('Edit', 'tutor')}
            data-cy="edit-subscription"
            onClick={handleEditSubscription}
          />
          <ThreeDots.Option
            icon={
              duplicateSubscriptionMutation.isPending ? (
                <LoadingSpinner size={16} />
              ) : (
                <SVGIcon name="duplicate" width={16} height={16} />
              )
            }
            text={__('Duplicate', 'tutor')}
            onClick={handleDuplicateSubscription}
          />
          <ThreeDots.Option
            icon={<SVGIcon name="delete" width={16} height={16} />}
            text={__('Delete', 'tutor')}
            isTrash
            onClick={() => {
              setIsThreeDotOpen(false);
              showModal({
                id: 'subscription-delete-modal',
                component: ConfirmationModal,
                props: {
                  // translators: %s is the title of the item to be deleted
                  title: sprintf(__('Delete "%s"', 'tutor'), subscription.plan_name),
                  description: __('Are you sure you want to delete this plan? This cannot be undone.', 'tutor'),
                  onConfirm: handleDeleteSubscription,
                  confirmButtonVariant: 'danger',
                },
              });
            }}
          />
        </ThreeDots>
      </div>
    </div>
  );
}

const styles = {
  wrapper: ({ isActionButtonVisible = false, isOverlay = false }) => css`
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
    background-color: ${colorTokens.background.white};
    padding: ${spacing[8]} ${spacing[12]};

    [data-grabber] {
      margin-left: -${spacing[4]};
      margin-right: ${spacing[4]};
      color: ${colorTokens.icon.default};
      flex-shrink: 0;
      cursor: grab;
    }

    [data-action-buttons] {
      opacity: ${isActionButtonVisible ? 1 : 0};
      background-color: inherit;
    }

    &:hover {
      background-color: ${colorTokens.background.hover};

      [data-action-buttons] {
        opacity: 1;
      }
    }

    &:not(:last-of-type) {
      border-bottom: 1px solid ${colorTokens.stroke.default};
    }

    ${isOverlay &&
    css`
      border-radius: ${borderRadius.card};
      box-shadow: ${shadow.drag};

      [data-grabber] {
        cursor: grabbing;
      }
    `}
  `,
  item: css`
    min-height: 48px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: ${spacing[4]};
  `,
  title: css`
    ${typography.caption('medium')};
    color: ${colorTokens.text.primary};
    display: flex;
    align-items: center;
    cursor: pointer;
  `,
  information: css`
    ${typography.small()};
    color: ${colorTokens.text.hints};
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: ${spacing[4]};
  `,
  featuredIcon: css`
    color: ${colorTokens.icon.brand};
  `,
  actionButtons: css`
    padding-inline: ${spacing[8]};
    position: absolute;
    top: 50%;
    right: 0;
    transform: translateY(-50%);
    ${styleUtils.display.flex()};
    height: 100%;
    align-items: center;
    gap: ${spacing[8]};
  `,
  badge: css`
    margin-left: ${spacing[8]};
    font-size: ${fontSize[11]};
    padding: 0 ${spacing[6]};
  `,
};
