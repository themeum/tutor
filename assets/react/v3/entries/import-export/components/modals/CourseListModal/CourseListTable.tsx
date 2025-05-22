import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { type UseFormReturn } from 'react-hook-form';

import SearchField from '@ImportExport/components/modals/CourseListModal/SearchField';
import Checkbox from '@TutorShared/atoms/CheckBox';
import { LoadingSection } from '@TutorShared/atoms/LoadingSpinner';
import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import { usePaginatedTable } from '@TutorShared/hooks/usePaginatedTable';
import Paginator from '@TutorShared/molecules/Paginator';
import Table, { type Column } from '@TutorShared/molecules/Table';
import { type Course, useBundleListQuery, useCourseListQuery } from '@TutorShared/services/course';
import { styleUtils } from '@TutorShared/utils/style-utils';

import coursePlaceholder from '@SharedImages/course-placeholder.png';

interface CourseListTableProps {
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  form: UseFormReturn<{ courses: Course[]; 'course-bundle': Course[] }, any, undefined>;
  type?: 'courses' | 'course-bundle';
}

const CourseListTable = ({ form, type }: CourseListTableProps) => {
  const selectedItems = form.watch(type as 'courses' | 'course-bundle') || [];
  const { pageInfo, onPageChange, itemsPerPage, offset, onFilterItems } = usePaginatedTable();

  const courseListQuery = useCourseListQuery({
    params: {
      offset,
      limit: itemsPerPage,
      filter: pageInfo.filter,
      exclude: [],
    },
    isEnabled: type === 'courses',
  });

  const bundleListQuery = useBundleListQuery({
    params: {
      offset,
      limit: itemsPerPage,
      filter: pageInfo.filter,
      exclude: [],
    },
    isEnabled: type === 'course-bundle',
  });

  const fetchedCourses =
    type === 'courses'
      ? ((courseListQuery.data?.results ?? []) as Course[])
      : ((bundleListQuery.data?.results ?? []) as Course[]);

  const toggleSelection = (isChecked = false) => {
    const selectedItemIds = selectedItems.map((course) => course.id);
    const fetchedItemIds = fetchedCourses.map((course) => course.id);

    if (isChecked) {
      const newItems = fetchedCourses.filter((course) => !selectedItemIds.includes(course.id));
      form.setValue(type as 'courses' | 'course-bundle', [...selectedItems, ...newItems]);
      return;
    }

    const newItems = selectedItems.filter((course) => !fetchedItemIds.includes(course.id));
    form.setValue(type as 'courses' | 'course-bundle', newItems);
  };

  const handleAllIsChecked = () => {
    return fetchedCourses.every((course) => selectedItems.map((course) => course.id).includes(course.id));
  };

  const columns: Column<Course>[] = [
    {
      Header: courseListQuery.data?.results.length ? (
        <Checkbox
          onChange={toggleSelection}
          checked={courseListQuery.isLoading || courseListQuery.isRefetching ? false : handleAllIsChecked()}
          label={__('Name', 'tutor-pro')}
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
                const filteredItems = selectedItems.filter((course) => course.id !== item.id);
                const isNewItem = filteredItems?.length === selectedItems.length;

                if (isNewItem) {
                  form.setValue(type as 'courses' | 'course-bundle', [...filteredItems, item]);
                } else {
                  form.setValue(type as 'courses' | 'course-bundle', filteredItems);
                }
              }}
              checked={selectedItems.map((course) => course.id).includes(item.id)}
            />
            <div css={styles.courseItemWrapper}>
              <img src={item.image || coursePlaceholder} css={styles.thumbnail} alt={__('Course item', 'tutor-pro')} />
              <div css={styles.title}>{item.title}</div>
            </div>
          </div>
        );
      },
    },
  ];

  if (courseListQuery.isLoading || bundleListQuery.isLoading) {
    return <LoadingSection />;
  }

  if (!courseListQuery.data && !bundleListQuery.data) {
    return <div css={styles.errorMessage}>{__('Something went wrong', 'tutor-pro')}</div>;
  }

  return (
    <>
      <div css={styles.tableActions}>
        <SearchField onFilterItems={onFilterItems} />
      </div>

      <div css={styles.tableWrapper}>
        <Table
          columns={columns}
          data={(fetchedCourses as Course[]) ?? []}
          itemsPerPage={itemsPerPage}
          loading={courseListQuery.isFetching || courseListQuery.isRefetching}
        />
      </div>

      <div css={styles.paginatorWrapper}>
        <Paginator
          currentPage={pageInfo.page}
          onPageChange={onPageChange}
          totalItems={(type === 'courses' ? courseListQuery.data?.total_items : bundleListQuery.data?.total_items) ?? 0}
          itemsPerPage={itemsPerPage}
        />
      </div>
    </>
  );
};

export default CourseListTable;

const styles = {
  tableLabel: css`
    text-align: left;
  `,
  tablePriceLabel: css`
    text-align: right;
  `,
  tableActions: css`
    padding: ${spacing[20]};
  `,
  tableWrapper: css`
    max-height: calc(100vh - 350px);
    overflow: auto;
  `,
  checkboxWrapper: css`
    display: flex;
    align-items: center;
    gap: ${spacing[12]};
  `,
  checkboxLabel: css`
    ${typography.body()};
    color: ${colorTokens.text.primary};
  `,
  paginatorWrapper: css`
    margin: ${spacing[20]} ${spacing[16]};
  `,
  courseItemWrapper: css`
    display: flex;
    align-items: center;
    gap: ${spacing[16]};
  `,
  bundleBadge: css`
    ${typography.tiny()};
    display: inline-block;
    padding: 0px ${spacing[8]};
    background-color: #9342e7;
    color: ${colorTokens.text.white};
    border-radius: ${borderRadius[40]};
  `,
  subscriptionBadge: css`
    ${typography.tiny()};
    display: flex;
    align-items: center;
    width: fit-content;
    padding: 0px ${spacing[6]} 0px ${spacing[4]};
    background-color: ${colorTokens.color.warning[90]};
    color: ${colorTokens.text.white};
    border-radius: ${borderRadius[40]};
  `,
  selectedBadge: css`
    margin-left: ${spacing[4]};
    ${typography.tiny()};
    padding: ${spacing[4]} ${spacing[8]};
    background-color: ${colorTokens.background.disable};
    color: ${colorTokens.text.title};
    border-radius: ${borderRadius[2]};
    white-space: nowrap;
  `,
  title: css`
    ${typography.caption()};
    color: ${colorTokens.text.primary};
    ${styleUtils.text.ellipsis(2)};
    text-wrap: pretty;
  `,
  thumbnail: css`
    width: 76px;
    height: 48px;
    border-radius: ${borderRadius[4]};
    object-fit: cover;
    object-position: center;
  `,
  priceWrapper: css`
    min-width: 200px;
    text-align: right;
    [data-button] {
      display: none;
    }
  `,
  price: css`
    ${typography.caption()};
    display: flex;
    gap: ${spacing[4]};
    justify-content: end;
  `,
  startingFrom: css`
    color: ${colorTokens.text.hints};
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
