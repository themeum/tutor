import { css } from '@emotion/react';
import FormInputWithPresets from '@TutorShared/components/fields/FormInputWithPresets';
import { DEFAULT_FORM_FIELD_PROPS, DEFAULT_FORM_FILED_STATE_PROPS } from '@TutorShared/config/constants';
import { colorTokens } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import { Controller, useForm } from 'react-hook-form';
import { type Meta, type StoryObj } from 'storybook-react-rsbuild';

const presetOptions = [
  { value: '10', label: 'Ten' },
  { value: '20', label: 'Twenty' },
  { value: '30', label: 'Thirty' },
  { value: '40', label: 'Forty' },
];

const meta = {
  title: 'Components/Fields/FormInputWithPresets',
  component: FormInputWithPresets,
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
    presetOptions,
    selectOnFocus: false,
    wrapperCss: undefined,
    contentCss: undefined,
    removeBorder: false,
    removeOptionsMinWidth: true,
    isHidden: false,
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
    presetOptions: {
      control: false,
      description: 'Array of preset options for quick selection. Each option should have a value and label.',
    },
    selectOnFocus: {
      control: 'boolean',
      description: 'Selects the input value when focused if true.',
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
    removeOptionsMinWidth: {
      control: 'boolean',
      description: 'Removes the minimum width from the options dropdown if true.',
    },
    isHidden: {
      control: 'boolean',
      description: 'Hides the input field if true.',
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
FormInputWithPresets is a flexible, accessible input field for forms, styled with EmotionJs and config tokens.
It supports left/right content (e.g., currency, unit), vertical bar, preset options dropdown, custom styling, help text, error state, and integrates with form controllers.
        `,
      },
    },
  },
  render: (args) => <FormInputWithPresets {...args} />,
} satisfies Meta<typeof FormInputWithPresets>;

export default meta;
type Story = StoryObj<typeof meta>;

export const Default = {} satisfies Story;

export const WithLeftContent = {
  args: {
    content: '$',
    contentPosition: 'left',
    label: 'Amount',
    presetOptions,
    placeholder: 'Enter amount',
  },
} satisfies Story;

export const WithRightContent = {
  args: {
    content: 'kg',
    contentPosition: 'right',
    label: 'Weight',
    presetOptions,
    placeholder: 'Enter weight',
  },
} satisfies Story;

export const LargeSize = {
  args: {
    size: 'large',
    label: 'Large Input',
    content: 'USD',
    presetOptions,
    placeholder: 'Enter amount',
  },
} satisfies Story;

export const NumberType = {
  args: {
    type: 'number',
    label: 'Quantity',
    content: 'pcs',
    contentPosition: 'right',
    presetOptions: [
      { value: '1', label: 'One' },
      { value: '5', label: 'Five' },
      { value: '10', label: 'Ten' },
    ],
    placeholder: 'Enter quantity',
  },
} satisfies Story;

export const WithHelpText = {
  args: {
    label: 'Price',
    content: '$',
    helpText: 'Enter the price in USD or select a preset.',
    presetOptions,
    placeholder: '0.00',
  },
} satisfies Story;

export const Disabled = {
  args: {
    label: 'Disabled Input',
    content: '$',
    disabled: true,
    presetOptions,
    placeholder: 'Cannot edit',
  },
} satisfies Story;

export const ReadOnly = {
  args: {
    label: 'Read Only Input',
    content: '$',
    readOnly: true,
    presetOptions,
    placeholder: 'Read only value',
    field: { ...DEFAULT_FORM_FIELD_PROPS, value: '123.45' },
  },
} satisfies Story;

export const CustomContentStyle = {
  args: {
    label: 'Styled Content',
    content: 'ETH',
    presetOptions,
    placeholder: 'Enter amount',
  },
  render: (args) => (
    <FormInputWithPresets
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
          <FormInputWithPresets
            {...args}
            {...controllerProps}
            label="Controlled Input"
            content="$"
            presetOptions={presetOptions}
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
    presetOptions,
    fieldState: {
      ...DEFAULT_FORM_FILED_STATE_PROPS,
      error: {
        type: 'required',
        message: 'This field is required',
      },
    },
  },
} satisfies Story;

export const NoMinWidth = {
  args: {
    label: 'No Min Width',
    content: '$',
    presetOptions,
    removeOptionsMinWidth: true,
    placeholder: 'Dropdown has no min width',
  },
} satisfies Story;
