import { css } from '@emotion/react';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import Card from '@TutorShared/molecules/Card';
import type { Meta, StoryObj } from 'storybook-react-rsbuild';

const meta = {
  title: 'Molecules/Card',
  component: Card,
  tags: ['autodocs'],
  parameters: {
    layout: 'centered',
    docs: {
      description: {
        component:
          'Card is a flexible container component with optional border, title, subtitle, collapsible content, action tray, and alternative styling. Use for grouping related content in a visually distinct block.',
      },
    },
  },
  argTypes: {
    title: {
      control: 'text',
      description: 'Title of the card.',
      defaultValue: 'Card Title',
    },
    subtitle: {
      control: 'text',
      description: 'Subtitle of the card.',
      defaultValue: 'This is a subtitle.',
    },
    hasBorder: {
      control: 'boolean',
      description: 'Show border around the card.',
      defaultValue: false,
    },
    actionTray: {
      control: false,
      description: 'Optional action tray (ReactNode).',
    },
    collapsed: {
      control: 'boolean',
      description: 'Collapse card content by default.',
      defaultValue: false,
    },
    noSeparator: {
      control: 'boolean',
      description: 'Hide separator under header.',
      defaultValue: false,
    },
    hideArrow: {
      control: 'boolean',
      description: 'Hide collapse arrow.',
      defaultValue: false,
    },
    isAlternative: {
      control: 'boolean',
      description: 'Use alternative header padding.',
      defaultValue: false,
    },
    children: {
      control: 'text',
      description: 'Card body content.',
      defaultValue: 'This is the card content. You can put any ReactNode here.',
    },
    collapsedAnimationDependencies: {
      control: false,
      description: 'Dependencies for collapse animation.',
    },
  },
  args: {
    title: 'Card Title',
    subtitle: 'This is a subtitle.',
    hasBorder: false,
    collapsed: false,
    noSeparator: false,
    hideArrow: false,
    isAlternative: false,
    children: 'This is the card content. You can put any ReactNode here.',
  },
  render: (args) => (
    <Card {...args} aria-label={typeof args.title === 'string' ? args.title : 'Card'}>
      {args.children}
    </Card>
  ),
} satisfies Meta<typeof Card>;

export default meta;

type Story = StoryObj<typeof meta>;

export const Default = {} satisfies Story;

export const WithBorder = {
  args: {
    hasBorder: true,
  },
} satisfies Story;

export const Collapsed = {
  args: {
    collapsed: true,
  },
} satisfies Story;

export const NoSeparator = {
  args: {
    noSeparator: true,
  },
} satisfies Story;

export const HideArrow = {
  args: {
    hideArrow: true,
  },
} satisfies Story;

export const AlternativeStyle = {
  args: {
    isAlternative: true,
  },
} satisfies Story;

export const WithActionTray = {
  args: {
    actionTray: (
      <button
        css={css`
          background: #1976d2;
          color: #fff;
          border: none;
          border-radius: 4px;
          padding: 6px 16px;
          cursor: pointer;
          font-size: 0.95rem;
        `}
        aria-label="Action Button"
        onClick={() => alert('Action clicked')}
        onKeyDown={(event) => {
          if (event.key === 'Enter' || event.key === ' ') {
            alert('Action clicked');
          }
        }}
      >
        Action
      </button>
    ),
  },
} satisfies Story;

export const CustomContent = {
  args: {
    children: (
      <div
        css={css`
          display: flex;
          flex-direction: column;
          gap: 12px;
        `}
      >
        <span>
          <SVGIcon name="star" width={20} height={20} /> Custom content with icon.
        </span>
        <span>
          <strong>More content:</strong> You can put any ReactNode here, including lists, images, or forms.
        </span>
      </div>
    ),
  },
} satisfies Story;
