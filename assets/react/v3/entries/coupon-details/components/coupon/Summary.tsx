import { Box, BoxTitle } from '@Atoms/Box';
import { spacing } from '@Config/styles';
import For from '@Controls/For';
import { useCouponContext } from '@CouponContexts/coupon-context';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { CouponItem } from './CouponItem';

function Summary() {
	const { coupon } = useCouponContext();

	return (
		<Box css={styles.outerBox} bordered>
			<BoxTitle>{__('Coupon Summary', 'tutor')}</BoxTitle>
			<Box css={styles.innerBox} bordered>
				<For each={coupon.courses}>{(course) => <CouponItem key={course.id} item={course} />}</For>
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
};
