import SVGIcon from '@Atoms/SVGIcon';
import Skeleton from '@Atoms/Skeleton';
import { borderRadius, colorPalate, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { styleUtils } from '@Utils/style-utils';
import { getRandom, range } from '@Utils/util';
import { css } from '@emotion/react';
import type { ReactNode } from 'react';

interface BaseColumn {
  name?: string;
  Header?: string | ReactNode;
  width?: number;
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

export interface TableProps<TableItem> {
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
  querySortProperty?: string;
  querySortDirection?: 'asc' | 'desc';
  onSortClick?: (sortProperty: string) => void;
}

const defaultColors = {
  bodyRowSelected: colorPalate.surface.pressed,
  bodyRowHover: colorPalate.surface.hover,
};

const Table = <TableItem,>({
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
  querySortProperty,
  querySortDirection = 'asc',
  onSortClick,
}: TableProps<TableItem>) => {
  const renderRow = (index: number, content: (column: Column<TableItem>) => ReactNode) => {
    return (
      <tr
        key={index}
        css={[
          styles.tableRow({ isBordered, isStriped }),
          styles.bodyTr({ colors, isSelected: stripedBySelectedIndex.includes(index), isRounded }),
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

    // @TODO: the icons need to be updated with proper one.
    if (column.sortProperty === querySortProperty) {
      if (querySortDirection === 'asc') {
        icon = <SVGIcon name="chevronDown" />;
      } else {
        icon = <SVGIcon name="chevronUp" />;
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
          <th key={index} css={[styles.th, { width: column.width }]} colSpan={column.headerColSpan}>
            {renderHeaderWithSortIcon(column)}
          </th>
        );
      }
    });
  };

  const renderTableBody = () => {
    if (loading) {
      return range(itemsPerPage).map((index) =>
        renderRow(index, () => <Skeleton animation height={20} width={`${getRandom(40, 80)}%`} />)
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

    return data.map((item, index) => {
      return renderRow(index, (column) => {
        return 'Cell' in column ? column.Cell(item, index) : column.accessor(item, index);
      });
    });
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

    ${
      isRounded &&
      css`
      border: 1px solid ${colorPalate.surface.neutral.hover};
      border-radius: ${borderRadius[6]};
    `
    }
  `,
  headerWithIcon: css`
    ${styleUtils.resetButton};
    ${typography.body()};
    color: ${colorPalate.text.neutral};
    display: flex;
    gap: ${spacing[4]};
    align-items: center;
  `,
  table: css`
    width: 100%;
  `,
  tableRow: ({ isBordered, isStriped }: { isBordered: boolean; isStriped: boolean }) => css`
    ${
      isBordered &&
      css`
      border-bottom: 1px solid ${colorPalate.surface.neutral.hover};
    `
    }

    ${
      isStriped &&
      css`
      &:nth-of-type(even) {
        background-color: ${colorPalate.surface.pressed};
      }
    `
    }
  `,
  th: css`
    ${typography.body()};
    background-color: ${colorPalate.surface.neutral.default};
    color: ${colorPalate.text.neutral};
    padding: 0 ${spacing[16]};
  `,
  bodyTr: ({ colors, isSelected, isRounded }: { colors: Colors; isSelected: boolean; isRounded: boolean }) => {
    const {
      bodyRowDefault,
      bodyRowSelectedHover,
      bodyRowHover = defaultColors.bodyRowHover,
      bodyRowSelected = defaultColors.bodyRowSelected,
    } = colors;

    return css`
      ${
        bodyRowDefault &&
        css`
        background-color: ${bodyRowDefault};
      `
      }

      &:hover {
        background-color: ${isSelected && bodyRowSelectedHover ? bodyRowSelectedHover : bodyRowHover};
      }

      ${
        isSelected &&
        css`
        background-color: ${bodyRowSelected};
      `
      }

      ${
        isRounded &&
        css`
        :last-of-type {
          border-bottom: none;
        }
      `
      }
    `;
  },
  td: css`
    ${typography.body()};
    padding: ${spacing[16]};
  `,
};
