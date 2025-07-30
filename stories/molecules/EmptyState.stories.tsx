import { css } from '@emotion/react';
import Button from '@TutorShared/atoms/Button';
import EmptyState from '@TutorShared/molecules/EmptyState';
import type { Meta, StoryObj } from 'storybook-react-rsbuild';

const meta = {
  title: 'Molecules/EmptyState',
  component: EmptyState,
  tags: ['autodocs'],
  parameters: {
    layout: 'centered',
    docs: {
      description: {
        component:
          'EmptyState displays a placeholder message, image, and optional actions when there is no content. Supports small/medium size, border, custom styles, and accessibility.',
      },
    },
  },
  argTypes: {
    title: {
      control: 'text',
      description: 'Title of the empty state.',
      defaultValue: 'No Data Available',
    },
    description: {
      control: 'text',
      description: 'Description text.',
      defaultValue: 'There is currently no data to display.',
    },
    size: {
      control: 'select',
      options: ['small', 'medium'],
      description: 'Size of the empty state.',
      defaultValue: 'medium',
    },
    emptyStateImage: {
      control: 'text',
      description: 'Image URL for the empty state.',
    },
    emptyStateImage2x: {
      control: 'text',
      description: '2x Image URL for retina screens.',
    },
    imageAltText: {
      control: 'text',
      description: 'Alt text for the image.',
    },
    removeBorder: {
      control: 'boolean',
      description: 'Remove border around the empty state.',
      defaultValue: true,
    },
    actions: {
      control: false,
      description: 'Optional ReactNode for actions.',
    },
    wrapperCss: {
      control: false,
      description: 'Custom Emotion CSS for the wrapper.',
    },
  },
  args: {
    title: 'No Data Available',
    description: 'There is currently no data to display.',
    size: 'medium',
    removeBorder: true,
    emptyStateImage: '',
    emptyStateImage2x: '',
    imageAltText: '',
  },
  render: (args) => <EmptyState {...args} aria-label={args.title || 'Empty state'} wrapperCss={args.wrapperCss} />,
} satisfies Meta<typeof EmptyState>;

export default meta;

type Story = StoryObj<typeof meta>;

export const Default = {} satisfies Story;

export const WithImage = {
  args: {
    emptyStateImage: 'https://placehold.co/320x160?text=No+Image',
    imageAltText: 'No image available',
    title: 'No Images Found',
    description: 'Try uploading a new image.',
  },
} satisfies Story;

export const SmallSize = {
  args: {
    size: 'small',
    title: 'Nothing Here',
    description: 'No items found in this section.',
  },
} satisfies Story;

export const WithActions = {
  args: {
    emptyStateImage: 'https://placehold.co/620x360?text=No+Image',
    imageAltText: 'No image available',
    actions: (
      <>
        <Button
          variant="primary"
          aria-label="Add Item"
          tabIndex={0}
          onClick={() => alert('Add Item clicked')}
          onKeyDown={(event) => {
            if (event.key === 'Enter' || event.key === ' ') {
              alert('Add Item clicked');
            }
          }}
        >
          Add Item
        </Button>
        <Button
          variant="secondary"
          aria-label="Learn More"
          tabIndex={0}
          onClick={() => alert('Learn More clicked')}
          onKeyDown={(event) => {
            if (event.key === 'Enter' || event.key === ' ') {
              alert('Learn More clicked');
            }
          }}
        >
          Learn More
        </Button>
      </>
    ),
    title: 'No Results',
    description: 'Try adding a new item or learn more.',
  },
} satisfies Story;

export const WithBorder = {
  args: {
    removeBorder: false,
    title: 'No Courses',
    description: 'You have not created any courses yet.',
  },
} satisfies Story;

export const CustomStyle = {
  args: {
    title: 'Custom Styled Empty State',
    description: 'This empty state uses custom styles.',
    wrapperCss: css`
      background: #f0f4ff;
      border-radius: 16px;
      padding: 32px;
      border: 2px dashed #1976d2;
    `,
  },
} satisfies Story;
