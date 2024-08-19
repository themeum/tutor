import { LoadingSection } from '@Atoms/LoadingSpinner';
import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { css } from '@emotion/react';
import { usePaginatedTable } from '@Hooks/usePaginatedTable';
import Paginator from '@Molecules/Paginator';
import Table, { Column } from '@Molecules/Table';
import coursePlaceholder from '@Images/common/course-placeholder.png';
import { __, sprintf } from '@wordpress/i18n';
import SearchField from './SearchField';
import Button from '@Atoms/Button';
import { Course, useCurseListQuery } from '@EnrollmentServices/enrollment';

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
            <img src={item.image || coursePlaceholder} css={styles.thumbnail} alt="course item" />
            <div css={styles.courseContent}>
              {item.total_course && (
                <div css={styles.bundleBadge}>{sprintf(__('%d Course Bundle', 'tutor'), item.total_course)}</div>
              )}
              <div css={styles.title}>{item.title}</div>
            </div>
          </div>
        );
      },
      width: 600,
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
              <span>{item.sale_price ? item.sale_price : item.regular_price}</span>
              {item.sale_price && <span css={styles.discountPrice}>{item.regular_price}</span>}
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
    display: flex;
    gap: ${spacing[4]};
    justify-content: end;
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
