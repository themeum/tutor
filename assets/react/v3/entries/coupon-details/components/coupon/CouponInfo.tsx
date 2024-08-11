import { Box, BoxSubtitle, BoxTitle } from '@Atoms/Box';
import Button from '@Atoms/Button';
import FormInput from '@Components/fields/FormInput';
import FormRadioGroup from '@Components/fields/FormRadioGroup';
import { DateFormats } from '@Config/constants';
import { colorPalate, spacing } from '@Config/styles';
import { Coupon } from '@CouponServices/coupon';
import { styleUtils } from '@Utils/style-utils';
import { generateCouponCode } from '@Utils/util';
import { requiredRule } from '@Utils/validation';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { format } from 'date-fns';
import { Controller, useFormContext } from 'react-hook-form';

const couponTypeOptions = [
	{ label: __('Code', 'tutor'), value: 'code' },
	{ label: __('Automatic', 'tutor'), value: 'automatic' },
];

function CouponInfo() {
	const form = useFormContext<Coupon>();
	const couponType = form.watch('coupon_type');

	function handleGenerateCouponCode() {
		const newCouponCode = generateCouponCode();
		form.setValue('coupon_code', newCouponCode, { shouldValidate: true });
	}

	return (
		<Box bordered css={styles.discountWrapper}>
			<div css={styles.couponWrapper}>
				<BoxTitle>{__('Coupon Info', 'tutor')}</BoxTitle>
				<BoxSubtitle>
					{__('Add Topics in the Course Builder section to create lessons, quizzes, and assignments.', 'tutor')}
				</BoxSubtitle>
			</div>
			<Controller
				name="coupon_type"
				control={form.control}
				render={(controllerProps) => (
					<FormRadioGroup
						{...controllerProps}
						label={__('Deduction type', 'tutor')}
						options={couponTypeOptions}
						wrapperCss={styles.radioWrapper}
					/>
				)}
			/>
			<Controller
				name="coupon_title"
				control={form.control}
				rules={requiredRule()}
				render={(controllerProps) => (
					<FormInput 
						{...controllerProps} 
						label={__('Coupon name', 'tutor')} 
						placeholder={__(`Summer Sale ${format(new Date(), DateFormats.year)}`, 'tutor')}
					/>
				)}
			/>

			{couponType === 'code' && (
				<div css={styles.couponCodeWrapper}>
					<Controller
						name="coupon_code"
						control={form.control}
						rules={requiredRule()}
						render={(controllerProps) => (
							<FormInput
								{...controllerProps}
								label={__('Coupon code', 'tutor')}
								placeholder={__('SUMMER20', 'tutor')}
							/>
						)}
					/>
					<Button variant="text" onClick={handleGenerateCouponCode} buttonCss={styles.generateCode}>
						{__('Generate code', 'tutor')}
					</Button>
				</div>
			)}
		</Box>
	);
}

export default CouponInfo;

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
	couponCodeWrapper: css`
		position: relative;
	`,
	radioWrapper: css`
		display: flex;
		gap: ${spacing[40]};
	`,
	generateCode: css`
		${styleUtils.resetButton};
		color: ${colorPalate.actions.primary.default};
		position: absolute;
		right: ${spacing[0]};
		top: ${spacing[0]};

		&:hover,
		&:active,
		&:focus {
			color: ${colorPalate.actions.primary.hover};
		}
	`,
};
