import Checkbox from '@Atoms/CheckBox';
import LoadingSpinner from '@Atoms/LoadingSpinner';
import { borderRadius, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { css } from '@emotion/react';
import { usePaginatedTable } from '@Hooks/usePaginatedTable';
import Paginator from '@Molecules/Paginator';
import Table, { Column } from '@Molecules/Table';

import { Coupon, Course, mockCouponData, useCourseListQuery } from '@CouponServices/coupon';
import coursePlaceholder from '@Images/common/course-placeholder.png';
import { __ } from '@wordpress/i18n';
import { UseFormReturn } from 'react-hook-form';
import SearchField from './SearchField';

interface CourseListTableProps {
	form: UseFormReturn<Coupon, any, undefined>;
}

const CourseListTable = ({ form }: CourseListTableProps) => {
	console.log(form.getValues());
	const { pageInfo, onPageChange, itemsPerPage, offset, onFilterItems } = usePaginatedTable({
		updateQueryParams: false,
	});
	const courseListQuery = useCourseListQuery({
		offset,
		limit: itemsPerPage,
		filter: pageInfo.filter,
	});

	const columns: Column<Course>[] = [
		{
			Header: courseListQuery.data?.results.length ? (
				<Checkbox onChange={() => {}} checked={true} />
			) : (
				__('Courses', 'tutor')
			),
			Cell: (item) => {
				return (
					<div css={styles.checkboxWrapper}>
						<Checkbox onChange={() => {}} checked={true} />
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
				return <div>{item.regular_price_formatted}</div>;
			},
		},
	];

	if (courseListQuery.isLoading) {
		return <LoadingSpinner />;
	}

	if (!courseListQuery.data) {
		return <div>{__('Something went wrong', 'tutor')}</div>;
	}

	return (
		<>
			<div css={styles.tableActions}>
				<SearchField onFilterItems={onFilterItems} />
			</div>

			<div css={styles.tableWrapper}>
				<Table
					columns={columns}
					data={mockCouponData.courses ?? []}
					itemsPerPage={itemsPerPage}
					loading={courseListQuery.isFetching || courseListQuery.isRefetching}
				/>
			</div>

			<div css={styles.paginatorWrapper}>
				<Paginator
					currentPage={pageInfo.page}
					onPageChange={onPageChange}
					totalItems={courseListQuery.data.totalItems}
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
		max-height: 450px;
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
};
