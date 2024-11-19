import { css } from '@emotion/react';
import { __, sprintf } from '@wordpress/i18n';

import { Box, BoxTitle } from '@Atoms/Box';

import Show from '@/v3/shared/controls/Show';
import { colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { useOrderContext } from '@OrderContexts/order-context';
import { styleUtils } from '@Utils/style-utils';

const PaymentInfo = () => {
  const {
    order: { payment_method_readable, transaction_id },
  } = useOrderContext();

  return (
    <Box bordered css={styleUtils.boxReset}>
      <BoxTitle separator>{__('Payment Info', 'tutor')}</BoxTitle>
      <div css={styles.content}>
        <div>{sprintf(__('Payment Method: %s'), payment_method_readable)}</div>
        <Show when={transaction_id}>
          <div>{sprintf(__('Transaction ID: %s'), transaction_id)}</div>
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
