import { css } from '@emotion/react';
import { __, _n, sprintf } from '@wordpress/i18n';
import React, { useMemo } from 'react';
import { useFormContext } from 'react-hook-form';

import { LoadingSection } from '@TutorShared/atoms/LoadingSpinner';
import Paginator from '@TutorShared/molecules/Paginator';
import Table, { type Column } from '@TutorShared/molecules/Table';

import SearchField from '@ImportExport/components/modals/CourseListModal/SearchField';
import { type ContentSelectionForm } from '@TutorShared/components/modals/ContentBankContentSelectModal';
import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import Show from '@TutorShared/controls/Show';
import { usePaginatedTable } from '@TutorShared/hooks/usePaginatedTable';
import { useGetCollectionsPaginatedQuery } from '@TutorShared/services/content-bank';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { type Collection, type CollectionContentType } from '@TutorShared/utils/types';

const CollectionListTable = () => {
  const { pageInfo, onPageChange, itemsPerPage, onFilterItems } = usePaginatedTable();
  const form = useFormContext<ContentSelectionForm>();

  const collectionListQuery = useGetCollectionsPaginatedQuery({
    page: pageInfo.page,
    per_page: itemsPerPage,
    search: pageInfo.filter.search ? String(pageInfo.filter.search) : '',
  });

  const fetchedItems = useMemo(() => collectionListQuery.data?.data ?? [], [collectionListQuery.data]);

  const columns: Column<Collection>[] = useMemo(
    () => [
      {
        Cell: (item) => (
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
          >
            <div css={styles.title}>
              <div data-collection-title>{item.post_title}</div>
              <Show when={(item?.count_stats?.total ?? 0) > 0}>
                <div>
                  {
                    /* translators: %d is the total number of contents */
                    sprintf(_n('%d Item', '%d Items', item.count_stats.total, 'tutor'), item.count_stats.total)
                  }
                </div>
              </Show>
            </div>
            <Show when={item.count_stats.total > 0}>
              <div css={styles.contentsWrapper}>
                <Show when={item.count_stats.lesson > 0}>
                  <span css={styles.contentBadge({ type: 'lesson' })}>
                    {
                      /* translators: %d is the number of lessons */
                      sprintf(_n('%d Lesson', '%d Lessons', item.count_stats.lesson, 'tutor'), item.count_stats.lesson)
                    }
                  </span>
                </Show>
                <Show when={item.count_stats.assignment > 0}>
                  <span css={styles.contentBadge({ type: 'assignment' })}>
                    {
                      /* translators: %d is the number of assignments */
                      sprintf(
                        _n('%d Assignment', '%d Assignments', item.count_stats.assignment, 'tutor'),
                        item.count_stats.assignment,
                      )
                    }
                  </span>
                </Show>
                <Show when={item.count_stats.question > 0}>
                  <span css={styles.contentBadge({ type: 'question' })}>
                    {
                      /* translators: %d is the number of questions */
                      sprintf(
                        _n('%d Question', '%d Questions', item.count_stats.question, 'tutor'),
                        item.count_stats.question,
                      )
                    }
                  </span>
                </Show>
              </div>
            </Show>
          </button>
        ),
      },
    ],
    [form],
  );

  if (collectionListQuery.isLoading) {
    return <LoadingSection aria-label={__('Loading', 'tutor')} />;
  }

  if (!collectionListQuery.data) {
    return (
      <div css={styles.errorMessage} role="alert" aria-live="assertive">
        {__('Something went wrong', 'tutor')}
      </div>
    );
  }

  const totalItems = collectionListQuery.data?.total_record ?? 0;

  return (
    <>
      <div css={styles.tableActions}>
        <SearchField onFilterItems={onFilterItems} />
      </div>

      <div css={styles.tableWrapper({ isLoading: collectionListQuery.isFetching || collectionListQuery.isRefetching })}>
        <Table
          noHeader
          columns={columns}
          data={fetchedItems}
          itemsPerPage={itemsPerPage}
          loading={collectionListQuery.isFetching || collectionListQuery.isRefetching}
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

export default React.memo(CollectionListTable);

const styles = {
  tableActions: css`
    padding: ${spacing[20]};
  `,
  tableWrapper: ({ isLoading = false }) => css`
    max-height: calc(100vh - 350px);
    overflow: auto;
    border-top: 1px solid ${colorTokens.stroke.divider};

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

    ${type === 'lesson' &&
    css`
      background-color: #e8f4fd;
      color: ${colorTokens.icon.brand};
    `}

    ${type === 'assignment' &&
    css`
      background-color: #e6f8f1;
      color: ${colorTokens.icon.processing};
    `}

    ${type === 'question' &&
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
