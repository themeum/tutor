import { css } from '@emotion/react';
import ImageInput, { type ImageInputSize } from '@TutorShared/atoms/ImageInput';
import { useState } from 'react';
import type { Meta, StoryObj } from 'storybook-react-rsbuild';

const mockMedia = {
  id: 1,
  url: 'https://placehold.co/300x168/png',
  title: 'Sample Image',
  alt: 'Sample Image Alt',
  mime: 'image/png',
};

const meta: Meta<typeof ImageInput> = {
  title: 'Atoms/ImageInput',
  component: ImageInput,
  tags: ['autodocs'],
  parameters: {
    layout: 'centered',
    docs: {
      description: {
        component:
          'ImageInput is a flexible, accessible component for uploading and previewing images. It supports loading, disabled, clearable, and custom styling states. Use for media uploads, avatars, or image pickers.',
      },
    },
  },
  argTypes: {
    buttonText: {
      control: 'text',
      description: 'Text for the upload button.',
      defaultValue: 'Upload Media',
    },
    infoText: {
      control: 'text',
      description: 'Optional info text below the button.',
      defaultValue: '',
    },
    size: {
      control: 'select',
      options: ['large', 'regular', 'small'],
      description: 'Size of the input.',
      defaultValue: 'regular',
    },
    value: {
      control: false,
      description: 'The selected media object.',
    },
    loading: {
      control: 'boolean',
      description: 'Show loading overlay.',
      defaultValue: false,
    },
    disabled: {
      control: 'boolean',
      description: 'Disable the input.',
      defaultValue: false,
    },
    isClearAble: {
      control: 'boolean',
      description: 'Show the clear/remove button.',
      defaultValue: true,
    },
    replaceButtonText: {
      control: 'text',
      description: 'Text for the replace button.',
    },
    emptyImageCss: {
      control: false,
      description: 'Custom Emotion CSS for the empty state.',
    },
    previewImageCss: {
      control: false,
      description: 'Custom Emotion CSS for the preview image.',
    },
    overlayCss: {
      control: false,
      description: 'Custom Emotion CSS for the overlay.',
    },
  },
  args: {
    buttonText: 'Upload Media',
    size: 'regular',
    value: null,
    uploadHandler: () => alert('Upload handler not implemented'),
    clearHandler: () => alert('Clear handler not implemented'),
    emptyImageCss: undefined,
    previewImageCss: undefined,
    overlayCss: undefined,
    replaceButtonText: 'Replace Image',
    loading: false,
    disabled: false,
    isClearAble: true,
    infoText: '',
  },
  render: (args) => (
    <div
      css={css`
        width: 500px;
      `}
    >
      <ImageInput {...args} />
    </div>
  ),
};
export default meta;

type Story = StoryObj<typeof meta>;

const handleUpload = () => {
  alert('Upload handler triggered');
};

const handleClear = () => {
  alert('Clear handler triggered');
};

export const Default: Story = {
  args: {
    value: null,
    buttonText: 'Upload Media',
    size: 'regular',
    uploadHandler: handleUpload,
    clearHandler: handleClear,
    disabled: false,
    loading: false,
    isClearAble: true,
  },
};

export const WithImage: Story = {
  args: {
    value: mockMedia,
    buttonText: 'Upload Media',
    size: 'regular',
    uploadHandler: handleUpload,
    clearHandler: handleClear,
    disabled: false,
    loading: false,
    isClearAble: true,
  },
};

export const Loading: Story = {
  args: {
    value: null,
    loading: true,
    uploadHandler: handleUpload,
    clearHandler: handleClear,
    size: 'regular',
  },
};

export const Disabled: Story = {
  args: {
    value: null,
    disabled: true,
    uploadHandler: handleUpload,
    clearHandler: handleClear,
    size: 'regular',
  },
};

export const Sizes: Story = {
  render: () => (
    <div style={{ display: 'flex', gap: 24 }}>
      {(['large', 'regular', 'small'] as ImageInputSize[]).map((size) => (
        <div
          key={size}
          css={css`
            width: ${size === 'large' ? '400px' : size === 'regular' ? '300px' : '200px'};
          `}
        >
          <ImageInput
            key={size}
            value={mockMedia}
            size={size}
            buttonText={`Upload (${size})`}
            uploadHandler={handleUpload}
            clearHandler={handleClear}
            aria-label={`Image input ${size}`}
          />
        </div>
      ))}
    </div>
  ),
};

export const WithInfoText: Story = {
  args: {
    value: null,
    buttonText: 'Upload Media',
    infoText: 'Recommended size: 300x168px. PNG or JPG.',
    uploadHandler: handleUpload,
    clearHandler: handleClear,
    size: 'regular',
  },
};

export const CustomStyles: Story = {
  args: {
    value: mockMedia,
    buttonText: 'Upload Media',
    size: 'regular',
    uploadHandler: handleUpload,
    clearHandler: handleClear,
    previewImageCss: css`
      border: 2px solid #1976d2;
      border-radius: 12px;
    `,
    emptyImageCss: css`
      background: #f0f4ff;
      border: 2px dashed #1976d2;
    `,
    overlayCss: css`
      background: rgba(25, 118, 210, 0.7);
    `,
  },
};

export const NotClearable: Story = {
  args: {
    value: mockMedia,
    isClearAble: false,
    uploadHandler: handleUpload,
    clearHandler: handleClear,
    size: 'regular',
  },
};

export const Controlled: Story = {
  render: () => {
    const [media, setMedia] = useState<typeof mockMedia | null>(null);

    const handleControlledUpload = () => {
      setMedia(mockMedia);
    };

    const handleControlledClear = () => {
      setMedia(null);
    };

    return (
      <div
        css={css`
          width: 500px;
        `}
      >
        <ImageInput
          value={media}
          buttonText={media ? 'Change Image' : 'Upload Media'}
          uploadHandler={handleControlledUpload}
          clearHandler={handleControlledClear}
          size="regular"
          aria-label="Controlled image input"
          isClearAble={!!media}
        />
      </div>
    );
  },
};
