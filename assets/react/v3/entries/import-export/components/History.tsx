import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useCallback, useState } from 'react';

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
  const [deletingItemId, setDeletingItemId] = useState<string | null>(null);

  const history = getImportExportHistoryQuery.data || [];

  const handleDeleteHistory = async (itemId: string) => {
    setDeletingItemId(itemId);
    try {
      await deleteImportExportHistoryMutation.mutateAsync(itemId);
    } finally {
      setDeletingItemId(null);
    }
  };

  const renderImportExportLabel = useCallback((type: 'import' | 'export') => {
    return (
      <span css={styles.importExportLabel}>
        <SVGIcon name={type} width={16} height={16} />
        {type === 'import' ? __('Imported', 'tutor') : __('Exported', 'tutor')}
      </span>
    );
  }, []);

  if (!getImportExportHistoryQuery.isLoading && history.length === 0) {
    return null;
  }

  const columns: Column<ImportExportHistory>[] = [
    {
      Header: <span css={styles.tableHeader}>{__('Title', 'tutor')}</span>,
      Cell: (item) => {
        return <div css={styles.historyTitle}>{item.title}</div>;
      },
    },
    {
      Header: <span css={styles.tableHeader}>{__('Type', 'tutor')}</span>,
      Cell: (item) => {
        return renderImportExportLabel(item.type);
      },
    },
    {
      Header: <span css={styles.tableHeader}>{__('User', 'tutor')}</span>,
      Cell: (item) => {
        return <div css={styles.historyTitle}>{item.user_name}</div>;
      },
    },
    {
      Header: <span css={styles.tableHeader}>{__('Date', 'tutor')}</span>,
      Cell: (item) => {
        return <div css={styles.historyTitle}>{item.created_at}</div>;
      },
    },
    {
      Cell: (item) => {
        const isCurrentItemDeleting = deletingItemId === item.id;

        return (
          <div css={styles.action}>
            <Button
              data-delete-history
              size="small"
              variant="secondary"
              isOutlined
              loading={isCurrentItemDeleting}
              onClick={() => handleDeleteHistory(item.id)}
            >
              {__('Delete', 'tutor')}
            </Button>
          </div>
        );
      },
    },
  ];

  return (
    <div css={styles.wrapper}>
      <div css={styles.title}>{__('History', 'tutor')}</div>

      <div css={styles.history({ deletingItemId })}>
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
  history: ({ deletingItemId = null }: { deletingItemId?: string | null }) => css`
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

          ${deletingItemId ? `[data-delete-history="${deletingItemId}"] { opacity: 1; }` : ''}

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
