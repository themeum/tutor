import Checkbox from '@Atoms/CheckBox';
import { LoadingSection } from '@Atoms/LoadingSpinner';
import { borderRadius, colorPalate, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { css } from '@emotion/react';
import { usePaginatedTable } from '@Hooks/usePaginatedTable';
import Paginator from '@Molecules/Paginator';
import Table, { Column } from '@Molecules/Table';

import { Coupon, Course, useAppliesToQuery } from '@CouponServices/coupon';
import coursePlaceholder from '@Images/common/course-placeholder.png';
import { __ } from '@wordpress/i18n';
import { UseFormReturn } from 'react-hook-form';
import SearchField from './SearchField';
import { Enrollment } from '@EnrollmentServices/enrollment';

interface CourseListTableProps {
	form: UseFormReturn<Enrollment, any, undefined>;
}

const CourseListTable = ({ form }: CourseListTableProps) => {
	const courseList = form.watch('courses') || [];	
	const { pageInfo, onPageChange, itemsPerPage, offset, onFilterItems } = usePaginatedTable({
		updateQueryParams: false,
	});
	const courseListQuery = useAppliesToQuery({
		applies_to: 'specific_courses',
		offset,
		limit: itemsPerPage,
		filter: pageInfo.filter,
	});

	function toggleSelection(isChecked = false) {
		form.setValue('courses', isChecked ? courseListQuery.data?.results as Course[] : []);
	}

	function handleAllIsChecked() {
		return (
			courseList.length === courseListQuery.data?.results.length &&
			courseList?.every((item) => courseListQuery.data?.results?.map((result) => result.id).includes(item.id))
		);
	}

	const columns: Column<Course>[] = [
		{
			Header: courseListQuery.data?.results.length ? (
				<Checkbox
					onChange={toggleSelection}
					checked={handleAllIsChecked()}
					label={__('Name', 'tutor')}
					labelCss={styles.checkboxLabel}
				/>
			) : (
				__('#', 'tutor')
			),
			Cell: (item) => {
				return (
					<div css={styles.checkboxWrapper}>
						<Checkbox
							onChange={() => {
								const filteredItems = courseList.filter((course) => course.id !== item.id);
								const isNewItem = filteredItems?.length === courseList.length;

								if (isNewItem) {
									form.setValue('courses', [...filteredItems, item]);
								} else {
									form.setValue('courses', filteredItems);
								}
							}}
							checked={courseList.map((course) => course.id).includes(item.id)}
						/>
						<img src={item.image || coursePlaceholder} css={styles.thumbnail} alt="course item" />
						<div css={styles.courseItem}>
							<div>{item.title}</div>
							<p>{item.author}</p>
						</div>
					</div>
				);
			},
			width: 600,
		},
		{
			Header: __('Price', 'tutor'),
			Cell: (item) => {
				return (
					<div css={styles.price}>
						<span>{item.sale_price ? item.sale_price : item.regular_price}</span>
						{item.sale_price && <span css={styles.discountPrice}>{item.regular_price}</span>}
					</div>
				);
			},
		},
	];

	if (courseListQuery.isLoading) {
		return <LoadingSection />;
	}

	if (!courseListQuery.data) {
		return <div css={styles.errorMessage}>{__('Something went wrong', 'tutor')}</div>;
	}

	return (
		<>
			<div css={styles.tableActions}>
				<SearchField onFilterItems={onFilterItems} />
			</div>

			<div css={styles.tableWrapper}>
				<Table
					columns={columns}
					data={courseListQuery.data.results as Course[] ?? []}
					itemsPerPage={itemsPerPage}
					loading={courseListQuery.isFetching || courseListQuery.isRefetching}
				/>
			</div>

			<div css={styles.paginatorWrapper}>
				<Paginator
					currentPage={pageInfo.page}
					onPageChange={onPageChange}
					totalItems={courseListQuery.data.total_items}
					itemsPerPage={itemsPerPage}
				/>
			</div>
		</>
	);
};

export default CourseListTable;

const styles = {
	tableActions: css`
		padding: ${spacing[20]};
	`,
	tableWrapper: css`
		max-height: calc(100vh - 350px);
		overflow: auto;
	`,
	paginatorWrapper: css`
		margin: ${spacing[20]} ${spacing[16]};
	`,
	checkboxWrapper: css`
		display: flex;
		align-items: center;
		gap: ${spacing[12]};
	`,
	courseItem: css`
		${typography.caption()};
		margin-left: ${spacing[4]};
	`,
	thumbnail: css`
		width: 48px;
		height: 48px;
		border-radius: ${borderRadius[4]};
	`,
	checkboxLabel: css`
		${typography.body()};
		color: ${colorPalate.text.neutral};
	`,
	price: css`
		display: flex;
		gap: ${spacing[4]};
		justify-content: end;
	`,
	discountPrice: css`
		text-decoration: line-through;
		color: ${colorTokens.text.subdued};
	`,
	errorMessage: css`
		height: 100px;
		display: flex;
		align-items: center;
		justify-content: center;
	`,
};
