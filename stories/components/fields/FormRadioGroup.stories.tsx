import { css } from '@emotion/react';
import FormRadioGroup from '@TutorShared/components/fields/FormRadioGroup';
import { DEFAULT_FORM_FIELD_PROPS, DEFAULT_FORM_FILED_STATE_PROPS } from '@TutorShared/config/constants';
import { colorTokens } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import { Controller, useForm } from 'react-hook-form';
import { type Meta, type StoryObj } from 'storybook-react-rsbuild';

const radioOptions = [
  { label: 'Beginner', value: 'beginner' },
  { label: 'Intermediate', value: 'intermediate' },
  { label: 'Advanced', value: 'advanced' },
];

const radioOptionsWithDescription = [
  { label: 'Beginner', value: 'beginner', description: 'For new learners.' },
  { label: 'Intermediate', value: 'intermediate', description: 'Some experience required.' },
  { label: 'Advanced', value: 'advanced', description: 'Expert level.' },
];

const radioOptionsWithLegend = [
  { label: 'Free', value: 'free', legend: 'No cost' },
  { label: 'Paid', value: 'paid', legend: 'Requires payment' },
];

const radioOptionsWithLabelCss = [
  {
    label: 'Option A',
    value: 'a',
    labelCss: css`
      ${typography.heading6('bold')}
      color: ${colorTokens.brand.blue};
    `,
  },
  {
    label: 'Option B',
    value: 'b',
    labelCss: css`
      ${typography.heading6('bold')}
      color: ${colorTokens.brand.blue};
    `,
  },
];

const meta = {
  title: 'Components/Fields/FormRadioGroup',
  component: FormRadioGroup,
  args: {
    label: 'Select Level',
    options: radioOptions,
    disabled: false,
    wrapperCss: undefined,
    gap: undefined,
    field: DEFAULT_FORM_FIELD_PROPS,
    fieldState: DEFAULT_FORM_FILED_STATE_PROPS,
    onSelect: undefined,
    onSelectRender: undefined,
  },
  tags: ['autodocs'],
  argTypes: {
    label: {
      control: 'text',
      description: 'Label for the radio group. Can be a string or ReactNode.',
    },
    options: {
      control: false,
      description:
        'Array of radio options. Each option should have a label, value, and optional disabled, legend, labelCss, description.',
    },
    disabled: {
      control: 'boolean',
      description: 'Disables all radio options if true.',
    },
    wrapperCss: {
      control: false,
      description: 'Custom EmotionJs styles for the radio group wrapper.',
    },
    gap: {
      control: 'number',
      description: 'Custom gap (px) between radio options.',
    },
    field: {
      control: false,
      description: 'Form controller props for managing selected value and events.',
    },
    fieldState: {
      control: false,
      description: 'Form field state for validation and error handling.',
    },
    onSelect: {
      control: false,
      description: 'Callback when a radio option is selected. Receives the selected option.',
    },
    onSelectRender: {
      control: false,
      description: 'Render function for custom content when an option is selected.',
    },
  },
  parameters: {
    layout: 'centered',
    docs: {
      description: {
        component: `
FormRadioGroup is a flexible, accessible radio group field for forms, styled with EmotionJs and config tokens.
It supports option descriptions, legends, custom styling, error state, controlled usage, and integrates with form controllers.
        `,
      },
    },
  },
  render: (args) => <FormRadioGroup {...args} />,
} satisfies Meta<typeof FormRadioGroup>;

export default meta;
type Story = StoryObj<typeof meta>;

export const Default = {} satisfies Story;

export const WithDescription = {
  args: {
    label: 'Select Level',
    options: radioOptionsWithDescription,
  },
} satisfies Story;

export const Disabled = {
  args: {
    label: 'Disabled Group',
    options: radioOptions,
    disabled: true,
  },
} satisfies Story;

export const WithLegend = {
  args: {
    label: 'Select Plan',
    options: radioOptionsWithLegend,
  },
} satisfies Story;

export const CustomOptionLabelStyle = {
  args: {
    label: 'Styled Option Labels',
    options: radioOptionsWithLabelCss,
  },
} satisfies Story;

export const Controlled = {
  render: (args) => {
    const form = useForm({
      defaultValues: {
        level: 'beginner',
      },
    });

    return (
      <Controller
        name="level"
        control={form.control}
        render={(controllerProps) => (
          <FormRadioGroup {...args} {...controllerProps} label="Controlled Radio Group" options={radioOptions} />
        )}
      />
    );
  },
} satisfies Story;

export const WithError = {
  args: {
    label: 'Error Radio Group',
    options: radioOptions,
    fieldState: {
      ...DEFAULT_FORM_FILED_STATE_PROPS,
      error: {
        type: 'required',
        message: 'Please select a level',
      },
    },
  },
} satisfies Story;

export const Gap = {
  args: {
    label: 'Custom Gap',
    options: radioOptions,
    gap: 24,
    wrapperCss: css`
      display: flex;
      flex-direction: column;
      gap: 24px;
    `,
  },
} satisfies Story;
