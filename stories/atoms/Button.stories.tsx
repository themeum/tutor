import { css, type SerializedStyles } from '@emotion/react';
import Button from '@TutorShared/atoms/Button';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import { type IconCollection, icons } from '@TutorShared/icons/types';
import type { Meta, StoryObj } from 'storybook-react-rsbuild';

const meta = {
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
} satisfies Meta<typeof Button>;

export default meta;

type Story = StoryObj<typeof meta>;

export const Primary = {
  args: {
    children: 'Primary Button',
    variant: 'primary',
  },
} satisfies Story;

export const Secondary = {
  args: {
    children: 'Secondary Button',
    variant: 'secondary',
  },
} satisfies Story;

export const Tertiary = {
  args: {
    children: 'Tertiary Button',
    variant: 'tertiary',
  },
} satisfies Story;

export const Danger = {
  args: {
    children: 'Danger Button',
    variant: 'danger',
  },
} satisfies Story;

export const WP = {
  args: {
    children: 'WP Button',
    variant: 'WP',
  },
} satisfies Story;

export const Text = {
  args: {
    children: 'Text Button',
    variant: 'text',
  },
} satisfies Story;

export const Outlined = {
  args: {
    children: 'Outlined Button',
    variant: 'primary',
    isOutlined: true,
  },
} satisfies Story;

export const Loading = {
  args: {
    children: 'Loading Button',
    loading: true,
  },
} satisfies Story;

export const Disabled = {
  args: {
    children: 'Disabled Button',
    disabled: true,
  },
} satisfies Story;

export const WithIconLeft = {
  args: {
    children: 'With Icon',
    icon: 'plus',
    iconPosition: 'left',
  },
} satisfies Story;

export const WithIconRight = {
  args: {
    children: 'Export',
    icon: 'export',
    iconPosition: 'right',
  },
} satisfies Story;

export const Large = {
  args: {
    children: 'Large Button',
    size: 'large',
  },
} satisfies Story;

export const Small = {
  args: {
    children: 'Small Button',
    size: 'small',
  },
} satisfies Story;
