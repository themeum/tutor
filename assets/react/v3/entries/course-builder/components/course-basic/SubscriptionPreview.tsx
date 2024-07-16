import Button from "@Atoms/Button";
import SVGIcon from "@Atoms/SVGIcon";
import { borderRadius, colorTokens, spacing } from "@Config/styles";
import { typography } from "@Config/typography";
import For from "@Controls/For";
import Show from "@Controls/Show";
import type { CourseFormData } from "@CourseBuilderServices/course";
import { css } from "@emotion/react";
import { styleUtils } from "@Utils/style-utils";
import { __ } from "@wordpress/i18n";
import { useFormContext } from "react-hook-form";


type DurationUnit = 'day' | 'week' | 'month' | 'year';

interface RecurringSubscription {
	pricing_option: 'recurring';
	repeat_every: number;
	repeat_unit: DurationUnit;
}

interface LifetimeSubscription {
	pricing_option: 'one-time-purchase';
}

type Subscription = {
	title: string;
	trial: number;
	trial_unit: DurationUnit; 
	lifetime: number;
	lifetime_unit: DurationUnit | 'until_cancellation';
} & (RecurringSubscription | LifetimeSubscription);

const subscriptions: Subscription[] = [
	{
		title: 'Monthly Subscription',
		pricing_option: 'recurring',
		repeat_every: 3,
		repeat_unit: 'month',
		trial: 15,
		trial_unit: 'day',
		lifetime: 3,
		lifetime_unit: 'month'
	},
	{
		title: 'Yearly Subscription',
		pricing_option: 'recurring',
		repeat_every: 1,
		repeat_unit: 'year',
		trial: 10,
		trial_unit: 'day',
		lifetime: 3,
		lifetime_unit: 'year'
	},
	{
		title: 'Lifetime Subscription',
		pricing_option: 'one-time-purchase',
		trial: 5,
		trial_unit: 'day',
		lifetime: -1,
		lifetime_unit: 'until_cancellation'
	},
];

function formatRepeatUnit(unit: DurationUnit | 'until_cancellation', value: number) {
	switch(unit) {
		case 'day':
			return value > 1 ? __('Days', 'tutor'): __('Day', 'tutor');
		case 'week':
			return value > 1 ? __('Weeks', 'tutor'): __('Week', 'tutor');
		case 'month':
			return value > 1 ? __('Months', 'tutor'): __('Month', 'tutor');
		case 'year':
			return value > 1 ? __('Years', 'tutor'): __('Year', 'tutor');
		case 'until_cancellation':
			return __('Until Cancellation', 'tutor');
	}
}

function SubscriptionItem({subscription}: {subscription: Subscription}) {
	return <div css={styles.item}>
		<p css={styles.title}>{subscription.title}</p>
		<div css={styles.information}>
			{subscription.pricing_option === 'recurring' && (
				<span>{__('Renew every', 'tutor')} {subscription.repeat_every.toString().padStart(2, '0')} {formatRepeatUnit(subscription.repeat_unit, subscription.repeat_every)}</span>
			)}
			{subscription.pricing_option === 'one-time-purchase' && (
				<span>{__('Lifetime', 'tutor')}</span>
			)}
			
			<Show when={subscription.trial}>
				<span css={styles.dot} />
				<span>{subscription.trial.toString().padStart(2, '0')} {formatRepeatUnit(subscription.trial_unit, subscription.trial)} {__('trial', 'tutor')}</span>
			</Show>

			{subscription.lifetime_unit === 'until_cancellation' ? (
				<>
					<span css={styles.dot} />
					<span>{formatRepeatUnit(subscription.lifetime_unit, 0)}</span>
				</>
			): (
				<>
					<span css={styles.dot} />
					<span>{subscription.lifetime.toString().padStart(2, '0')} {formatRepeatUnit(subscription.lifetime_unit, subscription.lifetime)}</span>
				</>
			)}
		</div>
	</div>
}

function SubscriptionPreview() {
	const form = useFormContext<CourseFormData>();
	return <div css={styles.outer}>
		<div css={styles.header}>
			<p>{__('Subscriptions')}</p>
			<Show when={subscriptions.length > 0}>
				<button type="button" css={styles.editButton} onClick={() => alert('@TODO: will be implemented later.')}>
					<SVGIcon name="edit" width={24} height={24} />
				</button>
			</Show>
		</div>
		<Show when={subscriptions.length > 0} fallback={<div css={styles.emptyState}>
			<Button variant="secondary" icon={<SVGIcon name="dollar-recurring" width={24} height={24} />} onClick={() => alert('@TODO: will be implemented later.')}>
				{__('Add Subscription', 'tutor')}
			</Button>
		</div>}>
			<div css={styles.inner}>
				<For each={subscriptions}>
					{(subscription, index) => (
						<SubscriptionItem key={index} subscription={subscription} />
					)}
				</For>
			</div>
		</Show>
	</div>;
}

export default SubscriptionPreview;
const styles = {
	outer: css`
		width: 100%;
		display: flex;
		flex-direction: column;
		gap: ${spacing[8]};
	`,
	inner: css`
		border: 1px solid ${colorTokens.stroke.default};
		border-radius: ${borderRadius.card};
		padding: ${spacing[16]};
		width: 100%;
		margin-top: ${spacing[8]};
		display: flex;
		flex-direction: column;
		gap: ${spacing[8]};
	`,
	header: css`
		display: flex;
		align-items: center;
		justify-content: space-between;
		${typography.body()};
		color: ${colorTokens.text.title};
	`,
	editButton: css`
		${styleUtils.resetButton};
		color: ${colorTokens.icon.default};
		transition: color 0.3s ease;
		&:hover {
			color: ${colorTokens.icon.hover};
		}
	`,
	emptyState: css`
		width: 100%;
		
		& > button {
			width: 100%;
		}
	`,
	item: css`
		background-color: ${colorTokens.background.white};
		padding: ${spacing[8]} ${spacing[12]};
		border-radius: ${borderRadius.input};
		min-height: 48px;
		display: flex;
		flex-direction: column;
		justify-content: center;
		gap: ${spacing[4]};
	`,
	dot: css`
		width: 2px;
		height: 2px;
		background-color: ${colorTokens.icon.default};
		border-radius: ${borderRadius.circle};
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
	`
}