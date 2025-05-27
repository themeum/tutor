import { css } from '@emotion/react';
import { __, sprintf } from '@wordpress/i18n';
import { useCallback, useState } from 'react';

import {
  type ImportExportHistory,
  useDeleteImportExportHistoryMutation,
  useImportExportHistoryQuery,
} from '@ImportExport/services/import-export';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import { borderRadius, colorTokens, fontWeight, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import Table, { type Column } from '@TutorShared/molecules/Table';
import ThreeDots, { ThreeDotsOption } from '@TutorShared/molecules/ThreeDots';
import { styleUtils } from '@TutorShared/utils/style-utils';

const History = () => {
  const [isThreeDotMenuOpen, setIsThreeDotMenuOpen] = useState(-1);
  const getImportExportHistoryQuery = useImportExportHistoryQuery();
  const deleteImportExportHistoryMutation = useDeleteImportExportHistoryMutation();

  const history = getImportExportHistoryQuery.data || [];

  const handleDeleteHistory = async (itemId: string) => {
    await deleteImportExportHistoryMutation.mutateAsync(itemId);
  };

  const renderImportExportLabel = useCallback((type: 'import' | 'export') => {
    return (
      <span css={styles.importExportLabel({ type })}>
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

  const formatItemCount = useCallback((count: number, singular: string, plural: string): string => {
    return sprintf(count === 1 ? singular : plural, count);
  }, []);

  const generateHistoryTitle = useCallback(
    (item: ImportExportHistory): string => {
      const completedContents = item.option_value.completed_contents || {};

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

      for (const [key, value] of Object.entries(completedContents)) {
        if (!value || (Array.isArray(value) && value.length === 0)) {
          continue;
        }

        const contentKey = key as keyof typeof contentTypeConfig;
        const config = contentTypeConfig[contentKey];

        if (!config) {
          continue;
        }

        if (contentKey === 'settings') {
          if ('label' in config) {
            formattedItems.push(config.label);
          }
          continue;
        }

        if (Array.isArray(value) && value.length > 0) {
          if ('singular' in config && 'plural' in config) {
            const itemText = formatItemCount(value.length, config.singular, config.plural);
            formattedItems.push(itemText);
          }
        } else if ('singular' in config && 'plural' in config) {
          const itemText = formatItemCount(1, config.singular, config.plural);
          formattedItems.push(itemText);
        }
      }

      return formattedItems.join(', ');
    },
    [formatItemCount],
  );

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
    {
      Cell: (item, index) => {
        return (
          <div css={styles.action}>
            <ThreeDots
              isOpen={isThreeDotMenuOpen === index}
              onClick={() => setIsThreeDotMenuOpen(isThreeDotMenuOpen === index ? -1 : index)}
              closePopover={() => setIsThreeDotMenuOpen(-1)}
              isInverse
              size="small"
              dotsOrientation="vertical"
            >
              <ThreeDotsOption
                isTrash
                text={__('Delete', 'tutor')}
                disabled={deleteImportExportHistoryMutation.isPending}
                onClick={async () => {
                  await handleDeleteHistory(item.option_id);
                  setIsThreeDotMenuOpen(-1);
                }}
                icon={<SVGIcon name="delete" width={16} height={16} />}
              />
            </ThreeDots>
          </div>
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
