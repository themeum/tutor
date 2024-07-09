import { Box, BoxTitle } from '@Atoms/Box';
import { spacing } from '@Config/styles';
import For from '@Controls/For';
import { useOrderContext } from '@OrderContexts/order-context';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { OrderItem } from './OrderItem';

function Summary() {
	const { order } = useOrderContext();

	return (
		<Box css={styles.outerBox} bordered>
			<BoxTitle>{__('Order Summary', 'tutor')}</BoxTitle>
			<Box css={styles.innerBox} bordered>
				<For each={order.courses}>{(course) => <OrderItem key={course.id} item={course} />}</For>
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
		padding: 0;
	`,
};
