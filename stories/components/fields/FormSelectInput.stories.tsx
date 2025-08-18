import { css } from '@emotion/react';
import FormSelectInput from '@TutorShared/components/fields/FormSelectInput';
import { DEFAULT_FORM_FIELD_PROPS, DEFAULT_FORM_FILED_STATE_PROPS } from '@TutorShared/config/constants';
import { colorTokens } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import { Controller, useForm } from 'react-hook-form';
import { type Meta, type StoryObj } from 'storybook-react-rsbuild';

const selectOptionsWithoutDescription = [
  { label: 'ReactJS', value: 'react' },
  { label: 'VueJS', value: 'vue' },
  { label: 'Angular', value: 'angular' },
  { label: 'Svelte', value: 'svelte' },
  { label: 'Ember', value: 'ember' },
];

const selectOptionsWithDescription = [
  { label: 'ReactJS', value: 'react', description: 'A JavaScript library for building UI.' },
  { label: 'VueJS', value: 'vue', description: 'The Progressive JavaScript Framework.' },
  { label: 'Angular', value: 'angular', description: 'One framework. Mobile & desktop.' },
  { label: 'Svelte', value: 'svelte', description: 'Cybernetically enhanced web apps.' },
  { label: 'Ember', value: 'ember', description: 'A framework for ambitious web developers.' },
];

const meta = {
  title: 'Components/Fields/FormSelectInput',
  component: FormSelectInput,
  args: {
    label: 'Select Framework',
    options: selectOptionsWithoutDescription,
    placeholder: '',
    disabled: false,
    readOnly: false,
    loading: false,
    isSearchable: false,
    isInlineLabel: false,
    hideCaret: false,
    listLabel: '',
    isClearable: false,
    helpText: '',
    removeOptionsMinWidth: false,
    leftIcon: undefined,
    removeBorder: false,
    dataAttribute: undefined,
    isSecondary: false,
    isMagicAi: false,
    isAiOutline: false,
    selectOnFocus: false,
    isHidden: false,
    field: DEFAULT_FORM_FIELD_PROPS,
    fieldState: DEFAULT_FORM_FILED_STATE_PROPS,
    onChange: undefined,
  },
  tags: ['autodocs'],
  argTypes: {
    label: {
      control: 'text',
      description: 'Label for the select input. Can be a string or ReactNode.',
    },
    options: {
      control: false,
      description: 'Array of selectable options. Each option should have a value, label, and optional description.',
    },
    placeholder: {
      control: 'text',
      description: 'Placeholder text for the select input.',
    },
    disabled: {
      control: 'boolean',
      description: 'Disables the select input if true.',
    },
    readOnly: {
      control: 'boolean',
      description: 'Makes the select input read-only if true.',
    },
    loading: {
      control: 'boolean',
      description: 'Shows a loading indicator if true.',
    },
    isSearchable: {
      control: 'boolean',
      description: 'Enables search functionality for options.',
    },
    isInlineLabel: {
      control: 'boolean',
      description: 'Displays the label inline with the select input if true.',
    },
    hideCaret: {
      control: 'boolean',
      description: 'Hides the caret icon if true.',
    },
    listLabel: {
      control: 'text',
      description: 'Label displayed above the options list.',
    },
    isClearable: {
      control: 'boolean',
      description: 'Shows a clear button to reset the selection if true.',
    },
    helpText: {
      control: 'text',
      description: 'Additional help text displayed below the select input.',
    },
    removeOptionsMinWidth: {
      control: 'boolean',
      description: 'Removes the minimum width from the options dropdown if true.',
    },
    leftIcon: {
      control: false,
      description: 'Custom icon to display on the left side of the input.',
    },
    removeBorder: {
      control: 'boolean',
      description: 'Removes the border from the select input if true.',
    },
    dataAttribute: {
      control: 'text',
      description: 'Custom data attribute for the select element.',
    },
    isSecondary: {
      control: 'boolean',
      description: 'Applies secondary styling to the select input.',
    },
    isMagicAi: {
      control: 'boolean',
      description: 'Enables Magic AI styling and features if true.',
    },
    isAiOutline: {
      control: 'boolean',
      description: 'Enables AI outline styling if true.',
    },
    selectOnFocus: {
      control: 'boolean',
      description: 'Selects the input value when focused if true.',
    },
    isHidden: {
      control: 'boolean',
      description: 'Hides the select input if true.',
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
      description: 'Callback for select value change event.',
    },
  },
  parameters: {
    layout: 'centered',
    docs: {
      description: {
        component: `
FormSelectInput is a flexible, accessible select input field for forms, styled with EmotionJs and config tokens.
It supports searching, option descriptions, clearable, custom styling, help text, error state, and integrates with form controllers.
        `,
      },
    },
  },
  render: (args) => <FormSelectInput {...args} />,
} satisfies Meta<typeof FormSelectInput>;

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
    helpText: 'Select your favorite framework.',
  },
} satisfies Story;

export const WithOptionDescription = {
  args: {
    options: selectOptionsWithDescription,
    placeholder: 'Select a framework',
    field: { ...DEFAULT_FORM_FIELD_PROPS, value: 'react' },
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
    field: { ...DEFAULT_FORM_FIELD_PROPS, value: 'react' },
  },
} satisfies Story;

export const Loading = {
  args: {
    loading: true,
    placeholder: 'Loading options...',
  },
} satisfies Story;

export const IsSearchable = {
  args: {
    isSearchable: true,
    placeholder: 'Type to search...',
  },
} satisfies Story;

export const CustomLabelStyle = {
  args: {
    label: 'Styled Label',
    placeholder: 'Styled label select',
  },
  render: (args) => (
    <FormSelectInput
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
        framework: '',
      },
    });

    return (
      <Controller
        name="framework"
        control={form.control}
        render={(controllerProps) => (
          <FormSelectInput
            {...args}
            {...controllerProps}
            label="Controlled Select"
            options={selectOptionsWithoutDescription}
            placeholder="Controlled by react-hook-form"
          />
        )}
      />
    );
  },
} satisfies Story;

export const WithError = {
  args: {
    label: 'Error Select',
    fieldState: {
      ...DEFAULT_FORM_FILED_STATE_PROPS,
      error: {
        type: 'required',
        message: 'Please select a framework',
      },
    },
  },
} satisfies Story;

export const IsClearable = {
  args: {
    isClearable: true,
    field: { ...DEFAULT_FORM_FIELD_PROPS, value: 'vue' },
  },
} satisfies Story;

export const RemoveOptionsMinWidth = {
  args: {
    removeOptionsMinWidth: true,
    placeholder: 'Dropdown has no min width',
  },
} satisfies Story;

export const WithListLabel = {
  args: {
    listLabel: 'Frameworks',
  },
} satisfies Story;
