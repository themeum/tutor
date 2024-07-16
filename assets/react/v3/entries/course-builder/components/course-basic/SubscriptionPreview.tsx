import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import { useModal } from '@Components/modals/Modal';
import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import For from '@Controls/For';
import Show from '@Controls/Show';
import SubscriptionModal from '@CourseBuilderComponents/modals/SubscriptionModal';
import type { CourseFormData } from '@CourseBuilderServices/course';
import { styleUtils } from '@Utils/style-utils';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useFormContext } from 'react-hook-form';

export type DurationUnit = 'day' | 'week' | 'month' | 'year';

export interface RecurringSubscription {
  pricing_option: 'recurring';
  repeat_every: number;
  repeat_unit: DurationUnit;
}

export interface LifetimeSubscription {
  pricing_option: 'one-time-purchase';
}

export type Subscription = {
  id: number;
  title: string;
  trial: number;
  trial_unit: DurationUnit;
  lifetime: number;
  lifetime_unit: DurationUnit | 'until_cancellation';
  price: number;
  charge_enrolment_fee: boolean;
  enrolment_fee: number;
  enable_trial: boolean;
  has_sale: boolean;
  sale_price: number;
  schedule_sale_price: boolean;
  schedule_start_date: string;
  schedule_start_time: string;
  schedule_end_date: string;
  schedule_end_time: string;
} & (RecurringSubscription | LifetimeSubscription);

const subscriptions: Subscription[] = [
  {
    id: 1,
    title: 'Monthly Subscription',
    pricing_option: 'recurring',
    repeat_every: 3,
    repeat_unit: 'month',
    lifetime: 3,
    lifetime_unit: 'month',
    price: 100,
    charge_enrolment_fee: false,
    enrolment_fee: 0,
    enable_trial: false,
    trial: 15,
    trial_unit: 'day',
    has_sale: false,
    sale_price: 0,
    schedule_sale_price: true,
    schedule_start_date: '',
    schedule_start_time: '',
    schedule_end_date: '',
    schedule_end_time: '',
  },
  {
    id: 2,
    title: 'Yearly Subscription',
    pricing_option: 'recurring',
    repeat_every: 1,
    repeat_unit: 'year',
    trial: 10,
    trial_unit: 'day',
    lifetime: 3,
    lifetime_unit: 'year',
    price: 100,
    charge_enrolment_fee: false,
    enrolment_fee: 0,
    enable_trial: false,
    has_sale: false,
    sale_price: 0,
    schedule_sale_price: true,
    schedule_start_date: '',
    schedule_start_time: '',
    schedule_end_date: '',
    schedule_end_time: '',
  },
  {
    id: 3,
    title: 'Lifetime Subscription',
    pricing_option: 'one-time-purchase',
    trial: 5,
    trial_unit: 'day',
    lifetime: -1,
    lifetime_unit: 'until_cancellation',
    price: 100,
    charge_enrolment_fee: true,
    enrolment_fee: 0,
    enable_trial: true,
    has_sale: false,
    sale_price: 0,
    schedule_sale_price: true,
    schedule_start_date: '',
    schedule_start_time: '',
    schedule_end_date: '',
    schedule_end_time: '',
  },
];

function formatRepeatUnit(unit: DurationUnit | 'until_cancellation', value: number) {
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

function SubscriptionItem({ subscription }: { subscription: Subscription }) {
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
          <span css={styles.dot} />
          <span>
            {subscription.trial.toString().padStart(2, '0')}{' '}
            {formatRepeatUnit(subscription.trial_unit, subscription.trial)} {__('trial', 'tutor')}
          </span>
        </Show>

        {subscription.lifetime_unit === 'until_cancellation' ? (
          <>
            <span css={styles.dot} />
            <span>{formatRepeatUnit(subscription.lifetime_unit, 0)}</span>
          </>
        ) : (
          <>
            <span css={styles.dot} />
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

function SubscriptionPreview() {
  const form = useFormContext<CourseFormData>();
  const { showModal } = useModal();
  return (
    <div css={styles.outer}>
      <div css={styles.header}>
        <p>{__('Subscriptions')}</p>
        <Show when={subscriptions.length > 0}>
          <button
            type="button"
            css={styles.editButton}
            onClick={() => {
              showModal({
                component: SubscriptionModal,
                props: {
                  title: 'Manage Subscriptions',
                  icon: <SVGIcon name="dollar-recurring" width={24} height={24} />,
                  subscriptions: subscriptions.map((item) => ({ ...item, isEdit: false })),
                },
              });
            }}
          >
            <SVGIcon name="edit" width={24} height={24} />
          </button>
        </Show>
      </div>
      <Show
        when={subscriptions.length > 0}
        fallback={
          <div css={styles.emptyState}>
            <Button
              variant="secondary"
              icon={<SVGIcon name="dollar-recurring" width={24} height={24} />}
              onClick={() => alert('@TODO: will be implemented later.')}
            >
              {__('Add Subscription', 'tutor')}
            </Button>
          </div>
        }
      >
        <div css={styles.inner}>
          <For each={subscriptions}>
            {(subscription, index) => <SubscriptionItem key={index} subscription={subscription} />}
          </For>
        </div>
      </Show>
    </div>
  );
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
		width: 100%;
		margin-top: ${spacing[8]};
		overflow: hidden;
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
		min-height: 48px;
		display: flex;
		flex-direction: column;
		justify-content: center;
		gap: ${spacing[4]};

    &:not(:last-of-type) {
      border-bottom: 1px solid ${colorTokens.stroke.default};
    }
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
	`,
};
