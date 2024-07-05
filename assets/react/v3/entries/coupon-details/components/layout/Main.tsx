import Container from '@Components/Container';
import { colorTokens, spacing } from '@Config/styles';
import Activities from '@CouponComponents/coupon/Activities';
import Notes from '@CouponComponents/coupon/Notes';
import Payment from '@CouponComponents/coupon/Payment';
import Student from '@CouponComponents/coupon/Student';
import Summary from '@CouponComponents/coupon/Summary';
import { CouponProvider } from '@CouponContexts/coupon-context';
import { css } from '@emotion/react';
import Topbar, { TOPBAR_HEIGHT } from './Topbar';

function Main() {
	return (
		<div css={styles.wrapper}>
			<CouponProvider couponId={1}>
				<Topbar />
				<Container>
					<div css={styles.content}>
						<div css={styles.left}>
							<Summary />
							<Payment />
							<Activities />
						</div>
						<div css={styles.right}>
							<Student />
							<Notes />
						</div>
					</div>
				</Container>
			</CouponProvider>
		</div>
	);
}

export default Main;

const styles = {
	wrapper: css`
		background-color: ${colorTokens.background.default};
	`,

	content: css`
		min-height: calc(100vh - ${TOPBAR_HEIGHT}px);
		width: 100%;
		display: flex;
		gap: ${spacing[24]};
		margin-top: ${spacing[32]};
	`,
	left: css`
		max-width: 736px;
		width: 100%;
		flex-shrink: 0;
		display: flex;
		flex-direction: column;
		gap: ${spacing[16]};
	`,
	right: css`
		width: 100%;
		display: flex;
		flex-direction: column;
		gap: ${spacing[24]};
	`,
};
