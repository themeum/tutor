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
      <BoxTitle separator>{__('Payment Method', 'tutor')}</BoxTitle>
      <div css={styles.content}>
        <div>{sprintf(__('Name: %s', 'tutor'), payment_method_readable || __('Manual', 'tutor'))}</div>
        <Show when={transaction_id}>
          <div>{sprintf(__('Trx ID: %s', 'tutor'), transaction_id)}</div>
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
