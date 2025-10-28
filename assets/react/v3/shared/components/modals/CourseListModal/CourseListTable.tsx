import { css } from '@emotion/react';
import { __, sprintf } from '@wordpress/i18n';
import React, { useCallback, useMemo } from 'react';

import Checkbox from '@TutorShared/atoms/CheckBox';
import { LoadingSection } from '@TutorShared/atoms/LoadingSpinner';
import Paginator from '@TutorShared/molecules/Paginator';
import Table, { type Column } from '@TutorShared/molecules/Table';

import SearchField from '@TutorShared/components/modals/CourseListModal/SearchField';
import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import Show from '@TutorShared/controls/Show';
import { type FormWithGlobalErrorType } from '@TutorShared/hooks/useFormWithGlobalError';
import { usePaginatedTable } from '@TutorShared/hooks/usePaginatedTable';
import { type Bundle, type Course, useBundleListQuery, useCourseListQuery } from '@TutorShared/services/course';
import { styleUtils } from '@TutorShared/utils/style-utils';

import coursePlaceholder from '@SharedImages/course-placeholder.png';

type CourseType = 'courses' | 'course-bundle';

type CourseBundleCombined = Course & Bundle;

interface CourseListTableProps {
  form: FormWithGlobalErrorType<{
    courses: CourseBundleCombined[];
    'course-bundle': CourseBundleCombined[];
  }>;
  type?: CourseType;
}

const CourseListTable = ({ form, type = 'courses' }: CourseListTableProps) => {
  const { pageInfo, onPageChange, itemsPerPage, offset, onFilterItems } = usePaginatedTable();
  const selectedItems = useMemo(() => form.watch(type) || [], [form, type]);
  const selectedItemIds = useMemo(() => selectedItems.map((course) => course.id), [selectedItems]);
  const queryParams = useMemo(
    () => ({
      offset,
      limit: itemsPerPage,
      filter: pageInfo.filter,
      post_status: 'any',
      exclude: [],
    }),
    [offset, itemsPerPage, pageInfo.filter],
  );

  const courseListQuery = useCourseListQuery({
    params: queryParams,
    isEnabled: type === 'courses',
  });

  const bundleListQuery = useBundleListQuery({
    params: queryParams,
    isEnabled: type === 'course-bundle',
  });

  const fetchedItems = useMemo(
    () => (type === 'courses' ? (courseListQuery.data?.results ?? []) : (bundleListQuery.data?.results ?? [])),
    [type, courseListQuery.data?.results, bundleListQuery.data?.results],
  );
  const fetchedItemIds = useMemo(() => fetchedItems.map((course) => course.id), [fetchedItems]);
  const areAllItemsSelected = useMemo(
    () => fetchedItems.length > 0 && fetchedItems.every((course) => selectedItemIds.includes(course.id)),
    [fetchedItems, selectedItemIds],
  );

  const handleToggleSelection = useCallback(
    (isChecked: boolean) => {
      if (isChecked) {
        // Add all fetched items that aren't already selected
        const newItems = fetchedItems.filter((course) => !selectedItemIds.includes(course.id));
        form.setValue(type, [...selectedItems, ...(newItems as CourseBundleCombined[])]);
      } else {
        // Keep only items that aren't in the current view
        const newItems = selectedItems.filter((course) => !fetchedItemIds.includes(course.id));
        form.setValue(type, newItems);
      }
    },
    [fetchedItems, selectedItemIds, fetchedItemIds, selectedItems, form, type],
  );

  const handleItemToggle = useCallback(
    (item: CourseBundleCombined) => {
      const isSelected = selectedItemIds.includes(item.id);

      if (isSelected) {
        form.setValue(
          type,
          selectedItems.filter((course) => course.id !== item.id),
        );
      } else {
        form.setValue(type, [...selectedItems, item]);
      }
    },
    [selectedItemIds, selectedItems, form, type],
  );

  const columns: Column<CourseBundleCombined>[] = useMemo(
    () => [
      {
        Header: fetchedItems.length ? (
          <Checkbox
            onChange={handleToggleSelection}
            checked={!(courseListQuery.isLoading || courseListQuery.isRefetching) && areAllItemsSelected}
            label={__('Name', __TUTOR_TEXT_DOMAIN__)}
            labelCss={styles.checkboxLabel}
            aria-label={__('Select all items', __TUTOR_TEXT_DOMAIN__)}
          />
        ) : (
          '#'
        ),
        Cell: (item) => (
          <div css={styles.checkboxWrapper}>
            <Checkbox
              onChange={() => handleItemToggle(item)}
              checked={selectedItemIds.includes(item.id)}
              aria-label={`${__('Select', __TUTOR_TEXT_DOMAIN__)} ${item.title}`}
            />
            <div css={styles.courseItemWrapper}>
              <img
                src={item.image || coursePlaceholder}
                css={styles.thumbnail}
                alt={item.title || __('Course item', __TUTOR_TEXT_DOMAIN__)}
              />
              <div css={styles.title}>
                <div>{item.title}</div>
                <Show when={type === 'course-bundle' && item?.total_courses}>
                  <div>
                    {
                      /* translators: %d is the total number of courses */
                      sprintf(__('Total Courses: %d', __TUTOR_TEXT_DOMAIN__), item.total_courses || 0)
                    }
                  </div>
                </Show>
              </div>
            </div>
          </div>
        ),
      },
    ],
    [
      fetchedItems.length,
      handleToggleSelection,
      courseListQuery.isLoading,
      courseListQuery.isRefetching,
      areAllItemsSelected,
      handleItemToggle,
      selectedItemIds,
      type,
    ],
  );

  if (courseListQuery.isLoading || bundleListQuery.isLoading) {
    return <LoadingSection aria-label={__('Loading', __TUTOR_TEXT_DOMAIN__)} />;
  }

  if (!courseListQuery.data && !bundleListQuery.data) {
    return (
      <div css={styles.errorMessage} role="alert" aria-live="assertive">
        {__('Something went wrong', __TUTOR_TEXT_DOMAIN__)}
      </div>
    );
  }

  const totalItems =
    type === 'courses' ? (courseListQuery.data?.total_items ?? 0) : (bundleListQuery.data?.total_items ?? 0);

  return (
    <>
      <div css={styles.tableActions}>
        <SearchField onFilterItems={onFilterItems} />
      </div>

      <div css={styles.tableWrapper}>
        <Table
          columns={columns}
          data={fetchedItems as CourseBundleCombined[]}
          itemsPerPage={itemsPerPage}
          loading={courseListQuery.isFetching || courseListQuery.isRefetching}
        />
      </div>

      <div css={styles.paginatorWrapper}>
        <Paginator
          currentPage={pageInfo.page}
          onPageChange={onPageChange}
          totalItems={totalItems}
          itemsPerPage={itemsPerPage}
        />
      </div>
    </>
  );
};

export default React.memo(CourseListTable);

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

    div:is(:last-of-type):not(:only-of-type) {
      margin-top: ${spacing[4]};
      ${typography.small('medium')};
      color: ${colorTokens.text.hints};
    }
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
