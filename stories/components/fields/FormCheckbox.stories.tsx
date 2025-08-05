import { css } from '@emotion/react';
import FormCheckbox from '@TutorShared/components/fields/FormCheckbox';
import { DEFAULT_FORM_FIELD_PROPS, DEFAULT_FORM_FILED_STATE_PROPS } from '@TutorShared/config/constants';
import { colorTokens } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import { Controller, useForm } from 'react-hook-form';
import { type Meta, type StoryObj } from 'storybook-react-rsbuild';

const meta = {
  title: 'Components/Fields/FormCheckbox',
  component: FormCheckbox,
  args: {
    label: 'Accept Terms & Conditions',
    description: '',
    helpText: '',
    disabled: false,
    isHidden: false,
    labelCss: undefined,
    value: undefined,
    field: DEFAULT_FORM_FIELD_PROPS,
    fieldState: DEFAULT_FORM_FILED_STATE_PROPS,
    onChange: undefined,
  },
  tags: ['autodocs'],
  argTypes: {
    labelCss: { control: false },
    field: { control: false },
    fieldState: { control: false },
  },
  parameters: {
    layout: 'centered',
    docs: {
      description: {
        component: `
FormCheckbox is a flexible, accessible checkbox field for forms, styled with EmotionJs and config tokens.
It supports labels, descriptions, tooltips, custom styling, and integrates with form controllers.
        `,
      },
    },
  },
  render: (args) => <FormCheckbox {...args} />,
} satisfies Meta<typeof FormCheckbox>;

export default meta;
type Story = StoryObj<typeof meta>;

export const Default = {} satisfies Story;

export const WithDescription = {
  args: {
    label: 'Enable notifications',
    description: 'You will receive updates about new features.',
  },
} satisfies Story;

export const WithHelpText = {
  args: {
    label: 'I agree to the privacy policy',
    helpText: 'Read our privacy policy for more details.',
  },
} satisfies Story;

export const Disabled = {
  args: {
    label: 'Disabled Checkbox',
    disabled: true,
  },
} satisfies Story;

export const WithError = {
  args: {
    label: 'Error Checkbox',
    fieldState: {
      ...DEFAULT_FORM_FILED_STATE_PROPS,
      error: {
        type: 'required',
        message: 'This field is required',
      },
    },
  },
} satisfies Story;

export const Hidden = {
  args: {
    label: 'Hidden Checkbox',
    isHidden: true,
  },
} satisfies Story;

export const CustomLabelStyle = {
  args: {
    label: 'Custom Styled Label',
    labelCss: css`
      ${typography.heading6('bold')}
      color: ${colorTokens.brand.blue} satisfies Story;
    `,
  },
} satisfies Story;

export const Controlled = {
  render: () => {
    const form = useForm({
      defaultValues: {
        checkbox: false,
      },
    });

    return (
      <Controller
        name="checkbox"
        control={form.control}
        render={(controllerProps) => <FormCheckbox {...controllerProps} label="Controlled Checkbox" />}
      />
    );
  },
} satisfies Story;
