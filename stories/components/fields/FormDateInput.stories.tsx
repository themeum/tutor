import FormDateInput from '@TutorShared/components/fields/FormDateInput';
import { DEFAULT_FORM_FIELD_PROPS, DEFAULT_FORM_FILED_STATE_PROPS, DateFormats } from '@TutorShared/config/constants';
import { Controller, useForm } from 'react-hook-form';
import { type Meta, type StoryObj } from 'storybook-react-rsbuild';

const meta = {
  title: 'Components/Fields/FormDateInput',
  component: FormDateInput,
  args: {
    label: 'Select a date',
    disabled: false,
    disabledBefore: undefined,
    disabledAfter: undefined,
    loading: false,
    placeholder: 'MM/DD/YYYY',
    helpText: '',
    isClearable: true,
    onChange: undefined,
    dateFormat: DateFormats.monthDayYear,
    field: DEFAULT_FORM_FIELD_PROPS,
    fieldState: DEFAULT_FORM_FILED_STATE_PROPS,
  },
  tags: ['autodocs'],
  argTypes: {
    field: { control: false },
    fieldState: { control: false },
  },
  parameters: {
    layout: 'centered',
    docs: {
      description: {
        component: `
FormDateInput is a flexible, accessible date input field for forms, styled with EmotionJs and config tokens.
It supports labels, descriptions, tooltips, custom styling, disabled dates, loading state, and integrates with form controllers.
        `,
      },
    },
  },
  render: (args) => <FormDateInput {...args} />,
} satisfies Meta<typeof FormDateInput>;

export default meta;
type Story = StoryObj<typeof meta>;

export const Default = {} satisfies Story;

export const WithHelpText = {
  args: {
    label: 'Birthday',
    helpText: 'Select your birth date.',
  },
} satisfies Story;

export const Disabled = {
  args: {
    label: 'Disabled Date Input',
    disabled: true,
  },
} satisfies Story;

export const Loading = {
  args: {
    label: 'Loading Date Input',
    loading: true,
  },
} satisfies Story;

export const WithDisabledRange = {
  args: {
    label: 'Date Range',
    disabledBefore: '2024-01-01',
    disabledAfter: '2024-12-31',
    helpText: 'Only dates in 2024 are selectable.',
  },
} satisfies Story;

export const CustomPlaceholder = {
  args: {
    label: 'Custom Placeholder',
    placeholder: 'YYYY-MM-DD',
    dateFormat: DateFormats.yearMonthDay,
  },
} satisfies Story;

export const Controlled = {
  render: () => {
    const form = useForm({
      defaultValues: {
        date: '',
      },
    });

    return (
      <Controller
        name="date"
        control={form.control}
        render={(controllerProps) => (
          <FormDateInput
            {...controllerProps}
            placeholder="YYYY-MM-DD"
            label="Controlled Date Input"
            helpText="This field is controlled by react-hook-form."
          />
        )}
      />
    );
  },
} satisfies Story;
