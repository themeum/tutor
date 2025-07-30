import { Avatar, AvatarFallback } from '@TutorShared/atoms/Avatar';
import { type Meta, type StoryObj } from 'storybook-react-rsbuild';

const meta = {
  title: 'Atoms/Avatar',
  component: Avatar,
  parameters: {
    layout: 'centered',
    docs: {
      description: {
        component:
          'Avatar component displays a user image or, if not provided, a fallback with initials. Accessible and styled for modern UI.',
      },
    },
  },
  tags: ['autodocs'],
  argTypes: {
    image: {
      control: 'text',
      description: 'Image URL for the avatar. If not provided, fallback is shown.',
    },
    name: {
      control: 'text',
      description: 'Full name to display and use for initials.',
      defaultValue: 'Jane Doe',
    },
  },
} satisfies Meta<typeof Avatar>;

export default meta;

type Story = StoryObj<typeof meta>;

export const WithImage = {
  render: Avatar,
  args: {
    image: 'https://randomuser.me/api/portraits/women/44.jpg',
    name: 'Jane Doe',
  },
} satisfies Story;

export const FallbackMultiWord = {
  render: Avatar,
  args: {
    name: 'Jane Doe',
    image: '',
  },
} satisfies Story;

export const FallbackSingleWord = {
  render: Avatar,
  args: {
    name: 'Plato',
    image: '',
  },
} satisfies Story;

export const FallbackLongName = {
  render: Avatar,
  args: {
    name: 'Alexandria Cassandra Johnson',
    image: '',
  },
} satisfies Story;

export const FallbackDirect = {
  render: (args) => <AvatarFallback {...args} />,
  args: {
    name: 'Fallback Only',
  },
  parameters: {
    docs: {
      description: {
        story: 'Direct usage of `AvatarFallback` for advanced cases.',
      },
    },
  },
} satisfies StoryObj<typeof AvatarFallback>;
