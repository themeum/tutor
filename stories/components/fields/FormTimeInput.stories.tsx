import FormTimeInput from '@TutorShared/components/fields/FormTimeInput';
import { DEFAULT_FORM_FIELD_PROPS, DEFAULT_FORM_FILED_STATE_PROPS } from '@TutorShared/config/constants';
import { Controller, useForm } from 'react-hook-form';
import { type Meta, type StoryObj } from 'storybook-react-rsbuild';

const meta = {
  title: 'Components/Fields/FormTimeInput',
  component: FormTimeInput,
  args: {
    label: 'Select Time',
    interval: 30,
    disabled: false,
    loading: false,
    placeholder: '',
    helpText: '',
    isClearable: true,
    field: DEFAULT_FORM_FIELD_PROPS,
    fieldState: DEFAULT_FORM_FILED_STATE_PROPS,
  },
  tags: ['autodocs'],
  argTypes: {
    label: {
      control: 'text',
      description: 'Label for the time input. Can be a string or ReactNode.',
    },
    interval: {
      control: 'number',
      description: 'Minute interval between selectable times (e.g., 15, 30, 60).',
    },
    disabled: {
      control: 'boolean',
      description: 'Disables the time input if true.',
    },
    loading: {
      control: 'boolean',
      description: 'Shows a loading indicator if true.',
    },
    placeholder: {
      control: 'text',
      description: 'Placeholder text for the time input.',
    },
    helpText: {
      control: 'text',
      description: 'Additional help text displayed below the input.',
    },
    isClearable: {
      control: 'boolean',
      description: 'Shows a clear button to reset the input value if true.',
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
FormTimeInput is a flexible, accessible time input field for forms, styled with EmotionJs and config tokens.
It supports minute intervals, clearable, custom styling, help text, error state, and integrates with form controllers.
        `,
      },
    },
  },
  render: (args) => <FormTimeInput {...args} />,
} satisfies Meta<typeof FormTimeInput>;

export default meta;
type Story = StoryObj<typeof meta>;

export const Default = {} satisfies Story;

export const WithPlaceholder = {
  args: {
    placeholder: 'Select a time...',
  },
} satisfies Story;

export const WithHelpText = {
  args: {
    helpText: 'Choose a time for your appointment.',
  },
} satisfies Story;

export const Interval15 = {
  args: {
    interval: 15,
    label: '15 Minute Interval',
    placeholder: 'Select time...',
  },
} satisfies Story;

export const Interval60 = {
  args: {
    interval: 60,
    label: 'Hourly Interval',
    placeholder: 'Select time...',
  },
} satisfies Story;

export const Disabled = {
  args: {
    disabled: true,
    label: 'Disabled Time Input',
    placeholder: 'Cannot select',
  },
} satisfies Story;

export const Loading = {
  args: {
    loading: true,
    label: 'Loading Time Input',
    placeholder: 'Loading...',
  },
} satisfies Story;

export const IsClearable = {
  args: {
    isClearable: true,
    field: { ...DEFAULT_FORM_FIELD_PROPS, value: '10:00' },
  },
} satisfies Story;

export const Controlled = {
  render: (args) => {
    const form = useForm({
      defaultValues: {
        time: '',
      },
    });

    return (
      <Controller
        name="time"
        control={form.control}
        render={(controllerProps) => (
          <FormTimeInput
            {...args}
            {...controllerProps}
            label="Controlled Time Input"
            placeholder="Controlled by react-hook-form"
          />
        )}
      />
    );
  },
} satisfies Story;

export const WithError = {
  args: {
    label: 'Error Time Input',
    fieldState: {
      ...DEFAULT_FORM_FILED_STATE_PROPS,
      error: {
        type: 'required',
        message: 'Please select a time',
      },
    },
  },
} satisfies Story;
