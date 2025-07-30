import Paginator from '@TutorShared/molecules/Paginator';
import { useState } from 'react';
import type { Meta, StoryObj } from 'storybook-react-rsbuild';

const meta = {
  title: 'Molecules/Paginator',
  component: Paginator,
  tags: ['autodocs'],
  parameters: {
    layout: 'centered',
    docs: {
      description: {
        component:
          'Paginator is a navigation component for paginated data sets. It supports custom total items, items per page, and accessibility features.',
      },
    },
  },
  argTypes: {
    currentPage: {
      control: 'number',
      description: 'Current active page.',
      defaultValue: 1,
    },
    totalItems: {
      control: 'number',
      description: 'Total number of items.',
      defaultValue: 100,
    },
    itemsPerPage: {
      control: 'number',
      description: 'Items per page.',
      defaultValue: 10,
    },
    onPageChange: { control: false },
  },
  args: {
    currentPage: 1,
    totalItems: 100,
    itemsPerPage: 10,
    onPageChange: () => {},
  },
  render: (args) => {
    const [currentPage, setCurrentPage] = useState(args.currentPage);

    const handlePageChange = (page: number) => setCurrentPage(page);

    return <Paginator {...args} currentPage={currentPage} onPageChange={handlePageChange} />;
  },
} satisfies Meta<typeof Paginator>;

export default meta;

type Story = StoryObj<typeof meta>;

export const Default = {} satisfies Story;

export const FewPages = {
  args: {
    totalItems: 15,
    itemsPerPage: 5,
  },
} satisfies Story;

export const SinglePage = {
  args: {
    totalItems: 5,
    itemsPerPage: 10,
  },
} satisfies Story;

export const LargeDataset = {
  args: {
    totalItems: 1000,
    itemsPerPage: 20,
  },
} satisfies Story;
