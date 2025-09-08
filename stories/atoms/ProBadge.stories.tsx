import Button from '@TutorShared/atoms/Button';
import ProBadge from '@TutorShared/atoms/ProBadge';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import type { Meta, StoryObj } from 'storybook-react-rsbuild';

const meta = {
  title: 'Atoms/ProBadge',
  component: ProBadge,
  tags: ['autodocs'],
  parameters: {
    layout: 'centered',
    docs: {
      description: {
        component:
          'ProBadge is a compact badge component for highlighting premium or special features. It supports multiple sizes, custom content, text-only mode, and can wrap children. Use for marking "Pro" features, premium tags, or special statuses.',
      },
    },
  },
  argTypes: {
    content: {
      control: 'text',
      description: 'Content to display inside the badge (if no children).',
      defaultValue: 'Pro',
    },
    size: {
      control: 'select',
      options: ['tiny', 'small', 'regular', 'large'],
      description: 'Size of the badge.',
      defaultValue: 'regular',
    },
    textOnly: {
      control: 'boolean',
      description: 'Show only text, no icon or background.',
      defaultValue: false,
    },
    children: {
      control: false,
      description: 'Optional children to render inside the badge.',
    },
  },
  render: (args) => <ProBadge {...args} aria-label={typeof args.content === 'string' ? args.content : 'Pro Badge'} />,
} satisfies Meta<typeof ProBadge>;

export default meta;

type Story = StoryObj<typeof meta>;

export const Default = {
  args: {
    content: 'Pro',
    size: 'regular',
    textOnly: false,
  },
} satisfies Story;

export const Sizes = {
  args: {
    content: 'Pro',
    textOnly: false,
  },
  render: (args) => (
    <div style={{ display: 'flex', gap: 16, alignItems: 'center' }}>
      {(['tiny', 'small', 'regular', 'large'] as const).map((size) => (
        <ProBadge {...args} key={size} size={size} aria-label={`Pro Badge ${size}`} />
      ))}
    </div>
  ),
} satisfies Story;

export const SizesWithChildren = {
  args: {
    content: 'Premium',
    textOnly: false,
  },
  render: () => (
    <div style={{ display: 'flex', gap: 16, alignItems: 'center' }}>
      {(['tiny', 'small', 'regular', 'large'] as const).map((size) => (
        <ProBadge key={size} size={size} aria-label={`Premium Badge ${size}`}>
          <Button variant="tertiary" size="small">
            <SVGIcon name="magicAi" width={16} height={16} />
            {size.charAt(0).toUpperCase() + size.slice(1)}
          </Button>
        </ProBadge>
      ))}
    </div>
  ),
} satisfies Story;

export const WithContent = {
  args: {
    content: <span>Premium</span>,
    size: 'regular',
    textOnly: false,
  },
} satisfies Story;

export const TextOnly = {
  args: {
    content: 'PRO',
    size: 'large',
    textOnly: true,
  },
} satisfies Story;

export const WithChildren = {
  args: {
    children: (
      <Button variant="secondary" size="small">
        <SVGIcon name="magicAiColorize" width={20} height={20} />
        Pro Feature
      </Button>
    ),
    size: 'regular',
  },
} satisfies Story;
