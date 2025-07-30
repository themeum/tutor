import { css } from '@emotion/react';
import { colorTokens } from '@TutorShared/config/styles';
import Table, { type Column } from '@TutorShared/molecules/Table';
import React, { useState } from 'react';
import type { Meta, StoryObj } from 'storybook-react-rsbuild';

type User = {
  id: number;
  name: string;
  email: string;
  status: 'active' | 'inactive';
};

const users: User[] = [
  { id: 1, name: 'Alice Johnson', email: 'alice@example.com', status: 'active' },
  { id: 2, name: 'Bob Smith', email: 'bob@example.com', status: 'inactive' },
  { id: 3, name: 'Charlie Lee', email: 'charlie@example.com', status: 'active' },
];

const columns: Column<User>[] = [
  {
    Header: 'ID',
    accessor: (item) => item.id,
    Cell: (item) => item.id,
    width: '60px',
  },
  {
    Header: 'Name',
    accessor: (item) => item.name,
    Cell: (item) => item.name,
    width: '160px',
  },
  {
    Header: 'Email',
    accessor: (item) => item.email,
    Cell: (item) => item.email,
    width: '220px',
  },
  {
    Header: 'Status',
    accessor: (item) => item.status,
    Cell: (item) => (
      <span
        css={css`
          color: ${item.status === 'active' ? colorTokens.text.success : colorTokens.text.error};
          font-weight: bold;
        `}
        aria-label={`Status: ${item.status}`}
        tabIndex={0}
      >
        {item.status === 'active' ? 'Active' : 'Inactive'}
      </span>
    ),
    width: '100px',
  },
];

const meta = {
  title: 'Molecules/Table',
  component: Table,
  tags: ['autodocs'],
  parameters: {
    layout: 'centered',
    docs: {
      description: {
        component:
          'Table is a flexible, accessible table component supporting custom columns, cell renderers, sorting, loading, striped and rounded styles, and more.',
      },
    },
  },
  argTypes: {
    columns: { control: false, description: 'Table columns definition.' },
    data: { control: false, description: 'Table data array.' },
    entireHeader: { control: false, description: 'Custom header spanning all columns.', defaultValue: '' },
    headerHeight: { control: 'number', description: 'Header row height.', defaultValue: 60 },
    noHeader: { control: 'boolean', description: 'Hide table header.', defaultValue: false },
    isStriped: { control: 'boolean', description: 'Enable striped rows.', defaultValue: false },
    isRounded: { control: 'boolean', description: 'Enable rounded corners.', defaultValue: false },
    isBordered: { control: 'boolean', description: 'Enable table borders.', defaultValue: true },
    loading: { control: 'boolean', description: 'Show loading skeletons.', defaultValue: false },
    itemsPerPage: { control: 'number', description: 'Number of skeleton rows when loading.', defaultValue: 3 },
    renderInLastRow: { control: false, description: 'Custom ReactNode for last row.' },
    rowStyle: { control: false, description: 'Custom Emotion CSS for table rows.' },
    sortIcons: { control: false, description: 'Custom sort icons.' },
    onSortClick: { control: false },
  },
  args: {
    // @ts-ignore
    columns,
    data: users,
    entireHeader: null,
    headerHeight: 60,
    noHeader: false,
    isStriped: false,
    isRounded: false,
    isBordered: true,
    loading: false,
    itemsPerPage: 3,
    renderInLastRow: undefined,
    rowStyle: undefined,
    sortIcons: undefined,
  },
  render: (args) => (
    <div
      css={css`
        max-width: 600px;
        margin: 0 auto;
      `}
      aria-label="User Table"
      tabIndex={0}
    >
      <Table {...args} />
    </div>
  ),
} satisfies Meta<typeof Table>;

export default meta;

type Story = StoryObj<typeof meta>;

export const Default = {} satisfies Story;

export const Loading = {
  args: {
    loading: true,
    itemsPerPage: 4,
  },
} satisfies Story;

export const NoData = {
  args: {
    data: [],
  },
} satisfies Story;

export const NoHeader = {
  args: {
    noHeader: true,
  },
} satisfies Story;

export const CustomHeader = {
  args: {
    entireHeader: (
      <span
        css={css`
          font-size: 1.1rem;
          font-weight: bold;
          color: ${colorTokens.text.brand} satisfies Story;
        `}
        aria-label="Custom Table Header"
        tabIndex={0}
      >
        Custom Table Header
      </span>
    ),
  },
} satisfies Story;

export const Striped = {
  args: {
    isStriped: true,
  },
} satisfies Story;

export const Rounded = {
  args: {
    isRounded: true,
  },
} satisfies Story;

export const CustomRowStyle = {
  args: {
    rowStyle: css`
      td {
        background: #f0f4ff;
      }
    `,
  },
} satisfies Story;

export const Sortable = {
  render: (args: React.ComponentProps<typeof Table>) => {
    const [sortDirections, setSortDirections] = useState<{ [key: string]: 'asc' | 'desc' }>({});
    const [sortProperty, setSortProperty] = useState<string | null>(null);

    const handleSortClick = (property: string) => {
      setSortProperty(property);
      setSortDirections((prev) => ({
        ...prev,
        [property]: prev[property] === 'asc' ? 'desc' : 'asc',
      }));
    };

    const sortedData = React.useMemo(() => {
      if (!sortProperty) return users;
      const direction = sortDirections[sortProperty];
      return [...users].sort((a, b) => {
        if (a[sortProperty as keyof User] < b[sortProperty as keyof User]) return direction === 'asc' ? -1 : 1;
        if (a[sortProperty as keyof User] > b[sortProperty as keyof User]) return direction === 'asc' ? 1 : -1;
        return 0;
      });
    }, [sortProperty, sortDirections]);

    const sortableColumns: Column<User>[] = [
      {
        Header: 'ID',
        accessor: (item) => item.id,
        Cell: (item) => item.id,
        width: '60px',
        sortProperty: 'id',
      },
      {
        Header: 'Name',
        accessor: (item) => item.name,
        Cell: (item) => item.name,
        width: '160px',
        sortProperty: 'name',
      },
      {
        Header: 'Email',
        accessor: (item) => item.email,
        Cell: (item) => item.email,
        width: '220px',
        sortProperty: 'email',
      },
      {
        Header: 'Status',
        accessor: (item) => item.status,
        Cell: (item) => (
          <span
            css={css`
              color: ${item.status === 'active' ? colorTokens.text.success : colorTokens.text.error};
              font-weight: bold;
            `}
            aria-label={`Status: ${item.status}`}
            tabIndex={0}
          >
            {item.status === 'active' ? 'Active' : 'Inactive'}
          </span>
        ),
        width: '100px',
      },
    ];

    return (
      <div
        css={css`
          max-width: 600px;
          margin: 0 auto;
        `}
        aria-label="Sortable User Table"
        tabIndex={0}
      >
        <Table
          {...args}
          columns={sortableColumns}
          data={sortedData}
          querySortProperties={['id', 'name', 'email'] as const}
          querySortDirections={sortDirections}
          onSortClick={handleSortClick}
        />
      </div>
    );
  },
} satisfies Story;

export const LastRowCustomRender = {
  args: {
    renderInLastRow: (
      <span
        css={css`
          font-size: 1rem;
          color: ${colorTokens.text.brand};
          font-weight: bold;
        `}
        aria-label="Last Row Custom Render"
        tabIndex={0}
      >
        This is a custom last row!
      </span>
    ),
  },
} satisfies Story;
