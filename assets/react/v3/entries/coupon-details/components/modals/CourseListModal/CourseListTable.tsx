import Checkbox from '@TutorShared/atoms/CheckBox';
import { LoadingSection } from '@TutorShared/atoms/LoadingSpinner';
import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import { usePaginatedTable } from '@TutorShared/hooks/usePaginatedTable';
import Paginator from '@TutorShared/molecules/Paginator';
import Table, { type Column } from '@TutorShared/molecules/Table';
import { css } from '@emotion/react';

import { type Coupon, type Course, useAppliesToQuery } from '@CouponDetails/services/coupon';
import coursePlaceholder from '@SharedImages/course-placeholder.png';
import { __, sprintf } from '@wordpress/i18n';
import type { UseFormReturn } from 'react-hook-form';
import SearchField from './SearchField';

interface CourseListTableProps {
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  form: UseFormReturn<Coupon, any, undefined>;
  type: 'bundles' | 'courses';
}

const CourseListTable = ({ type, form }: CourseListTableProps) => {
  const selectedCourses = form.watch(type) || [];
  const { pageInfo, onPageChange, itemsPerPage, offset, onFilterItems } = usePaginatedTable();
  const courseListQuery = useAppliesToQuery({
    applies_to: type === 'courses' ? 'specific_courses' : 'specific_bundles',
    offset,
    limit: itemsPerPage,
    filter: pageInfo.filter,
  });

  const fetchedCourses = (courseListQuery.data?.results ?? []) as Course[];

  function toggleSelection(isChecked = false) {
    const selectedCourseIds = selectedCourses.map((course) => course.id);
    const fetchedCourseIds = fetchedCourses.map((course) => course.id);

    if (isChecked) {
      const newCourses = fetchedCourses.filter((course) => !selectedCourseIds.includes(course.id));
      form.setValue(type, [...selectedCourses, ...newCourses]);
      return;
    }

    const newCourses = selectedCourses.filter((course) => !fetchedCourseIds.includes(course.id));
    form.setValue(type, newCourses);
  }

  function handleAllIsChecked() {
    return fetchedCourses.every((course) => selectedCourses.map((course) => course.id).includes(course.id));
  }

  const columns: Column<Course>[] = [
    {
      Header: courseListQuery.data?.results.length ? (
        <Checkbox
          onChange={toggleSelection}
          checked={courseListQuery.isLoading || courseListQuery.isRefetching ? false : handleAllIsChecked()}
          label={type === 'courses' ? __('Courses', 'tutor') : __('Bundles', 'tutor')}
          labelCss={styles.checkboxLabel}
        />
      ) : (
        '#'
      ),
      Cell: (item) => {
        return (
          <div css={styles.checkboxWrapper}>
            <Checkbox
              onChange={() => {
                const filteredItems = selectedCourses.filter((course) => course.id !== item.id);
                const isNewItem = filteredItems?.length === selectedCourses.length;

                if (isNewItem) {
                  form.setValue(type, [...filteredItems, item]);
                } else {
                  form.setValue(type, filteredItems);
                }
              }}
              checked={selectedCourses.map((course) => course.id).includes(item.id)}
            />
            <img src={item.image || coursePlaceholder} css={styles.thumbnail} alt={__('course item', 'tutor')} />
            <div css={styles.courseItem}>
              <div>{item.title}</div>
              <p>{item.author}</p>
            </div>
          </div>
        );
      },
    },
    {
      Header: __('Price', 'tutor'),
      Cell: (item) => {
        return (
          <div css={styles.price}>
            {item.plan_start_price ? (
              <span css={styles.startingFrom}>
                {
                  /* translators: %s is the starting price of the plan */
                  sprintf(__('Starting from %s', 'tutor'), item.plan_start_price)
                }
              </span>
            ) : (
              <>
                <span>{item.sale_price ? item.sale_price : item.regular_price}</span>
                {item.sale_price && <span css={styles.discountPrice}>{item.regular_price}</span>}
              </>
            )}
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
          data={(courseListQuery.data.results as Course[]) ?? []}
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
    object-fit: cover;
    object-position: center;
  `,
  checkboxLabel: css`
    ${typography.body()};
    color: ${colorTokens.text.primary};
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
  startingFrom: css`
    color: ${colorTokens.text.hints};
  `,
};
