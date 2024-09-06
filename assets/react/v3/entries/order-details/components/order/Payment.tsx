import { Box, BoxTitle } from '@Atoms/Box';
import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import { useModal } from '@Components/modals/Modal';
import { colorTokens, fontWeight, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import For from '@Controls/For';
import Show from '@Controls/Show';
import DiscountModal from '@OrderComponents/modals/DiscountModal';
import MarkAsPaidModal from '@OrderComponents/modals/MarkAsPaidModal';
import RefundModal from '@OrderComponents/modals/RefundModal';
import { useOrderContext } from '@OrderContexts/order-context';
import type { PaymentStatus } from '@OrderServices/order';
import { calculateDiscountValue, formatPrice } from '@Utils/currency';
import { styleUtils } from '@Utils/style-utils';
import { css } from '@emotion/react';
import { __, sprintf } from '@wordpress/i18n';
import { PaymentBadge } from './PaymentBadge';

function PaymentActionButton({
  status,
  onClick,
}: {
  status: PaymentStatus;
  onClick: (buttonType: 'refund' | 'mark-as-paid') => void;
}) {
  switch (status) {
    case 'paid':
    case 'partially-refunded':
    case 'failed':
      return (
        <Button variant="tertiary" size="small" isOutlined onClick={() => onClick('refund')}>
          {__('Refund', 'tutor')}
        </Button>
      );
    case 'unpaid':
      return (
        <Button variant="primary" size="small" isOutlined onClick={() => onClick('mark-as-paid')}>
          {__('Mark as paid', 'tutor')}
        </Button>
      );
    default:
      return null;
  }
}

function Payment() {
  const { showModal } = useModal();
  const { order } = useOrderContext();
  return (
    <Box bordered>
      <BoxTitle>
        <div css={styles.paymentTitle}>
          <span>{__('Payment', 'tutor')}</span>
          <PaymentBadge status={order.payment_status} />
        </div>
      </BoxTitle>
      <div css={styles.content}>
        <Box bordered css={styleUtils.boxReset}>
          <div css={styles.item({ action: 'regular' })}>
            <div>{__('Subtotal', 'tutor')}</div>
            <div>
              {order.items.length} {__('Items', 'tutor')}
            </div>
            <div>{formatPrice(order.subtotal_price)}</div>
          </div>

          <div css={styles.item({ action: 'regular' })}>
            <Show
              when={order.discount_amount}
              fallback={
                <>
                  <button
                    type="button"
                    css={styles.discountButton}
                    onClick={() =>
                      showModal({
                        component: DiscountModal,
                        props: {
                          title: __('Add discount', 'tutor'),
                          discount: {
                            amount: 0,
                            discounted_value: 0,
                            reason: '',
                            type: 'percentage',
                          },
                          total_price: order.subtotal_price,
                          order_id: order.id,
                        },
                      })
                    }
                  >
                    {__('Add discount', 'tutor')}
                  </button>
                  <div>-</div>
                  <div>-{formatPrice(0)}</div>
                </>
              }
            >
              <div css={styles.discountTitleWrapper}>
                <span>{__('Discount', 'tutor')}</span>
                <button
                  type="button"
                  css={styles.editDiscountButton}
                  onClick={() => {
                    showModal({
                      component: DiscountModal,
                      props: {
                        title: __('Add discount', 'tutor'),
                        discount: {
                          amount: order.discount_amount ?? 0,
                          discounted_value: 0,
                          reason: order.discount_reason ?? '',
                          type: order.discount_type ?? 'percentage',
                        },
                        total_price: order.subtotal_price,
                        order_id: order.id,
                      },
                    });
                  }}
                >
                  <SVGIcon name="edit" width={20} height={20} />
                </button>
              </div>
              <div>
                {order.discount_reason ?? '-'}
                <strong> ({`${order.discount_amount}${order.discount_type === 'percentage' ? '%' : ''}`})</strong>
              </div>
              <div>
                -
                {formatPrice(
                  calculateDiscountValue({
                    discount_amount: order.discount_amount,
                    discount_type: order.discount_type,
                    total: order.subtotal_price,
                  })
                )}
              </div>
            </Show>
          </div>
          <Show when={order.tax_amount}>
            {(taxAmount) => (
              <div css={styles.item({ action: 'regular' })}>
                <div>{__('Estimated tax', 'tutor')}</div>
                <div>{order.tax_rate}%</div>
                <div>{formatPrice(taxAmount)}</div>
              </div>
            )}
          </Show>

          <Show when={order.fees}>
            {(fees) => (
              <div css={styles.item({ action: 'regular' })}>
                <div>{__('Fees', 'tutor')}</div>
                <div>-</div>
                <div>{formatPrice(fees)}</div>
              </div>
            )}
          </Show>

          <div css={styles.item({ action: 'bold' })}>
            <div>{__('Total Paid', 'tutor')}</div>
            <div />
            <div>{formatPrice(order.total_price)}</div>
          </div>

          <Show when={order.refunds?.length}>
            <div css={styles.separator} />
            <Show when={order.refunds}>
              {(refunds) => (
                <For each={refunds}>
                  {(refund, index) => (
                    <div css={styles.item({ action: 'destructive' })} key={index}>
                      <div>{index === 0 ? 'Refunded' : ''}</div>
                      <div>{sprintf(__('Reason: %s', 'tutor'), refund.reason ?? '-')}</div>
                      <div>-{formatPrice(refund.amount)}</div>
                    </div>
                  )}
                </For>
              )}
            </Show>

            <div css={styles.item({ action: 'bold' })}>
              <div>{__('Net payment', 'tutor')}</div>
              <div />
              <div>{formatPrice(order.net_payment)}</div>
            </div>
          </Show>
        </Box>

        <div css={styles.markAsPaid}>
          <PaymentActionButton
            status={order.payment_status}
            onClick={(buttonType) => {
              if (buttonType === 'refund') {
                return showModal({
                  component: RefundModal,
                  props: {
                    title: __('Refund', 'tutor'),
                    available_amount: order.refunds?.length ? order.net_payment : order.total_price,
                    order_id: order.id,
                  },
                });
              }

              if (buttonType === 'mark-as-paid') {
                return showModal({
                  component: MarkAsPaidModal,
                  props: {
                    title: __('Mark as Paid', 'tutor'),
                    total: order.total_price,
                    order_id: order.id,
                  },
                });
              }
            }}
          />
        </div>
      </div>
    </Box>
  );
}

export default Payment;

const styles = {
  content: css`
    padding-top: ${spacing[12]};
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
  discountTitleWrapper: css`
    display: flex;
    align-items: center;
    gap: ${spacing[4]};
    &:hover {
      button {
        opacity: 1;
      }
    }
  `,
  editDiscountButton: css`
    ${styleUtils.resetButton};
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: 0.3s ease opacity;
    color: ${colorTokens.icon.brand};
  `,
  item: ({ action = 'regular' }: { action: 'regular' | 'bold' | 'destructive' }) => css`
    ${typography.caption()};
    display: grid;
    grid-template-columns: 120px 1fr auto;
    align-items: center;
    min-height: 32px;
    color: ${colorTokens.text.primary};
    padding-inline: ${spacing[12]};

    ${action === 'bold' &&
    css`
      font-weight: ${fontWeight.bold};
    `}

    ${action === 'destructive' &&
    css`
      & > div:first-of-type {
        color: ${colorTokens.text.error};
      }
    `}

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
