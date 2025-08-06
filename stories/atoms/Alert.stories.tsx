import Alert from '@TutorShared/atoms/Alert';
import { icons } from '@TutorShared/icons/types';
import React from 'react';
import { type Meta, type StoryObj } from 'storybook-react-rsbuild';

const meta = {
  title: 'Atoms/Alert',
  component: Alert,
  parameters: {
    layout: 'centered',
    accessibility: {
      // Ensures the alert role is present for screen readers
      element: 'div[role="alert"]',
    },
    docs: {
      description: {
        component:
          'A versatile alert component that displays messages with different types and optional icons. It supports various alert types like success, warning, danger, info, and primary.\n\n> 🚨 **Notice:** Only `warning` and `danger` (error) alert types are implemented. Other types are not available.',
      },
    },
  },
  tags: ['autodocs'],
  argTypes: {
    type: {
      control: { type: 'select' },
      options: ['success', 'warning', 'danger', 'info', 'primary'],
      description: 'Alert type for color and icon',
      defaultValue: 'warning',
    },
    icon: {
      control: { type: 'select' },
      options: [undefined, ...icons],
      description: 'Optional icon to display',
    },
    children: {
      control: 'text',
      description: 'Alert message content',
      defaultValue: 'This is an alert message.',
    },
  },
} satisfies Meta<typeof Alert>;

export default meta;

type Story = StoryObj<typeof meta>;

const Template = (args: React.ComponentProps<typeof Alert>) => <Alert {...args} />;

export const Warning = {
  render: Template,
  args: {
    type: 'warning',
    icon: 'warning',
    children: 'This is a warning alert. Please pay attention.',
  },
} satisfies Story;

export const Success = {
  render: Template,
  args: {
    type: 'success',
    icon: 'checkFilled',
    children: 'Operation completed successfully!',
  },
} satisfies Story;

export const Danger = {
  render: Template,
  args: {
    type: 'danger',
    icon: 'warning',
    children: 'There was an error processing your request.',
  },
} satisfies Story;

export const Info = {
  render: Template,
  args: {
    type: 'info',
    icon: 'info',
    children: 'For your information: updates are available.',
  },
} satisfies Story;

export const Primary = {
  render: Template,
  args: {
    type: 'primary',
    icon: 'star',
    children: 'This is a primary alert.',
  },
} satisfies Story;

export const WithoutIcon = {
  render: Template,
  args: {
    type: 'info',
    icon: undefined,
    children: 'This alert does not have an icon.',
  },
} satisfies Story;
