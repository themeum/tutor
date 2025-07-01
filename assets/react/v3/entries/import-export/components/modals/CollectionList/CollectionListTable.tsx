import { css } from '@emotion/react';
import { __, _n, sprintf } from '@wordpress/i18n';
import React, { useCallback, useMemo } from 'react';

import { LoadingSection } from '@TutorShared/atoms/LoadingSpinner';
import Paginator from '@TutorShared/molecules/Paginator';
import Table, { type Column } from '@TutorShared/molecules/Table';

import SearchField from '@ImportExport/components/modals/CollectionList/SearchField';
import Checkbox from '@TutorShared/atoms/CheckBox';
import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import Show from '@TutorShared/controls/Show';
import { type FormWithGlobalErrorType } from '@TutorShared/hooks/useFormWithGlobalError';
import { usePaginatedTable } from '@TutorShared/hooks/usePaginatedTable';
import { useGetCollectionsPaginatedQuery } from '@TutorShared/services/content-bank';
import { type Course } from '@TutorShared/services/course';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { type Collection, type CollectionContentType } from '@TutorShared/utils/types';

interface BulkSelectionFormData {
  courses: Course[];
  'course-bundle': Course[];
  collections: Collection[];
}

interface CourseListTableProps {
  form: FormWithGlobalErrorType<BulkSelectionFormData>;
}

const CollectionListTable = ({ form }: CourseListTableProps) => {
  const { pageInfo, onPageChange, itemsPerPage, onFilterItems } = usePaginatedTable();
  const selectedItems = useMemo(() => form.watch('collections') || [], [form]);
  const selectedItemIds = useMemo(() => selectedItems.map((item) => String(item.ID)), [selectedItems]);

  const getCollectionListQuery = useGetCollectionsPaginatedQuery({
    page: pageInfo.page,
    per_page: itemsPerPage,
    ...(pageInfo.filter.search ? { search: String(pageInfo.filter.search) } : {}),
  });

  const fetchedItems = useMemo(() => getCollectionListQuery.data?.data ?? [], [getCollectionListQuery.data]);
  const fetchedItemIds = useMemo(() => fetchedItems.map((item) => String(item.ID)), [fetchedItems]);
  const totalItems = getCollectionListQuery.data?.total_record ?? 0;
  const totalPages = Number(getCollectionListQuery.data?.total_page ?? 0);

  const areAllItemsSelected = useMemo(
    () => fetchedItems.length > 0 && fetchedItems.every((item) => selectedItemIds.includes(String(item.ID))),
    [fetchedItems, selectedItemIds],
  );

  const handleToggleSelection = useCallback(
    (isChecked: boolean) => {
      if (isChecked) {
        // Add all fetched items that aren't already selected
        const newItems = fetchedItems.filter((course) => !selectedItemIds.includes(String(course.ID)));
        form.setValue('collections', [...selectedItems, ...newItems]);
      } else {
        // Keep only items that aren't in the current view
        const newItems = selectedItems.filter((course) => !fetchedItemIds.includes(String(course.ID)));
        form.setValue('collections', newItems);
      }
    },
    [fetchedItems, selectedItemIds, fetchedItemIds, selectedItems, form],
  );

  const handleItemToggle = useCallback(
    (item: Collection) => {
      const isSelected = selectedItemIds.includes(String(item.ID));

      if (isSelected) {
        form.setValue(
          'collections',
          selectedItems.filter((collection) => String(collection.ID) !== String(item.ID)),
        );
      } else {
        form.setValue('collections', [...selectedItems, item]);
      }
    },
    [selectedItemIds, selectedItems, form],
  );

  const columns: Column<Collection>[] = useMemo(
    () => [
      {
        Header: totalItems ? (
          <div css={styles.tableHeader}>
            <Checkbox
              onChange={handleToggleSelection}
              checked={
                getCollectionListQuery.isLoading || getCollectionListQuery.isRefetching ? false : areAllItemsSelected
              }
              label={__('Collection Name', 'tutor')}
              labelCss={styles.tableTitle}
              isIndeterminate={fetchedItems.length > 0 && !areAllItemsSelected && selectedItems.length > 0}
              aria-label={__('Select all collections', 'tutor')}
            />

            <span css={styles.tableTitle}>{__('Items', 'tutor')}</span>
          </div>
        ) : (
          <div css={styles.tableHeader}>
            <span css={styles.tableTitle}>{__('Collection Name', 'tutor')}</span>
            <span css={styles.tableTitle}>{__('Items', 'tutor')}</span>
          </div>
        ),
        Cell: (item) => {
          const totalLessons = Number(item.count_stats.lesson) || 0;
          const totalAssignments = Number(item.count_stats.assignment) || 0;
          const totalQuestions = Number(item.count_stats.question) || 0;
          const totalItems = Number(item.count_stats.total) || 0;

          return (
            <button
              css={styles.collectionItemWrapper}
              aria-label={
                /* translators: %s is the collection title */
                sprintf(__('Select collection: %s', 'tutor'), item.post_title)
              }
              tabIndex={0}
            >
              <div css={styles.rowWrapper}>
                <Checkbox
                  checked={selectedItemIds.includes(String(item.ID))}
                  onChange={() => {
                    handleItemToggle(item);
                  }}
                  aria-label={__('Select this collection', 'tutor')}
                />
                <div css={styles.title}>
                  <div data-collection-title>{item.post_title}</div>
                  <Show when={(totalItems ?? 0) > 0}>
                    <div>
                      {
                        /* translators: %d is the total number of contents */
                        sprintf(_n('%d Item', '%d Items', totalItems, 'tutor'), totalItems)
                      }
                    </div>
                  </Show>
                </div>
              </div>
              <Show when={totalItems > 0}>
                <div css={styles.contentsWrapper}>
                  <Show when={totalLessons > 0}>
                    <span css={styles.contentBadge({ type: 'cb-lesson' })}>
                      {
                        /* translators: %d is the number of lessons */
                        sprintf(_n('%d Lesson', '%d Lessons', totalLessons, 'tutor'), totalLessons)
                      }
                    </span>
                  </Show>
                  <Show when={totalAssignments > 0}>
                    <span css={styles.contentBadge({ type: 'cb-assignment' })}>
                      {
                        /* translators: %d is the number of assignments */
                        sprintf(_n('%d Assignment', '%d Assignments', totalAssignments, 'tutor'), totalAssignments)
                      }
                    </span>
                  </Show>

                  <span css={styles.contentBadge({ type: 'cb-question' })}>
                    {
                      /* translators: %d is the number of questions */
                      sprintf(_n('%d Question', '%d Questions', totalQuestions, 'tutor'), totalQuestions)
                    }
                  </span>
                </div>
              </Show>
            </button>
          );
        },
      },
    ],
    [
      areAllItemsSelected,
      fetchedItems.length,
      getCollectionListQuery.isLoading,
      getCollectionListQuery.isRefetching,
      handleItemToggle,
      handleToggleSelection,
      selectedItemIds,
      selectedItems.length,
      totalItems,
    ],
  );

  if (getCollectionListQuery.isLoading) {
    return <LoadingSection aria-label={__('Loading', 'tutor')} />;
  }

  if (!getCollectionListQuery.data) {
    return (
      <div css={styles.errorMessage} role="alert" aria-live="assertive">
        {__('Something went wrong', 'tutor')}
      </div>
    );
  }

  return (
    <>
      <SearchField onFilterItems={onFilterItems} />

      <div
        css={styles.tableWrapper({
          isLoading: getCollectionListQuery.isFetching || getCollectionListQuery.isRefetching,
          hasPagination: totalPages > 1,
        })}
      >
        <Table
          columns={columns}
          data={fetchedItems}
          isBordered
          isRounded
          headerHeight={46}
          itemsPerPage={itemsPerPage}
          loading={getCollectionListQuery.isFetching || getCollectionListQuery.isRefetching}
        />
      </div>

      <Show when={totalPages > 1}>
        <div css={styles.paginatorWrapper}>
          <Paginator
            currentPage={pageInfo.page}
            onPageChange={onPageChange}
            totalItems={totalItems}
            itemsPerPage={itemsPerPage}
          />
        </div>
      </Show>
    </>
  );
};

