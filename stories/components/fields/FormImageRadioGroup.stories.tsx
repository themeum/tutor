import FormImageRadioGroup from '@TutorShared/components/fields/FormImageRadioGroup';
import { DEFAULT_FORM_FIELD_PROPS, DEFAULT_FORM_FILED_STATE_PROPS } from '@TutorShared/config/constants';
import { Controller, useForm } from 'react-hook-form';
import { type Meta, type StoryObj } from 'storybook-react-rsbuild';

const options = [
  {
    value: 'cat',
    label: 'Cat',
    image: 'https://placekeanu.com//64/64',
  },
  {
    value: 'dog',
    label: 'Dog',
    image: 'https://placedog.net/64/64',
  },
  {
    value: 'fox',
    label: 'Fox',
    image: 'https://randomfox.ca/images/64.jpg',
  },
  {
    value: 'panda',
    label: 'Panda',
    image: 'https://placebear.com/64/64',
  },
];

const moreOptions = [
  ...options,
  {
    value: 'lion',
    label: 'Lion',
    image: 'https://placedog.net/200/200?random=1',
  },
  {
    value: 'tiger',
    label: 'Tiger',
    image: 'https://placedog.net/200/200?random=2',
  },
];

const meta = {
  title: 'Components/Fields/FormImageRadioGroup',
  component: FormImageRadioGroup,
  args: {
    label: 'Select an animal',
    options,
    disabled: false,
    field: DEFAULT_FORM_FIELD_PROPS,
    fieldState: DEFAULT_FORM_FILED_STATE_PROPS,
  },
  tags: ['autodocs'],
  argTypes: {
    options: { control: false },
    field: { control: false },
    fieldState: { control: false },
  },
  parameters: {
    layout: 'centered',
    docs: {
      description: {
        component: `
FormImageRadioGroup is a flexible, accessible radio group field with images for forms, styled with EmotionJs and config tokens.
It supports labels, custom styling, disabled state, error state, and integrates with form controllers.
        `,
      },
    },
  },
  render: (args) => <FormImageRadioGroup {...args} />,
} satisfies Meta<typeof FormImageRadioGroup>;

export default meta;
type Story = StoryObj<typeof meta>;

export const Default = {} satisfies Story;

export const Disabled = {
  args: {
    disabled: true,
  },
} satisfies Story;

export const Controlled = {
  render: () => {
    const form = useForm({
      defaultValues: {
        animal: 'cat',
      },
    });

    return (
      <Controller
        name="animal"
        control={form.control}
        render={(controllerProps) => (
          <FormImageRadioGroup {...controllerProps} label="Controlled Animal" options={options} />
        )}
      />
    );
  },
} satisfies Story;

export const WithError = {
  args: {
    label: 'Select an animal (required)',
    options,
    fieldState: {
      ...DEFAULT_FORM_FILED_STATE_PROPS,
      error: {
        type: 'required',
        message: 'Please select an animal',
      },
    },
  },
} satisfies Story;

export const MoreOptions = {
  args: {
    label: 'Select your favorite animal',
    options: moreOptions,
  },
} satisfies Story;
