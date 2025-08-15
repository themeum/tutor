import { css } from '@emotion/react';
import { TutorBadge } from '@TutorShared/atoms/TutorBadge';
import type { Meta, StoryObj } from 'storybook-react-rsbuild';

const meta = {
  title: 'Atoms/TutorBadge',
  component: TutorBadge,
  tags: ['autodocs'],
  parameters: {
    layout: 'centered',
    docs: {
      description: {
        component:
          'TutorBadge is a flexible badge component for status, tags, or highlights. Supports multiple variants and custom styles.',
      },
    },
  },
  argTypes: {
    variant: {
      control: 'select',
      options: ['default', 'secondary', 'critical', 'warning', 'success', 'outline'],
      description: 'Visual style of the badge.',
      defaultValue: 'default',
    },
    className: {
      control: false,
      description: 'Custom className for the badge.',
    },
    children: {
      control: 'text',
      description: 'Badge content.',
      defaultValue: 'Badge',
    },
    'aria-label': {
      control: false,
    },
  },
  args: {
    variant: 'default',
    children: 'Badge',
  },
  render: (args) => (
    <TutorBadge {...args} aria-label={typeof args.children === 'string' ? args.children : 'Tutor Badge'} tabIndex={0}>
      {args.children}
    </TutorBadge>
  ),
} satisfies Meta<typeof TutorBadge>;
export default meta;

type Story = StoryObj<typeof meta>;

export const Default = {} satisfies Story;

export const Variants = {
  render: () => (
    <div
      css={css`
        display: flex;
        gap: 16px;
        flex-wrap: wrap;
        align-items: center;
      `}
    >
      {(['default', 'secondary', 'critical', 'warning', 'success', 'outline'] as const).map((variant) => (
        <TutorBadge key={variant} variant={variant} aria-label={`Tutor Badge ${variant}`} tabIndex={0}>
          {variant.charAt(0).toUpperCase() + variant.slice(1)}
        </TutorBadge>
      ))}
    </div>
  ),
} satisfies Story;

export const CustomStyle = {
  args: {
    children: 'Custom Styled Badge',
    variant: 'success',
  },
  render: (args) => (
    <TutorBadge
      {...args}
      css={css`
        font-size: 1.1rem;
        padding: 10px 24px;
        background: linear-gradient(90deg, #1976d2 0%, #42a5f5 100%);
        color: #fff;
        border: none;
      `}
      aria-label="Custom Styled Tutor Badge"
      tabIndex={0}
    >
      {args.children}
    </TutorBadge>
  ),
} satisfies Story;
