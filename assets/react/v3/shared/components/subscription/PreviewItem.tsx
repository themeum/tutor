import { useSortable } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import { css } from '@emotion/react';
import { __, sprintf } from '@wordpress/i18n';
import { useLayoutEffect, useRef, useState } from 'react';

import SVGIcon from '@TutorShared/atoms/SVGIcon';
import { TutorBadge } from '@TutorShared/atoms/TutorBadge';
import { useModal } from '@TutorShared/components/modals/Modal';
import SubscriptionModal from '@TutorShared/components/modals/SubscriptionModal';

import LoadingSpinner from '@TutorShared/atoms/LoadingSpinner';
import Switch from '@TutorShared/atoms/Switch';
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

export const formatRepeatUnit = (unit: Omit<DurationUnit, 'hour'>, value: number) => {
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
};

const MARQUEE_SPEED_PX_PER_SEC = 180;

export const PreviewItem = ({ subscription, courseId, isBundle, isOverlay }: PreviewItemProps) => {
  const [isThreeDotOpen, setIsThreeDotOpen] = useState(false);
  const [shouldAnimate, setShouldAnimate] = useState(false);
  const [isItemHovered, setIsItemHovered] = useState(false);
  const [marqueeDuration, setMarqueeDuration] = useState(0);
  const { showModal, updateModal, closeModal } = useModal();
  const updateSubscriptionMutation = useSaveCourseSubscriptionMutation(courseId);
  const deleteSubscriptionMutation = useDeleteCourseSubscriptionMutation(courseId);
  const duplicateSubscriptionMutation = useDuplicateCourseSubscriptionMutation(courseId);
  const marqueeContainerRef = useRef<HTMLParagraphElement>(null);
  const marqueeContentRef = useRef<HTMLSpanElement>(null);
  const marqueeSeparatorRef = useRef<HTMLSpanElement>(null);

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

    if (response.data) {
      setIsThreeDotOpen(false);
    }
  };

  const handleItemMouseEnter = () => setIsItemHovered(true);
  const handleItemMouseLeave = () => setIsItemHovered(false);

  const marqueeText = (
    <>
      <Show when={subscription.payment_type === 'recurring'} fallback={<span>{__('Lifetime', 'tutor')}</span>}>
        <span>
          {sprintf(
            __('Renew every %1$s %2$s', 'tutor'),
            subscription.recurring_value.toString().padStart(2, '0'),
            formatRepeatUnit(subscription.recurring_interval, Number(subscription.recurring_value)),
          )}
        </span>
      </Show>
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
    </>
  );

  useLayoutEffect(() => {
    const container = marqueeContainerRef.current;
    const content = marqueeContentRef.current;
    if (!container || !content) return;

    const overflow = content.scrollWidth > container.clientWidth;
    setShouldAnimate(overflow);

    if (overflow) {
      // Calculate separator width when animating
      const separator = marqueeSeparatorRef.current;
      const separatorWidth = separator ? separator.offsetWidth : 0;

      // Calculate duration for seamless loop (content width + separator width + duplicate content width)
      const totalWidth = content.scrollWidth * 2 + separatorWidth;
      setMarqueeDuration(totalWidth / MARQUEE_SPEED_PX_PER_SEC);
    }
  }, [
    subscription.plan_name,
    subscription.payment_type,
    subscription.recurring_value,
    subscription.recurring_interval,
    subscription.recurring_limit,
    isItemHovered, // Re-calculate when hover state changes to ensure separator is rendered
  ]);

  return (
    <div
      data-cy="subscription-preview-item"
      css={styles.wrapper({ isActionButtonVisible: isThreeDotOpen || updateSubscriptionMutation.isPending, isOverlay })}
      style={style}
      ref={setNodeRef}
      onMouseEnter={handleItemMouseEnter}
      onMouseLeave={handleItemMouseLeave}
      tabIndex={0}
      aria-label={__('Subscription plan item', 'tutor')}
    >
      <SVGIcon {...listeners} data-grabber name="threeDotsVerticalDouble" width={20} height={20} />

      <div css={styles.item}>
        <div css={styles.header}>
          <p
            css={styles.title}
            {...attributes}
            onClick={handleEditSubscription}
            onKeyDown={(event) => {
              if (event.key === 'Enter' || event.key === ' ') {
                handleEditSubscription();
              }
            }}
            tabIndex={0}
            aria-label={__('Edit subscription plan', 'tutor')}
          >
            <span data-plan-name title={subscription.plan_name}>
              {subscription.plan_name}
            </span>
            <Show when={subscription.is_featured}>
              <SVGIcon style={styles.featuredIcon} name="star" height={20} width={20} />
            </Show>
            <Show when={!subscription.is_enabled}>
              <TutorBadge css={styles.badge} variant="secondary" title={__('Inactive', 'tutor')}>
                {__('Inactive', 'tutor')}
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
              data-three-dot
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
        <p css={styles.information} ref={marqueeContainerRef} aria-label={__('Subscription plan details', 'tutor')}>
          <span
            css={[
              styles.marqueeContent({ shouldEllipsis: shouldAnimate && !isItemHovered }),
              shouldAnimate && isItemHovered && styles.marqueeLoop,
            ]}
            ref={marqueeContentRef}
            style={
              shouldAnimate && isItemHovered
                ? {
                    ['--marquee-duration' as string]: `${marqueeDuration}s`,
                  }
                : undefined
            }
          >
            <span>{marqueeText}</span>
            {shouldAnimate && isItemHovered && (
              <>
                <span css={styles.marqueeSeparator} ref={marqueeSeparatorRef} aria-hidden="true">
                  •
                </span>
                <span aria-hidden="true">{marqueeText}</span>
              </>
            )}
          </span>
        </p>
      </div>
    </div>
  );
};

const styles = {
  wrapper: ({ isActionButtonVisible = false, isOverlay = false }) => css`
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

    &:hover {
      background-color: ${colorTokens.background.hover};

      [data-action-buttons] {
        opacity: 1;
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

    :focus-visible {
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
    gap: ${spacing[4]};
    overflow: hidden;
    position: relative;
    white-space: nowrap;
  `,
  marqueeContent: ({ shouldEllipsis = false }: { shouldEllipsis?: boolean }) => css`
    display: ${shouldEllipsis ? 'inline-block' : 'flex'};
    white-space: nowrap;
    vertical-align: middle;
    min-width: 100%;
    align-items: center;
    span {
      margin-right: ${spacing[4]};
      white-space: nowrap;

      &:last-of-type {
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
  marqueeSeparator: css`
    flex-shrink: 0;
  `,
  marqueeLoop: css`
    animation: marquee-loop var(--marquee-duration, 4s) linear infinite;
    will-change: transform;
    @keyframes marquee-loop {
      0% {
        transform: translateX(0);
      }
      100% {
        transform: translateX(-50%);
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
