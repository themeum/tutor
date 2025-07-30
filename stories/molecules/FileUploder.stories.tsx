import { css } from '@emotion/react';
import FileUploader from '@TutorShared/molecules/FileUploader';
import { useState } from 'react';
import type { Meta, StoryObj } from 'storybook-react-rsbuild';

const meta = {
  title: 'Molecules/FileUploader',
  component: FileUploader,
  tags: ['autodocs'],
  parameters: {
    layout: 'centered',
    docs: {
      description: {
        component:
          'FileUploader is a flexible file upload component supporting custom file types, multiple selection, max file size, and accessibility features.',
      },
    },
  },
  argTypes: {
    label: {
      control: 'text',
      description: 'Label for the upload button.',
      defaultValue: 'Upload File',
    },
    acceptedTypes: {
      control: 'object',
      description: 'Accepted file extensions.',
      defaultValue: [],
    },
    multiple: {
      control: 'boolean',
      description: 'Allow multiple file selection.',
      defaultValue: false,
    },
    disabled: {
      control: 'boolean',
      description: 'Disable the uploader.',
      defaultValue: false,
    },
    maxFileSize: {
      control: 'number',
      description: 'Maximum file size in bytes.',
      defaultValue: 2 * 1024 * 1024, // 2MB
    },
    onUpload: { control: false },
    onError: { control: false },
    fullWidth: {
      control: 'boolean',
      description: 'Make the uploader full width.',
      defaultValue: true,
    },
  },
  args: {
    label: 'Upload File',
    acceptedTypes: ['jpg', 'jpeg', 'png', 'pdf'],
    multiple: false,
    disabled: false,
    maxFileSize: 2 * 1024 * 1024,
    fullWidth: true,
    onError: () => {},
    onUpload: () => {},
  },
  render: (args) => {
    const [uploadedFiles, setUploadedFiles] = useState<File[]>([]);
    const [errorMessages, setErrorMessages] = useState<string[]>([]);

    const handleUpload = (files: File[]) => {
      setUploadedFiles(files);
      setErrorMessages([]);
    };

    const handleError = (errors: string[]) => {
      setErrorMessages(errors);
      setUploadedFiles([]);
    };

    return (
      <div
        css={css`
          display: flex;
          flex-direction: column;
          align-items: center;
          gap: 24px;
          width: 400px;
          height: 200px;
        `}
      >
        <FileUploader {...args} onUpload={handleUpload} onError={handleError} aria-label={args.label} />
        {uploadedFiles.length > 0 && (
          <div
            css={css`
              margin-top: 8px;
              color: #1976d2;
              font-size: 1rem;
            `}
            aria-label="Uploaded files"
            tabIndex={0}
          >
            <strong>Uploaded Files:</strong>
            <ul>
              {uploadedFiles.map((file) => (
                <li key={file.name}>
                  {file.name} ({(file.size / 1024).toFixed(1)} KB)
                </li>
              ))}
            </ul>
          </div>
        )}
        {errorMessages.length > 0 && (
          <div
            css={css`
              margin-top: 8px;
              color: #d32f2f;
              font-size: 1rem;
            `}
            aria-label="Upload errors"
            tabIndex={0}
          >
            <strong>Error:</strong>
            <ul>
              {errorMessages.map((msg, idx) => (
                <li key={idx}>{msg}</li>
              ))}
            </ul>
          </div>
        )}
      </div>
    );
  },
} satisfies Meta<typeof FileUploader>;

export default meta;

type Story = StoryObj<typeof meta>;

export const Default = {} satisfies Story;

export const MultipleFiles = {
  args: {
    multiple: true,
    label: 'Upload Multiple Files',
  },
} satisfies Story;

export const Disabled = {
  args: {
    disabled: true,
    label: 'Uploader Disabled',
  },
} satisfies Story;

export const CustomFileTypes = {
  args: {
    acceptedTypes: ['docx', 'xlsx', 'pptx'],
    label: 'Upload Document',
  },
} satisfies Story;

export const CustomMaxFileSize = {
  args: {
    maxFileSize: 512 * 1024, // 512KB
    label: 'Max 512KB',
  },
} satisfies Story;
