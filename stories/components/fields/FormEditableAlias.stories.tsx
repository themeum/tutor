import FormEditableAlias from '@TutorShared/components/fields/FormEditableAlias';
import { DEFAULT_FORM_FIELD_PROPS, DEFAULT_FORM_FILED_STATE_PROPS } from '@TutorShared/config/constants';
import { Controller, useForm } from 'react-hook-form';
import { type Meta, type StoryObj } from 'storybook-react-rsbuild';

const meta = {
  title: 'Components/Fields/FormEditableAlias',
  component: FormEditableAlias,
  args: {
    label: 'Course URL',
    baseURL: 'https://tutor.local/course',
    field: { ...DEFAULT_FORM_FIELD_PROPS, value: 'my-course' },
    fieldState: DEFAULT_FORM_FILED_STATE_PROPS,
    onChange: undefined,
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
FormEditableAlias is an editable alias field for URLs, styled with EmotionJs and config tokens.
It supports editing, saving, canceling, and displays the full URL with accessibility features.
        `,
      },
    },
  },
  render: (args) => <FormEditableAlias {...args} />,
} satisfies Meta<typeof FormEditableAlias>;

export default meta;
type Story = StoryObj<typeof meta>;

export const Default = {} satisfies Story;

export const CustomBaseURL = {
  args: {
    baseURL: 'https://mysite.com/lesson',
    field: { ...DEFAULT_FORM_FIELD_PROPS, value: 'intro-to-react' },
  },
} satisfies Story;

export const WithOnChange = {
  args: {
    onChange: (newValue: string) => {
      alert(`Alias changed to: ${newValue}`);
    },
  },
} satisfies Story;

export const Controlled = {
  render: () => {
    const form = useForm({
      defaultValues: {
        alias: 'controlled-alias',
      },
    });

    return (
      <Controller
        name="alias"
        control={form.control}
        render={(controllerProps) => (
          <FormEditableAlias
            {...controllerProps}
            label="Controlled Alias"
            baseURL="https://tutor.local/course"
            onChange={(newValue) => form.setValue('alias', newValue)}
          />
        )}
      />
    );
  },
} satisfies Story;
