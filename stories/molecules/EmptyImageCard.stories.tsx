import EmptyImageCard from '@TutorShared/molecules/EmptyImageCard';
import type { Meta, StoryObj } from 'storybook-react-rsbuild';

const meta: Meta<typeof EmptyImageCard> = {
  title: 'Molecules/EmptyImageCard',
  component: EmptyImageCard,
  tags: ['autodocs'],
  parameters: {
    layout: 'centered',
    docs: {
      description: {
        component:
          'EmptyImageCard is a placeholder card for empty image slots. It displays an icon and optional placeholder text, and is useful for upload or gallery UIs.',
      },
    },
  },
  argTypes: {
    placeholder: {
      control: 'text',
      description: 'Optional placeholder text below the icon.',
      defaultValue: '',
    },
  },
  args: {
    placeholder: '',
  },
  render: (args) => (
    <div>
      <EmptyImageCard {...args} aria-label={args.placeholder ? args.placeholder : 'Empty image card'} />,
    </div>
  ),
};
export default meta;

type Story = StoryObj<typeof meta>;

export const Default: Story = {};

export const WithPlaceholder: Story = {
  args: {
    placeholder: 'Upload an image here',
  },
};
