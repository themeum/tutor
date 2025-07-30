import { css } from '@emotion/react';
import TextInput from '@TutorShared/atoms/TextInput';
import { useState } from 'react';
import type { Meta, StoryObj } from 'storybook-react-rsbuild';

const meta = {
  title: 'Atoms/TextInput',
  component: TextInput,
  tags: ['autodocs'],
  parameters: {
    layout: 'centered',
    docs: {
      description: {
        component:
          'TextInput is a flexible, accessible input component supporting labels, inline labels, clearable, search, number, custom styles, and more. Use for forms, search bars, and settings.',
      },
    },
  },
  argTypes: {
    label: {
      control: 'text',
      description: 'Label for the input.',
    },
    isInlineLabel: {
      control: 'boolean',
      description: 'Show label inline with input.',
      defaultValue: false,
    },
    type: {
      control: 'select',
      options: ['text', 'number'],
      description: 'Input type.',
      defaultValue: 'text',
    },
    value: {
      control: 'text',
      description: 'Input value.',
    },
    disabled: {
      control: 'boolean',
      description: 'Disable the input.',
      defaultValue: false,
    },
    readOnly: {
      control: 'boolean',
      description: 'Make the input read-only.',
      defaultValue: false,
    },
    placeholder: {
      control: 'text',
      description: 'Placeholder text.',
    },
    isClearable: {
      control: 'boolean',
      description: 'Show clear button.',
      defaultValue: false,
    },
    variant: {
      control: 'select',
      options: ['regular', 'search'],
      description: 'Input variant.',
      defaultValue: 'regular',
    },
    focusOnMount: {
      control: 'boolean',
      description: 'Focus input on mount.',
      defaultValue: false,
    },
    autoFocus: {
      control: 'boolean',
      description: 'Autofocus input.',
      defaultValue: false,
    },
    size: {
      control: 'select',
      options: ['regular', 'small'],
      description: 'Input size.',
      defaultValue: 'regular',
    },
    inputCss: {
      control: false,
      description: 'Custom Emotion CSS for the input.',
    },
    onChange: {
      control: false,
    },
    onBlur: {
      control: false,
    },
    onKeyDown: {
      control: false,
    },
    onFocus: {
      control: false,
    },
    handleMediaIconClick: {
      control: false,
    },
  },
  args: {
    value: '',
    label: '',
    isInlineLabel: false,
    type: 'text',
    disabled: false,
    readOnly: false,
    placeholder: '',
    isClearable: false,
    variant: 'regular',
    focusOnMount: false,
    autoFocus: false,
    size: 'regular',
    onChange: () => {},
  },
  render: (args) => {
    const [value, setValue] = useState('');
    const handleChange = (val: string) => setValue(val);

    return <TextInput {...args} value={value} onChange={handleChange} />;
  },
} satisfies Meta<typeof TextInput>;

export default meta;

type Story = StoryObj<typeof meta>;

export const Default = {} satisfies Story;

export const WithLabel = {
  args: {
    label: 'Your Name',
    placeholder: 'Enter your name',
  },
} satisfies Story;

export const InlineLabel = {
  args: {
    label: 'Email',
    isInlineLabel: true,
    placeholder: 'Enter your email',
  },
} satisfies Story;

export const Placeholder = {
  args: {
    placeholder: 'Type something...',
  },
} satisfies Story;

export const NumberType = {
  args: {
    type: 'number',
    label: 'Age',
    placeholder: 'Enter your age',
  },
} satisfies Story;

export const Clearable = {
  args: {
    label: 'Clearable',
    isClearable: true,
    value: 'Clear me',
  },
} satisfies Story;

export const SearchVariant = {
  args: {
    variant: 'search',
    placeholder: 'Search...',
  },
} satisfies Story;

export const SmallSize = {
  args: {
    size: 'small',
    label: 'Small Input',
    placeholder: 'Small input',
  },
} satisfies Story;

export const Disabled = {
  args: {
    label: 'Disabled',
    disabled: true,
    value: 'Disabled input',
  },
} satisfies Story;

export const ReadOnly = {
  args: {
    label: 'Read Only',
    readOnly: true,
    value: 'Read only value',
  },
} satisfies Story;

export const FocusOnMount = {
  args: {
    label: 'Focus On Mount',
    focusOnMount: true,
    placeholder: 'Focused on mount',
  },
} satisfies Story;

export const CustomStyle = {
  args: {
    label: 'Styled Input',
    inputCss: css`
      border: 2px solid #1976d2;
      border-radius: 8px;
      background: #f0f4ff;
    `,
    placeholder: 'Styled input',
  },
} satisfies Story;
