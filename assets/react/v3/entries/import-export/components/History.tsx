import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { format } from 'date-fns';
import { useState } from 'react';

import { useImportExportHistoryQuery, type ImportExportHistory } from '@ImportExport/services/import-export';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import { DateFormats } from '@TutorShared/config/constants';
import { borderRadius, colorTokens, fontWeight, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import Show from '@TutorShared/controls/Show';
import Table, { type Column } from '@TutorShared/molecules/Table';
import ThreeDots from '@TutorShared/molecules/ThreeDots';
import { styleUtils } from '@TutorShared/utils/style-utils';

const History = () => {
  const [dateSortType, setDateSortType] = useState<'asc' | 'desc'>('asc');
  const [isThreeDotOpenIndex, setThreeDotOpenIndex] = useState<number>(-1);

  const getImportExportHistoryQuery = useImportExportHistoryQuery();

  console.log(getImportExportHistoryQuery);

  // @TODO: need to integrate with the API
  const history: ImportExportHistory[] = [
    {
      title: 'Global Settings',
      type: 'export',
      author: {
        user_id: 1,
        display_name: 'Bessie Cooper',
        user_email: 'bessie.cooper@example.com',
        avatar_url: 'https://example.com/avatar/bessie.jpg',
      },
      date: '2026-07-20T12:47:00Z',
    },
    {
      title: 'Global Settings',
      type: 'import',
      author: {
        user_id: 1,
        display_name: 'Bessie Cooper',
        user_email: 'bessie.cooper@example.com',
        avatar_url: 'https://example.com/avatar/bessie.jpg',
      },
      date: '2026-07-20T12:47:00Z',
    },
    {
      title: 'Global Settings',
      type: 'export',
      isSetting: true,
      isActive: true,
      author: {
        user_id: 1,
        display_name: 'Bessie Cooper',
        user_email: 'bessie.cooper@example.com',
        avatar_url: 'https://example.com/avatar/bessie.jpg',
      },
      date: '2026-07-20T12:47:00Z',
    },
    {
      title: 'Lessons',
      type: 'import',
      author: {
        user_id: 1,
        display_name: 'Bessie Cooper',
        user_email: 'bessie.cooper@example.com',
        avatar_url: 'https://example.com/avatar/bessie.jpg',
      },
      date: '2026-07-20T12:47:00Z',
    },
    {
      title: 'Courses (All)',
      type: 'export',
      author: {
        user_id: 1,
        display_name: 'Bessie Cooper',
        user_email: 'bessie.cooper@example.com',
        avatar_url: 'https://example.com/avatar/bessie.jpg',
      },
      date: '2026-07-20T12:47:00Z',
    },
    {
      title: 'Questions',
      type: 'export',
      author: {
        user_id: 1,
        display_name: 'Bessie Cooper',
        user_email: 'bessie.cooper@example.com',
        avatar_url: 'https://example.com/avatar/bessie.jpg',
      },
      date: '2026-07-20T12:47:00Z',
    },
  ];

  const renderImportExportLabel = (type: 'import' | 'export') => {
    return (
      <span css={styles.importExportLabel({ type })}>
        <SVGIcon name={type} width={16} height={16} />
        {type === 'import' ? __('Imported', 'tutor') : __('Exported', 'tutor')}
      </span>
    );
  };

  const columns: Column<ImportExportHistory>[] = [
    {
      Header: <span css={styles.tableHeader}>{__('Title', 'tutor')}</span>,
      Cell: (item) => {
        return (
          <div css={styles.historyTitle}>
            {item.title}
            {item.isActive && <span css={styles.activeTag}>{__('Active', 'tutor')}</span>}
          </div>
        );
      },
    },
    {
      Header: <span css={styles.tableHeader}>{__('Type', 'tutor')}</span>,
      Cell: (item) => {
        return renderImportExportLabel(item.type);
      },
    },
    {
      Header: <span css={styles.tableHeader}>{__('Author', 'tutor')}</span>,
      Cell: (item) => {
        return <div css={styles.historyTitle}>{item.author.display_name}</div>;
      },
    },
    {
      Header: (
        <div css={[styles.dateWithSort, styles.tableHeader]}>
          <span>{__('Date', 'tutor')}</span>
          <button
            css={styleUtils.actionButton}
            onClick={() => setDateSortType((prev) => (prev === 'asc' ? 'desc' : 'asc'))}
          >
            {dateSortType === 'asc' ? (
              <SVGIcon name="sort" width={16} height={16} />
            ) : (
              <SVGIcon name="sort" width={16} height={16} />
            )}
          </button>
        </div>
      ),
      Cell: (item) => {
        return <div>{format(new Date(item.date), DateFormats.monthDayYearHoursMinutes)}</div>;
      },
    },
    {
      Header: <></>,
      Cell: (item, index) => (
        <div css={styles.action}>
          <ThreeDots
            isOpen={isThreeDotOpenIndex === index}
            onClick={() => setThreeDotOpenIndex(index)}
            closePopover={() => setThreeDotOpenIndex(-1)}
            dotsOrientation="vertical"
            maxWidth={'170px'}
            isInverse
            arrowPosition="top"
            hideArrow={false}
            size="small"
          >
            {/* @TODO: need to integrate with the API */}
            <Show when={item.isSetting}>
              <ThreeDots.Option
                size="small"
                text={__('Apply Settings', 'tutor')}
                icon={<SVGIcon style={styles.brandIcon} name="materialCheck" width={24} height={24} />}
                onClick={() => {}}
              />
            </Show>
            <ThreeDots.Option
              text={__('Download', 'tutor')}
              icon={<SVGIcon style={styles.brandIcon} name="download" width={24} height={24} />}
              onClick={() => {}}
            />
            <ThreeDots.Option
              text={__('Delete', 'tutor')}
              icon={<SVGIcon name="delete" width={24} height={24} />}
              onClick={() => {}}
              isTrash
            />
          </ThreeDots>
        </div>
      ),
    },
  ];

  return (
    <div css={styles.wrapper}>
      <div css={styles.title}>{__('History', 'tutor')}</div>

      <div css={styles.history}>
        <Table columns={columns} data={history} isRounded isBordered />
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

          td:nth-child(n + 3) {
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
