import { css } from '@emotion/react';
import { __, _n, sprintf } from '@wordpress/i18n';
import React, { useMemo } from 'react';
import { useFormContext } from 'react-hook-form';

import { LoadingSection } from '@TutorShared/atoms/LoadingSpinner';
import Paginator from '@TutorShared/molecules/Paginator';
import Table, { type Column } from '@TutorShared/molecules/Table';

import { type ContentSelectionForm } from '@CourseBuilderComponents/modals/ContentBankContentSelectModal';
import SearchField from '@CourseBuilderComponents/modals/ContentBankContentSelectModal/SearchField';
import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import Show from '@TutorShared/controls/Show';
import { usePaginatedTable } from '@TutorShared/hooks/usePaginatedTable';
import { useGetCollectionsPaginatedQuery } from '@TutorShared/services/content-bank';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { type Collection, type CollectionContentType } from '@TutorShared/utils/types';

interface CollectionListTableProps {
  type: 'lesson_assignment' | 'question';
}

const CollectionListTable = ({
  type,
}: CollectionListTableProps & {
  type: 'lesson_assignment' | 'question';
}) => {
  const { pageInfo, onPageChange, itemsPerPage, onFilterItems } = usePaginatedTable();
  const form = useFormContext<ContentSelectionForm>();

  const getCollectionListQuery = useGetCollectionsPaginatedQuery({
    page: pageInfo.page,
    per_page: itemsPerPage,
    ...(pageInfo.filter.search ? { search: String(pageInfo.filter.search) } : {}),
    hide_empty: 1,
    context: type === 'question' ? 'quiz_builder' : 'topic',
  });

  const fetchedItems = useMemo(() => getCollectionListQuery.data?.data ?? [], [getCollectionListQuery.data]);
  const totalItems = getCollectionListQuery.data?.total_record ?? 0;
  const totalPages = Number(getCollectionListQuery.data?.total_page ?? 0);

  const columns: Column<Collection>[] = useMemo(
    () => [
      {
        Cell: (item) => {
          const totalLessons = Number(item.count_stats.lesson) || 0;
          const totalAssignments = Number(item.count_stats.assignment) || 0;
          const totalQuestions = Number(item.count_stats.question) || 0;
          const totalItems = type === 'lesson_assignment' ? totalLessons + totalAssignments : totalQuestions;

          return (
            <button
              css={styles.collectionItemWrapper}
              onClick={() => form.setValue('selectedCollection', item)}
              onKeyDown={(event) => {
                if (event.key === 'Enter' || event.key === ' ') {
                  event.preventDefault();
                  form.setValue('selectedCollection', item);
                }
              }}
              aria-label={
                /* translators: %s is the collection title */
                sprintf(__('Select collection: %s', 'tutor'), item.post_title)
              }
              tabIndex={0}
              disabled={totalItems === 0}
            >
              <div css={styles.title}>
                <div data-collection-title aria-disabled={totalItems === 0}>
                  {item.post_title}
                </div>
                <Show when={(totalItems ?? 0) > 0}>
                  <div>
                    {
                      /* translators: %d is the total number of contents */
                      sprintf(_n('%d Item', '%d Items', totalItems, 'tutor'), totalItems)
                    }
                  </div>
                </Show>
              </div>
              <Show
                when={totalItems > 0}
                fallback={
                  <span css={styles.title} aria-disabled={totalItems === 0}>
                    {__('No Items', 'tutor')}
                  </span>
                }
              >
                <div css={styles.contentsWrapper}>
                  <Show when={type === 'lesson_assignment'}>
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
                  </Show>
                  <Show when={totalQuestions > 0 && type === 'question'}>
                    <span css={styles.contentBadge({ type: 'cb-question' })}>
                      {
                        /* translators: %d is the number of questions */
                        sprintf(_n('%d Question', '%d Questions', totalQuestions, 'tutor'), totalQuestions)
                      }
                    </span>
                  </Show>
                </div>
              </Show>
            </button>
          );
        },
      },
    ],
    [form, type],
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
      <div css={styles.tableActions}>
        <SearchField onFilterItems={onFilterItems} />
      </div>

      <div
        css={styles.tableWrapper({
          isLoading: getCollectionListQuery.isFetching || getCollectionListQuery.isRefetching,
          hasPagination: totalPages > 1,
          hasData: fetchedItems.length > 0,
        })}
      >
        <Table
          noHeader
          columns={columns}
          data={fetchedItems}
          itemsPerPage={itemsPerPage}
          loading={getCollectionListQuery.isFetching || getCollectionListQuery.isRefetching}
          colors={{
            bodyRowHover: colorTokens.background.white,
          }}
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
  tableActions: css`
    padding: ${spacing[20]};
  `,
  tableWrapper: ({ isLoading = false, hasPagination = false, hasData = true }) => css`
    max-height: calc(100vh - 350px);
    overflow: auto;
    border-top: 1px solid ${colorTokens.stroke.divider};

    ${!hasPagination &&
    css`
      padding-bottom: ${spacing[12]};
    `}

    ${!isLoading &&
    css`
      tr {
        &:last-of-type {
          border-bottom: none;
        }
      }
      td {
        padding: 0;

        [aria-disabled='true'] {
          color: ${colorTokens.text.disable};
        }

        &:hover {
          [data-collection-title] {
            &:not([aria-disabled='true']) {
              color: ${colorTokens.text.brand};
            }
          }
        }
      }
    `}

    ${!hasData &&
    css`
      td {
        padding: ${spacing[20]};
      }
    `}
  `,
  paginatorWrapper: css`
    margin: ${spacing[20]} ${spacing[16]};
  `,
  collectionItemWrapper: css`
    ${styleUtils.resetButton};
    min-height: 60px;
    width: 100%;
    height: 100%;
    padding: ${spacing[12]} ${spacing[24]} ${spacing[12]} ${spacing[20]};
    ${styleUtils.display.flex('row')};
    justify-content: space-between;
    align-items: center;
    gap: ${spacing[16]};

    &:hover {
      background-color: ${colorTokens.background.hover};
    }

    &:disabled {
      cursor: not-allowed;
      pointer-events: none;
      background-color: ${colorTokens.background.disable};
    }
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
  errorMessage: css`
    height: 100px;
    display: flex;
    align-items: center;
    justify-content: center;
  `,
};
