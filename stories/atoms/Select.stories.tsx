import Select from '@TutorShared/atoms/Select';
import { useState } from 'react';
import type { Meta, StoryObj } from 'storybook-react-rsbuild';

const options = [
  { label: 'Option One', value: 'one' },
  { label: 'Option Two', value: 'two' },
  { label: 'Option Three', value: 'three' },
];

const manyOptions = Array.from({ length: 100 }, (_, i) => ({
  label: `Option ${i + 1}`,
  value: `${i + 1}`,
}));

const meta: Meta<typeof Select> = {
  title: 'Atoms/Select',
  component: Select,
  tags: ['autodocs'],
  parameters: {
    layout: 'centered',
    docs: {
      description: {
        component:
          'Select is a flexible dropdown component supporting search, clear, infinite scroll, custom styles, and accessibility. Use for single-choice selections in forms or filters.',
      },
    },
  },
  argTypes: {
    options: {
      control: false,
      description: 'Array of options for the dropdown.',
    },
    value: {
      control: false,
      description: 'Selected option object.',
    },
    label: {
      control: 'text',
      description: 'Label for the select input.',
    },
    placeholder: {
      control: 'text',
      description: 'Placeholder text for the input.',
    },
    isSearchable: {
      control: 'boolean',
      description: 'Enable search input.',
      defaultValue: false,
    },
    isClearable: {
      control: 'boolean',
      description: 'Show clear button.',
      defaultValue: true,
    },
    disabled: {
      control: 'boolean',
      description: 'Disable the select input.',
      defaultValue: false,
    },
    loading: {
      control: 'boolean',
      description: 'Show loading spinner.',
      defaultValue: false,
    },
    size: {
      control: 'select',
      options: ['regular', 'small'],
      description: 'Size of the select input.',
      defaultValue: 'regular',
    },
    optionsStyleVariant: {
      control: 'select',
      options: ['regular', 'small'],
      description: 'Style variant for the options list.',
      defaultValue: 'regular',
    },
    infiniteScroll: {
      control: 'boolean',
      description: 'Enable infinite scroll for large option lists.',
      defaultValue: false,
    },
    isInlineLabel: {
      control: 'boolean',
      description: 'Show label inline with input.',
      defaultValue: false,
    },
    isFontWeight: {
      control: 'boolean',
      description: 'Apply font weight to options.',
      defaultValue: false,
    },
    inputCss: {
      control: false,
      description: 'Custom Emotion CSS for the input element.',
    },
    wrapperStyle: {
      control: false,
      description: 'Custom Emotion CSS for the wrapper element.',
    },
    readOnly: {
      control: 'boolean',
      description: 'Make the select input read-only.',
      defaultValue: false,
    },
    caretSize: {
      control: 'number',
      description: 'Size of the caret icon.',
      defaultValue: 16,
    },
    clearOption: {
      control: false,
      description: 'Function to clear the selected option.',
    },
  },
  args: {
    options: [],
    value: undefined,
    label: '',
    placeholder: 'Select an option',
    isSearchable: false,
    isClearable: true,
    disabled: false,
    loading: false,
    size: 'regular',
    optionsStyleVariant: 'regular',
    infiniteScroll: false,
    isInlineLabel: false,
    isFontWeight: false,
    readOnly: false,
    caretSize: 16,
    clearOption: undefined,
  },
  render: (args) => <Select {...args} aria-label={args.label || 'Select'} />,
};
export default meta;

type Story = StoryObj<typeof meta>;

export const Default: Story = {
  args: {
    options,
    placeholder: 'Select an option',
  },
};

export const WithLabelAndPlaceholder: Story = {
  args: {
    options,
    label: 'Choose an option',
    placeholder: 'Please select...',
  },
};

export const Searchable: Story = {
  args: {
    options,
    isSearchable: true,
    placeholder: 'Search options...',
  },
};

export const Clearable: Story = {
  args: {
    options,
    isClearable: true,
    placeholder: 'Clearable select',
  },
};

export const Disabled: Story = {
  args: {
    options,
    disabled: true,
    label: 'Disabled Select',
    placeholder: 'Cannot select',
  },
};

export const Loading: Story = {
  args: {
    options,
    loading: true,
    label: 'Loading Select',
    placeholder: 'Loading...',
  },
};

export const SmallSize: Story = {
  args: {
    options,
    size: 'small',
    label: 'Small Select',
    placeholder: 'Small size',
  },
};

export const CustomOptionStyle: Story = {
  args: {
    options,
    optionsStyleVariant: 'small',
    label: 'Custom Option Style',
    placeholder: 'Small style options',
  },
};

export const WithManyOptionsInfiniteScroll: Story = {
  args: {
    options: manyOptions,
    infiniteScroll: true,
    isSearchable: true,
    label: 'Infinite Scroll',
    placeholder: 'Scroll or search...',
  },
};

export const Controlled: Story = {
  render: () => {
    const [selected, setSelected] = useState<{ label: string; value: string } | undefined>(undefined);

    const handleChange = (option: { label: string; value: string }) => setSelected(option);
    const handleClear = () => setSelected(undefined);

    return (
      <Select
        options={options}
        value={selected}
        onChange={handleChange}
        clearOption={handleClear}
        label="Controlled Select"
        placeholder="Pick one"
        isClearable
        aria-label="Controlled Select"
      />
    );
  },
};
