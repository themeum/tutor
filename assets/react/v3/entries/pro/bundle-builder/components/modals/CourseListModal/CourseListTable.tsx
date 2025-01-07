import SVGIcon from '@/v3/shared/atoms/SVGIcon';
import { styleUtils } from '@/v3/shared/utils/style-utils';
import { type Course, useCurseListQuery } from '@BundleBuilderServices/bundle';
import coursePlaceholder from '@SharedImages/course-placeholder.png';
import Button from '@TutorShared/atoms/Button';
import { LoadingSection } from '@TutorShared/atoms/LoadingSpinner';
import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import Show from '@TutorShared/controls/Show';
import { usePaginatedTable } from '@TutorShared/hooks/usePaginatedTable';
import Paginator from '@TutorShared/molecules/Paginator';
import Table, { type Column } from '@TutorShared/molecules/Table';
import { css } from '@emotion/react';
import { __, sprintf } from '@wordpress/i18n';
import SearchField from './SearchField';

interface CourseListTableProps {
  onSelectClick: (item: Course) => void;
  selectedCourseIds: number[];
}

const CourseListTable = ({ onSelectClick, selectedCourseIds }: CourseListTableProps) => {
  const { pageInfo, onPageChange, itemsPerPage, offset, onFilterItems } = usePaginatedTable({
    updateQueryParams: false,
  });

  const courseListQuery = useCurseListQuery({
    offset,
    limit: itemsPerPage,
    filter: pageInfo.filter,
  });

  const columns: Column<Course>[] = [
    {
      Header: <div css={styles.tableLabel}>{__('Name', 'tutor')}</div>,
      Cell: (item) => {
        const isSelected = selectedCourseIds.includes(item.id);

        return (
          <div css={styles.courseItemWrapper}>
            <img src={item.image || coursePlaceholder} css={styles.thumbnail} alt={__('Course item', 'tutor')} />
            <div>
              {item.total_course && (
                <div css={styles.bundleBadge}>{sprintf(__('%d Course Bundle', 'tutor'), item.total_course)}</div>
              )}
              {item.plan_start_price && (
                <div css={styles.subscriptionBadge}>
                  <SVGIcon name="dollar-recurring" width={16} height={16} />
                  {__('Subscription', 'tutor')}
                </div>
              )}
              <div css={styles.title({ isSelected })}>
                {item.title}
                {isSelected && <span css={styles.selectedBadge}>{__('Already Selected', 'tutor')}</span>}
              </div>
            </div>
          </div>
        );
      },
    },
    {
      Header: <div css={styles.tablePriceLabel}>{__('Price', 'tutor')}</div>,
      Cell: (item) => {
        const isSelected = selectedCourseIds.includes(item.id);

        return (
          <div css={styles.priceWrapper}>
            <Show when={!isSelected}>
              <div data-button>
                <Button size="small" onClick={() => onSelectClick(item)}>
                  {__('Select', 'tutor')}
                </Button>
              </div>
            </Show>
            <div css={styles.price} data-price={!isSelected}>
              <Show when={item.is_purchasable} fallback={__('Free', 'tutor')}>
                <span>{item.sale_price ? item.sale_price : item.regular_price}</span>
                {item.sale_price && <span css={styles.discountPrice}>{item.regular_price}</span>}
                {/* <Show
                  when={item.plan_start_price}
                  fallback={
                    <>
                      <span>{item.sale_price ? item.sale_price : item.regular_price}</span>
                      {item.sale_price && <span css={styles.discountPrice}>{item.regular_price}</span>}
                    </>
                  }
                >
                  <span css={styles.startingFrom}>
                    {sprintf(__('Starting from %s', 'tutor'), item.plan_start_price)}
                  </span>
                </Show> */}
              </Show>
            </div>
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

    tr {
      &:hover {
        [data-button] {
          display: block;
        }
        [data-price='true'] {
          display: none;
        }
      }
    }
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
  `,
  title: ({ isSelected = false }) => css`
    ${typography.caption()};
    color: ${colorTokens.text.primary};
    ${styleUtils.text.ellipsis(2)};
    text-wrap: pretty;

    ${isSelected &&
    css`
      color: ${colorTokens.text.disable};
    `}
  `,
  thumbnail: css`
    width: 48px;
    height: 48px;
    border-radius: ${borderRadius[4]};
    object-fit: cover;
    object-position: center;
  `,
  priceWrapper: css`
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
