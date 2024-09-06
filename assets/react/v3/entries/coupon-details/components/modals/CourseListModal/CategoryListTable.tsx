import Checkbox from '@Atoms/CheckBox';
import { LoadingSection } from '@Atoms/LoadingSpinner';
import { borderRadius, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { usePaginatedTable } from '@Hooks/usePaginatedTable';
import Paginator from '@Molecules/Paginator';
import Table, { type Column } from '@Molecules/Table';
import { css } from '@emotion/react';

import { type Coupon, type CourseCategory, useAppliesToQuery } from '@CouponServices/coupon';
import coursePlaceholder from '@Images/common/course-placeholder.png';
import { __ } from '@wordpress/i18n';
import type { UseFormReturn } from 'react-hook-form';
import SearchField from './SearchField';

interface CategoryListTableProps {
  // biome-ignore lint/suspicious/noExplicitAny: <explanation>
  form: UseFormReturn<Coupon, any, undefined>;
}

const CategoryListTable = ({ form }: CategoryListTableProps) => {
  const categoryList = form.watch('categories') ?? [];
  const { pageInfo, onPageChange, itemsPerPage, offset, onFilterItems } = usePaginatedTable({
    updateQueryParams: false,
  });
  const categoryListQuery = useAppliesToQuery({
    applies_to: 'specific_category',
    offset,
    limit: itemsPerPage,
    filter: pageInfo.filter,
  });

  function toggleSelection(isChecked = false) {
    form.setValue('categories', isChecked ? (categoryListQuery.data?.results as CourseCategory[]) : []);
  }

  function handleAllIsChecked() {
    return (
      categoryList.length === categoryListQuery.data?.results.length &&
      categoryList?.every((item) => categoryListQuery.data?.results?.map((result) => result.id).includes(item.id))
    );
  }

  const columns: Column<CourseCategory>[] = [
    {
      Header: categoryListQuery.data?.results.length ? (
        <Checkbox onChange={toggleSelection} checked={handleAllIsChecked()} label={__('Category', 'tutor')} />
      ) : (
        __('Category', 'tutor')
      ),
      Cell: (item) => {
        return (
          <div css={styles.checkboxWrapper}>
            <Checkbox
              onChange={() => {
                const filteredItems = categoryList.filter((course) => course.id !== item.id);
                const isNewItem = filteredItems?.length === categoryList.length;

                if (isNewItem) {
                  form.setValue('categories', [...filteredItems, item]);
                } else {
                  form.setValue('categories', filteredItems);
                }
              }}
              checked={categoryList.map((course) => course.id).includes(item.id)}
            />
            <img src={item.image || coursePlaceholder} css={styles.thumbnail} alt={__('course item', 'tutor')} />
            <div css={styles.courseItem}>
              <div>{item.title}</div>
              <p>{`${item.total_courses} ${__('Courses', 'tutor')}`}</p>
            </div>
          </div>
        );
      },
      width: 720,
    },
  ];

  if (categoryListQuery.isLoading) {
    return <LoadingSection />;
  }

  if (!categoryListQuery.data) {
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
          data={(categoryListQuery.data.results as CourseCategory[]) ?? []}
          itemsPerPage={itemsPerPage}
          loading={categoryListQuery.isFetching || categoryListQuery.isRefetching}
        />
      </div>

      <div css={styles.paginatorWrapper}>
        <Paginator
          currentPage={pageInfo.page}
          onPageChange={onPageChange}
          totalItems={categoryListQuery.data.total_items}
          itemsPerPage={itemsPerPage}
        />
      </div>
    </>
  );
};

export default CategoryListTable;

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
  errorMessage: css`
		height: 100px;
		display: flex;
		align-items: center;
		justify-content: center;
	`,
};
