import Checkbox from '@TutorShared/atoms/CheckBox';
import { LoadingSection } from '@TutorShared/atoms/LoadingSpinner';
import { borderRadius, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import { usePaginatedTable } from '@TutorShared/hooks/usePaginatedTable';
import Paginator from '@TutorShared/molecules/Paginator';
import Table, { type Column } from '@TutorShared/molecules/Table';
import { css } from '@emotion/react';

import coursePlaceholder from '@SharedImages/course-placeholder.png';
import { useCourseCategoryQuery, type Category } from '@TutorShared/services/course_category';
import { __ } from '@wordpress/i18n';
import type { UseFormReturn } from 'react-hook-form';
import SearchField from './SearchField';

interface CategoryListTableProps {
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  form: UseFormReturn<any>;
}

const CategoryListTable = ({ form }: CategoryListTableProps) => {
  const selectedCategories: Category[] = form.watch('categories') ?? [];
  const { pageInfo, onPageChange, itemsPerPage, offset, onFilterItems } = usePaginatedTable();
  const categoryListQuery = useCourseCategoryQuery({
    applies_to: 'specific_category',
    offset,
    limit: itemsPerPage,
    filter: pageInfo.filter,
  });
  const fetchedCategories = (categoryListQuery.data?.results ?? []) as Category[];

  function toggleSelection(isChecked = false) {
    const selectedCategoryIds = selectedCategories.map((category) => category.id);
    const fetchedCategoryIds = fetchedCategories.map((category) => category.id);

    if (isChecked) {
      const newCategories = fetchedCategories.filter((category) => !selectedCategoryIds.includes(category.id));
      form.setValue('categories', [...selectedCategories, ...newCategories]);
      return;
    }

    const newCategories = selectedCategories.filter((category) => !fetchedCategoryIds.includes(category.id));
    form.setValue('categories', newCategories);
  }

  function handleAllIsChecked() {
    return fetchedCategories.every((category) => selectedCategories.map((course) => course.id).includes(category.id));
  }

  const columns: Column<Category>[] = [
    {
      Header: categoryListQuery.data?.results.length ? (
        <Checkbox
          onChange={toggleSelection}
          checked={categoryListQuery.isLoading || categoryListQuery.isRefetching ? false : handleAllIsChecked()}
          label={__('Category', __TUTOR_TEXT_DOMAIN__)}
        />
      ) : (
        __('Category', __TUTOR_TEXT_DOMAIN__)
      ),
      Cell: (item) => {
        return (
          <div css={styles.checkboxWrapper}>
            <Checkbox
              onChange={() => {
                const filteredItems = selectedCategories.filter((category) => category.id !== item.id);
                const isNewItem = filteredItems?.length === selectedCategories.length;

                if (isNewItem) {
                  form.setValue('categories', [...filteredItems, item]);
                } else {
                  form.setValue('categories', filteredItems);
                }
              }}
              checked={selectedCategories.map((category) => category.id).includes(item.id)}
            />
            <img
              src={item.image || coursePlaceholder}
              css={styles.thumbnail}
              alt={__('category item', __TUTOR_TEXT_DOMAIN__)}
            />
            <div css={styles.courseItem}>
              <div>{item.title}</div>
              <p>{`${item.total_courses} ${__('Courses', __TUTOR_TEXT_DOMAIN__)}`}</p>
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
    return <div css={styles.errorMessage}>{__('Something went wrong', __TUTOR_TEXT_DOMAIN__)}</div>;
  }

  return (
    <>
      <div css={styles.tableActions}>
        <SearchField onFilterItems={onFilterItems} />
      </div>

      <div css={styles.tableWrapper}>
        <Table
          columns={columns}
          data={(categoryListQuery.data.results as Category[]) ?? []}
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
    object-fit: cover;
    object-position: center;
  `,
  errorMessage: css`
    height: 100px;
    display: flex;
    align-items: center;
    justify-content: center;
  `,
};
