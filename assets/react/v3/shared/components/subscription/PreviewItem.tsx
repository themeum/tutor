import { useSortable } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import { css } from '@emotion/react';
import { __, sprintf } from '@wordpress/i18n';
import { useCallback, useEffect, useMemo, useRef, useState } from 'react';

import LoadingSpinner from '@TutorShared/atoms/LoadingSpinner';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import Switch from '@TutorShared/atoms/Switch';
import { TutorBadge } from '@TutorShared/atoms/TutorBadge';
import ThreeDots from '@TutorShared/molecules/ThreeDots';

import ConfirmationModal from '@TutorShared/components/modals/ConfirmationModal';
import { useModal } from '@TutorShared/components/modals/Modal';
import SubscriptionModal from '@TutorShared/components/modals/SubscriptionModal';
import { borderRadius, colorTokens, fontSize, shadow, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import Show from '@TutorShared/controls/Show';
import {
  BILLING_CYCLE_CUSTOM_PRESETS,
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

const MARQUEE_SPEED_PX_PER_SEC = 60;

export const formatRepeatUnit = (unit: Omit<DurationUnit, 'hour'>, value: number) => {
  switch (unit) {
    case 'hour':
      return value > 1 ? __('Hours', __TUTOR_TEXT_DOMAIN__) : __('Hour', __TUTOR_TEXT_DOMAIN__);
    case 'day':
      return value > 1 ? __('Days', __TUTOR_TEXT_DOMAIN__) : __('Day', __TUTOR_TEXT_DOMAIN__);
    case 'week':
      return value > 1 ? __('Weeks', __TUTOR_TEXT_DOMAIN__) : __('Week', __TUTOR_TEXT_DOMAIN__);
    case 'month':
      return value > 1 ? __('Months', __TUTOR_TEXT_DOMAIN__) : __('Month', __TUTOR_TEXT_DOMAIN__);
    case 'year':
      return value > 1 ? __('Years', __TUTOR_TEXT_DOMAIN__) : __('Year', __TUTOR_TEXT_DOMAIN__);
    case 'until_cancellation':
      return __('Until Cancellation', __TUTOR_TEXT_DOMAIN__);
  }
};

export const PreviewItem = ({ subscription, courseId, isBundle, isOverlay }: PreviewItemProps) => {
  const [isThreeDotOpen, setIsThreeDotOpen] = useState(false);
  const [marqueeDuration, setMarqueeDuration] = useState(0);
  const [marqueeDistance, setMarqueeDistance] = useState(0);

  const { showModal, updateModal, closeModal } = useModal();
  const updateSubscriptionMutation = useSaveCourseSubscriptionMutation(courseId);
  const deleteSubscriptionMutation = useDeleteCourseSubscriptionMutation(courseId);
  const duplicateSubscriptionMutation = useDuplicateCourseSubscriptionMutation(courseId);

  const marqueeContainerRef = useRef<HTMLParagraphElement>(null);
  const marqueeContentRef = useRef<HTMLSpanElement>(null);

  const { attributes, listeners, setNodeRef, transform, transition, isDragging } = useSortable({
    id: subscription.id || '',
    animateLayoutChanges,
  });

  const sortableStyle = {
    transform: CSS.Transform.toString(transform),
    transition,
    opacity: isDragging ? 0.3 : undefined,
    background: isDragging ? colorTokens.stroke.hover : undefined,
  };

  const recurringLimit = useMemo(() => {
    let recurringLimitText: string | JSX.Element =
      `${subscription.recurring_limit.toString().padStart(2, '0')} ${__('Billing Cycles', __TUTOR_TEXT_DOMAIN__)}`;

    if (subscription.recurring_limit === BILLING_CYCLE_CUSTOM_PRESETS.untilCancelled) {
      recurringLimitText = __('Until Cancellation', __TUTOR_TEXT_DOMAIN__);
    }

    if (subscription.recurring_limit === BILLING_CYCLE_CUSTOM_PRESETS.noRenewal) {
      recurringLimitText = __('No Renewal', __TUTOR_TEXT_DOMAIN__);
    }

    return (
      <>
        <span>•</span>
        <span>{recurringLimitText}</span>
      </>
    );
  }, [subscription.recurring_limit]);

  const marqueeText = useMemo(
    () => (
      <>
        <Show
          when={subscription.payment_type === 'recurring'}
          fallback={<span>{__('Lifetime', __TUTOR_TEXT_DOMAIN__)}</span>}
        >
          <span>
            <Show
              when={subscription.recurring_limit !== BILLING_CYCLE_CUSTOM_PRESETS.noRenewal}
              fallback={`${subscription.recurring_value.toString().padStart(2, '0')} ${formatRepeatUnit(subscription.recurring_interval, Number(subscription.recurring_value))}`}
            >
              {sprintf(
                // translators: %1$s - recurring value, %2$s - recurring unit
                __('Renew every %1$s %2$s', __TUTOR_TEXT_DOMAIN__),
                subscription.recurring_value.toString().padStart(2, '0'),
                formatRepeatUnit(subscription.recurring_interval, Number(subscription.recurring_value)),
              )}
            </Show>
          </span>
        </Show>
        <Show when={subscription.payment_type !== 'onetime'}>{recurringLimit}</Show>
      </>
    ),
    [
      subscription.payment_type,
      subscription.recurring_limit,
      subscription.recurring_interval,
      subscription.recurring_value,
      recurringLimit,
    ],
  );

  const handleToggleSubscription = useCallback(
    (isEnabled: boolean) => {
      const payload = convertFormDataToSubscription(subscription);
      updateSubscriptionMutation.mutate({
        ...payload,
        is_enabled: isEnabled ? '1' : '0',
      });
    },
    [subscription, updateSubscriptionMutation],
  );

  const handleEditSubscription = useCallback(() => {
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
  }, [subscription, showModal, courseId, isBundle]);

  const handleDeleteSubscription = useCallback(async () => {
    updateModal<typeof ConfirmationModal>('subscription-delete-modal', {
      isLoading: true,
    });

    const response = await deleteSubscriptionMutation.mutateAsync(Number(subscription.id));

    if (response.status_code === 200) {
      closeModal();
    }
  }, [updateModal, deleteSubscriptionMutation, subscription.id, closeModal]);

  const handleDuplicateSubscription = useCallback(async () => {
    const response = await duplicateSubscriptionMutation.mutateAsync(Number(subscription.id));

    if (response.data) {
      setIsThreeDotOpen(false);
    }
  }, [duplicateSubscriptionMutation, subscription.id]);

  const handleKeyDown = useCallback(
    (event: React.KeyboardEvent) => {
      if (event.key === 'Enter' || event.key === ' ') {
        handleEditSubscription();
      }
    },
    [handleEditSubscription],
  );

  const handleDeleteClick = useCallback(() => {
    setIsThreeDotOpen(false);
    showModal({
      id: 'subscription-delete-modal',
      component: ConfirmationModal,
      props: {
        title: sprintf(
          // translators: %s is the title of the item to be deleted
          __('Delete "%s"', __TUTOR_TEXT_DOMAIN__),
          subscription.plan_name,
        ),
        description: __('Are you sure you want to delete this plan? This cannot be undone.', __TUTOR_TEXT_DOMAIN__),
        onConfirm: handleDeleteSubscription,
        confirmButtonVariant: 'danger' as const,
      },
    });
  }, [showModal, subscription.plan_name, handleDeleteSubscription]);

  useEffect(() => {
    const container = marqueeContainerRef.current;
    const content = marqueeContentRef.current;

    if (!container || !content) {
      return;
    }

    const overflow = content.scrollWidth > container.clientWidth;

    if (overflow) {
      const distance = content.scrollWidth - container.clientWidth;
      setMarqueeDistance(distance);
      setMarqueeDuration(distance / MARQUEE_SPEED_PX_PER_SEC);
    }
  }, [
    subscription.plan_name,
    subscription.payment_type,
    subscription.recurring_value,
    subscription.recurring_interval,
    subscription.recurring_limit,
  ]);

  return (
    <div
      data-cy="subscription-preview-item"
      css={styles.wrapper({
        isActionButtonVisible: isThreeDotOpen || updateSubscriptionMutation.isPending,
        isOverlay,
        marqueeDuration,
        marqueeDistance,
      })}
      style={sortableStyle}
      ref={setNodeRef}
      aria-label={__('Subscription plan item', __TUTOR_TEXT_DOMAIN__)}
    >
      <SVGIcon {...listeners} {...attributes} data-grabber name="threeDotsVerticalDouble" width={20} height={20} />

      <div css={styles.item}>
        <div css={styles.header}>
          <p
            css={styles.title}
            onClick={handleEditSubscription}
            onKeyDown={handleKeyDown}
            tabIndex={0}
            aria-label={__('Edit subscription plan', __TUTOR_TEXT_DOMAIN__)}
          >
            <span data-plan-name title={subscription.plan_name}>
              {subscription.plan_name}
            </span>
            <Show when={subscription.is_featured}>
              <SVGIcon style={styles.featuredIcon} name="star" height={20} width={20} />
            </Show>
            <Show when={!subscription.is_enabled}>
              <TutorBadge css={styles.badge} variant="secondary" title={__('Inactive', __TUTOR_TEXT_DOMAIN__)}>
                {__('Inactive', __TUTOR_TEXT_DOMAIN__)}
              </TutorBadge>
            </Show>
          </p>
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
              arrow={true}
              data-three-dot
            >
              <ThreeDots.Option
                icon={<SVGIcon name="edit" width={16} height={16} />}
                text={__('Edit', __TUTOR_TEXT_DOMAIN__)}
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
                text={__('Duplicate', __TUTOR_TEXT_DOMAIN__)}
                onClick={handleDuplicateSubscription}
              />
              <ThreeDots.Option
                icon={<SVGIcon name="delete" width={16} height={16} />}
                text={__('Delete', __TUTOR_TEXT_DOMAIN__)}
                isTrash
                onClick={handleDeleteClick}
              />
            </ThreeDots>
          </div>
        </div>
        <p
          css={styles.information}
          ref={marqueeContainerRef}
          aria-label={__('Subscription plan details', __TUTOR_TEXT_DOMAIN__)}
          title={marqueeContainerRef.current?.textContent}
        >
          <span css={styles.marqueeSlide} ref={marqueeContentRef} data-marquee-content>
            <span>{marqueeText}</span>
          </span>
        </p>
      </div>
    </div>
  );
};

const styles = {
  wrapper: ({ isActionButtonVisible = false, isOverlay = false, marqueeDuration = 0, marqueeDistance = 0 }) => css`
    ${styleUtils.display.flex()};
    gap: ${spacing[4]};
    background-color: ${colorTokens.background.white};
    padding: ${spacing[8]} ${spacing[12]} ${spacing[8]} ${spacing[4]};
    min-width: 0;

    [data-grabber] {
      align-self: flex-start;
      margin-top: ${spacing[2]};
      color: ${colorTokens.icon.default};
      flex-shrink: 0;
      cursor: grab;

      &:focus-visible {
        border-radius: ${borderRadius[4]};
        outline: 2px solid ${colorTokens.stroke.brand};
      }
    }

    [data-three-dot] {
      height: 20px;
      width: 20px;

      svg {
        height: 24px;
        width: 24px;
        flex-shrink: 0;
      }
    }

    [data-action-buttons] {
      opacity: ${isActionButtonVisible ? 1 : 0};
      background-color: inherit;
    }

    [data-marquee-content] {
      ${marqueeDistance > 0 &&
      css`
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100%;
        min-width: 0;
      `}
    }

    &:hover {
      background-color: ${colorTokens.background.hover};

      [data-action-buttons] {
        opacity: 1;
      }

      [data-marquee-content] {
        ${marqueeDistance > 0 &&
        css`
          overflow: unset;
          text-overflow: unset;
          animation: marquee-slide ${marqueeDuration}s ease-out forwards;
          will-change: transform;

          @keyframes marquee-slide {
            0% {
              transform: translateX(0);
            }
            100% {
              transform: translateX(-${marqueeDistance}px);
            }
          }
        `}
      }
    }

    &:not(:last-of-type) {
      border-bottom: 1px solid ${colorTokens.stroke.default};
    }

    &:focus-within {
      [data-action-buttons] {
        opacity: 1;
      }
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
    width: 100%;
    min-height: 48px;
    ${styleUtils.display.flex('column')};
    justify-content: center;
    gap: ${spacing[4]};
    min-width: 0;
  `,
  header: css`
    ${styleUtils.display.flex()};
    justify-content: space-between;
    gap: ${spacing[8]};
    min-width: 0;
  `,
  title: css`
    ${typography.caption('medium')};
    color: ${colorTokens.text.primary};
    display: flex;
    align-items: center;
    cursor: pointer;

    [data-plan-name] {
      ${styleUtils.text.ellipsis(1)};
    }

    &:focus-visible {
      border-radius: ${borderRadius[4]};
      outline: 2px solid ${colorTokens.stroke.brand};
    }
  `,
  information: css`
    width: 100%;
    max-width: 100%;
    min-width: 0;
    ${typography.small()};
    color: ${colorTokens.text.hints};
    display: flex;
    align-items: center;
    flex-grow: 1;
    overflow: hidden;
    position: relative;
    white-space: nowrap;
  `,
  marqueeContent: ({ shouldEllipsis = false }: { shouldEllipsis?: boolean }) => css`
    display: inline-block;
    white-space: nowrap;
    vertical-align: middle;
    min-width: 100%;

    span {
      margin-right: ${spacing[4]};
      white-space: nowrap;

      &:last-child {
        margin-right: 0;
      }
    }

    ${shouldEllipsis &&
    css`
      overflow: hidden;
      text-overflow: ellipsis;
      max-width: 100%;
      min-width: 0;
    `}
  `,
  marqueeSlide: css`
    display: inline-block;
    white-space: nowrap;
    vertical-align: middle;
    min-width: 100%;

    span {
      margin-right: ${spacing[4]};
      white-space: nowrap;

      &:last-child {
        margin-right: 0;
      }
    }
  `,
  featuredIcon: css`
    flex-shrink: 0;
    color: ${colorTokens.icon.brand};
  `,
  actionButtons: css`
    ${styleUtils.display.flex()};
    height: 100%;
    align-items: center;
    gap: ${spacing[8]};
  `,
  badge: css`
    flex-shrink: 0;
    margin-left: ${spacing[8]};
    font-size: ${fontSize[11]};
    padding: 0 ${spacing[6]};
  `,
};
