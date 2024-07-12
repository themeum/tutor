import { tutorConfig } from '@Config/config';
import { DateFormats } from '@Config/constants';
import { headerHeight } from '@Config/styles';
import Show from '@Controls/Show';
import { Coupon, CouponAppliesTo } from '@CouponServices/coupon';
import { useSticky } from '@Hooks/useSticky';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { format } from 'date-fns';
import { useFormContext } from 'react-hook-form';

const STICKY_GAP = 40;
const appliesToLabel: Record<CouponAppliesTo, string> = {
	all_courses_and_bundles: __('all courses', 'tutor'),
	all_bundles: __('all bundles', 'tutor'),
	specific_courses: __('specific courses', 'tutor'),
	specific_bundles: __('specific bundles', 'tutor'),
	all_courses: __('all courses', 'tutor'),
	specific_category: __('specific category', 'tutor'),
};

function CouponPreview() {
	const form = useFormContext<Coupon>();
	const { stickyRef, isSticky } = useSticky(STICKY_GAP);
	const { tutor_currency } = tutorConfig;

	const couponName = form.watch('coupon_name');
	const couponCode = form.watch('code');
	const discountType = form.watch('discount_type');
	const discountValue = form.watch('discount_value');
	const endDate = form.watch('end_date');
	const appliesTo = form.watch('applies_to');
	const isOneUserPerCustomer = form.watch('is_limit_to_one_use_per_customer');

	const discountText =
		discountType === 'amount' ? `${tutor_currency?.symbol ?? '$'}${discountValue ?? 0}` : `${discountValue ?? 0}%`;

	return (
		<div ref={stickyRef}>
			<div css={styles.previewWrapper(isSticky)}>
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
				<div css={styles.previewBottom}>
					<div>
						<h6>{__('Type', 'tutor')}</h6>
						<ul css={styles.previewList}>
							<li>{__('Amount off percentage', 'tutor')}</li>
							<li>{__('One use per customer', 'tutor')}</li>
						</ul>
					</div>
					<div>
						<h6>{__('Details', 'tutor')}</h6>
						<ul css={styles.previewList}>
							<li>{`${discountText} ${__('off', 'tutor')} ${appliesToLabel[appliesTo]}`}</li>
							<Show when={isOneUserPerCustomer}>
								<li>{__('One use per customer', 'tutor')}</li>
							</Show>
							<li>{__("Can't combine with discounts", 'tutor')}</li>
							<li>{__('Active from today', 'tutor')}</li>
						</ul>
					</div>
					<div>
						<h6>{__('Activity', 'tutor')}</h6>
						<ul css={styles.previewList}>
							<li>{__('Not active yet', 'tutor')}</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	);
}

export default CouponPreview;

const styles = {
	previewWrapper: (isSticky: boolean) =>
		isSticky &&
		css`
			position: fixed;
			top: ${headerHeight + STICKY_GAP}px;
			width: 354px;
		`,
	previewTop: css``,
	previewMiddle: css``,
	previewBottom: css``,
	saleSection: css``,
	couponName: css``,
	couponCode: css``,
	couponSubtitle: css``,
	previewList: css``,
};
