import { css } from '@emotion/react';
import FormRangeSliderField from '@TutorShared/components/fields/FormRangeSliderField';
import { DEFAULT_FORM_FIELD_PROPS, DEFAULT_FORM_FILED_STATE_PROPS } from '@TutorShared/config/constants';
import { Controller, useForm } from 'react-hook-form';
import { type Meta, type StoryObj } from 'storybook-react-rsbuild';

const meta = {
  title: 'Components/Fields/FormRangeSliderField',
  component: FormRangeSliderField,
  args: {
    label: 'Progress',
    min: 0,
    max: 100,
    isMagicAi: false,
    hasBorder: false,
    field: { ...DEFAULT_FORM_FIELD_PROPS, value: 50 },
    fieldState: DEFAULT_FORM_FILED_STATE_PROPS,
  },
  tags: ['autodocs'],
  argTypes: {
    label: {
      control: 'text',
      description: 'Label for the range slider field. Can be a string or ReactNode.',
    },
    min: {
      control: 'number',
      description: 'Minimum value for the slider.',
    },
    max: {
      control: 'number',
      description: 'Maximum value for the slider.',
    },
    isMagicAi: {
      control: 'boolean',
      description: 'Enables Magic AI styling for the slider thumb and fill.',
    },
    hasBorder: {
      control: 'boolean',
      description: 'Adds a border around the slider field.',
    },
    field: {
      control: false,
      description: 'Form controller props for managing slider value and events.',
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
FormRangeSliderField is a flexible, accessible range slider field for forms, styled with EmotionJs and config tokens.
It supports custom min/max, Magic AI styling, border, error state, controlled usage, and integrates with form controllers.
        `,
      },
    },
  },
  render: (args) => (
    <div
      css={css`
        width: 300px;
      `}
    >
      <FormRangeSliderField {...args} />
    </div>
  ),
} satisfies Meta<typeof FormRangeSliderField>;

export default meta;
type Story = StoryObj<typeof meta>;

export const Default = {} satisfies Story;

export const WithLabel = {
  args: {
    label: 'Completion',
  },
} satisfies Story;

export const CustomMinMax = {
  args: {
    label: 'Custom Range',
    min: 10,
    max: 200,
    field: { ...DEFAULT_FORM_FIELD_PROPS, value: 50 },
  },
} satisfies Story;

export const MagicAi: Story = {
  args: {
    label: 'AI Progress',
    isMagicAi: true,
  },
};

export const WithBorder = {
  args: {
    label: 'Slider with Border',
    hasBorder: true,
  },
} satisfies Story;

export const Disabled = {
  args: {
    label: 'Disabled Slider',
    field: { ...DEFAULT_FORM_FIELD_PROPS, value: 30 },
  },
} satisfies Story;

export const Controlled = {
  render: (args) => {
    const form = useForm({
      defaultValues: {
        progress: 75,
      },
    });

    return (
      <div
        css={css`
          width: 300px;
        `}
      >
        <Controller
          name="progress"
          control={form.control}
          render={(controllerProps) => (
            <FormRangeSliderField {...args} {...controllerProps} label="Controlled Range Slider" />
          )}
        />
      </div>
    );
  },
} satisfies Story;

export const WithError = {
  args: {
    label: 'Error Slider',
    fieldState: {
      ...DEFAULT_FORM_FILED_STATE_PROPS,
      error: {
        type: 'required',
        message: 'Please select a value',
      },
    },
  },
} satisfies Story;
