import { Box, BoxSubtitle, BoxTitle } from '@Atoms/Box';
import FormCheckbox from '@Components/fields/FormCheckbox';
import FormDateInput from '@Components/fields/FormDateInput';
import FormTimeInput from '@Components/fields/FormTimeInput';
import { colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { Coupon } from '@CouponServices/coupon';
import { styleUtils } from '@Utils/style-utils';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { Controller, useFormContext } from 'react-hook-form';

function CouponValidity() {
	const form = useFormContext<Coupon>();

	const isEndEnabled = form.watch('is_end_enabled');
	const startDate = form.watch('start_date');
	const startTime = form.watch('start_time');
	const hasStartDateTime = !!startDate && !!startTime;

	return (
		<Box bordered css={styles.discountWrapper}>
			<div css={styles.couponWrapper}>
				<BoxTitle>{__('Validity', 'tutor')}</BoxTitle>
				<BoxSubtitle>
					{__(
						'You can set activation period from here, if not set an end date the coupon will have unlimited time',
						'tutor',
					)}
				</BoxSubtitle>
			</div>

			<Box css={[styleUtils.boxReset, styles.validityWrapper]}>
				<BoxSubtitle css={styles.dateTimeTitle}>{__('Starts from', 'tutor')}</BoxSubtitle>
				<div css={styles.dateTimeWrapper}>
					<Controller
						control={form.control}
						name="start_date"
						render={(controllerProps) => <FormDateInput {...controllerProps} placeholder="2030-10-24" />}
					/>
					<Controller
						control={form.control}
						name="start_time"
						render={(controllerProps) => <FormTimeInput {...controllerProps} placeholder="12:30 PM" />}
					/>
				</div>
				<Controller
					control={form.control}
					name="is_end_enabled"
					render={(controllerProps) => (
						<FormCheckbox
							{...controllerProps}
							label={__('Set end date', 'tutor')}
							disabled={!hasStartDateTime}
							labelCss={styles.setEndDateLabel}
						/>
					)}
				/>
				{hasStartDateTime && isEndEnabled && (
					<>
						<BoxSubtitle css={styles.dateTimeTitle}>{__('Ends in', 'tutor')}</BoxSubtitle>
						<div css={styles.dateTimeWrapper}>
							<Controller
								control={form.control}
								name="end_date"
								render={(controllerProps) => <FormDateInput {...controllerProps} placeholder="2030-10-24" />}
							/>
							<Controller
								control={form.control}
								name="end_time"
								render={(controllerProps) => <FormTimeInput {...controllerProps} placeholder="12:30 PM" />}
							/>
						</div>
					</>
				)}
			</Box>
		</Box>
	);
}

export default CouponValidity;

const styles = {
	discountWrapper: css`
		display: flex;
		flex-direction: column;
		gap: ${spacing[12]};
	`,
	couponWrapper: css`
		display: flex;
		flex-direction: column;
		gap: ${spacing[4]};
	`,
	validityWrapper: css`
		display: flex;
		flex-direction: column;
		gap: ${spacing[12]};
	`,
	dateTimeWrapper: css`
		display: flex;
		gap: ${spacing[12]};
		width: fit-content;
	`,
	dateTimeTitle: css`
		color: ${colorTokens.text.title};
	`,
	setEndDateLabel: css`
		${typography.caption()};
		color: ${colorTokens.text.title};
	`,
};
