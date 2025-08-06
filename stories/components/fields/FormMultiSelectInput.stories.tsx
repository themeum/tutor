import FormMultiSelectInput from '@TutorShared/components/fields/FormMultiSelectInput';
import { DEFAULT_FORM_FIELD_PROPS, DEFAULT_FORM_FILED_STATE_PROPS } from '@TutorShared/config/constants';
import { Controller, useForm } from 'react-hook-form';
import { type Meta, type StoryObj } from 'storybook-react-rsbuild';

const options = [
  { value: 'react', label: 'ReactJS' },
  { value: 'vue', label: 'VueJS' },
  { value: 'angular', label: 'Angular' },
  { value: 'svelte', label: 'Svelte' },
  { value: 'ember', label: 'Ember' },
  { value: 'solid', label: 'SolidJS' },
];

const manyOptions = [
  ...options,
  { value: 'next', label: 'Next.js' },
  { value: 'nuxt', label: 'Nuxt.js' },
  { value: 'remix', label: 'Remix' },
  { value: 'gatsby', label: 'Gatsby' },
  { value: 'astro', label: 'Astro' },
  { value: 'qwik', label: 'Qwik' },
  { value: 'preact', label: 'Preact' },
  { value: 'lit', label: 'Lit' },
];

const meta = {
  title: 'Components/Fields/FormMultiSelectInput',
  component: FormMultiSelectInput,
  args: {
    label: 'Select Frameworks',
    options,
    placeholder: '',
    disabled: false,
    readOnly: false,
    loading: false,
    helpText: '',
    isHidden: false,
    removeOptionsMinWidth: false,
    field: DEFAULT_FORM_FIELD_PROPS,
    fieldState: DEFAULT_FORM_FILED_STATE_PROPS,
  },
  tags: ['autodocs'],
  argTypes: {
    label: {
      control: 'text',
      description: 'Label for the multi-select input. Can be a string or ReactNode.',
    },
    options: {
      control: false,
      description: 'Array of selectable options. Each option should have a value and label.',
    },
    placeholder: {
      control: 'text',
      description: 'Placeholder text for the input field.',
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
    helpText: {
      control: 'text',
      description: 'Additional help text displayed below the input.',
    },
    isHidden: {
      control: 'boolean',
      description: 'Hides the input field if true.',
    },
    removeOptionsMinWidth: {
      control: 'boolean',
      description: 'Removes the minimum width from the options dropdown if true.',
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
FormMultiSelectInput is a flexible, accessible multi-select input field for forms, styled with EmotionJs and config tokens.
It supports searching, selecting multiple options, custom styling, help text, error state, and integrates with form controllers.
        `,
      },
    },
  },
  render: (args) => <FormMultiSelectInput {...args} />,
} satisfies Meta<typeof FormMultiSelectInput>;

export default meta;
type Story = StoryObj<typeof meta>;

export const Default = {} satisfies Story;

export const WithPlaceholder = {
  args: {
    placeholder: 'Search frameworks...',
  },
} satisfies Story;

export const WithHelpText = {
  args: {
    helpText: 'Select all frameworks you are familiar with.',
  },
} satisfies Story;

export const Disabled = {
  args: {
    disabled: true,
    placeholder: 'Cannot select',
  },
} satisfies Story;

export const ReadOnly = {
  args: {
    readOnly: true,
    placeholder: 'Read only',
    field: { ...DEFAULT_FORM_FIELD_PROPS, value: ['react', 'vue'] },
  },
} satisfies Story;

export const Loading = {
  args: {
    loading: true,
    placeholder: 'Loading options...',
  },
} satisfies Story;

export const Controlled = {
  render: (args) => {
    const form = useForm({
      defaultValues: {
        frameworks: [],
      },
    });

    return (
      <Controller
        name="frameworks"
        control={form.control}
        render={(controllerProps) => (
          <FormMultiSelectInput
            {...args}
            {...controllerProps}
            label="Controlled Multi-Select"
            options={options}
            placeholder="Controlled by react-hook-form"
          />
        )}
      />
    );
  },
} satisfies Story;

export const WithError = {
  args: {
    label: 'Error Multi-Select',
    fieldState: {
      ...DEFAULT_FORM_FILED_STATE_PROPS,
      error: {
        type: 'required',
        message: 'Please select at least one framework',
      },
    },
  },
} satisfies Story;

export const NoMinWidth = {
  args: {
    removeOptionsMinWidth: true,
    placeholder: 'Dropdown has no min width',
  },
} satisfies Story;

export const ManyOptions = {
  args: {
    label: 'Select Many Frameworks',
    options: manyOptions,
    placeholder: 'Search frameworks...',
  },
} satisfies Story;
