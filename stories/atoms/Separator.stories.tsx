import { css } from '@emotion/react';
import { Separator } from '@TutorShared/atoms/Separator';
import type { Meta, StoryObj } from 'storybook-react-rsbuild';

const meta = {
  title: 'Atoms/Separator',
  component: Separator,
  tags: ['autodocs'],
  parameters: {
    layout: 'centered',
    docs: {
      description: {
        component:
          'Separator is a flexible divider component for visually separating content. It supports horizontal and vertical orientation and can be styled with Emotion CSS.',
      },
    },
  },
  argTypes: {
    variant: {
      control: 'select',
      options: ['horizontal', 'vertical'],
      description: 'Orientation of the separator.',
      defaultValue: 'horizontal',
    },
    className: {
      control: false,
      description: 'Custom className for the separator.',
    },
    'aria-label': {
      control: false,
    },
  },
  args: {
    variant: 'horizontal',
  },
  render: (args) => (
    <div
      css={css`
        display: flex;
        flex-direction: ${args.variant === 'vertical' ? 'row' : 'column'};
        align-items: center;
        gap: 16px;
        padding: 16px;
        height: ${args.variant === 'vertical' ? '48px' : '100%'};
        width: ${args.variant === 'vertical' ? '48px' : '100%'};
      `}
    >
      <span>{args.variant === 'horizontal' ? 'Top' : 'Left'} </span>
      <Separator
        {...args}
        variant={args.variant}
        aria-label={args.variant === 'vertical' ? 'Vertical Separator' : 'Horizontal Separator'}
        tabIndex={0}
      />
      <span>{args.variant === 'horizontal' ? 'Bottom' : 'Right'}</span>
    </div>
  ),
} satisfies Meta<typeof Separator>;

export default meta;

type Story = StoryObj<typeof meta>;

export const Default = {
  args: {
    variant: 'horizontal',
  },
} satisfies Story;

export const Vertical = {
  args: {
    variant: 'vertical',
  },
} satisfies Story;

export const CustomStyle = {
  args: {
    variant: 'horizontal',
  },
  render: (args) => (
    <div
      css={css`
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 16px;
        padding: 16px;
      `}
    >
      <span>Top</span>
      <Separator
        {...args}
        variant="horizontal"
        css={css`
          height: 10px;
          background-color: #0073aa;
        `}
        aria-label="Custom Styled Separator"
      />
      <span>Bottom</span>
    </div>
  ),
} satisfies Story;
