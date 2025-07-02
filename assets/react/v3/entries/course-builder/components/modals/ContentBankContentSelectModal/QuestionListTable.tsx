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
import { type IconCollection } from '@TutorShared/icons/types';
import { useGetContentBankContents } from '@TutorShared/services/content-bank';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { type ContentBankContent, type QuizQuestionType } from '@TutorShared/utils/types';
import FilterFields from './FilterFields';
import SearchField from './SearchField';

type SortDirection = 'asc' | 'desc';

const QuestionListTable = () => {
  const { pageInfo, onPageChange, itemsPerPage, onFilterItems } = usePaginatedTable();
  const form = useFormContext<ContentSelectionForm>();
  const selectedCollection = form.watch('selectedCollection');
  const [questionTypes, setQuestionTypes] = useState<QuizQuestionType[]>([]);
  const [sortDirection, setSortDirection] = useState<SortDirection>('asc');
  const [orderDirection, setOrderDirection] = useState<SortDirection>('asc');

  const getContentsQuery = useGetContentBankContents({
    page: String(pageInfo.page),
    collection_id: selectedCollection?.ID ?? null,
    content_types: ['question'],
    context: 'quiz_builder',
    ...(orderDirection ? { order: orderDirection.toUpperCase() } : {}),
    ...(questionTypes.length ? { question_types: questionTypes } : {}),
    ...(pageInfo.filter.search ? { search: String(pageInfo.filter.search) } : {}),
  });

  const totalItems = getContentsQuery.data?.total_record || 0;
  const totalPages = Number(getContentsQuery.data?.total_page || 0);
  const collectionName = selectedCollection?.post_title ?? __('All Contents', 'tutor');
  const selectedContents = form.watch('contents') || [];
  const selectedContentIds = selectedContents.map((content) => String(content.ID));
  const fetchedContents = useMemo(() => getContentsQuery.data?.data ?? [], [getContentsQuery.data]);

  const handleBack = () => {
    form.setValue('selectedCollection', null);
    onPageChange(1);
    onFilterItems({ search: '' });
  };

  const handleToggleSelection = (isChecked = false) => {
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

  const handleSortData = () => {
    setSortDirection((prevDirection) => (prevDirection === 'asc' ? 'desc' : 'asc'));
    return fetchedContents.sort((a, b) => {
      return sortDirection === 'asc'
        ? a.post_title.localeCompare(b.post_title)
        : b.post_title.localeCompare(a.post_title);
    });
  };

  const questionTypeOptions: {
    label: string;
    value: QuizQuestionType;
    icon: IconCollection;
    isPro: boolean;
  }[] = [
    {
      label: __('True/False', 'tutor'),
      value: 'true_false',
      icon: 'quizTrueFalse',
      isPro: false,
    },
    {
      label: __('Multiple Choice', 'tutor'),
      value: 'multiple_choice',
      icon: 'quizMultiChoice',
      isPro: false,
    },
    {
      label: __('Open Ended/Essay', 'tutor'),
      value: 'open_ended',
      icon: 'quizEssay',
      isPro: false,
    },
    {
      label: __('Fill in the Blanks', 'tutor'),
      value: 'fill_in_the_blank',
      icon: 'quizFillInTheBlanks',
      isPro: false,
    },
    {
      label: __('Short Answer', 'tutor'),
      value: 'short_answer',
      icon: 'quizShortAnswer',
      isPro: true,
    },
    {
      label: __('Matching', 'tutor'),
      value: 'matching',
      icon: 'quizImageMatching',
      isPro: true,
    },
    {
      label: __('Image Answering', 'tutor'),
      value: 'image_answering',
      icon: 'quizImageAnswer',
      isPro: true,
    },
    {
      label: __('Ordering', 'tutor'),
      value: 'ordering',
      icon: 'quizOrdering',
      isPro: true,
    },
  ];

  const columns: Column<ContentBankContent>[] = [
    {
      Header: totalItems ? (
        <Checkbox
          onChange={handleToggleSelection}
          checked={getContentsQuery.isLoading || getContentsQuery.isRefetching ? false : handleAllIsChecked()}
          label={__('Title', 'tutor')}
          labelCss={styles.tableTitle}
          isIndeterminate={fetchedContents.length > 0 && !handleAllIsChecked() && selectedContents.length > 0}
          aria-label={__('Select all questions', 'tutor')}
        />
      ) : (
        __('# Title', 'tutor')
      ),
      sortProperty: 'title',
      name: 'title',
      Cell: (item) => {
        const handleItemToggle = () => {
          const selectedContents = form.watch('contents') || [];
          const filteredContents = selectedContents.filter((content) => String(content.ID) !== String(item.ID));
          const isNewItem = filteredContents.length === selectedContents.length;

          if (isNewItem) {
            form.setValue('contents', [...filteredContents, item]);
          } else {
            form.setValue('contents', filteredContents);
          }
        };

        return (
          <div css={styles.checkboxWrapper}>
            <Checkbox
              onChange={handleItemToggle}
              checked={
                getContentsQuery.isLoading || getContentsQuery.isRefetching
                  ? false
                  : selectedContentIds.includes(String(item.ID))
              }
            />
            <div css={styles.checkboxLabel}>
              <Show when={questionTypeOptions.find((option) => option.value === item.question_type)?.icon}>
                <SVGIcon
                  name={
                    questionTypeOptions.find((option) => option.value === item.question_type)?.icon as IconCollection
                  }
                  height={24}
                  width={24}
                />
              </Show>
              <span>{item.post_title}</span>
            </div>
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
          <button
            css={styleUtils.backButton}
            onClick={handleBack}
            aria-label={__('Go back to collection list', 'tutor')}
            tabIndex={0}
          >
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
            type="question"
            onFilterChange={(values) => {
              setOrderDirection(values.order);
              setQuestionTypes(values.questionTypes || []);
            }}
          />
        </div>

        <div css={styles.tableWrapper}>
          <Table
            headerHeight={56}
            isBordered
            isRounded
            columns={columns}
            data={fetchedContents}
            itemsPerPage={itemsPerPage}
            loading={getContentsQuery.isFetching || getContentsQuery.isRefetching}
            querySortProperties={['title']}
            querySortDirections={{
              title: sortDirection,
            }}
            onSortClick={(sortProperty) => {
              if (sortProperty === 'title') {
                const sortedData = handleSortData();
                return sortedData;
              }
            }}
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

export default QuestionListTable;

const styles = {
  tableActions: css`
    width: 100%;
    display: grid;
    grid-template-columns: 212px 1fr;
    justify-content: space-between;
    gap: ${spacing[16]};
    padding: ${spacing[16]};

    [data-filter] {
      justify-self: end;
    }
  `,
  tableWrapper: css`
    margin: 0 ${spacing[16]} ${spacing[12]} ${spacing[16]};
    max-height: calc(100vh - 350px);
    overflow: auto;

    th {
      border-bottom: 1px solid ${colorTokens.stroke.divider};
    }

    [data-type] {
      text-align: right;
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
    ${typography.small('regular')};
    color: ${colorTokens.text.hints};
  `,
  checkboxLabel: css`
    ${styleUtils.display.flex()};
    align-items: center;
    gap: ${spacing[12]};
    ${typography.caption('medium')};
    color: ${colorTokens.text.primary};

    svg {
      flex-shrink: 0;
    }

    span {
      width: 100%;
      ${styleUtils.text.ellipsis(1)};
    }
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
