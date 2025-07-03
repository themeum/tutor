import { css } from '@emotion/react';
import { __, _n, sprintf } from '@wordpress/i18n';
import { useMemo, useState } from 'react';
import { useFormContext } from 'react-hook-form';

import { type ContentSelectionForm } from '@CourseBuilderComponents/modals/ContentBankContentSelectModal';
import Checkbox from '@TutorShared/atoms/CheckBox';
import { LoadingSection } from '@TutorShared/atoms/LoadingSpinner';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import Paginator from '@TutorShared/molecules/Paginator';
import Table, { type Column } from '@TutorShared/molecules/Table';

import { colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import Show from '@TutorShared/controls/Show';
import { usePaginatedTable } from '@TutorShared/hooks/usePaginatedTable';
import { CONTENT_BANK_POST_TYPE_MAP, useGetContentBankContents } from '@TutorShared/services/content-bank';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { type ContentBankContent } from '@TutorShared/utils/types';
import FilterFields from './FilterFields';
import SearchField from './SearchField';

const ContentListTable = () => {
  const { pageInfo, onPageChange, itemsPerPage, onFilterItems } = usePaginatedTable();
  const form = useFormContext<ContentSelectionForm>();
  const selectedCollection = form.watch('selectedCollection');
  const [contentTypes, setContentTypes] = useState<('lesson' | 'assignment')[]>([]);
  const getContentsQuery = useGetContentBankContents({
    page: String(pageInfo.page),
    collection_id: selectedCollection?.ID ?? null,
    content_types: contentTypes.length ? contentTypes : ['lesson', 'assignment'],
    ...(pageInfo.filter.search ? { search: String(pageInfo.filter.search) } : {}),
  });

  const totalItems = getContentsQuery.data?.total_record || 0;
  const totalPages = Number(getContentsQuery.data?.total_page || 0);
  const collectionName = selectedCollection?.post_title ?? __('All Contents', 'tutor');
  const selectedContents = form.watch('contents') || [];
  const selectedContentIds = selectedContents.map((content) => String(content.ID));
  const fetchedContents = useMemo(() => getContentsQuery.data?.data ?? [], [getContentsQuery.data]);

  const onBack = () => {
    form.setValue('selectedCollection', null);
    onPageChange(1);
    onFilterItems({ search: '' });
  };

  const toggleSelection = (isChecked = false) => {
    if (isChecked) {
      const newContents = fetchedContents.filter((content) => !selectedContentIds.includes(String(content.ID)));
      form.setValue('contents', [...selectedContents, ...newContents]);
      return;
    }

    const newContents = selectedContents.filter(
      (content) => !fetchedContents.map((content) => String(content.ID)).includes(String(content.ID)),
    );
    form.setValue('contents', newContents);
  };

  const handleAllIsChecked = () => {
    return (
      fetchedContents.length > 0 && fetchedContents.every((content) => selectedContentIds.includes(String(content.ID)))
    );
  };

  const columns: Column<ContentBankContent>[] = [
    {
      Header: totalItems ? (
        <Checkbox
          onChange={toggleSelection}
          checked={getContentsQuery.isLoading || getContentsQuery.isRefetching ? false : handleAllIsChecked()}
          label={__('Title', 'tutor')}
          labelCss={styles.tableTitle}
          isIndeterminate={fetchedContents.length > 0 && !handleAllIsChecked() && selectedContents.length > 0}
          aria-label={__('Select all contents', 'tutor')}
        />
      ) : (
        __('# Title', 'tutor')
      ),
      Cell: (item) => {
        return (
          <div css={styles.checkboxWrapper}>
            <Checkbox
              onChange={() => {
                const selectedContents = form.watch('contents') || [];

                const filteredContents = selectedContents.filter((content) => String(content.ID) !== String(item.ID));
                const isNewItem = filteredContents.length === selectedContents.length;

                if (isNewItem) {
                  form.setValue('contents', [...filteredContents, item]);
                } else {
                  form.setValue('contents', filteredContents);
                }
              }}
              checked={
                getContentsQuery.isLoading || getContentsQuery.isRefetching
                  ? false
                  : selectedContentIds.includes(String(item.ID))
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
    <>
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

          <FilterFields
            type="lesson_assignment"
            onFilterChange={(values) => {
              setContentTypes(values.contentTypes as ('lesson' | 'assignment')[]);
            }}
          />
        </div>

        <div css={styles.tableWrapper}>
          <Table
            headerHeight={48}
            isBordered={false}
            columns={columns}
            data={fetchedContents}
            itemsPerPage={itemsPerPage}
            loading={getContentsQuery.isFetching || getContentsQuery.isRefetching}
            rowStyle={styles.tableRow}
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
    </>
  );
};

export default ContentListTable;

const styles = {
  tableActions: css`
    width: 100%;
    display: grid;
    grid-template-columns: 212px 1fr;
    justify-content: space-between;
    gap: ${spacing[16]};
    padding: ${spacing[20]};

    div:last-of-type {
      justify-self: end;
    }
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

    td {
      padding: ${spacing[12]} ${spacing[20]};
    }
  `,
  tableRow: css`
    border-bottom: 1px solid ${colorTokens.border.tertiary};
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
