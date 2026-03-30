import { css } from '@emotion/react';
import { __, sprintf } from '@wordpress/i18n';

import { Box, BoxTitle } from '@TutorShared/atoms/Box';

import { useOrderContext } from '@OrderDetails/contexts/order-context';
import { colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import Show from '@TutorShared/controls/Show';
import { styleUtils } from '@TutorShared/utils/style-utils';

const PaymentInfo = () => {
  const {
    order: { payment_method_readable, transaction_id },
  } = useOrderContext();

  return (
    <Box bordered css={styleUtils.boxReset}>
      <BoxTitle separator>{__('Payment Method', 'tutor')}</BoxTitle>
      <div css={styles.content}>
        <div>
          {
            /* translators: %s is the payment gateway name */
            sprintf(__('Gateway: %s', 'tutor'), payment_method_readable || __('Manual', 'tutor'))
          }
        </div>
        <Show when={transaction_id}>
          <div>
            {
              /* translators: %s is the transaction ID */
              sprintf(__('Trx ID: %s', 'tutor'), transaction_id)
            }
          </div>
        </Show>
      </div>
    </Box>
  );
};

export default PaymentInfo;

const styles = {
  content: css`
    ${typography.caption()};
    color: ${colorTokens.text.subdued};
    padding: ${spacing[16]} ${spacing[20]};
  `,
};
