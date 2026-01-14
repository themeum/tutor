import SVGIcon from '@TutorShared/atoms/SVGIcon';
import Skeleton from '@TutorShared/atoms/Skeleton';
import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { getRandom, range } from '@TutorShared/utils/util';
import { type SerializedStyles, css } from '@emotion/react';
import type { ReactNode } from 'react';

interface BaseColumn {
  name?: string;
  Header?: string | ReactNode;
  width?: string;
  css?: SerializedStyles;
  sortProperty?: string;
  headerColSpan?: number;
}

type ColumnWithAccessor<TableItem> = BaseColumn & {
  accessor: (item: TableItem, index: number) => string | number;
};

type ColumnWithCell<TableItem> = BaseColumn & {
  Cell: (item: TableItem, index: number) => ReactNode;
};

type ColumnWithAccessorAndCell<TableItem> = BaseColumn & {
  accessor: (item: TableItem, index: number) => string | number;
  Cell: (item: TableItem, index: number) => ReactNode;
};

export type Column<TableItem> =
  | ColumnWithAccessor<TableItem>
  | ColumnWithCell<TableItem>
  | ColumnWithAccessorAndCell<TableItem>;

export type Colors = {
  bodyRowDefault?: string;
  bodyRowHover?: string;
  bodyRowSelected?: string;
  bodyRowSelectedHover?: string;
};

export interface TableProps<TableItem, TSortProperties extends readonly string[] = string[]> {
  columns: Column<TableItem>[];
  data: TableItem[];
  entireHeader?: ReactNode | string | null;
  headerHeight?: number;
  noHeader?: boolean;
  colors?: Colors;
  isStriped?: boolean;
  isRounded?: boolean;
  stripedBySelectedIndex?: number[];
  isBordered?: boolean;
  loading?: boolean;
  itemsPerPage?: number;
  querySortProperties?: TSortProperties;
  querySortDirections?: { [K in TSortProperties[number]]?: 'asc' | 'desc' };
  onSortClick?: (sortProperty: string) => void;
  renderInLastRow?: ReactNode;
  rowStyle?: SerializedStyles;
  sortIcons?: {
    asc?: ReactNode;
    desc?: ReactNode;
  };
}

const defaultColors = {
  bodyRowSelected: colorTokens.background.active,
  bodyRowHover: colorTokens.background.hover,
};

