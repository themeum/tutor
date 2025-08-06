import MagicButton from '@TutorShared/atoms/MagicButton';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import type { Meta, StoryObj } from 'storybook-react-rsbuild';

const meta = {
  title: 'Atoms/MagicButton',
  component: MagicButton,
  tags: ['autodocs'],
  parameters: {
    layout: 'centered',
    docs: {
      description: {
        component:
          'MagicButton is a visually rich, highly customizable button component supporting multiple variants, sizes, loading state, and accessibility features. Use for primary actions, secondary actions, icon buttons, and more.',
      },
    },
  },
  argTypes: {
    variant: {
      control: 'select',
      options: ['default', 'primary', 'secondary', 'outline', 'primary_outline', 'ghost', 'plain'],
      description: 'Visual style of the button.',
      defaultValue: 'default',
    },
    size: {
      control: 'select',
      options: ['default', 'sm', 'icon'],
      description: 'Size of the button.',
      defaultValue: 'default',
    },
    roundedFull: {
      control: 'boolean',
      description: 'If true, button is fully rounded.',
      defaultValue: true,
    },
    loading: {
      control: 'boolean',
      description: 'Show loading spinner.',
      defaultValue: false,
    },
    disabled: {
      control: 'boolean',
      description: 'Disable the button.',
      defaultValue: false,
    },
    type: {
      control: 'select',
      options: ['button', 'submit'],
      description: 'Button type.',
      defaultValue: 'button',
    },
    children: {
      control: 'text',
      description: 'Button label or content.',
      defaultValue: 'Magic Button',
    },
  },
  args: {
    variant: 'default',
    size: 'default',
    roundedFull: true,
    loading: false,
    disabled: false,
    type: 'button',
    children: 'Magic Button',
  },
  render: (args) => (
    <MagicButton {...args} aria-label={typeof args.children === 'string' ? args.children : 'Magic Button'} tabIndex={0}>
      {args.children}
    </MagicButton>
  ),
} satisfies Meta<typeof MagicButton>;

export default meta;

type Story = StoryObj<typeof meta>;

export const Default = {
  args: {
    children: 'Magic Button',
  },
} satisfies Story;

export const Variants = {
  args: {
    children: 'Magic Button',
  },
  render: (args) => (
    <div style={{ display: 'flex', flexDirection: 'row', gap: 16 }}>
      {['default', 'primary', 'secondary', 'outline', 'primary_outline', 'ghost', 'plain'].map((variant) => (
        <MagicButton
          {...args}
          key={variant}
          variant={variant as typeof args.variant}
          aria-label={`Magic Button ${variant}`}
          tabIndex={0}
        >
          {variant.charAt(0).toUpperCase() + variant.slice(1)}
        </MagicButton>
      ))}
    </div>
  ),
} satisfies Story;

export const Sizes = {
  args: {
    children: 'Magic Button',
  },
  render: (args) => (
    <div style={{ display: 'flex', gap: 16 }}>
      <MagicButton {...args} size="default" aria-label="Magic Button Default" tabIndex={0}>
        Default
      </MagicButton>
      <MagicButton {...args} size="sm" aria-label="Magic Button Small" tabIndex={0}>
        Small
      </MagicButton>
      <MagicButton {...args} size="icon" aria-label="Magic Button Icon" tabIndex={0}>
        <SVGIcon name="magicAi" width={20} height={20} />
      </MagicButton>
    </div>
  ),
} satisfies Story;

export const Rounded = {
  args: {
    children: 'Rounded',
  },
  render: (args) => (
    <div style={{ display: 'flex', gap: 16 }}>
      <MagicButton {...args} roundedFull={true} aria-label="Magic Button Rounded" tabIndex={0}>
        Rounded
      </MagicButton>
      <MagicButton {...args} roundedFull={false} aria-label="Magic Button Not Rounded" tabIndex={0}>
        Not Rounded
      </MagicButton>
    </div>
  ),
} satisfies Story;

export const Loading = {
  args: {
    children: 'Loading...',
    loading: true,
  },
} satisfies Story;

export const Disabled = {
  args: {
    children: 'Disabled',
    disabled: true,
  },
} satisfies Story;

export const WithIcon = {
  args: {
    children: (
      <>
        <SVGIcon name="magicAi" width={20} height={20} />
        With Icon
      </>
    ),
  },
} satisfies Story;
