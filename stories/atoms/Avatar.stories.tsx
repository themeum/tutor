import { Avatar, AvatarFallback } from '@TutorShared/atoms/Avatar';
import React from 'react';
import { type Meta, type StoryObj } from 'storybook-react-rsbuild';

const meta: Meta<typeof Avatar> = {
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
};
export default meta;

type Story = StoryObj<typeof meta>;

const Template = (args: React.ComponentProps<typeof Avatar>) => <Avatar {...args} />;

export const WithImage: Story = {
  render: Template,
  args: {
    image: 'https://randomuser.me/api/portraits/women/44.jpg',
    name: 'Jane Doe',
  },
};

export const FallbackMultiWord: Story = {
  render: Template,
  args: {
    name: 'Jane Doe',
    image: '',
  },
};

export const FallbackSingleWord: Story = {
  render: Template,
  args: {
    name: 'Plato',
    image: '',
  },
};

export const FallbackLongName: Story = {
  render: Template,
  args: {
    name: 'Alexandria Cassandra Johnson',
    image: '',
  },
};

export const FallbackDirect: StoryObj<typeof AvatarFallback> = {
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
};
