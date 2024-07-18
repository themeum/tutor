import Checkbox from '@Atoms/CheckBox';
import LoadingSpinner from '@Atoms/LoadingSpinner';
import { borderRadius, colorPalate, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { css } from '@emotion/react';
import { usePaginatedTable } from '@Hooks/usePaginatedTable';
import Paginator from '@Molecules/Paginator';
import Table, { Column } from '@Molecules/Table';

import { Coupon, Course, useCourseListQuery } from '@CouponServices/coupon';
import coursePlaceholder from '@Images/common/course-placeholder.png';
import { __ } from '@wordpress/i18n';
import { UseFormReturn } from 'react-hook-form';
import SearchField from './SearchField';

interface CourseListTableProps {
	form: UseFormReturn<Coupon, any, undefined>;
	type: 'bundles' | 'courses';
}

const CourseListTable = ({ type, form }: CourseListTableProps) => {
	const courseList = form.watch(type) || [];
	const { pageInfo, onPageChange, itemsPerPage, offset, onFilterItems } = usePaginatedTable({
		updateQueryParams: false,
	});
	const courseListQuery = useCourseListQuery({
		offset,
		limit: itemsPerPage,
		filter: pageInfo.filter,
	});

	function toggleSelection(isChecked = false) {
		form.setValue(type, isChecked ? courseListQuery.data?.results : []);
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
					label={type === 'courses' ? __('Courses', 'tutor') : __('Bundles', 'tutor')}
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

								if (filteredItems?.length === courseList.length) {
									form.setValue(type, [...filteredItems, item]);
								} else {
									form.setValue(type, filteredItems);
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
					data={courseListQuery.data.results ?? []}
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
	checkboxLabel: css`
		${typography.body()};
		color: ${colorPalate.text.neutral};
	`,
};
