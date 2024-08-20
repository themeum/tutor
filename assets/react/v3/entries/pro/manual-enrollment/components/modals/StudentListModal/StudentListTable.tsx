import Checkbox from '@Atoms/CheckBox';
import { LoadingSection } from '@Atoms/LoadingSpinner';
import { borderRadius, colorPalate, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { css } from '@emotion/react';
import { usePaginatedTable } from '@Hooks/usePaginatedTable';
import Paginator from '@Molecules/Paginator';
import Table, { Column } from '@Molecules/Table';
import { UseFormReturn } from 'react-hook-form';
import SearchField from './SearchField';
import { Enrollment, Student, useStudentListQuery } from '@EnrollmentServices/enrollment';
const { __ } = wp.i18n;

interface StudentListTableProps {
  form: UseFormReturn<Enrollment, any, undefined>;
}

const StudentListTable = ({ form }: StudentListTableProps) => {
  const course = form.watch('course');
  const courseList = form.watch('students') || [];

  const { pageInfo, onPageChange, itemsPerPage, offset, onFilterItems } = usePaginatedTable({
    updateQueryParams: false,
  });

  const studentListQuery = useStudentListQuery({
    offset,
    limit: itemsPerPage,
    filter: pageInfo.filter,
    object_id: course?.id,
  });

  function toggleSelection(isChecked = false) {
    form.setValue('students', isChecked ? (studentListQuery.data?.results as Student[]) : []);
  }

  function handleAllIsChecked() {
    return (
      courseList.length === studentListQuery.data?.results.length &&
      courseList?.every((item) => studentListQuery.data?.results?.map((result) => result.ID).includes(item.ID))
    );
  }

  const columns: Column<Student>[] = [
    {
      Header: studentListQuery.data?.results.length ? (
        <Checkbox
          onChange={toggleSelection}
          checked={handleAllIsChecked()}
          label={__('Name', 'tutor')}
          labelCss={styles.checkboxLabel}
        />
      ) : (
        __('#', 'tutor')
      ),
      Cell: (item) => {
        return (
          <div css={styles.checkboxWrapper}>
            <Checkbox
              onChange={() => {
                const filteredItems = courseList.filter((course) => course.ID !== item.ID);
                const isNewItem = filteredItems?.length === courseList.length;

                if (isNewItem) {
                  form.setValue('students', [...filteredItems, item]);
                } else {
                  form.setValue('students', filteredItems);
                }
              }}
              checked={courseList.map((course) => course.ID).includes(item.ID)}
            />
            <div css={styles.studentInfo}>
              <img src={item.avatar_url} css={styles.thumbnail} alt={__('Student item', 'tutor')} />
              <div>
                <div css={styles.title}>{item.display_name}</div>
                <p css={styles.subTitle}>{item.user_email}</p>
              </div>
            </div>
          </div>
        );
      },
      width: 600,
    },
  ];

  if (studentListQuery.isLoading) {
    return <LoadingSection />;
  }

  if (!studentListQuery.data) {
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
          data={studentListQuery.data.results ?? []}
          itemsPerPage={itemsPerPage}
          loading={studentListQuery.isFetching || studentListQuery.isRefetching}
        />
      </div>

      <div css={styles.paginatorWrapper}>
        <Paginator
          currentPage={pageInfo.page}
          onPageChange={onPageChange}
          totalItems={studentListQuery.data.total_items}
          itemsPerPage={itemsPerPage}
        />
      </div>
    </>
  );
};

export default StudentListTable;

const styles = {
  tableActions: css`
    padding: ${spacing[20]};
  `,
  tableWrapper: css`
    max-height: calc(100vh - 350px);
    overflow: auto;
  `,
  paginatorWrapper: css`
    margin: ${spacing[20]} ${spacing[16]};
  `,
  checkboxWrapper: css`
    display: flex;
    align-items: center;
    gap: ${spacing[12]};
  `,
  studentInfo: css`
    display: flex;
    align-items: center;
    gap: ${spacing[8]};
  `,
  thumbnail: css`
    width: 34px;
    height: 34px;
    border-radius: ${borderRadius.circle};
  `,
  title: css`
    ${typography.body('medium')};
    color: ${colorTokens.text.primary};
  `,
  subTitle: css`
    ${typography.small()};
    color: ${colorTokens.text.subdued};
    margin: 0px;
  `,
  checkboxLabel: css`
    ${typography.body()};
    color: ${colorPalate.text.neutral};
  `,
  errorMessage: css`
    height: 100px;
    display: flex;
    align-items: center;
    justify-content: center;
  `,
};
