import { Box, BoxTitle } from '@Atoms/Box';
import Button from '@Atoms/Button';
import { TutorBadge, type Variant } from '@Atoms/TutorBadge';
import { colorTokens, fontWeight, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import For from '@Controls/For';
import Show from '@Controls/Show';
import { css } from '@emotion/react';
import { useOrderContext } from '@OrderContexts/order-context';
import type { PaymentStatus } from '@OrderServices/order';
import { createPriceFormatter } from '@Utils/currency';
import { styleUtils } from '@Utils/style-utils';
import { __ } from '@wordpress/i18n';

const badgeMap: Record<PaymentStatus, { label: string; type: Variant }> = {
  paid: { label: __('Paid', 'tutor'), type: 'success' },
  failed: { label: __('Failed', 'tutor'), type: 'critical' },
  'partially-refunded': { label: __('Partially refunded', 'tutor'), type: 'secondary' },
  refunded: { label: __('Refunded', 'tutor'), type: 'critical' },
  pending: { label: __('Pending', 'tutor'), type: 'warning' },
};

function PaymentBadge({ status }: { status: PaymentStatus }) {
  return <TutorBadge variant={badgeMap[status].type}>{badgeMap[status].label}</TutorBadge>;
}

function PaymentActionButton({ status, onClick }: { status: PaymentStatus; onClick: () => void }) {
  switch (status) {
    case 'paid':
    case 'partially-refunded':
    case 'failed':
      return (
        <Button variant="tertiary" size="small" isOutlined onClick={onClick}>
          {__('Refund', 'tutor')}
        </Button>
      );
    case 'pending':
      return (
        <Button variant="primary" size="small" isOutlined onClick={onClick}>
          {__('Mark as paid', 'tutor')}
        </Button>
      );
    default:
      return null;
  }
}

function Payment() {
  const { order } = useOrderContext();
  const formatPrice = createPriceFormatter({ locale: 'en-US', currency: 'USD' });
  return (
    <Box bordered>
      <BoxTitle>
        <div css={styles.paymentTitle}>
          <span>{__('Payment', 'tutor')}</span>
          <PaymentBadge status={order.payment_status} />
        </div>
      </BoxTitle>
      <div css={styles.content}>
        <Box bordered>
          <div css={styles.item({ action: 'regular' })}>
            <div>{__('Subtotal', 'tutor')}</div>
            <div>
              {order.courses.length} {__('Items', 'tutor')}
            </div>
            <div>{formatPrice(order.subtotal_price)}</div>
          </div>

          <div css={styles.item({ action: 'regular' })}>
            <Show
              when={order.discount}
              fallback={
                <>
                  <button
                    type="button"
                    css={styles.discountButton}
                    onClick={() => alert('@TODO: Will be implemented later.')}
                  >
                    {__('Add discount', 'tutor')}
                  </button>
                  <div>-</div>
                  <div>-{formatPrice(0)}</div>
                </>
              }
            >
              {(discount) => (
                <>
                  <div>{__('Discount', 'tutor')}</div>
                  <div>
                    {discount.reason ?? '-'}
                    <strong> ({`${discount.amount}${discount.type === 'percentage' ? '%' : ''}`})</strong>
                  </div>
                  <div>-{formatPrice(discount.discounted_value)}</div>
                </>
              )}
            </Show>
          </div>
          <Show when={order.tax}>
            {(tax) => (
              <div css={styles.item({ action: 'regular' })}>
                <div>{__('Estimated tax', 'tutor')}</div>
                <div>{tax.rate}%</div>
                <div>{formatPrice(tax.taxable_amount)}</div>
              </div>
            )}
          </Show>
          <div css={styles.item({ action: 'bold' })}>
            <div>{__('Total Paid', 'tutor')}</div>
            <div />
            <div>{formatPrice(order.total_price)}</div>
          </div>

          <Show when={order.refunds.length > 0}>
            <div css={styles.separator} />

            <Show when={order.refunds}>
              {(refunds) => (
                <For each={refunds}>
                  {(refund, index) => (
                    <div css={styles.item({ action: 'destructive' })} key={index}>
                      <div>{index === 0 ? 'Refunded' : ''}</div>
                      <div>
                        {__('Reason: ')}
                        {refund.reason ?? '-'}
                      </div>
                      <div>-{formatPrice(refund.amount)}</div>
                    </div>
                  )}
                </For>
              )}
            </Show>

            <div css={styles.item({ action: 'bold' })}>
              <div>{__('Net payment', 'tutor')}</div>
              <div />
              <div>{formatPrice(order.net_total_price)}</div>
            </div>
          </Show>
        </Box>

        <div css={styles.markAsPaid}>
          <PaymentActionButton status={order.payment_status} onClick={() => alert('@TODO: will be handled later.')} />
        </div>
      </div>
    </Box>
  );
}

export default Payment;

const styles = {
  content: css`
		padding: ${spacing[12]} ${spacing[20]} ${spacing[16]} ${spacing[20]};
	`,
  paymentTitle: css`
		display: flex;
		gap: ${spacing[4]};
		align-items: center;
	`,
  markAsPaid: css`
		margin-top: ${spacing[12]};
		text-align: right;
	`,
  item: ({ action = 'regular' }: { action: 'regular' | 'bold' | 'destructive' }) => css`
		${typography.caption()};
		display: grid;
		grid-template-columns: 120px 1fr auto;
		align-items: center;
		min-height: 32px;
		color: ${colorTokens.text.primary};
		padding-inline: ${spacing[12]};

		${
      action === 'bold' &&
      css`
			font-weight: ${fontWeight.bold};
		`
    }

		${
      action === 'destructive' &&
      css`
			& > div:first-of-type {
				color: ${colorTokens.text.error};
			}
		`
    }

		& > div:nth-of-type(2) {
			color: ${colorTokens.text.subdued};
		}

		:first-of-type {
			padding-top: ${spacing[4]};
		}

		:last-of-type {
			padding-bottom: ${spacing[4]};
		}
	`,
  separator: css`
		height: 1px;
		width: 100%;
		background-color: ${colorTokens.stroke.divider};
		margin-block: ${spacing[12]};
	`,
  discountButton: css`
		${styleUtils.resetButton};
		${typography.small('medium')};
		color: ${colorTokens.brand.blue};
	`,
};
