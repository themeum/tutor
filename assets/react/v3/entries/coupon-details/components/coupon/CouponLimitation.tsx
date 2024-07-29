import { Box, BoxSubtitle, BoxTitle } from '@Atoms/Box';
import FormCheckbox from '@Components/fields/FormCheckbox';
import FormInput from '@Components/fields/FormInput';
import { colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import { Coupon } from '@CouponServices/coupon';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { Controller, useFormContext } from 'react-hook-form';

function CouponLimitation() {
	const form = useFormContext<Coupon>();
	const enabledLimit = form.watch('usage_limit_status');

	return (
		<Box bordered css={styles.discountWrapper}>
			<div css={styles.couponWrapper}>
				<BoxTitle>{__('Usage limitation', 'tutor')}</BoxTitle>
				<BoxSubtitle>
					{__('Add Topics in the Course Builder section to create lessons, quizzes, and assignments.', 'tutor')}
				</BoxSubtitle>
			</div>
			<div css={styles.couponWrapper}>
				<div css={styles.limitWrapper}>
					<Controller
						name="usage_limit_status"
						control={form.control}
						render={(controllerProps) => (
							<FormCheckbox
								{...controllerProps}
								label={__('Limit number of times this discount can be used in total', 'tutor')}
								labelCss={styles.checkBoxLabel}
							/>
						)}
					/>
					<Show when={enabledLimit}>
						<Controller
							name="usage_limit_value"
							control={form.control}
							render={(controllerProps) => (
								<div css={styles.limitInput}>
									<FormInput {...controllerProps} placeholder={__('Placeholder', 'tutor')} />
								</div>
							)}
						/>
					</Show>
				</div>
			</div>
			<Controller
				name="is_one_use_per_user"
				control={form.control}
				render={(controllerProps) => (
					<FormCheckbox
						{...controllerProps}
						label={__('Limit to one use per customer', 'tutor')}
						labelCss={styles.checkBoxLabel}
					/>
				)}
			/>
		</Box>
	);
}

export default CouponLimitation;

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
	limitWrapper: css`
		display: flex;
		flex-direction: column;
		gap: ${spacing[8]};
	`,
	checkBoxLabel: css`
		${typography.caption()};
		color: ${colorTokens.text.title};
	`,
	limitInput: css`
		width: fit-content;
		margin-left: ${spacing[28]};
	`,
};
