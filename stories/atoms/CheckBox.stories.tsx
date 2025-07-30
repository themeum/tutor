import { css } from '@emotion/react';
import CheckBox from '@TutorShared/atoms/CheckBox';
import { useState } from 'react';
import type { Meta, StoryObj } from 'storybook-react-rsbuild';

const meta = {
  title: 'Atoms/CheckBox',
  component: CheckBox,
  tags: ['autodocs'],
  parameters: {
    layout: 'centered',
    docs: {
      description: {
        component:
          'CheckBox is a versatile, accessible component supporting controlled and uncontrolled usage, indeterminate state, custom labels, and Emotion CSS styling. Use for forms, settings, and toggles.',
      },
    },
  },
  argTypes: {
    checked: {
      control: 'boolean',
      description: 'Whether the checkbox is checked.',
      defaultValue: false,
    },
    label: {
      control: 'text',
      description: 'Label for the checkbox.',
      defaultValue: 'CheckBox label',
    },
    disabled: {
      control: 'boolean',
      description: 'Whether the checkbox is disabled.',
      defaultValue: false,
    },
    'aria-invalid': {
      control: 'boolean',
      description: 'Indicates whether the checkbox is in an invalid state.',
      defaultValue: false,
    },
    isIndeterminate: {
      control: 'boolean',
      description: 'Whether the checkbox is in an indeterminate state.',
      defaultValue: false,
    },
    labelCss: {
      control: false,
      description: 'Custom Emotion CSS for the label.',
    },
    inputCss: {
      control: false,
      description: 'Custom Emotion CSS for the input.',
    },
    onBlur: {
      control: false,
      description: 'Function called when the checkbox loses focus.',
    },
    id: {
      control: false,
      description: 'Unique identifier for the checkbox input, useful for linking with labels.',
    },
    value: {
      control: false,
      description: 'Value of the checkbox input, useful for form submissions.',
    },
    name: {
      control: false,
      description: 'Name attribute for the checkbox input, useful for form submissions.',
    },
  },
  args: {
    checked: false,
    label: 'CheckBox label',
    disabled: false,
    isIndeterminate: false,
    'aria-invalid': 'false',
  },
  render: (args) => <CheckBox {...args} aria-label={typeof args.label === 'string' ? args.label : 'CheckBox'} />,
} satisfies Meta<typeof CheckBox>;

export default meta;

type Story = StoryObj<typeof meta>;

export const Default = {
  args: {
    label: 'Default CheckBox',
    checked: false,
    disabled: false,
    isIndeterminate: false,
  },
} satisfies Story;

export const Checked = {
  args: {
    label: 'Checked CheckBox',
    checked: true,
  },
} satisfies Story;

export const Disabled = {
  args: {
    label: 'Disabled CheckBox',
    disabled: true,
  },
} satisfies Story;

export const Indeterminate = {
  args: {
    label: 'Indeterminate CheckBox',
    isIndeterminate: true,
  },
} satisfies Story;

export const WithCustomLabel = {
  render: () => (
    <CheckBox
      label={
        <span>
          <span role="img" aria-label="star" style={{ color: '#f5c518', marginRight: 4 }}>
            ★
          </span>
          Custom label with icon
        </span>
      }
      aria-label="Custom label with icon"
    />
  ),
} satisfies Story;

export const WithCustomStyles = {
  render: () => (
    <CheckBox
      label="Styled CheckBox"
      labelCss={css`
        color: #1976d2;
        font-weight: bold;
      `}
      inputCss={[
        css`
          & + span::before {
            border: 2px dashed #1976d2;
          }
        `,
      ]}
      aria-label="Styled CheckBox"
    />
  ),
} satisfies Story;

export const Controlled = {
  render: () => {
    const [checked, setChecked] = useState(false);
    const handleChange = (isChecked: boolean) => setChecked(isChecked);

    return (
      <CheckBox
        label={`Controlled CheckBox (${checked ? 'Checked' : 'Unchecked'})`}
        checked={checked}
        onChange={handleChange}
        aria-label="Controlled CheckBox"
      />
    );
  },
} satisfies Story;
