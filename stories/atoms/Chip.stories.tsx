import Chip from '@TutorShared/atoms/Chip';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import type { Meta, StoryObj } from 'storybook-react-rsbuild';

const meta: Meta<typeof Chip> = {
  title: 'Atoms/Chip',
  component: Chip,
  tags: ['autodocs'],
  parameters: {
    layout: 'centered',
    docs: {
      description: {
        component:
          'Chip is a compact, rounded UI element for displaying tags, filters, or actions. It supports optional icons, clickability, and custom styling. Use for selections, filters, or dismissible tags.',
      },
    },
  },
  argTypes: {
    label: {
      control: 'text',
      description: 'Text label for the chip.',
      defaultValue: 'Chip Label',
    },
    onClick: {
      action: 'clicked',
      description: 'Click handler for the chip or icon.',
    },
    showIcon: {
      control: 'boolean',
      description: 'Show the icon (cross or custom) on the chip.',
      defaultValue: true,
    },
    icon: {
      control: false,
      description: 'Custom icon ReactNode for the chip.',
    },
    isClickable: {
      control: 'boolean',
      description: 'If true, the entire chip is clickable (renders as a button).',
      defaultValue: false,
    },
  },
};
export default meta;

type Story = StoryObj<typeof meta>;

const handleClick = () => {
  alert('Chip clicked!');
};

const customIcon = <SVGIcon name="star" width={20} height={20} />;

export const Default: Story = {
  args: {
    label: 'Default Chip',
    showIcon: true,
    isClickable: false,
  },
  render: (args) => <Chip {...args} aria-label={args.label} />,
};

export const Clickable: Story = {
  args: {
    label: 'Clickable Chip',
    showIcon: true,
    isClickable: true,
    onClick: handleClick,
  },
  render: (args) => <Chip {...args} aria-label={args.label} />,
};

export const WithCustomIcon: Story = {
  args: {
    label: 'Custom Icon Chip',
    showIcon: true,
    icon: customIcon,
    isClickable: true,
    onClick: handleClick,
  },
  render: (args) => <Chip {...args} aria-label={args.label} />,
};

export const WithoutIcon: Story = {
  args: {
    label: 'No Icon Chip',
    showIcon: false,
    isClickable: false,
  },
  render: (args) => <Chip {...args} aria-label={args.label} />,
};

export const WithLongLabel: Story = {
  args: {
    label: 'This is a very long chip label to test overflow and responsiveness',
    showIcon: true,
    isClickable: false,
  },
  render: (args) => <Chip {...args} aria-label={args.label} />,
};
