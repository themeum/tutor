import { css } from '@emotion/react';
import { __, sprintf } from '@wordpress/i18n';
import { useCallback } from 'react';

import SVGIcon from '@TutorShared/atoms/SVGIcon';
import Table, { type Column } from '@TutorShared/molecules/Table';

import {
  type ImportExportHistory,
  useDeleteImportExportHistoryMutation,
  useImportExportHistoryQuery,
} from '@ImportExport/services/import-export';
import Button from '@TutorShared/atoms/Button';
import { borderRadius, colorTokens, fontWeight, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import { styleUtils } from '@TutorShared/utils/style-utils';

const History = () => {
  const getImportExportHistoryQuery = useImportExportHistoryQuery();
  const deleteImportExportHistoryMutation = useDeleteImportExportHistoryMutation();

  const history = getImportExportHistoryQuery.data || [];

  const handleDeleteHistory = async (itemId: string) => {
    await deleteImportExportHistoryMutation.mutateAsync(itemId);
  };

  const renderImportExportLabel = useCallback((type: 'import' | 'export') => {
    return (
      <span css={styles.importExportLabel}>
        <SVGIcon name={type} width={16} height={16} />
        {type === 'import' ? __('Imported', 'tutor') : __('Exported', 'tutor')}
      </span>
    );
  }, []);

  const itemType = useCallback((item: ImportExportHistory) => {
    if (item.option_name.includes('export')) {
      return 'export';
    }
    if (item.option_name.includes('import')) {
      return 'import';
    }
    return 'import';
  }, []);

  const formatItemCount = (count: number, singular: string, plural: string): string => {
    return sprintf(count === 1 ? singular : plural, count);
  };

  const generateHistoryTitle = (item: ImportExportHistory): string => {
    const completedContents = item.option_value.completed_contents;

    if (!completedContents) {
      return '';
    }

    const contentTypeConfig = {
      courses: {
        singular: __('Course (%d)', 'tutor'),
        plural: __('Courses (%d)', 'tutor'),
      },
      'course-bundle': {
        singular: __('Bundle (%d)', 'tutor'),
        plural: __('Bundles (%d)', 'tutor'),
      },
      settings: {
        label: __('Settings', 'tutor'),
      },
    } as const;

    const formattedItems: string[] = [];

    const successfulCourses = completedContents.courses?.success || [];
    if (successfulCourses.length > 0) {
      const coursesText = formatItemCount(
        successfulCourses.length,
        contentTypeConfig.courses.singular,
        contentTypeConfig.courses.plural,
      );
      formattedItems.push(coursesText);
    }

    const successfulBundles = completedContents['course-bundle']?.success || [];
    if (successfulBundles.length > 0) {
      const bundlesText = formatItemCount(
        successfulBundles.length,
        contentTypeConfig['course-bundle'].singular,
        contentTypeConfig['course-bundle'].plural,
      );
      formattedItems.push(bundlesText);
    }

    if (completedContents.settings === true) {
      formattedItems.push(contentTypeConfig.settings.label);
    }

    return formattedItems.join(', ');
  };

  if (history.length === 0) {
    return null;
  }

  const columns: Column<ImportExportHistory>[] = [
    {
      Header: <span css={styles.tableHeader}>{__('Title', 'tutor')}</span>,
      Cell: (item) => {
        return <div css={styles.historyTitle}>{generateHistoryTitle(item)}</div>;
      },
    },
    {
      Header: <span css={styles.tableHeader}>{__('Type', 'tutor')}</span>,
      Cell: (item) => {
        return renderImportExportLabel(itemType(item));
      },
    },
    {
      Header: <span css={styles.tableHeader}>{__('User', 'tutor')}</span>,
      Cell: (item) => {
        return <div css={styles.historyTitle}>{item.option_value.user_name}</div>;
      },
    },
    {
      Header: <span css={styles.tableHeader}>{__('Date', 'tutor')}</span>,
      Cell: (item) => {
        return <div css={styles.historyTitle}>{item.option_value.created_at}</div>;
      },
    },
    {
      Cell: (item) => {
        return (
          <Button
            data-delete-history
            size="small"
            variant="primary"
            isOutlined
            onClick={() => handleDeleteHistory(item.option_id)}
          >
            {__('Delete', 'tutor')}
          </Button>
        );
      },
    },
  ];

  return (
    <div css={styles.wrapper}>
      <div css={styles.title}>{__('History', 'tutor')}</div>

      <div css={styles.history}>
        <Table
          headerHeight={44}
          loading={getImportExportHistoryQuery.isLoading}
          columns={columns}
          data={history}
          isRounded
          isBordered
        />
      </div>
    </div>
  );
};

export default History;

const styles = {
  wrapper: css`
    ${styleUtils.display.flex('column')}
    gap: ${spacing[12]};
  `,
  title: css`
    ${typography.body()}
    color: ${colorTokens.text.subdued};
  `,
  history: css`
    border-radius: ${borderRadius[6]};
    overflow: hidden;

    table {
      tbody {
        tr {
          background-color: ${colorTokens.background.white};
          ${typography.small('medium')}
          [data-delete-history] {
            opacity: 0;
            transition: opacity 0.2s ease-in-out;
          }

          td:nth-of-type(n + 3) {
            font-weight: ${fontWeight.regular};
          }

          &:hover {
            background-color: ${colorTokens.background.white};
            [data-delete-history] {
              opacity: 1;
            }
          }
        }
      }
    }
  `,
  tableHeader: css`
    ${typography.small()}
    color: ${colorTokens.text.subdued};
  `,
  historyTitle: css`
    ${typography.small('medium')}
    ${styleUtils.display.flex()}
    align-items: center;
    gap: ${spacing[4]};
    min-width: 80px;
  `,
  activeTag: css`
    ${typography.tiny('medium')}
    background-color: ${colorTokens.color.success[40]};
    border-radius: ${borderRadius[4]};
    color: ${colorTokens.text.success};
    padding: ${spacing[4]} ${spacing[8]};
  `,
  dateWithSort: css`
    ${styleUtils.display.flex()}
    align-items: center;
    gap: ${spacing[4]};
  `,
  action: css`
    ${styleUtils.display.flex()}
    align-items: center;
    justify-content: flex-end;
  `,
  threeDot: css`
    width: 24px;
    height: 24px;
  `,
  importExportLabel: css`
    ${styleUtils.display.flex()}
    align-items: center;
    gap: ${spacing[4]};
    ${typography.small('medium')}
    color: ${colorTokens.text.hints};

    svg {
      color: ${colorTokens.icon.default};
    }
  `,
};
