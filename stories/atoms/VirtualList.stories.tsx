import { css } from '@emotion/react';
import VirtualList from '@TutorShared/atoms/VirtualList';
import React from 'react';
import type { Meta, StoryObj } from 'storybook-react-rsbuild';

const items = Array.from({ length: 100 }, (_, i) => `Item ${i + 1}`);

const meta = {
  title: 'Atoms/VirtualList',
  component: VirtualList,
  tags: ['autodocs'],
  parameters: {
    layout: 'centered',
    docs: {
      description: {
        component:
          'VirtualList efficiently renders large lists by only rendering visible items. Supports custom item height, container height, and custom item rendering.',
      },
    },
  },
  argTypes: {
    items: {
      control: false,
      description: 'Array of items to render.',
    },
    height: {
      control: 'number',
      description: 'Height of the list container (px).',
      defaultValue: 300,
    },
    itemHeight: {
      control: 'number',
      description: 'Height of each item (px).',
      defaultValue: 40,
    },
    renderItem: {
      control: false,
      description: 'Function to render each item.',
    },
  },
  args: {
    items: [...items],
    height: 300,
    itemHeight: 40,
    renderItem: (item: unknown, index: number, style: React.CSSProperties) => (
      <div
        key={index}
        style={style}
        css={css`
          display: flex;
          align-items: center;
          padding: 0 16px;
          border-bottom: 1px solid #e0e0e0;
          background: #fff;
          font-size: 1rem;
          color: #222;
        `}
        tabIndex={0}
        aria-label={`Virtual list item ${index + 1}`}
      >
        {item as string}
      </div>
    ),
  },
  render: (args) => (
    <div
      css={css`
        height: 500px;
        width: 500px;
      `}
    >
      <VirtualList {...args} aria-label="Virtual List" />
    </div>
  ),
} satisfies Meta<typeof VirtualList>;

export default meta;

type Story = StoryObj<typeof meta>;

export const Default = {} satisfies Story;

export const CustomHeight = {
  args: {
    height: 500,
  },
} satisfies Story;

export const CustomItemHeight = {
  args: {
    itemHeight: 60,
    renderItem: (item: unknown, index: number, style: React.CSSProperties) => (
      <div
        key={index}
        style={style}
        css={css`
          display: flex;
          align-items: center;
          padding: 0 16px;
          border-bottom: 1px solid #e0e0e0;
          background: #f5f5f5;
          font-size: 1.1rem;
          color: #1976d2;
        `}
        tabIndex={0}
        aria-label={`Virtual list item ${index + 1}`}
      >
        {item as string}
      </div>
    ),
  },
} satisfies Story;

export const Empty = {
  args: {
    items: [],
  },
} satisfies Story;
