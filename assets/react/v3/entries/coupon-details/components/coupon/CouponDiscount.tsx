import { Box, BoxSubtitle, BoxTitle } from '@Atoms/Box';
import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import FormInputWithContent from '@Components/fields/FormInputWithContent';
import FormSelectInput from '@Components/fields/FormSelectInput';
import { useModal } from '@Components/modals/Modal';
import { tutorConfig } from '@Config/config';
import { colorTokens, spacing } from '@Config/styles';
import Show from '@Controls/Show';
import CouponSelectItemModal from '@CouponComponents/modals/CourseListModal';
import CategoryListTable from '@CouponComponents/modals/CourseListModal/CategoryListTable';
import CourseListTable from '@CouponComponents/modals/CourseListModal/CourseListTable';

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
	const { tutor_currency } = tutorConfig;
	const { showModal } = useModal();

	const appliesTo = form.watch('applies_to');
	const discountType = form.watch('discount_type');

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
					name="discount_value"
					control={form.control}
					render={(controllerProps) => (
						<FormInputWithContent
							{...controllerProps}
							type="number"
							label={__('Discount Value', 'tutor')}
							placeholder="0"
							content={discountType === 'amount' ? tutor_currency?.symbol ?? '$' : '%'}
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
			<Show
				when={appliesTo === 'specific_courses' || appliesTo === 'specific_bundles' || appliesTo === 'specific_category'}
			>
				<Button
					variant="tertiary"
					isOutlined={true}
					buttonCss={styles.addCoursesButton}
					icon={<SVGIcon name="plusSquareBrand" width={24} height={25} />}
					onClick={() => {
						showModal({
							component: CouponSelectItemModal,
							props: {
								title: __('Selected items', 'tutor'),
								children:
									appliesTo === 'specific_category' ? (
										<CategoryListTable form={form} />
									) : (
										<CourseListTable form={form} />
									),
							},
						});
					}}
				>
					{__('Add Items', 'tutor')}
				</Button>
			</Show>
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
		color: ${colorTokens.text.brand};

		svg {
			color: ${colorTokens.text.brand};
		}
	`,
};