export default React.memo(CollectionListTable);

const styles = {
  tableWrapper: ({ isLoading = false, hasPagination = false }) => css`
    max-height: calc(100vh - 350px);
    overflow: auto;

    ${!hasPagination &&
    css`
      padding-bottom: ${spacing[12]};
    `}

    ${!isLoading &&
    css`
      td {
        padding: 0;

        &:hover {
          [data-collection-title] {
            color: ${colorTokens.text.brand};
          }
        }
      }
    `}
  `,
  tableHeader: css`
    ${styleUtils.display.flex()};
    align-items: center;
    justify-content: space-between;
    gap: ${spacing[16]};
    width: 100%;
  `,
  tableTitle: css`
    ${typography.small('medium')};
    color: ${colorTokens.text.primary};
  `,
  paginatorWrapper: css`
    margin: ${spacing[20]} ${spacing[16]};
  `,
  collectionItemWrapper: css`
    ${styleUtils.resetButton};
    min-height: 60px;
    width: 100%;
    height: 100%;
    padding: ${spacing[12]} ${spacing[24]} ${spacing[12]} ${spacing[16]};
    ${styleUtils.display.flex()};
    justify-content: space-between;
    align-items: center;
    gap: ${spacing[16]};
  `,
  contentsWrapper: css`
    ${styleUtils.display.flex()};
    align-items: center;
    gap: ${spacing[4]};
    flex-shrink: 0;
  `,
  contentBadge: ({ type }: { type: CollectionContentType }) => css`
    ${typography.tiny('medium')};
    padding: ${spacing[4]} ${spacing[8]};
    border-radius: ${borderRadius[4]};
    white-space: nowrap;

    ${type === 'cb-lesson' &&
    css`
      background-color: #e8f4fd;
      color: ${colorTokens.icon.brand};
    `}

    ${type === 'cb-assignment' &&
    css`
      background-color: #e6f8f1;
      color: ${colorTokens.icon.processing};
    `}

    ${type === 'cb-question' &&
    css`
      background-color: #fff5e6;
      color: #ff7c02;
    `}
  `,
  title: css`
    ${typography.small()};
    color: ${colorTokens.text.primary};
    ${styleUtils.text.ellipsis(2)};
    text-wrap: pretty;

    div:is(:last-of-type):not(:only-of-type) {
      ${typography.small()};
      color: ${colorTokens.text.hints};
    }
  `,
  rowWrapper: css`
    ${styleUtils.display.flex('row')};
    align-items: center;
    gap: ${spacing[8]};
  `,
  errorMessage: css`
    height: 100px;
    display: flex;
    align-items: center;
    justify-content: center;
  `,
};
