import { useSortable } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import { css } from '@emotion/react';
import { __, sprintf } from '@wordpress/i18n';
import { useState } from 'react';

import SVGIcon from '@TutorShared/atoms/SVGIcon';
import { useModal } from '@TutorShared/components/modals/Modal';
import SubscriptionModal from '@TutorShared/components/modals/SubscriptionModal';

import Switch from '@TutorShared/atoms/Switch';
import { TutorBadge } from '@TutorShared/atoms/TutorBadge';
import { borderRadius, colorTokens, shadow, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import Show from '@TutorShared/controls/Show';
import ThreeDots from '@TutorShared/molecules/ThreeDots';
import {
  convertFormDataToSubscription,
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
  const { showModal } = useModal();
  const updateSubscriptionMutation = useSaveCourseSubscriptionMutation(courseId);
  const [isThreeDotOpen, setIsThreeDotOpen] = useState(false);

  const { attributes, listeners, setNodeRef, transform, transition, isDragging } = useSortable({
    id: subscription.id || '',
    animateLayoutChanges,
  });

  const handleToggleSubscription = (isEnabled: boolean) => {
    const payload = convertFormDataToSubscription(subscription);
    updateSubscriptionMutation.mutate({
      ...payload,
      is_enabled: isEnabled ? '1' : '0',
    });
  };

  const style = {
    transform: CSS.Transform.toString(transform),
    transition,
    opacity: isDragging ? 0.3 : undefined,
    background: isDragging ? colorTokens.stroke.hover : undefined,
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
        <p css={styles.title} {...listeners}>
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
        <div css={styles.information}>
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
        </div>
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
            onClick={() => {
              showModal({
                component: SubscriptionModal,
                props: {
                  title: __('Manage Subscription Plans', 'tutor'),
                  icon: <SVGIcon name="dollarRecurring" width={24} height={24} />,
                  expandedSubscriptionId: subscription.id,
                  courseId,
                  isBundle,
                },
              });
              setIsThreeDotOpen(false);
            }}
          />
          <ThreeDots.Option
            icon={<SVGIcon name="duplicate" width={16} height={16} />}
            text={__('Duplicate', 'tutor')}
            onClick={() => {
              // Handle duplicate action
            }}
          />
          <ThreeDots.Option
            icon={<SVGIcon name="delete" width={16} height={16} />}
            text={__('Delete', 'tutor')}
            isTrash
            onClick={() => {
              // Handle delete action
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
    background-color: ${colorTokens.background.white};
    padding: ${spacing[8]} ${spacing[12]};

    [data-grabber] {
      margin-left: -${spacing[4]};
      color: ${colorTokens.icon.default};
      flex-shrink: 0;
    }

    [data-action-buttons] {
      opacity: ${isActionButtonVisible ? 1 : 0};
      transition: opacity 0.3s ease;
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
  editButton: css`
    ${styleUtils.resetButton};
    ${styleUtils.flexCenter()};
    width: 24px;
    height: 24px;
    border-radius: ${borderRadius[4]};
    color: ${colorTokens.icon.default};
    transition:
      color 0.3s ease,
      background 0.3s ease;

    &:hover:not(:disabled) {
      background: ${colorTokens.action.secondary.default};
      color: ${colorTokens.icon.brand};
    }

    &:disabled {
      color: ${colorTokens.icon.disable};
      background: ${colorTokens.background.hover};
    }
  `,
  actionButtons: css`
    ${styleUtils.display.flex()};
    align-items: center;
    gap: ${spacing[8]};
  `,
  badge: css`
    margin-left: ${spacing[8]};
  `,
};
