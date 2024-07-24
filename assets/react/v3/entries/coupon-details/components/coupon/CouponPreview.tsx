import { tutorConfig } from '@Config/config';
import { DateFormats } from '@Config/constants';
import { borderRadius, colorTokens, headerHeight, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import { Coupon, CouponAppliesTo } from '@CouponServices/coupon';
import { useSticky } from '@Hooks/useSticky';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { format, isToday, isTomorrow } from 'date-fns';
import { useFormContext } from 'react-hook-form';

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
	const { stickyRef, isSticky } = useSticky();
	const { tutor_currency } = tutorConfig;

	const couponName = form.watch('coupon_name');
	const couponCode = form.watch('code');
	const discountType = form.watch('discount_type');
	const discountValue = form.watch('discount_value');
	const startDate = form.watch('start_date');
	const startTime = form.watch('start_time');
	const endDate = form.watch('end_date');
	const appliesTo = form.watch('applies_to');
	const isOneUserPerCustomer = form.watch('is_one_use_per_user');
	const redeemedCouponCount = form.watch('redeemed_coupons_count');

	const startDateTime = `${startDate} ${startTime}`;
	const activeFromSuffix = `${
		isToday(new Date(startDateTime))
			? __('today', 'tutor')
			: isTomorrow(new Date(startDateTime))
			? __('tomorrow', 'tutor')
			: format(new Date(startDateTime), DateFormats.activityDate)
	}`;

	const discountText =
		discountType === 'amount' ? `${tutor_currency?.symbol ?? '$'}${discountValue ?? 0}` : `${discountValue ?? 0}%`;
	const totalUsedText = `${__('Total', 'tutor')} ${redeemedCouponCount} ${__('times used', 'tutor')}`;
	const activeFromText = `${__('Active from ', 'tutor')} ${activeFromSuffix}`;

	return (
		<div ref={stickyRef}>
			<div css={styles.previewWrapper(isSticky)}>
				<div css={styles.previewTop}>
					<div css={styles.saleSection}>
						<div css={styles.couponName}>{couponName}</div>
						<div css={styles.discountText}>{`${discountText} ${__('OFF', 'tutor')}`}</div>
					</div>
					<h1 css={styles.couponCode}>{couponCode}</h1>
					<p css={styles.couponSubtitle}>
						{__('Valid until', 'tutor') + ' ' + format(new Date(endDate), DateFormats.validityDate)}
					</p>
				</div>
				<div css={styles.previewMiddle}>
					<span css={styles.leftCircle} />
					<span css={styles.rightCircle} />
					<svg width="280" height="2" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path
							d="M1 1h278"
							stroke={colorTokens.stroke.border}
							strokeWidth="2"
							strokeLinecap="round"
							strokeLinejoin="round"
							strokeDasharray="7 7"
						/>
					</svg>
				</div>
				<div css={styles.previewBottom}>
					<div>
						<h6 css={styles.previewListTitle}>{__('Type', 'tutor')}</h6>
						<ul css={styles.previewList} data-preview-list>
							<li>{__('Amount off percentage', 'tutor')}</li>
						</ul>
					</div>
					<div>
						<h6 css={styles.previewListTitle}>{__('Details', 'tutor')}</h6>
						<ul css={styles.previewList} data-preview-list>
							<li>{`${discountText} ${__('off', 'tutor')} ${appliesToLabel[appliesTo]}`}</li>
							<Show when={isOneUserPerCustomer}>
								<li>{__('One use per customer', 'tutor')}</li>
							</Show>
							<li>{activeFromText}</li>
						</ul>
					</div>
					<div>
						<h6 css={styles.previewListTitle}>{__('Activity', 'tutor')}</h6>
						<ul css={styles.previewList} data-preview-list>
							<li>{__('Not active yet', 'tutor')}</li>
							<li>{totalUsedText}</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	);
}

export default CouponPreview;

const styles = {
	previewWrapper: (isSticky: boolean) => css`
		display: flex;
		flex-direction: column;
		gap: ${spacing[20]};
		background-color: ${colorTokens.background.white};
		padding: ${spacing[20]} ${spacing[32]} ${spacing[64]};
		box-shadow: 0px 2px 3px 0px rgba(0, 0, 0, 0.25);
		border-radius: ${borderRadius[6]};

		${isSticky &&
		css`
			position: fixed;
			top: ${headerHeight}px;
			width: 342px;
		`}
	`,
	previewTop: css`
		display: flex;
		flex-direction: column;
		gap: ${spacing[6]};
		align-items: center;
	`,
	previewMiddle: css`
		position: relative;
		margin-block: ${spacing[16]};
		display: flex;
	`,
	leftCircle: css`
		position: absolute;
		left: -${spacing[48]};
		top: 50%;
		transform: translate(0, -50%);
		width: 32px;
		height: 32px;
		border-radius: ${borderRadius.circle};
		background-color: ${colorTokens.background.default};
		box-shadow: inset 0px 2px 3px 0px rgba(0, 0, 0, 0.25);

		&::before {
			content: '';
			position: absolute;
			width: 50%;
			height: 100%;
			background: ${colorTokens.background.default};
		}
	`,
	rightCircle: css`
		position: absolute;
		right: -${spacing[48]};
		top: 50%;
		transform: translate(0, -50%);
		width: 32px;
		height: 32px;
		border-radius: ${borderRadius.circle};
		background-color: ${colorTokens.background.default};
		box-shadow: inset 0px 2px 3px 0px rgba(0, 0, 0, 0.25);

		&::before {
			content: '';
			position: absolute;
			width: 50%;
			height: 100%;
			background: ${colorTokens.background.default};
			right: 0;
		}
	`,
	previewBottom: css`
		display: flex;
		flex-direction: column;
		gap: ${spacing[32]};
	`,
	saleSection: css`
		display: flex;
		justify-content: space-between;
		align-items: center;
		width: 100%;
	`,
	couponName: css`
		${typography.heading6('medium')};
		color: ${colorTokens.text.primary};
	`,
	discountText: css`
		${typography.body('medium')};
		color: ${colorTokens.text.warning};
	`,
	couponCode: css`
		${typography.heading3('medium')};
		color: ${colorTokens.text.brand};
		margin-top: ${spacing[24]};
	`,
	couponSubtitle: css`
		${typography.small()};
		color: ${colorTokens.text.hints};
	`,
	previewListTitle: css`
		${typography.caption('medium')};
		color: ${colorTokens.text.primary};
	`,
	previewList: css`
		&[data-preview-list] {
			${typography.caption()};
			color: ${colorTokens.text.title};
			list-style: disc;
			padding-left: ${spacing[24]};
		}
	`,
};