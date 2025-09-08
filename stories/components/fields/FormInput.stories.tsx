import { css } from '@emotion/react';
import FormInput from '@TutorShared/components/fields/FormInput';
import { DEFAULT_FORM_FIELD_PROPS, DEFAULT_FORM_FILED_STATE_PROPS } from '@TutorShared/config/constants';
import { colorTokens } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import { Controller, useForm } from 'react-hook-form';
import { type Meta, type StoryObj } from 'storybook-react-rsbuild';

const meta = {
  title: 'Components/Fields/FormInput',
  component: FormInput,
  args: {
    label: 'Course Title',
    type: 'text',
    maxLimit: undefined,
    disabled: false,
    readOnly: false,
    loading: false,
    placeholder: '',
    helpText: '',
    onChange: undefined,
    onKeyDown: undefined,
    isHidden: false,
    isClearable: false,
    isSecondary: false,
    removeBorder: false,
    dataAttribute: undefined,
    isInlineLabel: false,
    isPassword: false,
    style: undefined,
    selectOnFocus: false,
    autoFocus: false,
    generateWithAi: false,
    onClickAiButton: undefined,
    isMagicAi: false,
    allowNegative: false,
    field: DEFAULT_FORM_FIELD_PROPS,
    fieldState: DEFAULT_FORM_FILED_STATE_PROPS,
  },
  tags: ['autodocs'],
  argTypes: {
    label: {
      control: 'text',
      description: 'Label for the input field. Can be a string or ReactNode.',
    },
    type: {
      control: 'select',
      options: ['text', 'number', 'password'],
      defaultValue: 'text',
      description: 'Input type (e.g., "text", "number", "password").',
    },
    maxLimit: {
      control: 'number',
      description: 'Maximum character limit for the input value.',
    },
    disabled: {
      control: 'boolean',
      description: 'Disables the input field if true.',
    },
    readOnly: {
      control: 'boolean',
      description: 'Makes the input field read-only if true.',
    },
    loading: {
      control: 'boolean',
      description: 'Shows a loading indicator if true.',
    },
    placeholder: {
      control: 'text',
      description: 'Placeholder text for the input field.',
    },
    helpText: {
      control: 'text',
      description: 'Additional help text displayed below the input.',
    },
    isHidden: {
      control: 'boolean',
      description: 'Hides the input field if true.',
    },
    isClearable: {
      control: 'boolean',
      description: 'Shows a clear button to reset the input value if true.',
    },
    isSecondary: {
      control: 'boolean',
      description: 'Applies secondary styling to the input field.',
    },
    removeBorder: {
      control: 'boolean',
      description: 'Removes the border from the input field if true.',
    },
    dataAttribute: {
      control: 'text',
      description: 'Custom data attribute for the input element.',
    },
    isInlineLabel: {
      control: 'boolean',
      description: 'Displays the label inline with the input field if true.',
    },
    isPassword: {
      control: 'boolean',
      description: 'Enables password visibility toggle if true.',
    },
    style: {
      control: false,
      description: 'Custom EmotionJs styles for the input field.',
    },
    selectOnFocus: {
      control: 'boolean',
      description: 'Selects the input value when focused if true.',
    },
    autoFocus: {
      control: 'boolean',
      description: 'Automatically focuses the input field on mount if true.',
    },
    generateWithAi: {
      control: 'boolean',
      description: 'Shows an AI button for generating input value if true.',
    },
    onClickAiButton: {
      control: false,
      description: 'Callback for AI button click event.',
    },
    isMagicAi: {
      control: 'boolean',
      description: 'Enables Magic AI styling and features if true.',
    },
    allowNegative: {
      control: 'boolean',
      description: 'Allows negative numbers for number input if true.',
    },
    field: {
      control: false,
      description: 'Form controller props for managing input value and events.',
    },
    fieldState: {
      control: false,
      description: 'Form field state for validation and error handling.',
    },
    onChange: {
      control: false,
      description: 'Callback for input value change event.',
    },
    onKeyDown: {
      control: false,
      description: 'Callback for keydown event on the input field.',
    },
  },
  parameters: {
    layout: 'centered',
    docs: {
      description: {
        component: `
FormInput is a flexible, accessible input field for forms, styled with EmotionJs and config tokens.
It supports text, number, password, clearable, character limit, help text, AI integration, custom styling, and integrates with form controllers.
        `,
      },
    },
  },
  render: (args) => <FormInput {...args} />,
} satisfies Meta<typeof FormInput>;

export default meta;
type Story = StoryObj<typeof meta>;

export const Default = {} satisfies Story;

export const WithPlaceholder = {
  args: {
    placeholder: 'Enter course title...',
  },
} satisfies Story;

export const Password = {
  args: {
    label: 'Password',
    type: 'password',
    isPassword: true,
    placeholder: 'Enter password',
  },
} satisfies Story;

export const Number = {
  args: {
    label: 'Course Price',
    type: 'number',
    placeholder: 'Enter price',
  },
} satisfies Story;

export const WithHelpText = {
  args: {
    label: 'Course Subtitle',
    helpText: 'A short subtitle for your course.',
  },
} satisfies Story;

export const Disabled = {
  args: {
    label: 'Disabled Input',
    disabled: true,
    placeholder: 'Cannot edit',
  },
} satisfies Story;

export const ReadOnly = {
  args: {
    label: 'Read Only Input',
    readOnly: true,
    placeholder: 'Read only value',
    field: { ...DEFAULT_FORM_FIELD_PROPS, value: 'Read only value' },
  },
} satisfies Story;

export const WithMaxLimit = {
  args: {
    label: 'Limited Input',
    maxLimit: 10,
    placeholder: 'Max 10 characters',
  },
} satisfies Story;

export const IsClearable = {
  args: {
    label: 'Clearable Input',
    isClearable: true,
    field: { ...DEFAULT_FORM_FIELD_PROPS, value: 'Clear me' },
  },
} satisfies Story;

export const CustomLabelStyle = {
  args: {
    label: 'Styled Label',
    placeholder: 'Styled label input',
  },
  render: (args) => (
    <FormInput
      {...args}
      label={
        <div
          css={css`
            ${typography.heading6('bold')}
            color: ${colorTokens.brand.blue};
          `}
        >
          Custom Styled Label
        </div>
      }
    />
  ),
} satisfies Story;

export const Controlled = {
  render: (args) => {
    const form = useForm({
      defaultValues: {
        input: '',
      },
    });

    return (
      <Controller
        name="input"
        control={form.control}
        render={(controllerProps) => (
          <FormInput
            {...args}
            {...controllerProps}
            label="Controlled Input"
            placeholder="Controlled by react-hook-form"
          />
        )}
      />
    );
  },
} satisfies Story;

export const WithError = {
  args: {
    label: 'Error Input',
    fieldState: {
      ...DEFAULT_FORM_FILED_STATE_PROPS,
      error: {
        type: 'required',
        message: 'This field is required',
      },
    },
  },
} satisfies Story;

export const AI = {
  args: {
    label: 'Generate with AI',
    generateWithAi: true,
    isMagicAi: true,
    placeholder: 'Let AI help you write a title',
  },
} satisfies Story;
