import { tutorConfig } from '@Config/config';
import { DateFormats } from '@Config/constants';
import { Coupon } from '@CouponServices/coupon';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { format } from 'date-fns';
import { useFormContext } from 'react-hook-form';

function CouponPreview() {
	const form = useFormContext<Coupon>();
	const { tutor_currency } = tutorConfig;

	const couponName = form.watch('coupon_name');
	const couponCode = form.watch('code');
	const discountType = form.watch('discount_type');
	const discountValue = form.watch('discount_value');
	const endDate = form.watch('end_date');

	const discountText =
		discountType === 'amount' ? `${tutor_currency?.symbol ?? '$'}${discountValue ?? 0}` : `${discountValue ?? 0}%`;

	return (
		<div css={styles.previewWrapper}>
			<div css={styles.previewTop}>
				<div css={styles.saleSection}>
					<div css={styles.couponName}>{couponName}</div>
					<div>{}</div>
				</div>
				<h1 css={styles.couponCode}>{couponCode}</h1>
				<p css={styles.couponSubtitle}>
					{__('Valid until', 'tutor') + ' ' + format(new Date(endDate), DateFormats.validityDate)}
				</p>
			</div>
			<div css={styles.previewMiddle}></div>
			<div css={styles.previewBottom}></div>
		</div>
	);
}

export default CouponPreview;

const styles = {
	previewWrapper: css``,
	previewTop: css``,
	previewMiddle: css``,
	previewBottom: css``,
	saleSection: css``,
	couponName: css``,
	couponCode: css``,
	couponSubtitle: css``,
};
