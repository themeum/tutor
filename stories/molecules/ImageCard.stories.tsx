import { css } from '@emotion/react';
import productPlaceholder from '@SharedImages/course-placeholder.png';
import ImageCard from '@TutorShared/molecules/ImageCard';
import type { Meta, StoryObj } from 'storybook-react-rsbuild';

const meta = {
  title: 'Molecules/ImageCard',
  component: ImageCard,
  tags: ['autodocs'],
  parameters: {
    layout: 'centered',
    docs: {
      description: {
        component:
          'ImageCard displays an image with a fallback placeholder and accessible alt text. Useful for product, course, or gallery thumbnails.',
      },
    },
  },
  argTypes: {
    name: {
      control: 'text',
      description: 'Alt text for the image.',
      defaultValue: 'Course Image',
    },
    path: {
      control: 'text',
      description: 'Image source path. If empty, shows placeholder.',
      defaultValue: '',
    },
  },
  args: {
    name: 'Course Image',
    path: '',
  },
  render: (args) => (
    <div
      css={css`
        width: 180px;
        margin: 0 auto;
      `}
      tabIndex={0}
      aria-label={args.name}
    >
      <ImageCard {...args} />
    </div>
  ),
} satisfies Meta<typeof ImageCard>;
export default meta;

type Story = StoryObj<typeof meta>;

export const Default = {} satisfies Story;

export const WithImage = {
  args: {
    name: 'React Course',
    path: 'https://placehold.co/180x120?text=React+Course',
  },
} satisfies Story;

export const CustomStyle = {
  args: {
    name: 'Styled Image',
    path: productPlaceholder,
  },
  render: (args) => (
    <div
      css={css`
        width: 220px;
        border: 2px dashed #1976d2;
        border-radius: 12px;
        padding: 12px;
        background: #f0f4ff;
      `}
      tabIndex={0}
      aria-label="Custom styled image card"
    >
      <ImageCard {...args} />
    </div>
  ),
} satisfies Story;
