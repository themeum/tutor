import CancelOrderModal from '@OrderComponents/modals/CancelOrderModal';
import { OrderBadge } from '@OrderComponents/order/OrderBadge';
import { PaymentBadge } from '@OrderComponents/order/PaymentBadge';
import { useOrderContext } from '@OrderContexts/order-context';
import Button from '@TutorShared/atoms/Button';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import Container from '@TutorShared/components/Container';
import { useModal } from '@TutorShared/components/modals/Modal';
import { tutorConfig } from '@TutorShared/config/config';
import { Breakpoint, colorTokens, spacing, zIndex } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import Show from '@TutorShared/controls/Show';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { css } from '@emotion/react';
import { __, sprintf } from '@wordpress/i18n';

export const TOPBAR_HEIGHT = 96;

function Topbar() {
  const { order } = useOrderContext();

  const { showModal } = useModal();

  function handleGoBack() {
    const urlParams = new URLSearchParams(window.location.search);
    const redirectUrl = urlParams.get('redirect_url');
    if (redirectUrl) {
      window.location.href = decodeURIComponent(redirectUrl);
    } else {
      window.location.href = `${tutorConfig.site_url}/wp-admin/admin.php?page=tutor_orders`;
    }
  }

  return (
    <div css={styles.wrapper}>
      <Container>
        <div css={styles.innerWrapper}>
          <div css={styles.left}>
            <button type="button" css={styleUtils.backButton} onClick={handleGoBack}>
              <SVGIcon name="arrowLeft" width={26} height={26} />
            </button>
            <div>
              <div css={styles.headerContent}>
                <h4 css={styles.headerTitle}>{sprintf(__('Order #%s', 'tutor'), order.id)}</h4>
                <Show when={order.payment_status}>
                  <PaymentBadge status={order.payment_status} />
                </Show>
                <Show when={order.order_status}>
                  <OrderBadge status={order.order_status} />
                </Show>
              </div>
              <Show
                when={order.updated_at_readable}
                fallback={
                  <p css={styles.updateMessage}>
                    {sprintf(__('Created by %s at %s', 'tutor'), order.created_by, order.created_at_readable)}
                  </p>
                }
              >
                {(date) => (
                  <p css={styles.updateMessage}>
                    {sprintf(__('Updated by %s at %s', 'tutor'), order.updated_by, date)}
                  </p>
                )}
              </Show>
            </div>
          </div>

          <Show when={order.order_type === 'single_order' && order.order_status !== 'cancelled'}>
            <Button
              variant="tertiary"
              onClick={() => {
                showModal({
                  component: CancelOrderModal,
                  props: {
                    title: sprintf(__('Cancel order #%s', 'tutor'), order.id),
                    order_id: order.id,
                  },
                });
              }}
              buttonCss={css`
                flex-shrink: 0;
              `}
            >
              {__('Cancel Order', 'tutor')}
            </Button>
          </Show>
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
    border: 1px solid ${colorTokens.stroke.divider};
    position: sticky;
    top: 32px;
    z-index: ${zIndex.positive};

    ${Breakpoint.mobile} {
      position: unset;
      padding-inline: ${spacing[8]};
    }

    ${Breakpoint.smallMobile} {
      height: auto;
    }
  `,
  innerWrapper: css`
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 100%;
    padding-inline: ${spacing[8]};

    ${Breakpoint.smallMobile} {
      padding-block: ${spacing[12]};
      flex-direction: column;
      gap: ${spacing[8]};
    }
  `,
  headerContent: css`
    display: flex;
    align-items: center;
    gap: ${spacing[16]};
  `,
  headerTitle: css`
    ${typography.heading5('medium')};

    ${Breakpoint.smallMobile} {
      ${typography.heading6('medium')};
    }
  `,
  left: css`
    display: flex;
    gap: ${spacing[16]};
    width: 100%;
  `,
  updateMessage: css`
    ${typography.body()};
    color: ${colorTokens.text.subdued};
  `,
};
