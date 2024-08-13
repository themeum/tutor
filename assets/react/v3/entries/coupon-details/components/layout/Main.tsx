import Container from '@Components/Container';
import { colorTokens, spacing } from '@Config/styles';

import CouponDiscount from '@CouponComponents/coupon/CouponDiscount';
import CouponInfo from '@CouponComponents/coupon/CouponInfo';
import CouponUsageLimitation from '@CouponComponents/coupon/CouponLimitation';
import CouponPreview from '@CouponComponents/coupon/CouponPreview';
import CouponValidity from '@CouponComponents/coupon/CouponValidity';
import PurchaseRequirements from '@CouponComponents/coupon/PurchaseRequirements';
import { Coupon, couponInitialValue, GetCouponResponse, useCouponDetailsQuery } from '@CouponServices/coupon';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
import { css } from '@emotion/react';
import { FormProvider } from 'react-hook-form';
import Topbar, { TOPBAR_HEIGHT } from './Topbar';
import { useEffect } from 'react';
import { format } from 'date-fns';
import { DateFormats } from '@Config/constants';

function Main() {
	const params = new URLSearchParams(window.location.search);
  	const courseId = params.get('coupon_id');
	const form = useFormWithGlobalError<Coupon>({ defaultValues: couponInitialValue });

	const couponDetailsQuery = useCouponDetailsQuery(Number(courseId));

	useEffect(() => {
		const couponData = couponDetailsQuery.data?.data;
		if (couponData) {
			form.reset({
				id: couponData.id,
				coupon_status: couponData.coupon_status,
				coupon_type: couponData.coupon_type,
				coupon_title: couponData.coupon_title,
				coupon_code: couponData.coupon_code,
				user_name: "", // @TODO:
				discount_type: couponData.discount_type,
				discount_amount: couponData.discount_amount,
				applies_to: couponData.applies_to,
				courses: couponData.applies_to === 'specific_courses' ? couponData.applies_to_items : [],
				bundles: couponData.applies_to === 'specific_bundles' ? couponData.applies_to_items : [],
				categories: couponData.applies_to === 'specific_category' ? couponData.applies_to_items : [],
				usage_limit_status: !!couponData.total_usage_limit,
				total_usage_limit: couponData.total_usage_limit,
				per_user_limit_status: !!couponData.per_user_usage_limit,
				per_user_usage_limit: couponData.per_user_usage_limit,
				purchase_requirement: couponData.purchase_requirement,
				purchase_requirement_value: couponData.purchase_requirement_value,
				start_date: format(new Date(couponData.start_date_gmt), DateFormats.yearMonthDay),
				start_time: format(new Date(couponData.start_date_gmt), DateFormats.hoursMinutes),
				...(couponData.expire_date_gmt && {
					is_end_enabled: !!couponData.expire_date_gmt,
					end_date: format(new Date(couponData.expire_date_gmt), DateFormats.yearMonthDay),
					end_time: format(new Date(couponData.expire_date_gmt), DateFormats.hoursMinutes),
				}),
			});
		}
	}, [couponDetailsQuery.data]);

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
