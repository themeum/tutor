import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';

import { type ImportExportHistory, useImportExportHistoryQuery } from '@ImportExport/services/import-export';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import { borderRadius, colorTokens, fontWeight, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import Table, { type Column } from '@TutorShared/molecules/Table';
import { styleUtils } from '@TutorShared/utils/style-utils';

const History = () => {
  const getImportExportHistoryQuery = useImportExportHistoryQuery();

  const history = getImportExportHistoryQuery.data || [];

  const renderImportExportLabel = (type: 'import' | 'export') => {
    return (
      <span css={styles.importExportLabel({ type })}>
        <SVGIcon name={type} width={16} height={16} />
        {type === 'import' ? __('Imported', 'tutor') : __('Exported', 'tutor')}
      </span>
    );
  };

  const itemType = (item: ImportExportHistory) => {
    if (item.option_name.includes('export')) {
      return 'export';
    }
    if (item.option_name.includes('import')) {
      return 'import';
    }
    return 'import';
  };

  const generateHistoryTitle = (item: ImportExportHistory) => {
    const completedContents = item.option_value.completed_contents || {};

    const contentTypeMap = {
      courses: __('Courses', 'tutor'),
      'course-bundle': __('Course Bundles', 'tutor'),
      settings: __('Settings', 'tutor'),
    };

    return Object.entries(completedContents)
      .filter(([, value]) => {
        if (!value) {
          return false;
        }

        // Skip empty arrays
        if (Array.isArray(value) && value.length === 0) {
          return false;
        }

        return true;
      })
      .map(([key, value]) => {
        const label = contentTypeMap[key as keyof typeof contentTypeMap];
        // Add count in parentheses if value is an array
        return value ? (Array.isArray(value) && value.length > 0 ? `${label} (${value.length})` : label) : '';
      })
      .join(', ');
  };

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
      Header: <span css={styles.tableHeader}>{__('Author', 'tutor')}</span>,
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

          td:nth-of-type(n + 3) {
            font-weight: ${fontWeight.regular};
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
  importExportLabel: ({ type }: { type: 'import' | 'export' }) => css`
    ${styleUtils.display.flex()}
    align-items: center;
    gap: ${spacing[4]};
    ${typography.small('medium')}

    color: ${type === 'import' ? colorTokens.text.brand : colorTokens.text.hints};

    svg {
      color: ${type === 'import' ? colorTokens.icon.brand : colorTokens.icon.default};
    }
  `,
  brandIcon: css`
    color: ${colorTokens.text.brand} !important;
  `,
};
