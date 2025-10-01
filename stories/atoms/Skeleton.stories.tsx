import { css } from '@emotion/react';
import Skeleton from '@TutorShared/atoms/Skeleton';
import type { Meta, StoryObj } from 'storybook-react-rsbuild';

const meta = {
  title: 'Atoms/Skeleton',
  component: Skeleton,
  tags: ['autodocs'],
  parameters: {
    layout: 'centered',
    docs: {
      description: {
        component:
          'Skeleton is a placeholder loading component for indicating content is loading. It supports custom width, height, animation, roundness, and Magic AI style.',
      },
    },
  },
  argTypes: {
    width: {
      control: 'text',
      description: 'Width of the skeleton (number in px or string).',
      defaultValue: '100%',
    },
    height: {
      control: 'number',
      description: 'Height of the skeleton (number in px or string).',
      defaultValue: 16,
    },
    animation: {
      control: 'boolean',
      description: 'Enable shimmer animation.',
      defaultValue: false,
    },
    isMagicAi: {
      control: 'boolean',
      description: 'Enable Magic AI gradient style.',
      defaultValue: false,
    },
    isRound: {
      control: 'boolean',
      description: 'Make the skeleton fully round.',
      defaultValue: false,
    },
    animationDuration: {
      control: 'number',
      description: 'Duration of the animation in seconds.',
      defaultValue: 1.6,
    },
    className: {
      control: false,
      description: 'Custom className for the skeleton.',
    },
    'aria-label': {
      control: false,
    },
  },
  args: {
    width: '100%',
    height: 16,
    animation: false,
    isMagicAi: false,
    isRound: false,
    animationDuration: 1.6,
  },
  render: (args) => (
    <div
      css={css`
        width: 500px;
      `}
    >
      <Skeleton {...args} aria-label="Skeleton Loader" tabIndex={0} />
    </div>
  ),
} satisfies Meta<typeof Skeleton>;

export default meta;

type Story = StoryObj<typeof meta>;

export const Default = {} satisfies Story;

export const CustomSize = {
  args: {
    width: 400,
    height: 64,
  },
} satisfies Story;

export const Animated = {
  args: {
    animation: true,
  },
} satisfies Story;

export const MagicAi = {
  args: {
    isMagicAi: true,
    animation: true,
  },
} satisfies Story;

export const Round = {
  args: {
    isRound: true,
    width: 40,
    height: 40,
  },
} satisfies Story;

export const CustomAnimationDuration = {
  args: {
    animation: true,
    animationDuration: 3,
  },
} satisfies Story;

export const CustomStyle = {
  args: {
    width: 120,
    height: 24,
    animation: true,
  },
  render: (args) => (
    <Skeleton
      {...args}
      css={css`
        background-color: rgb(25, 118, 210, 0.2);
        border-radius: 12px;
      `}
      aria-label="Custom Styled Skeleton"
      tabIndex={0}
    />
  ),
} satisfies Story;
