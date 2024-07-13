import Container from '@Components/Container';
import { colorTokens, spacing } from '@Config/styles';

import CouponDiscount from '@CouponComponents/coupon/CouponDiscount';
import CouponInfo from '@CouponComponents/coupon/CouponInfo';
import CouponUsageLimitation from '@CouponComponents/coupon/CouponLimitation';
import CouponPreview from '@CouponComponents/coupon/CouponPreview';
import CouponValidity from '@CouponComponents/coupon/CouponValidity';
import PurchaseRequirements from '@CouponComponents/coupon/PurchaseRequirements';
import { Coupon, mockCouponData } from '@CouponServices/coupon';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
import { css } from '@emotion/react';
import { FormProvider } from 'react-hook-form';
import Topbar, { TOPBAR_HEIGHT } from './Topbar';

function Main() {
	const form = useFormWithGlobalError<Coupon>({ defaultValues: mockCouponData });

	// @TODO: populate form data when in edit view

	return (
		<div css={styles.wrapper}>
			<FormProvider {...form}>
				<Topbar />
				<Container>
					<div css={styles.content}>
						<div css={styles.left}>
							<CouponInfo />
							<CouponDiscount />
							<CouponUsageLimitation />
							<PurchaseRequirements />
							<CouponValidity />
						</div>
						<div css={styles.right}>
							<CouponPreview />
						</div>
					</div>
				</Container>
			</FormProvider>
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
		gap: ${spacing[36]};
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
