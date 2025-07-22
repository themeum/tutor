import { css } from '@emotion/react';
import { __, sprintf } from '@wordpress/i18n';

import { Box, BoxTitle } from '@TutorShared/atoms/Box';
import Button from '@TutorShared/atoms/Button';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import { useModal } from '@TutorShared/components/modals/Modal';

import DiscountModal from '@OrderDetails/components/modals/DiscountModal';
import MarkAsPaidModal from '@OrderDetails/components/modals/MarkAsPaidModal';
import RefundModal from '@OrderDetails/components/modals/RefundModal';
import { useOrderContext } from '@OrderDetails/contexts/order-context';
import type { Order } from '@OrderDetails/services/order';

import { colorTokens, fontWeight, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import For from '@TutorShared/controls/For';
import Show from '@TutorShared/controls/Show';
import { calculateDiscountValue, formatPrice } from '@TutorShared/utils/currency';
import { styleUtils } from '@TutorShared/utils/style-utils';

import { PaymentBadge } from './PaymentBadge';

function PaymentActionButton({
  order,
  onClick,
}: {
  order: Order;
  onClick: (buttonType: 'refund' | 'mark-as-paid') => void;
}) {
  const { payment_status, net_payment } = order || {};

  switch (payment_status) {
    case 'paid':
    case 'partially-refunded':
    case 'failed': {
      if (net_payment <= 0) {
        return null;
      }

      return (
        <Button variant="tertiary" size="small" isOutlined onClick={() => onClick('refund')}>
          {__('Refund', 'tutor')}
        </Button>
      );
    }
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
          {order.subscription_fees?.map((item, idx) => (
            <div key={idx} css={styles.item({ action: 'regular' })}>
              <div>{item.title}</div>
              <div>-</div>
              <div>{formatPrice(Number(item.value))}</div>
            </div>
          ))}

          <div css={styles.item({ action: 'regular' })}>
            <div>{__('Subtotal', 'tutor')}</div>
            <div>
              {order.items.length} {__('Item(s)', 'tutor')}
            </div>
            <div>{formatPrice(order.subtotal_price)}</div>
          </div>

          <Show when={order.coupon_amount}>
            {(couponAmount) => (
              <div css={styles.item({ action: 'regular' })}>
                <div>{__('Coupon', 'tutor')}</div>
                <div>-</div>
                <div>-{formatPrice(couponAmount)}</div>
              </div>
            )}
          </Show>

          <div css={styles.item({ action: 'regular' })}>
            <Show
              when={order.discount_amount}
              fallback={
                <>
                  <Show when={order.payment_status === 'unpaid'} fallback={__('Discount', 'tutor')}>
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
                  </Show>
                  <div>-</div>
                  <div>-{formatPrice(0)}</div>
                </>
              }
            >
              <div css={styles.discountTitleWrapper}>
                <span>{__('Discount', 'tutor')}</span>
                <Show when={order.payment_status === 'unpaid'}>
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
                </Show>
              </div>
              <div>
                {order.discount_reason ?? '-'}
                <strong>
                  {' '}
                  (
                  {`${
                    order.discount_type === 'percentage'
                      ? `${order.discount_amount}%`
                      : formatPrice(order.discount_amount)
                  }`}
                  )
                </strong>
              </div>
              <div>
                -
                {formatPrice(
                  calculateDiscountValue({
                    discount_amount: order.discount_amount,
                    discount_type: order.discount_type,
                    total: order.subtotal_price,
                  }),
                )}
              </div>
            </Show>
          </div>
          <Show when={order.tax_amount && order.tax_type === 'exclusive'}>
            <div css={styles.item({ action: 'regular' })}>
              <div>{__('Estimated tax', 'tutor')}</div>
              <div>{order.tax_rate}%</div>
              <div>{order.tax_amount ? formatPrice(order.tax_amount) : ''}</div>
            </div>
          </Show>

          {/* <Show when={order.fees}>
            {(fees) => (
              <div css={styles.item({ action: 'regular' })}>
                <div>{__('Fees', 'tutor')}</div>
                <div>-</div>
                <div>{formatPrice(fees)}</div>
              </div>
            )}
          </Show> */}

          <div css={styles.item({ action: 'bold' })}>
            <div>{__('Total Paid', 'tutor')}</div>
            <div css={styles.includeTax}>
              <Show when={order.tax_type === 'inclusive'}>
                {
                  /* translators: %s is the tax amount formatted as a price */
                  sprintf(__('Incl. tax %s', 'tutor'), order.tax_amount ? formatPrice(order.tax_amount) : 0)
                }
              </Show>
            </div>
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
                      <div>
                        {
                          /* translators: %s is the refund reason or '-' if none */
                          sprintf(__('Reason: %s', 'tutor'), refund.reason ?? '-')
                        }
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
              <div>{formatPrice(order.net_payment)}</div>
            </div>
          </Show>
        </Box>

        <div css={styles.markAsPaid}>
          <PaymentActionButton
            order={order}
            onClick={(buttonType) => {
              if (buttonType === 'refund') {
                return showModal({
                  component: RefundModal,
                  props: {
                    title: __('Refund', 'tutor'),
                    available_amount: order.refunds?.length ? order.net_payment : order.total_price,
                    order_id: order.id,
                    order_type: order.order_type,
                    payment_method: order.payment_method,
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
  `,
  editDiscountButton: css`
    ${styleUtils.resetButton};
    display: flex;
    align-items: center;
    justify-content: center;
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
  includeTax: css`
    ${typography.caption()};
    color: ${colorTokens.text.subdued};
  `,
};
