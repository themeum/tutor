import { css } from '@emotion/react';
import FormInputWithContent from '@TutorShared/components/fields/FormInputWithContent';
import { DEFAULT_FORM_FIELD_PROPS, DEFAULT_FORM_FILED_STATE_PROPS } from '@TutorShared/config/constants';
import { colorTokens } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import { Controller, useForm } from 'react-hook-form';
import { type Meta, type StoryObj } from 'storybook-react-rsbuild';

const meta = {
  title: 'Components/Fields/FormInputWithContent',
  component: FormInputWithContent,
  args: {
    label: 'Amount',
    content: '$',
    contentPosition: 'left',
    showVerticalBar: true,
    type: 'text',
    size: 'regular',
    disabled: false,
    readOnly: false,
    loading: false,
    placeholder: '',
    helpText: '',
    onChange: undefined,
    onKeyDown: undefined,
    isHidden: false,
    wrapperCss: undefined,
    contentCss: undefined,
    removeBorder: false,
    selectOnFocus: false,
    field: DEFAULT_FORM_FIELD_PROPS,
    fieldState: DEFAULT_FORM_FILED_STATE_PROPS,
  },
  tags: ['autodocs'],
  argTypes: {
    label: {
      control: 'text',
      description: 'Label for the input field. Can be a string or ReactNode.',
    },
    content: {
      control: 'text',
      description: 'Content to display inside the input (e.g., currency symbol, unit, ReactNode).',
    },
    contentPosition: {
      control: 'select',
      options: ['left', 'right'],
      defaultValue: 'left',
      description: 'Position of the content inside the input (left or right).',
    },
    showVerticalBar: {
      control: 'boolean',
      description: 'Shows a vertical bar separating the content from the input.',
    },
    type: {
      control: 'select',
      options: ['text', 'number'],
      defaultValue: 'text',
      description: 'Input type (text or number).',
    },
    size: {
      control: 'select',
      options: ['regular', 'large'],
      defaultValue: 'regular',
      description: 'Size of the input field.',
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
    onChange: {
      control: false,
      description: 'Callback for input value change event.',
    },
    onKeyDown: {
      control: false,
      description: 'Callback for keydown event on the input field.',
    },
    isHidden: {
      control: 'boolean',
      description: 'Hides the input field if true.',
    },
    wrapperCss: {
      control: false,
      description: 'Custom EmotionJs styles for the input wrapper.',
    },
    contentCss: {
      control: false,
      description: 'Custom EmotionJs styles for the content area.',
    },
    removeBorder: {
      control: 'boolean',
      description: 'Removes the border from the input field if true.',
    },
    selectOnFocus: {
      control: 'boolean',
      description: 'Selects the input value when focused if true.',
    },
    field: {
      control: false,
      description: 'Form controller props for managing input value and events.',
    },
    fieldState: {
      control: false,
      description: 'Form field state for validation and error handling.',
    },
  },
  parameters: {
    layout: 'centered',
    docs: {
      description: {
        component: `
FormInputWithContent is a flexible, accessible input field for forms, styled with EmotionJs and config tokens.
It supports left/right content (e.g., currency, unit), vertical bar, custom styling, help text, error state, and integrates with form controllers.
        `,
      },
    },
  },
  render: (args) => <FormInputWithContent {...args} />,
} satisfies Meta<typeof FormInputWithContent>;

export default meta;
type Story = StoryObj<typeof meta>;

export const Default = {} satisfies Story;

export const RightContent = {
  args: {
    content: 'kg',
    contentPosition: 'right',
    label: 'Weight',
    placeholder: 'Enter weight',
  },
} satisfies Story;

export const NoVerticalBar = {
  args: {
    showVerticalBar: false,
    label: 'Amount',
    content: '$',
    placeholder: 'Enter amount',
  },
} satisfies Story;

export const LargeSize = {
  args: {
    size: 'large',
    label: 'Large Input',
    content: 'USD',
    placeholder: 'Enter amount',
  },
} satisfies Story;

export const NumberType = {
  args: {
    type: 'number',
    label: 'Quantity',
    content: 'pcs',
    contentPosition: 'right',
    placeholder: 'Enter quantity',
  },
} satisfies Story;

export const WithHelpText = {
  args: {
    label: 'Price',
    content: '$',
    helpText: 'Enter the price in USD.',
    placeholder: '0.00',
  },
} satisfies Story;

export const Disabled = {
  args: {
    label: 'Disabled Input',
    content: '$',
    disabled: true,
    placeholder: 'Cannot edit',
  },
} satisfies Story;

export const ReadOnly = {
  args: {
    label: 'Read Only Input',
    content: '$',
    readOnly: true,
    placeholder: 'Read only value',
    field: { ...DEFAULT_FORM_FIELD_PROPS, value: '123.45' },
  },
} satisfies Story;

export const CustomContentStyle = {
  args: {
    label: 'Styled Content',
    content: 'ETH',
    placeholder: 'Enter amount',
  },
  render: (args) => (
    <FormInputWithContent
      {...args}
      contentCss={css`
        ${typography.heading5('bold')}
        color: ${colorTokens.brand.blue} satisfies Story;
      `}
    />
  ),
} satisfies Story;

export const Controlled = {
  render: (args) => {
    const form = useForm({
      defaultValues: {
        amount: '',
      },
    });

    return (
      <Controller
        name="amount"
        control={form.control}
        render={(controllerProps) => (
          <FormInputWithContent
            {...args}
            {...controllerProps}
            label="Controlled Input"
            content="$"
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
    content: '$',
    fieldState: {
      ...DEFAULT_FORM_FILED_STATE_PROPS,
      error: {
        type: 'required',
        message: 'This field is required',
      },
    },
  },
} satisfies Story;
