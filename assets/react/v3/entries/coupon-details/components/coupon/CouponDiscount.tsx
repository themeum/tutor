import { Box, BoxSubtitle, BoxTitle } from '@Atoms/Box';
import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import FormInputWithContent from '@Components/fields/FormInputWithContent';
import FormSelectInput from '@Components/fields/FormSelectInput';
import { useModal } from '@Components/modals/Modal';
import { tutorConfig } from '@Config/config';
import { borderRadius, colorTokens, spacing } from '@Config/styles';
import Show from '@Controls/Show';
import CouponSelectItemModal from '@CouponComponents/modals/CourseListModal';

import { Coupon } from '@CouponServices/coupon';
import { isAddonEnabled } from '@CourseBuilderUtils/utils';
import { css } from '@emotion/react';
import { styleUtils } from '@Utils/style-utils';
import { requiredRule } from '@Utils/validation';
import { __ } from '@wordpress/i18n';
import { Controller, useFormContext } from 'react-hook-form';
import coursePlaceholder from '@Images/common/course-placeholder.png';
import { typography } from '@Config/typography';
import { ReactNode } from 'react';

const isTutorProActive = !!tutorConfig.tutor_pro_url;
const displayBundle = isTutorProActive && isAddonEnabled('Course Bundle');

const discountTypeOptions = [
	{ label: __('Percent', 'tutor'), value: 'percentage' },
	{ label: __('Amount', 'tutor'), value: 'flat' },
];

const appliesToOptions = [
	{ label: __('All courses', 'tutor'), value: 'all_courses' },
	...(displayBundle ? [{ label: __('All bundles', 'tutor'), value: 'all_bundles' }] : []),
	...(displayBundle ? [{ label: __('All courses and bundles', 'tutor'), value: 'all_courses_and_bundles' }] : []),
	{ label: __('Specific courses', 'tutor'), value: 'specific_courses' },
	...(displayBundle ? [{ label: __('Specific bundles', 'tutor'), value: 'specific_bundles' }] : []),
	{ label: __('Specific category', 'tutor'), value: 'specific_category' },
];

function CouponDiscount() {
	const form = useFormContext<Coupon>();
	const { tutor_currency } = tutorConfig;
	const { showModal } = useModal();

	const appliesTo = form.watch('applies_to');
	const discountType = form.watch('discount_type');
	const courses = form.watch('courses') ?? [];
	const bundles = form.watch('bundles') ?? [];
	const categories = form.watch('categories') ?? [];

	function removesSelectedItem(type: string, id: number) {
		if (type === 'courses') {
			form.setValue(type, courses?.filter((item) => item.id !== id));
		}
		if (type === 'bundles') {
			form.setValue(type, bundles?.filter((item) => item.id !== id));
		}
		if (type === 'categories') {
			form.setValue(type, categories?.filter((item) => item.id !== id));
		}
	}

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
					rules={requiredRule()}
					render={(controllerProps) => (
						<FormSelectInput {...controllerProps} label={__('Discount type', 'tutor')} options={discountTypeOptions} />
					)}
				/>
				<Controller
					name="discount_amount"
					control={form.control}
					rules={requiredRule()}
					render={(controllerProps) => (
						<FormInputWithContent
							{...controllerProps}
							type="number"
							label={__('Discount Value', 'tutor')}
							placeholder="0"
							content={discountType === 'flat' ? tutor_currency?.symbol ?? '$' : '%'}
							contentCss={styleUtils.inputCurrencyStyle}
						/>
					)}
				/>
			</div>
			<Controller
				name="applies_to"
				control={form.control}
				rules={requiredRule()}
				render={(controllerProps) => (
					<FormSelectInput {...controllerProps} label={__('Applies to', 'tutor')} options={appliesToOptions} />
				)}
			/>

			{appliesTo === 'specific_courses' && courses.length > 0 && (
				<div css={styles.selectedWrapper}>
					{courses?.map((item) => 
						<AppliesToItem
							key={item.id}
							image={item.image}
							title={item.title}
							subTitle={
								<div css={styles.price}>
									<span>{item.sale_price ? item.sale_price : item.regular_price}</span>
									{item.sale_price && <span css={styles.discountPrice}>{item.regular_price}</span>}
								</div>
							}
							handleDeleteClick={() => removesSelectedItem('courses', item.id)}
						/>
					)}
				</div>
			)}

			{appliesTo === 'specific_bundles' && bundles.length > 0 && (
				<div css={styles.selectedWrapper}>
					{bundles?.map((item) => 
						<AppliesToItem
							key={item.id}
							image={item.image}
							title={item.title}
							subTitle={
								<div css={styles.price}>
									<span>{item.sale_price ? item.sale_price : item.regular_price}</span>
									{item.sale_price && <span css={styles.discountPrice}>{item.regular_price}</span>}
								</div>
							}
							handleDeleteClick={() => removesSelectedItem('bundles', item.id)}
						/>
					)}
				</div>
			)}

			{appliesTo === 'specific_category' && categories.length > 0 && (
				<div css={styles.selectedWrapper}>
					{categories?.map((item) => 
						<AppliesToItem
							key={item.id}
							image={item.image}
							title={item.title}
							subTitle={`${item.total_courses} ${__('Courses', 'tutor')}`}
							handleDeleteClick={() => removesSelectedItem('categories', item.id)}
						/>
					)}
				</div>
			)}

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
								type:
									appliesTo === 'specific_category'
										? 'categories'
										: appliesTo === 'specific_courses'
										? 'courses'
										: 'bundles',
								form,
							},
							closeOnOutsideClick: true,
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

interface AppliesToItemProps {
	image: string;
	title: string;
	subTitle: string | ReactNode;
	handleDeleteClick: () => void;
}

function AppliesToItem({ image, title, subTitle, handleDeleteClick }: AppliesToItemProps) {
	return (
		<div css={styles.selectedItem}>
			<div css={styles.selectedThumb}>
				<img src={image || coursePlaceholder} css={styles.thumbnail} alt="course item" />
			</div>
			<div css={styles.selectedContent}>
				<div css={styles.selectedTitle}>{title}</div>
				<div css={styles.selectedSubTitle}>{subTitle}</div>
			</div>
			<div>
				<Button variant='text' onClick={handleDeleteClick}>
					<SVGIcon name='delete' width={24} height={24} />
				</Button>
			</div>
		</div>
	)
}

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
	price: css`
		display: flex;
		gap: ${spacing[4]};
	`,
	discountPrice: css`
		text-decoration: line-through;
	`,
	selectedWrapper: css`
		border: 1px solid ${colorTokens.stroke.divider};
		border-radius: ${borderRadius[6]};
	`,
	selectedItem: css`
		padding: ${spacing[12]};
		display: flex;
		align-items: center;
		gap: ${spacing[16]};
		
		&:not(:last-child) {
			border-bottom: 1px solid ${colorTokens.stroke.divider};
		}
	`,
	selectedContent: css`
		width: 100%;
	`,
	selectedTitle: css`
		${typography.small()};
		color: ${colorTokens.text.primary};
		margin-bottom: ${spacing[4]};
	`,
	selectedSubTitle: css`
		${typography.small()};
		color: ${colorTokens.text.hints};
	`,
	selectedThumb: css`
		height: 48px;
	`,
	thumbnail: css`
		width: 48px;
		height: 48px;
		border-radius: ${borderRadius[4]};
	`,
};
