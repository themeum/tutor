import { colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import type { DurationUnit, Subscription } from '@CourseBuilderServices/subscription';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';

export function formatRepeatUnit(unit: DurationUnit | 'until_cancellation', value: number) {
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

export function PreviewItem({ subscription }: { subscription: Subscription }) {
  return (
    <div css={styles.item}>
      <p css={styles.title}>{subscription.title}</p>
      <div css={styles.information}>
        {subscription.pricing_option === 'recurring' && (
          <span>
            {__('Renew every', 'tutor')} {subscription.repeat_every.toString().padStart(2, '0')}{' '}
            {formatRepeatUnit(subscription.repeat_unit, subscription.repeat_every)}
          </span>
        )}
        {subscription.pricing_option === 'one-time-purchase' && <span>{__('Lifetime', 'tutor')}</span>}

        <Show when={subscription.trial}>
          <span>•</span>
          <span>
            {subscription.trial.toString().padStart(2, '0')}{' '}
            {formatRepeatUnit(subscription.trial_unit, subscription.trial)} {__('trial', 'tutor')}
          </span>
        </Show>

        {subscription.lifetime_unit === 'until_cancellation' ? (
          <>
            <span>•</span>
            <span>{formatRepeatUnit(subscription.lifetime_unit, 0)}</span>
          </>
        ) : (
          <>
            <span>•</span>
            <span>
              {subscription.lifetime.toString().padStart(2, '0')}{' '}
              {formatRepeatUnit(subscription.lifetime_unit, subscription.lifetime)}
            </span>
          </>
        )}
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
