import { css, type SerializedStyles } from '@emotion/react';
import Button from '@TutorShared/atoms/Button';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import { type IconCollection, icons } from '@TutorShared/icons/types';
import type { Meta, StoryObj } from 'storybook-react-rsbuild';

const meta: Meta<typeof Button> = {
  title: 'Atoms/Button',
  component: Button,
  parameters: {
    layout: 'centered',
    docs: {
      description: {
        component: 'A versatile, accessible button component supporting multiple variants, sizes, loading, and icons.',
      },
    },
  },
  tags: ['autodocs'],
  argTypes: {
    variant: {
      control: 'select',
      options: ['primary', 'secondary', 'tertiary', 'danger', 'text', 'WP'],
      description: 'Visual style of the button.',
    },
    isOutlined: {
      control: 'boolean',
      description: 'If true, renders the outlined style for the variant.',
    },
    size: {
      control: 'select',
      options: ['large', 'regular', 'small'],
      description: 'Button size.',
    },
    disabled: {
      control: 'boolean',
      description: 'Disables the button.',
    },
    loading: {
      control: 'boolean',
      description: 'Shows a loading spinner.',
    },
    icon: {
      control: 'select',
      options: [undefined, ...icons],
      description: 'Optional icon element.',
    },
    iconPosition: {
      control: 'radio',
      options: ['left', 'right'],
      description: 'Position of the icon.',
    },
    children: {
      control: 'text',
      description: 'Button content.',
    },
    onClick: { action: 'clicked', description: 'Click handler.' },
    tabIndex: {
      control: 'number',
      description: 'Tab index for accessibility.',
    },
    buttonCss: {
      control: false,
      table: { disable: true },
    },
    buttonContentCss: {
      control: false,
      table: { disable: true },
    },
  },
  args: {
    children: 'Button',
    variant: 'primary',
    isOutlined: false,
    size: 'regular',
    disabled: false,
    loading: false,
    icon: undefined,
    iconPosition: 'left',
    tabIndex: 0,
  },
  render: (args) => {
    const { icon, buttonCss, buttonContentCss, ...rest } = args;
    const iconElement =
      typeof icon === 'string' && icon ? <SVGIcon name={icon as IconCollection} width={24} height={24} /> : undefined;
    const buttonCssStyles: SerializedStyles | undefined =
      typeof buttonCss === 'string' && String(buttonCss).trim()
        ? css`
            ${buttonCss}
          `
        : undefined;
    const buttonContentCssStyles: SerializedStyles | undefined =
      typeof buttonContentCss === 'string' && String(buttonContentCss).trim()
        ? css`
            ${buttonContentCss}
          `
        : undefined;
    return (
      <Button {...rest} icon={iconElement} buttonCss={buttonCssStyles} buttonContentCss={buttonContentCssStyles} />
    );
  },
};

export default meta;

type Story = StoryObj<typeof meta>;

export const Primary: Story = {
  args: {
    children: 'Primary Button',
    variant: 'primary',
  },
};

export const Secondary: Story = {
  args: {
    children: 'Secondary Button',
    variant: 'secondary',
  },
};

export const Tertiary: Story = {
  args: {
    children: 'Tertiary Button',
    variant: 'tertiary',
  },
};

export const Danger: Story = {
  args: {
    children: 'Danger Button',
    variant: 'danger',
  },
};

export const WP: Story = {
  args: {
    children: 'WP Button',
    variant: 'WP',
  },
};

export const Text: Story = {
  args: {
    children: 'Text Button',
    variant: 'text',
  },
};

export const Outlined: Story = {
  args: {
    children: 'Outlined Button',
    variant: 'primary',
    isOutlined: true,
  },
};

export const Loading: Story = {
  args: {
    children: 'Loading Button',
    loading: true,
  },
};

export const Disabled: Story = {
  args: {
    children: 'Disabled Button',
    disabled: true,
  },
};

export const WithIconLeft: Story = {
  args: {
    children: 'With Icon',
    icon: 'plus',
    iconPosition: 'left',
  },
};

export const WithIconRight: Story = {
  args: {
    children: 'Export',
    icon: 'export',
    iconPosition: 'right',
  },
};

export const Large: Story = {
  args: {
    children: 'Large Button',
    size: 'large',
  },
};

export const Small: Story = {
  args: {
    children: 'Small Button',
    size: 'small',
  },
};
