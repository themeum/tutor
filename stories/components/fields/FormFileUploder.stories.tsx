import FormFileUploader from '@TutorShared/components/fields/FormFileUploader';
import { DEFAULT_FORM_FIELD_PROPS, DEFAULT_FORM_FILED_STATE_PROPS } from '@TutorShared/config/constants';
import { Controller, useForm } from 'react-hook-form';
import { type Meta, type StoryObj } from 'storybook-react-rsbuild';

const meta = {
  title: 'Components/Fields/FormFileUploader',
  component: FormFileUploader,
  args: {
    label: 'Upload File',
    helpText: '',
    buttonText: undefined,
    selectMultiple: false,
    maxFiles: undefined,
    maxFileSize: undefined,
    field: DEFAULT_FORM_FIELD_PROPS,
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
FormFileUploader is a flexible, accessible file upload field for forms, styled with EmotionJs and config tokens.
It supports single/multiple uploads, file size and count limits, custom button text, help text, and integrates with form controllers.
        `,
      },
    },
  },
  render: (args) => <FormFileUploader {...args} />,
} satisfies Meta<typeof FormFileUploader>;

export default meta;
type Story = StoryObj<typeof meta>;

export const Default: Story = {
  args: {},
};

export const WithHelpText: Story = {
  args: {
    helpText: 'Supported formats: PDF, DOCX, JPG, PNG.',
  },
};

export const MultipleFiles: Story = {
  args: {
    selectMultiple: true,
    label: 'Upload Multiple Files',
    helpText: 'You can select multiple files.',
  },
};

export const MaxFiles: Story = {
  args: {
    selectMultiple: true,
    maxFiles: 2,
    label: 'Upload up to 2 files',
    helpText: 'Maximum 2 files allowed.',
  },
};

export const MaxFileSize: Story = {
  args: {
    maxFileSize: 1024 * 1024, // 1MB
    label: 'Upload File (Max 1MB)',
    helpText: 'Maximum file size: 1MB.',
  },
};

export const CustomButtonText: Story = {
  args: {
    buttonText: 'Attach Document',
    label: 'Document Upload',
  },
};

export const Controlled: Story = {
  render: () => {
    const form = useForm({
      defaultValues: {
        files: null,
      },
    });

    return (
      <Controller
        name="files"
        control={form.control}
        render={(controllerProps) => (
          <FormFileUploader
            {...controllerProps}
            label="Controlled File Uploader"
            helpText="This uploader is controlled by react-hook-form."
            selectMultiple
          />
        )}
      />
    );
  },
};
