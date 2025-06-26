import { css } from '@emotion/react';
import { __, _n, sprintf } from '@wordpress/i18n';
import { useMemo } from 'react';
import { useFormContext } from 'react-hook-form';

import Checkbox from '@TutorShared/atoms/CheckBox';
import { LoadingSection } from '@TutorShared/atoms/LoadingSpinner';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import { type ContentSelectionForm } from '@TutorShared/components/modals/ContentBankContentSelectModal';
import Paginator from '@TutorShared/molecules/Paginator';
import Table, { type Column } from '@TutorShared/molecules/Table';

import { colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import Show from '@TutorShared/controls/Show';
import { usePaginatedTable } from '@TutorShared/hooks/usePaginatedTable';
import { CONTENT_BANK_POST_TYPE_MAP, useGetContentBankContents } from '@TutorShared/services/content-bank';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { type ContentBankContent } from '@TutorShared/utils/types';
import SearchField from './SearchField';

const ContentListTable = () => {
  const { pageInfo, onPageChange, itemsPerPage, onFilterItems } = usePaginatedTable();
  const form = useFormContext<ContentSelectionForm>();
  const selectedCollection = form.watch('selectedCollection');
  const getContentsQuery = useGetContentBankContents({
    page: String(pageInfo.page),
    collection_id: selectedCollection?.ID ?? null,
    ...(pageInfo.filter.search ? { search: String(pageInfo.filter.search) } : {}),
  });

  const totalItems = getContentsQuery.data?.total_record || 0;
  const totalPages = Number(getContentsQuery.data?.total_page || 0);
  const collectionName = selectedCollection?.post_title ?? __('All Contents', 'tutor');
  const selectedLessons = form.watch('lessons') || [];
  const selectedAssignments = form.watch('assignments') || [];
  const fetchedContents = useMemo(() => getContentsQuery.data?.data ?? [], [getContentsQuery.data]);

  const onBack = () => {
    form.setValue('selectedCollection', null);
    onPageChange(1);
    onFilterItems({ search: '' });
  };

  const toggleSelection = (isChecked = false) => {
    if (isChecked) {
      const newLessons = fetchedContents.filter(
        (content) => content.post_type === 'cb_lesson' && !selectedLessons.includes(String(content.ID)),
      );
      const newAssignments = fetchedContents.filter(
        (content) => content.post_type === 'cb_assignment' && !selectedAssignments.includes(String(content.ID)),
      );
      form.setValue('lessons', [...selectedLessons, ...newLessons.map((lesson) => String(lesson.ID))]);
      form.setValue('assignments', [
        ...selectedAssignments,
        ...newAssignments.map((assignment) => String(assignment.ID)),
      ]);
      return;
    }

    const newLessons = selectedLessons.filter(
      (lesson) => !fetchedContents.map((content) => String(content.ID)).includes(lesson),
    );
    const newAssignments = selectedAssignments.filter(
      (assignment) => !fetchedContents.map((content) => String(content.ID)).includes(assignment),
    );
    form.setValue('lessons', newLessons);
    form.setValue('assignments', newAssignments);
  };

  function handleAllIsChecked() {
    const selectedContentIds = [...selectedLessons, ...selectedAssignments];
    return fetchedContents.every((content) => selectedContentIds.includes(String(content.ID)));
  }

  const columns: Column<ContentBankContent>[] = [
    {
      Header: totalItems ? (
        <Checkbox
          onChange={toggleSelection}
          checked={getContentsQuery.isLoading || getContentsQuery.isRefetching ? false : handleAllIsChecked()}
          label={__('Title', 'tutor')}
          labelCss={styles.tableTitle}
        />
      ) : (
        __('# Title', 'tutor')
      ),
      Cell: (item) => {
        return (
          <div css={styles.checkboxWrapper}>
            <Checkbox
              onChange={() => {
                const selectedLessons = form.watch('lessons') || [];
                const selectedAssignments = form.watch('assignments') || [];
                const isLesson = item.post_type === 'cb_lesson';
                const isAssignment = item.post_type === 'cb_assignment';

                if (isLesson) {
                  const filteredLessons = selectedLessons.filter((lesson) => lesson !== String(item.ID));
                  const isNewItem = filteredLessons.length === selectedLessons.length;

                  if (isNewItem) {
                    form.setValue('lessons', [...filteredLessons, String(item.ID)]);
                  } else {
                    form.setValue('lessons', filteredLessons);
                  }
                } else if (isAssignment) {
                  const filteredAssignments = selectedAssignments.filter(
                    (assignment) => assignment !== String(item.ID),
                  );
                  const isNewItem = filteredAssignments.length === selectedAssignments.length;

                  if (isNewItem) {
                    form.setValue('assignments', [...filteredAssignments, String(item.ID)]);
                  } else {
                    form.setValue('assignments', filteredAssignments);
                  }
                }
              }}
              checked={
                (item.post_type === 'cb_lesson' && selectedLessons.includes(String(item.ID))) ||
                (item.post_type === 'cb_assignment' && selectedAssignments.includes(String(item.ID)))
              }
            />
            <div>{item.post_title}</div>
          </div>
        );
      },
    },
    {
      Header: (
        <div data-type css={styles.tableTitle}>
          {__('Type', 'tutor')}
        </div>
      ),
      Cell: (item) => {
        return (
          <div css={styles.type}>
            <span css={styles.checkboxLabel}>{CONTENT_BANK_POST_TYPE_MAP[item.post_type]}</span>
          </div>
        );
      },
    },
  ];

  if (getContentsQuery.isLoading) {
    return <LoadingSection />;
  }

  if (!getContentsQuery.data) {
    return <div css={styles.errorMessage}>{__('Something went wrong', 'tutor')}</div>;
  }

  return (
    <div css={styles.wrapper}>
      <div css={styles.headerWithAction}>
        <button css={styleUtils.backButton} onClick={onBack} aria-label={__('Go back to collection list', 'tutor')}>
          <SVGIcon name="arrowLeft" height={24} width={24} />
        </button>
        <div css={styles.headerTitle}>
          <span>{collectionName} </span>
          <Show when={totalItems}>
            <span>
              (
              {
                /* translators: %d is the total number of contents */
                sprintf(_n('%d Item', '%d Items', totalItems, 'tutor'), totalItems)
              }
              )
            </span>
          </Show>
        </div>
      </div>

      <div css={styles.tableActions}>
        <SearchField onFilterItems={onFilterItems} />
      </div>

      <div css={styles.tableWrapper}>
        <Table
          headerHeight={48}
          isBordered={false}
          columns={columns}
          data={fetchedContents}
          itemsPerPage={itemsPerPage}
          loading={getContentsQuery.isFetching || getContentsQuery.isRefetching}
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
    </div>
  );
};

export default ContentListTable;

const styles = {
  tableActions: css`
    padding: ${spacing[20]};
  `,
  tableWrapper: css`
    max-height: calc(100vh - 350px);
    overflow: auto;

    th {
      border-bottom: 1px solid ${colorTokens.stroke.divider};
    }

    [data-type] {
      text-align: right;
    }
  `,
  paginatorWrapper: css`
    margin: ${spacing[20]} ${spacing[16]};
  `,
  headerWithAction: css`
    ${styleUtils.display.flex('row')};
    align-items: center;
    gap: ${spacing[8]};
    padding: 0 ${spacing[16]} ${spacing[12]} ${spacing[16]};
    border-bottom: 1px solid ${colorTokens.stroke.divider};
  `,
  headerTitle: css`
    ${typography.body('medium')};
    span {
      &:first-of-type {
        color: ${colorTokens.text.title};
      }
    }

    span:last-of-type:not(:only-of-type) {
      ${typography.tiny('medium')};
      color: ${colorTokens.text.hints};
    }
  `,
  wrapper: css`
    ${styleUtils.display.flex('column')};
  `,
  tableTitle: css`
    ${typography.body('medium')};
    color: ${colorTokens.text.primary};
  `,
  checkboxLabel: css`
    ${typography.caption()};
    color: ${colorTokens.text.primary};
  `,
  checkboxWrapper: css`
    display: flex;
    align-items: center;
    gap: ${spacing[12]};
  `,
  type: css`
    display: flex;
    gap: ${spacing[4]};
    justify-content: end;
  `,
  errorMessage: css`
    height: 100px;
    display: flex;
    align-items: center;
    justify-content: center;
  `,
};
