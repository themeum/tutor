import { css } from '@emotion/react';
import { __, sprintf } from '@wordpress/i18n';

import SVGIcon from '@Atoms/SVGIcon';
import { useModal } from '@Components/modals/Modal';
import SubscriptionModal from '@CourseBuilderComponents/modals/SubscriptionModal';

import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import type { DurationUnit, SubscriptionFormData } from '@CourseBuilderServices/subscription';
import { styleUtils } from '@Utils/style-utils';

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

export function PreviewItem({ subscription }: { subscription: SubscriptionFormData }) {
  const { showModal } = useModal();

  return (
    <div css={styles.wrapper}>
      <div css={styles.item}>
        <p css={styles.title}>
          {subscription.plan_name}
          <Show when={subscription.is_featured}>
            <SVGIcon style={styles.featuredIcon} name="star" height={20} width={20} />
          </Show>
        </p>
        <div css={styles.information}>
          <Show when={subscription.payment_type === 'recurring'} fallback={<span>{__('Lifetime', 'tutor')}</span>}>
            <span>
              {sprintf(
                __('Renew every %s %s', 'tutor'),
                subscription.recurring_value.toString().padStart(2, '0'),
                formatRepeatUnit(subscription.recurring_interval, Number(subscription.recurring_value)),
              )}
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
              when={subscription.recurring_limit === 'Until cancelled'}
              fallback={
                <>
                  <span>•</span>
                  <span>
                    {subscription.recurring_limit.toString().padStart(2, '0')} {__('Times', 'tutor')}
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
      <button
        type="button"
        css={styles.editButton}
        onClick={() => {
          showModal({
            component: SubscriptionModal,
            props: {
              title: __('Manage Subscription Plans', 'tutor'),
              icon: <SVGIcon name="dollar-recurring" width={24} height={24} />,
              expandedSubscriptionId: subscription.id,
            },
          });
        }}
        data-edit-button
      >
        <SVGIcon name="pen" width={19} height={19} />
      </button>
    </div>
  );
}

const styles = {
  wrapper: css`
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: ${colorTokens.background.white};
    padding: ${spacing[8]} ${spacing[12]};

    [data-edit-button] {
      opacity: 0;
      transition: opacity 0.3s ease;
    }

    &:hover {
      background-color: ${colorTokens.background.hover};

      [data-edit-button] {
        opacity: 1;
      }
    }

    &:not(:last-of-type) {
      border-bottom: 1px solid ${colorTokens.stroke.default};
    }
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
    transition: color 0.3s ease, background 0.3s ease;
    
    &:hover {
      background: ${colorTokens.action.secondary.default};
      color: ${colorTokens.icon.brand};
    }
  `,
};
