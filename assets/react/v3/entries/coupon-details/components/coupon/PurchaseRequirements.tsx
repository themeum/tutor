import { Box, BoxSubtitle, BoxTitle } from '@Atoms/Box';
import FormInput from '@Components/fields/FormInput';
import FormInputWithContent from '@Components/fields/FormInputWithContent';
import FormRadioGroup from '@Components/fields/FormRadioGroup';
import { tutorConfig } from '@Config/config';
import { spacing } from '@Config/styles';
import { Coupon } from '@CouponServices/coupon';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { Controller, useFormContext } from 'react-hook-form';

function PurchaseRequirements() {
	const form = useFormContext<Coupon>();
	const { tutor_currency } = tutorConfig;
	const purchaseAmountLabel = __('Minimum purchase amount', 'tutor') + ` (${tutor_currency?.symbol ?? '$'})`;

	const appliesTo = form.watch('applies_to');
	const purchaseRequirements = form.watch('purchase_requirements');

	const requirementOptions = [
		{
			label: __('No minimum requirements', 'tutor'),
			value: 'no_minimum',
		},
		{
			label: purchaseAmountLabel,
			value: 'minimum_purchase',
		},
		{
			label: __('Minimum quantity of items', 'tutor'),
			value: 'minimum_quantity',
		},
	];

	return (
		<Box bordered css={styles.discountWrapper}>
			<div css={styles.couponWrapper}>
				<BoxTitle>{__('Minimum purchase requirements', 'tutor')}</BoxTitle>
				<BoxSubtitle>
					{__('Add Topics in the Course Builder section to create lessons, quizzes, and assignments.', 'tutor')}
				</BoxSubtitle>
			</div>
			<Controller
				name="purchase_requirements"
				control={form.control}
				render={(controllerProps) => (
					<FormRadioGroup {...controllerProps} options={requirementOptions} wrapperCss={styles.radioGroupWrapper} />
				)}
			/>
			<div css={styles.requirementInput}>
				{purchaseRequirements === 'minimum_purchase' && (
					<Controller
						name="purchase_requirements_value"
						control={form.control}
						render={(controllerProps) => (
							<FormInputWithContent
								{...controllerProps}
								type="number"
								placeholder={__('0.00', 'tutor')}
								content={tutor_currency?.symbol ?? '$'}
							/>
						)}
					/>
				)}

				{purchaseRequirements === 'minimum_quantity' && (
					<Controller
						name="purchase_requirements_value"
						control={form.control}
						render={(controllerProps) => (
							<FormInput {...controllerProps} type="number" placeholder={__('0', 'tutor')} />
						)}
					/>
				)}
			</div>
		</Box>
	);
}

export default PurchaseRequirements;

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
	requirementInput: css`
		width: fit-content;
		margin-left: ${spacing[28]};
	`,
	radioGroupWrapper: css`
		display: flex;
		flex-direction: column;
		gap: ${spacing[8]};
	`,
};
