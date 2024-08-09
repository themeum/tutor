import { colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import type { DurationUnit, SubscriptionFormData } from '@CourseBuilderServices/subscription';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';

export function formatRepeatUnit(unit: Omit<DurationUnit, 'hour'>, value: number) {
  switch (unit) {
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
  return (
    <div css={styles.item}>
      <p css={styles.title}>{subscription.plan_name}</p>
      <div css={styles.information}>
        <span>
          {__('Renew every', 'tutor')} {subscription.recurring_value.toString().padStart(2, '0')}{' '}
          {formatRepeatUnit(subscription.recurring_interval, Number(subscription.recurring_value))}
        </span>

        <Show when={subscription.enable_free_trial}>
          <span>•</span>
          <span>
            {subscription.trial_value.toString().padStart(2, '0')}{' '}
            {formatRepeatUnit(subscription.trial_interval, Number(subscription.trial_value))} {__('trial', 'tutor')}
          </span>
        </Show>
      </div>
    </div>
  );
}

const styles = {
  item: css`
		background-color: ${colorTokens.background.white};
		padding: ${spacing[8]} ${spacing[12]};
		min-height: 48px;
		display: flex;
		flex-direction: column;
		justify-content: center;
		gap: ${spacing[4]};

    &:not(:last-of-type) {
      border-bottom: 1px solid ${colorTokens.stroke.default};
    }
	`,
  title: css`
		${typography.small('medium')};
		color: ${colorTokens.text.primary};
	`,
  information: css`
		${typography.tiny()};
		color: ${colorTokens.text.hints};
		display: flex;
		align-items: center;
		flex-wrap: wrap;
		gap: ${spacing[4]};
	`,
};
