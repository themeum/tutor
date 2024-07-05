import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import { TutorBadge } from '@Atoms/TutorBadge';
import Container from '@Components/Container';
import { DateFormats } from '@Config/constants';
import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import { useCouponContext } from '@CouponContexts/coupon-context';
import { styleUtils } from '@Utils/style-utils';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { format } from 'date-fns';

export const TOPBAR_HEIGHT = 96;

function Topbar() {
	const { coupon } = useCouponContext();
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
								<h4 css={typography.heading5('medium')}>
									{__('Coupon', 'tutor')} #{coupon.id}
								</h4>
								<TutorBadge variant="warning">Pending</TutorBadge>
								<TutorBadge variant="success">Paid</TutorBadge>
								<TutorBadge variant="secondary">Partially Refunded</TutorBadge>
								<TutorBadge variant="critical">Fully Refunded</TutorBadge>
								<TutorBadge variant="secondary">Cancelled</TutorBadge>
							</div>
							<Show
								when={coupon.updated_at}
								fallback={
									<p css={styles.updateMessage}>
										{__('Created by ')} {coupon.user} {__(' at ', 'tutor')}
										{format(new Date(coupon.created_at), DateFormats.activityDate)}
									</p>
								}
							>
								{(date) => (
									<p css={styles.updateMessage}>
										{__('Update by ')} {coupon.user} {__(' at ', 'tutor')}
										{format(new Date(date), DateFormats.activityDate)}
									</p>
								)}
							</Show>
						</div>
					</div>
					<Button variant="tertiary" onClick={() => alert('@TODO: will be implemented later.')}>
						Cancel Coupon
					</Button>
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