const Table = <TableItem, TSortProperties extends readonly string[] = string[]>({
  columns,
  data,
  entireHeader = null,
  headerHeight = 60,
  noHeader = false,
  isStriped = false,
  isRounded = false,
  stripedBySelectedIndex = [],
  colors = {},
  isBordered = true,
  loading = false,
  itemsPerPage = 1,
  querySortProperties,
  querySortDirections = {},
  onSortClick,
  renderInLastRow,
  rowStyle,
  sortIcons = {
    asc: <SVGIcon name="sortASC" height={16} width={16} />,
    desc: <SVGIcon name="sortDESC" height={16} width={16} />,
  },
}: TableProps<TableItem, TSortProperties>) => {
  const renderRow = (index: number, content: (column: Column<TableItem>) => ReactNode) => {
    return (
      <tr
        key={index}
        css={[
          styles.tableRow({ isBordered, isStriped }),
          styles.bodyTr({ colors, isSelected: stripedBySelectedIndex.includes(index), isRounded }),
          rowStyle,
        ]}
      >
        {columns.map((column, columnIndex) => {
          return (
            <td key={columnIndex} css={[styles.td, { width: column.width }]}>
              {content(column)}
            </td>
          );
        })}
      </tr>
    );
  };

  const renderHeaderWithSortIcon = (column: Column<TableItem>) => {
    let icon = null;

    const sortProperty = column.sortProperty;

    if (!sortProperty) {
      return column.Header;
    }

    if (querySortProperties?.includes(sortProperty as TSortProperties[number])) {
      if (querySortDirections?.[sortProperty as TSortProperties[number]] === 'asc') {
        icon = sortIcons.asc;
      } else {
        icon = sortIcons.desc;
      }
    }

    return (
      <button type="button" css={styles.headerWithIcon} onClick={() => onSortClick?.(sortProperty)}>
        {column.Header}
        {icon && icon}
      </button>
    );
  };

  const renderTableHeader = () => {
    if (entireHeader) {
      return (
        <th css={styles.th} colSpan={columns.length}>
          {entireHeader}
        </th>
      );
    }

    return columns.map((column, index) => {
      if (column.Header !== null) {
        return (
          <th key={index} css={[styles.th, column.css, { width: column.width }]} colSpan={column.headerColSpan}>
            {renderHeaderWithSortIcon(column)}
          </th>
        );
      }
    });
  };

  const renderTableBody = () => {
    if (loading) {
      return range(itemsPerPage).map((index) =>
        renderRow(index, () => <Skeleton animation height={20} width={`${getRandom(40, 80)}%`} />),
      );
    }

    if (!data.length) {
      return (
        <tr css={styles.tableRow({ isBordered: false, isStriped: false })}>
          <td
            colSpan={columns.length}
            css={[
              styles.td,
              css`
                text-align: center;
              `,
            ]}
          >
            No Data!
          </td>
        </tr>
      );
    }

    const rows = data.map((item, index) => {
      return renderRow(index, (column) => {
        return 'Cell' in column ? column.Cell(item, index) : column.accessor(item, index);
      });
    });

    if (renderInLastRow) {
      renderInLastRow = (
        <tr key={rows.length}>
          <td css={styles.td}>{renderInLastRow}</td>
        </tr>
      );

      rows.push(renderInLastRow);
    }

    return rows;
  };

  return (
    <div css={styles.tableContainer({ isRounded })}>
      <table css={styles.table}>
        {!noHeader && (
          <thead>
            <tr css={[styles.tableRow({ isBordered, isStriped }), { height: headerHeight }]}>{renderTableHeader()}</tr>
          </thead>
        )}
        <tbody>{renderTableBody()}</tbody>
      </table>
    </div>
  );
};

export default Table;

const styles = {
  tableContainer: ({ isRounded }: { isRounded: boolean }) => css`
    display: block;
    width: 100%;
    overflow-x: auto;

    ${isRounded &&
    css`
      border: 1px solid ${colorTokens.stroke.divider};
      border-radius: ${borderRadius[6]};
    `}
  `,
  headerWithIcon: css`
    ${styleUtils.resetButton};
    ${typography.body()};
    color: ${colorTokens.text.subdued};
    display: flex;
    gap: ${spacing[8]};
    align-items: center;

    svg {
      color: ${colorTokens.text.primary};
    }
  `,
  table: css`
    width: 100%;
    border-collapse: collapse;
    border: none;
  `,
  tableRow: ({ isBordered, isStriped }: { isBordered: boolean; isStriped: boolean }) => css`
    ${isBordered &&
    css`
      border-bottom: 1px solid ${colorTokens.stroke.divider};
    `}

    ${isStriped &&
    css`
      &:nth-of-type(even) {
        background-color: ${colorTokens.background.active};
      }
    `}
  `,
  th: css`
    ${typography.body()};
    background-color: ${colorTokens.background.white};
    color: ${colorTokens.text.primary};
    padding: 0 ${spacing[16]};
    border: none;
  `,
  bodyTr: ({ colors, isSelected, isRounded }: { colors: Colors; isSelected: boolean; isRounded: boolean }) => {
    const {
      bodyRowDefault,
      bodyRowSelectedHover,
      bodyRowHover = defaultColors.bodyRowHover,
      bodyRowSelected = defaultColors.bodyRowSelected,
    } = colors;

    return css`
      ${bodyRowDefault &&
      css`
        background-color: ${bodyRowDefault};
      `}

      &:hover {
        background-color: ${isSelected && bodyRowSelectedHover ? bodyRowSelectedHover : bodyRowHover};
      }

      ${isSelected &&
      css`
        background-color: ${bodyRowSelected};
      `}

      ${isRounded &&
      css`
        :last-of-type {
          border-bottom: none;
        }
      `}
    `;
  },
  td: css`
    ${typography.body()};
    padding: ${spacing[16]};
    border: none;
  `,
};
