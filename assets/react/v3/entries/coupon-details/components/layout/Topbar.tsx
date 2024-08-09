import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import { TutorBadge } from '@Atoms/TutorBadge';
import Container from '@Components/Container';
import { DateFormats } from '@Config/constants';
import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import { Coupon, useCreateCouponMutation, useUpdateCouponMutation } from '@CouponServices/coupon';
import DropdownButton from '@Molecules/DropdownButton';
import { styleUtils } from '@Utils/style-utils';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { format } from 'date-fns';
import { useFormContext } from 'react-hook-form';

export const TOPBAR_HEIGHT = 96;

function Topbar() {
	const form = useFormContext<Coupon>();
	const coupon = form.getValues();
	const createCouponMutation = useCreateCouponMutation();
	const updateCouponMutation = useUpdateCouponMutation();

	async function handleSubmit(data: Coupon) {
		const payload = {
			...(data.id && {
				id: data.id
			}),
			coupon_status: data.coupon_status,
			coupon_type: data.coupon_type,
			coupon_code: data.coupon_code,
			coupon_title: data.coupon_title,
			discount_type: data.discount_type,
			discount_amount: data.discount_amount,
			applies_to: data.applies_to,
			...(data.total_usage_limit && {
				total_usage_limit: data.total_usage_limit
			}),
			...(data.per_user_usage_limit && {
				per_user_usage_limit: data.per_user_usage_limit
			}),
			...(data.purchase_requirement && {
				purchase_requirement: data.purchase_requirement
			}),
			...(data.purchase_requirement_value && {
				purchase_requirement_value: data.purchase_requirement_value
			}),
			start_date_gmt: format(
				new Date(`${data.start_date} ${data.start_time}`), 
				DateFormats.yearMonthDayHourMinuteSecond
			),
			...(data.end_date && {
				expire_date_gmt: format(
					new Date(`${data.end_date} ${data.end_time}`), 
					DateFormats.yearMonthDayHourMinuteSecond
				),
			})
		}
		createCouponMutation.mutate(payload);
	}

	return (
		<div css={styles.wrapper}>
			<Container>
				<div css={styles.innerWrapper}>
					<div css={styles.left}>
						<button type="button" css={styles.backButton} onClick={() => alert('@TODO: will be implemented later.')}>
							<SVGIcon name="arrowLeft" width={26} height={26} />
						</button>
						<div>
							<div css={styles.headerContent}>
								<h4 css={typography.heading5('medium')}>{__('Create coupon', 'tutor')}</h4>
								<TutorBadge variant="success">Active</TutorBadge>
							</div>
							{/* <Show
								when={coupon.updated_at}
								fallback={
									<p css={styles.updateMessage}>
										{__('Updated by ')} {coupon.user_name} {__(' at ', 'tutor')}
										{format(new Date(coupon.created_at), DateFormats.activityDate)}
									</p>
								}
							>
								{(updatedDate) => (
									<p css={styles.updateMessage}>
										{__('Update by ')} {coupon.user_name} {__(' at ', 'tutor')}
										{format(new Date(updatedDate), DateFormats.activityDate)}
									</p>
								)}
							</Show> */}
						</div>
					</div>
					<div css={styles.right}>
						<Button variant="tertiary" onClick={() => alert('@TODO: will be implemented later.')}>
							Cancel
						</Button>
						<DropdownButton
							text={__('Save', 'tutor')}
							variant="primary"
							loading={createCouponMutation.isPending || updateCouponMutation.isPending}
							onClick={form.handleSubmit(handleSubmit)}
							dropdownMaxWidth="144px"
						>
							<DropdownButton.Item text="Save as Draft" onClick={() => alert('@TODO: will be implemented later.')} />
							<DropdownButton.Item
								text="Move to trash"
								onClick={() => alert('@TODO: will be implemented later.')}
								isDanger
							/>
						</DropdownButton>
					</div>
				</div>
			</Container>
		</div>
	);
}

export default Topbar;

const styles = {
	wrapper: css`
		height: ${TOPBAR_HEIGHT}px;
		background: ${colorTokens.background.white};
	`,
	innerWrapper: css`
		display: flex;
		align-items: center;
		justify-content: space-between;
		height: 100%;
	`,
	headerContent: css`
		display: flex;
		align-items: center;
		gap: ${spacing[16]};
	`,
	left: css`
		display: flex;
		gap: ${spacing[16]};
	`,
	right: css`
		display: flex;
		gap: ${spacing[12]};
	`,
	updateMessage: css`
		${typography.body()};
		color: ${colorTokens.text.subdued};
	`,
	backButton: css`
		${styleUtils.resetButton};
		background-color: transparent;
		width: 32px;
		height: 32px;
		display: flex;
		align-items: center;
		justify-content: center;
		border: 1px solid ${colorTokens.border.neutral};
		border-radius: ${borderRadius[4]};
		color: ${colorTokens.icon.default};
		transition: color 0.3s ease-in-out;

		:hover {
			color: ${colorTokens.icon.hover};
		}
	`,
};
