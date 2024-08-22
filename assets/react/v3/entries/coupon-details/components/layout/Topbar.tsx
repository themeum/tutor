import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import { TutorBadge } from '@Atoms/TutorBadge';
import Container from '@Components/Container';
import { tutorConfig } from '@Config/config';
import { DateFormats } from '@Config/constants';
import { borderRadius, colorTokens, spacing, zIndex } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import { convertFormDataToPayload, Coupon, useCreateCouponMutation, useUpdateCouponMutation } from '@CouponServices/coupon';
import { makeFirstCharacterUpperCase } from '@Utils/util';
import { styleUtils } from '@Utils/style-utils';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { format } from 'date-fns';
import { useFormContext } from 'react-hook-form';

export const TOPBAR_HEIGHT = 96;

const statusVariant = {
	active: 'success',
	inactive: 'secondary',
	trash: 'critical',
} as const;

function Topbar() {
	const form = useFormContext<Coupon>();
	const coupon = form.getValues();
	const createCouponMutation = useCreateCouponMutation();
	const updateCouponMutation = useUpdateCouponMutation();

	async function handleSubmit(data: Coupon) {
		const payload = convertFormDataToPayload(data);
		if (data.id) {
			updateCouponMutation.mutate(payload);
		} else {
			createCouponMutation.mutate(payload);
		}
	}

	function handleGoBack() {
		window.location.href = `${tutorConfig.home_url}/wp-admin/admin.php?page=tutor_coupons`
	}

	return (
		<div css={styles.wrapper}>
			<Container>
				<div css={styles.innerWrapper}>
					<div css={styles.left}>
						<button type="button" css={styles.backButton} onClick={handleGoBack}>
							<SVGIcon name="arrowLeft" width={26} height={26} />
						</button>
						<div>
							<div css={styles.headerContent}>
								<h4 css={typography.heading5('medium')}>{__('Create coupon', 'tutor')}</h4>
								<TutorBadge variant={statusVariant[coupon.coupon_status]}>{makeFirstCharacterUpperCase(coupon.coupon_status)}</TutorBadge>
							</div>
							<Show
								when={coupon.updated_at_gmt && coupon.coupon_update_by.length}
								fallback={
									coupon.created_at_gmt && (
										<p css={styles.updateMessage}>
											{__('Created by ')} {coupon.coupon_created_by} {__(' at ', 'tutor')}
											{format(new Date(coupon.created_at_gmt), DateFormats.activityDate)}
										</p>
									)
								}
							>
								{() => (
									<p css={styles.updateMessage}>
										{__('Update by ')} {coupon.coupon_update_by} {__(' at ', 'tutor')}
										{format(new Date(coupon.updated_at_gmt), DateFormats.activityDate)}
									</p>
								)}
							</Show>
						</div>
					</div>
					<div css={styles.right}>
						<Button variant="tertiary" onClick={handleGoBack}>
							{__('Cancel', 'tutor')}
						</Button>
						<Button 
							variant="primary"
							loading={createCouponMutation.isPending || updateCouponMutation.isPending}
							onClick={form.handleSubmit(handleSubmit)}
						>
							{__('Save', 'tutor')}
						</Button>
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
		position: sticky;
		top: 32px;
		z-index: ${zIndex.positive};
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
