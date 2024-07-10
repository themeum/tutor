import { Box, BoxSubtitle, BoxTitle } from '@Atoms/Box';
import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import FormInput from '@Components/fields/FormInput';
import FormSelectInput from '@Components/fields/FormSelectInput';
import { spacing } from '@Config/styles';
import { Coupon } from '@CouponServices/coupon';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { Controller, useFormContext } from 'react-hook-form';

const discountTypeOptions = [
	{ label: __('Percent', 'tutor'), value: 'percent' },
	{ label: __('Amount', 'tutor'), value: 'amount' },
];
const appliesToOptions = [
	{ label: __('All courses and bundles', 'tutor'), value: 'all_courses_and_bundles' },
	{ label: __('All courses', 'tutor'), value: 'all_courses' },
	{ label: __('All bundles', 'tutor'), value: 'all_bundles' },
	{ label: __('Specific courses', 'tutor'), value: 'specific_courses' },
	{ label: __('Specific bundles', 'tutor'), value: 'specific_bundles' },
	{ label: __('Specific category', 'tutor'), value: 'specific_category' },
];

function CouponDiscount() {
	const form = useFormContext<Coupon>();
	const appliesTo = form.watch('applies_to');

	return (
		<Box bordered css={styles.discountWrapper}>
			<div css={styles.couponWrapper}>
				<BoxTitle>{__('Discount', 'tutor')}</BoxTitle>
				<BoxSubtitle>
					{__('Add Topics in the Course Builder section to create lessons, quizzes, and assignments.', 'tutor')}
				</BoxSubtitle>
			</div>
			<div css={styles.discountTypeWrapper}>
				<Controller
					name="discount_type"
					control={form.control}
					render={(controllerProps) => (
						<FormSelectInput {...controllerProps} label={__('Discount type', 'tutor')} options={discountTypeOptions} />
					)}
				/>
				<Controller
					name="coupon_name"
					control={form.control}
					render={(controllerProps) => (
						<FormInput
							{...controllerProps}
							label={__('Coupon name', 'tutor')}
							placeholder={__('Placeholder', 'tutor')}
						/>
					)}
				/>
			</div>
			<Controller
				name="applies_to"
				control={form.control}
				render={(controllerProps) => (
					<FormSelectInput {...controllerProps} label={__('Applies to', 'tutor')} options={appliesToOptions} />
				)}
			/>
			{(appliesTo === 'specific_courses' || appliesTo === 'specific_bundles' || appliesTo === 'specific_category') && (
				<Button
					variant="secondary"
					isOutlined={true}
					buttonCss={styles.addCoursesButton}
					icon={<SVGIcon name="plusSquareBrand" width={24} height={25} />}
					onClick={() => {
						// @TODO: will be updated later.
					}}
				>
					{__('Add Items', 'tutor')}
				</Button>
			)}
		</Box>
	);
}

export default CouponDiscount;

const styles = {
	discountWrapper: css`
		display: flex;
		flex-direction: column;
		gap: ${spacing[12]};
	`,
	discountTypeWrapper: css`
		display: flex;
		gap: ${spacing[20]};
	`,
	couponWrapper: css`
		display: flex;
		flex-direction: column;
		gap: ${spacing[4]};
	`,
	addCoursesButton: css`
		width: fit-content;
	`,
};
