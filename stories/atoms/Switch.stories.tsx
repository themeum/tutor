import { css } from '@emotion/react';
import Switch from '@TutorShared/atoms/Switch';
import { useState } from 'react';
import type { Meta, StoryObj } from 'storybook-react-rsbuild';

const meta: Meta<typeof Switch> = {
  title: 'Atoms/Switch',
  component: Switch,
  tags: ['autodocs'],
  parameters: {
    layout: 'centered',
    docs: {
      description: {
        component:
          'Switch is a toggle component for boolean values. It supports labels, loading, disabled, different sizes, and custom label styling. Use for toggling settings or features.',
      },
    },
  },
  argTypes: {
    label: {
      control: 'text',
      description: 'Label for the switch.',
      defaultValue: 'Switch label',
    },
    checked: {
      control: 'boolean',
      description: 'Whether the switch is checked.',
      defaultValue: false,
    },
    disabled: {
      control: 'boolean',
      description: 'Disable the switch.',
      defaultValue: false,
    },
    loading: {
      control: 'boolean',
      description: 'Show loading spinner.',
      defaultValue: false,
    },
    labelPosition: {
      control: 'select',
      options: ['left', 'right'],
      description: 'Position of the label.',
      defaultValue: 'left',
    },
    size: {
      control: 'select',
      options: ['large', 'regular', 'small'],
      description: 'Size of the switch.',
      defaultValue: 'regular',
    },
    labelCss: {
      control: false,
      description: 'Custom Emotion CSS for the label.',
    },
    id: {
      control: false,
      description: 'Unique identifier for the switch input, useful for linking with labels.',
    },
    name: {
      control: false,
      description: 'Name for the switch input, useful for form submissions.',
    },
    value: {
      control: false,
      description: 'Value of the switch input, useful for form submissions.',
    },
  },
  args: {
    label: 'Switch label',
    checked: false,
    disabled: false,
    loading: false,
    labelPosition: 'left',
    size: 'regular',
  },
  render: (args) => <Switch {...args} aria-label={typeof args.label === 'string' ? args.label : 'Switch'} />,
};
export default meta;

type Story = StoryObj<typeof meta>;

export const Default: Story = {};

export const Checked: Story = {
  args: {
    checked: true,
  },
};

export const Disabled: Story = {
  args: {
    disabled: true,
  },
};

export const Loading: Story = {
  args: {
    loading: true,
    checked: true,
  },
};

export const LabelRight: Story = {
  args: {
    labelPosition: 'right',
  },
};

export const Small: Story = {
  args: {
    size: 'small',
  },
};

export const Large: Story = {
  args: {
    size: 'large',
  },
};

export const CustomLabelStyle: Story = {
  args: {
    label: 'Styled Label',
    labelCss: css`
      color: #1976d2;
      font-weight: bold;
      font-size: 1.1rem;
    `,
  },
};

export const Controlled: Story = {
  render: () => {
    const [checked, setChecked] = useState(false);
    const handleChange = (nextChecked: boolean) => setChecked(nextChecked);

    return (
      <Switch label="Controlled Switch" checked={checked} onChange={handleChange} aria-label="Controlled Switch" />
    );
  },
};
