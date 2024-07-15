import { Box, BoxTitle } from '@Atoms/Box';
import { colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import For from '@Controls/For';
import Show from '@Controls/Show';
import { css } from '@emotion/react';
import { useOrderContext } from '@OrderContexts/order-context';
import { __ } from '@wordpress/i18n';
import { OrderItem } from './OrderItem';

function Summary() {
  const { order } = useOrderContext();

  return (
    <Box css={styles.outerBox} bordered>
      <BoxTitle>{__('Order Summary', 'tutor')}</BoxTitle>
      <Box css={styles.innerBox} bordered>
        <Show when={order.courses.length > 0} fallback={<div css={styles.noCourse}>{__('No course added.', 'tutor')}</div>}>
          <For each={order.courses}>{(course) => <OrderItem key={course.id} item={course} />}</For>
        </Show>
      </Box>
    </Box>
  );
}

export default Summary;

const styles = {
  outerBox: css`
		display: flex;
		flex-direction: column;
		gap: ${spacing[12]};
	`,
  innerBox: css`
		display: flex;
		flex-direction: column;
		margin: 0 ${spacing[20]} ${spacing[16]} ${spacing[20]};
	`,
  noCourse: css`
    ${typography.small('medium')};
    min-height: 120px;
    display: flex;
    justify-content: center;
    align-items: center;
    color: ${colorTokens.text.subdued};
  `,
};
