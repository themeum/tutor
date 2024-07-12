import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import Container from '@Components/Container';
import { useModal } from '@Components/modals/Modal';
import { DateFormats } from '@Config/constants';
import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import { css } from '@emotion/react';
import CancelOrderModal from '@OrderComponents/modals/CancelOrderModal';
import { OrderBadge } from '@OrderComponents/order/OrderBadge';
import { PaymentBadge } from '@OrderComponents/order/PaymentBadge';
import { useOrderContext } from '@OrderContexts/order-context';
import { styleUtils } from '@Utils/style-utils';
import { __ } from '@wordpress/i18n';
import { format } from 'date-fns';

export const TOPBAR_HEIGHT = 96;

function Topbar() {
  const { order } = useOrderContext();
  
  const {showModal} = useModal();
  return (
    <div css={styles.wrapper}>
      <Container>
        <div css={styles.innerWrapper}>
          <div css={styles.left}>
            <button type="button" css={styles.backButton} onClick={() => alert('@TODO: will be implemented later.')}>
              <SVGIcon name="arrowLeft" width={26} height={26} />
            </button>
            <div>
              <div css={styles.headerContent}>
                <h4 css={typography.heading5('medium')}>
                  {__('Order', 'tutor')} #{order.id}
                </h4>
                <Show when={order.payment_status}>
                  <PaymentBadge status={order.payment_status} />
                </Show>
                <Show when={order.order_status}>
                  <OrderBadge status={order.order_status} />
                </Show>
              </div>
              <Show
                when={order.updated_at_gmt}
                fallback={
                  <p css={styles.updateMessage}>
                    {__('Created by ')} {order.created_by} {__(' at ', 'tutor')}
                    {format(new Date(order.created_at_gmt), DateFormats.activityDate)}
                  </p>
                }
              >
                {(date) => (
                  <p css={styles.updateMessage}>
                    {__('Update by ')} {order.updated_by} {__(' at ', 'tutor')}
                    {format(new Date(date), DateFormats.activityDate)}
                  </p>
                )}
              </Show>
            </div>
          </div>
          <Button variant="tertiary" onClick={() => {
            showModal({
              component: CancelOrderModal,
              props: {
                total: 30,
                title: __('Cancel order #', 'tutor') + order.id
              }
            })
          }}>
            {__('Cancel Order', 'tutor')}
          </Button>
        </div>
      </Container>
    </div>
  );
}

export default Topbar;

const styles = {
  wrapper: css`
		height: ${TOPBAR_HEIGHT}px;
		background: ${colorTokens.background.white};
	`,
  innerWrapper: css`
		display: flex;
		align-items: center;
		justify-content: space-between;
		height: 100%;
	`,
  headerContent: css`
		display: flex;
		align-items: center;
		gap: ${spacing[16]};
	`,
  left: css`
		display: flex;
		gap: ${spacing[16]};
	`,
  updateMessage: css`
		${typography.body()};
		color: ${colorTokens.text.subdued};
	`,
  backButton: css`
		${styleUtils.resetButton};
		background-color: transparent;
		width: 32px;
		height: 32px;
		display: flex;
		align-items: center;
		justify-content: center;
		border: 1px solid ${colorTokens.border.neutral};
		border-radius: ${borderRadius[4]};
		color: ${colorTokens.icon.default};
		transition: color .3s ease-in-out;

		:hover {
			color: ${colorTokens.icon.hover};
		}
	`,
};
