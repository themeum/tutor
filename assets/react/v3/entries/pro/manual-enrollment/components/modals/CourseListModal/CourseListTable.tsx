import Button from '@Atoms/Button';
import { LoadingSection } from '@Atoms/LoadingSpinner';
import SVGIcon from '@Atoms/SVGIcon';
import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { type Course, useCurseListQuery } from '@EnrollmentServices/enrollment';
import { usePaginatedTable } from '@Hooks/usePaginatedTable';
import Paginator from '@Molecules/Paginator';
import Table, { type Column } from '@Molecules/Table';
import { css } from '@emotion/react';
import { __, sprintf } from '@wordpress/i18n';
import SearchField from './SearchField';
import coursePlaceholder from '@Images/course-placeholder.png';
import Show from '@Controls/Show';

interface CourseListTableProps {
  onSelectClick: (item: Course) => void;
}

const CourseListTable = ({ onSelectClick }: CourseListTableProps) => {
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
        return (
          <div css={styles.courseItemWrapper}>
            <img src={item.image || coursePlaceholder} css={styles.thumbnail} alt={__('Course item', 'tutor')} />
            <div css={styles.courseContent}>
              {item.total_course && (
                <div css={styles.bundleBadge}>{sprintf(__('%d Course Bundle', 'tutor'), item.total_course)}</div>
              )}
              {/* {item.plan_start_price && (
                <div css={styles.subscriptionBadge}>
                  <SVGIcon name="dollar-recurring" width={16} height={16} />
                  {__('Subscription', 'tutor')}
                </div>
              )} */}
              <div css={styles.title}>{item.title}</div>
            </div>
          </div>
        );
      },
    },
    {
      Header: <div css={styles.tablePriceLabel}>{__('Price', 'tutor')}</div>,
      Cell: (item) => {
        return (
          <div css={styles.priceWrapper}>
            <div data-button>
              <Button size="small" onClick={() => onSelectClick(item)}>
                {__('Select', 'tutor')}
              </Button>
            </div>
            <div css={styles.price} data-price>
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
        &:hover {
          [data-button] {
            display: block;
          }
          [data-price] {
            display: none;
          }
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
  courseContent: css``,
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
  title: css`
    ${typography.caption()};
    color: ${colorTokens.text.primary};
  `,
  thumbnail: css`
    width: 48px;
    height: 48px;
    border-radius: ${borderRadius[4]};
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
