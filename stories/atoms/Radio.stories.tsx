import { css } from '@emotion/react';
import React, { useState } from 'react';
import type { Meta, StoryObj } from 'storybook-react-rsbuild';

import Radio from '@TutorShared/atoms/Radio';
import SVGIcon from '@TutorShared/atoms/SVGIcon';

const meta = {
  title: 'Atoms/Radio',
  component: Radio,
  tags: ['autodocs'],
  parameters: {
    layout: 'centered',
    docs: {
      description: {
        component:
          'Radio is an accessible, stylable radio button component supporting custom labels, descriptions, icons, and states. Use for single-choice selections in forms or settings.',
      },
    },
  },
  argTypes: {
    label: {
      control: 'text',
      description: 'Label for the radio button.',
      defaultValue: 'Radio label',
    },
    description: {
      control: 'text',
      description: 'Optional description below the radio.',
    },
    icon: {
      control: false,
      description: 'Optional icon to display next to the radio.',
    },
    checked: {
      control: 'boolean',
      description: 'Whether the radio is checked.',
      defaultValue: false,
    },
    disabled: {
      control: 'boolean',
      description: 'Whether the radio is disabled.',
      defaultValue: false,
    },
    readOnly: {
      control: 'boolean',
      description: 'Whether the radio is read-only.',
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
    name: {
      control: 'text',
      description: 'Name for grouping radios.',
    },
    value: {
      control: 'text',
      description: 'Value of the radio.',
    },
  },
  args: {
    label: 'Default Radio',
    checked: false,
    disabled: false,
    readOnly: false,
  },
  render: (args) => <Radio {...args} aria-label={typeof args.label === 'string' ? args.label : 'Radio'} />,
} satisfies Meta<typeof Radio>;

export default meta;

type Story = StoryObj<typeof meta>;

export const Default = {
  args: {
    label: 'Default Radio',
    checked: false,
    disabled: false,
    readOnly: false,
  },
} satisfies Story;

export const Checked = {
  args: {
    label: 'Checked Radio',
    checked: true,
  },
} satisfies Story;

export const Disabled = {
  args: {
    label: 'Disabled Radio',
    disabled: true,
  },
} satisfies Story;

export const WithDescription = {
  args: {
    label: 'Radio with Description',
    description: 'This is a helpful description for the radio button.',
  },
} satisfies Story;

export const WithIcon = {
  args: {
    label: 'Radio with Icon',
    icon: (
      <SVGIcon
        name="star"
        width={16}
        height={16}
        style={css`
          margin-right: 6px;
        `}
      />
    ),
  },
} satisfies Story;

export const CustomStyles = {
  args: {
    label: 'Styled Radio',
    labelCss: css`
      color: #1976d2;
      font-weight: bold;
    `,
    inputCss: [
      css`
        & + span {
          border: 2px dashed #1976d2;
        }
      `,
    ],
  },
} satisfies Story;

export const RadioGroup = {
  render: () => {
    const [selected, setSelected] = useState('option1');
    const handleChange = (event: React.ChangeEvent<HTMLInputElement>) => setSelected(event.target.value);

    return (
      <div style={{ display: 'flex', flexDirection: 'column', gap: 16 }}>
        <Radio
          name="group"
          value="option1"
          label="Option 1"
          checked={selected === 'option1'}
          onChange={handleChange}
          aria-label="Option 1"
        />
        <Radio
          name="group"
          value="option2"
          label="Option 2"
          checked={selected === 'option2'}
          onChange={handleChange}
          aria-label="Option 2"
        />
        <Radio
          name="group"
          value="option3"
          label="Option 3"
          checked={selected === 'option3'}
          onChange={handleChange}
          aria-label="Option 3"
        />
      </div>
    );
  },
} satisfies Story;
