import Button from '@TutorShared/atoms/Button';
import ButtonGroup from '@TutorShared/atoms/ButtonGroup';
import type { Meta, StoryObj } from 'storybook-react-rsbuild';

const meta: Meta<typeof ButtonGroup> = {
  title: 'Atoms/ButtonGroup',
  component: ButtonGroup,
  tags: ['autodocs'],
  parameters: {
    layout: 'centered',
    docs: {
      description: {
        component:
          'ButtonGroup arranges multiple Button components in a horizontal row, providing consistent spacing, border radius, and optional full width. It passes down variant, size, and disabled props to its children for unified styling. Use for toolbars, segmented controls, or grouped actions.',
      },
    },
  },
  argTypes: {
    children: {
      control: false,
      description: 'Button components to be grouped together.',
    },
    variant: {
      control: 'select',
      options: ['primary', 'secondary', 'tertiary', 'danger', 'text', 'WP'],
      description: 'Button variant for all children unless overridden.',
      defaultValue: 'primary',
    },
    size: {
      control: 'select',
      options: ['large', 'regular', 'small'],
      description: 'Button size for all children unless overridden.',
      defaultValue: 'regular',
    },
    gap: {
      control: 'number',
      description: 'Gap (px) between buttons.',
      defaultValue: 0,
    },
    fullWidth: {
      control: 'boolean',
      description: 'Whether the group should take full width.',
      defaultValue: false,
    },
    disabled: {
      control: 'boolean',
      description: 'Disable all buttons in the group.',
      defaultValue: false,
    },
  },
  args: {
    variant: 'primary',
    size: 'regular',
    gap: 0,
    fullWidth: false,
    disabled: false,
  },
};
export default meta;

type Story = StoryObj<typeof meta>;

const buttonLabels = ['First', 'Second', 'Third'];

export const Default: Story = {
  render: (args) => (
    <ButtonGroup {...args}>
      {buttonLabels.map((label) => (
        <Button key={label} tabIndex={0} aria-label={`${label} Button`}>
          {label}
        </Button>
      ))}
    </ButtonGroup>
  ),
};

export const Variants: Story = {
  render: () => (
    <>
      <div style={{ marginBottom: 16 }}>
        <ButtonGroup variant="primary">
          <Button tabIndex={0} aria-label="Primary Button">
            Primary
          </Button>
          <Button tabIndex={0} aria-label="Primary Button">
            Primary
          </Button>
        </ButtonGroup>
      </div>
      <div style={{ marginBottom: 16 }}>
        <ButtonGroup variant="secondary">
          <Button tabIndex={0} aria-label="Secondary Button">
            Secondary
          </Button>
          <Button tabIndex={0} aria-label="Secondary Button">
            Secondary
          </Button>
        </ButtonGroup>
      </div>
      <div style={{ marginBottom: 16 }}>
        <ButtonGroup variant="tertiary">
          <Button tabIndex={0} aria-label="Tertiary Button">
            Tertiary
          </Button>
          <Button tabIndex={0} aria-label="Tertiary Button">
            Tertiary
          </Button>
        </ButtonGroup>
      </div>
      <div style={{ marginBottom: 16 }}>
        <ButtonGroup variant="danger">
          <Button tabIndex={0} aria-label="Danger Button">
            Danger
          </Button>
          <Button tabIndex={0} aria-label="Danger Button">
            Danger
          </Button>
        </ButtonGroup>
      </div>
      <div style={{ marginBottom: 16 }}>
        <ButtonGroup variant="text">
          <Button tabIndex={0} aria-label="Text Button">
            Text
          </Button>
          <Button tabIndex={0} aria-label="Text Button">
            Text
          </Button>
        </ButtonGroup>
      </div>
      <div>
        <ButtonGroup variant="WP">
          <Button tabIndex={0} aria-label="WP Button">
            WP
          </Button>
          <Button tabIndex={0} aria-label="WP Button">
            WP
          </Button>
        </ButtonGroup>
      </div>
    </>
  ),
};

export const Sizes: Story = {
  render: () => (
    <>
      <div style={{ marginBottom: 16 }}>
        <ButtonGroup size="large">
          <Button tabIndex={0} aria-label="Large Button">
            Large
          </Button>
          <Button tabIndex={0} aria-label="Large Button">
            Large
          </Button>
        </ButtonGroup>
      </div>
      <div style={{ marginBottom: 16 }}>
        <ButtonGroup size="regular">
          <Button tabIndex={0} aria-label="Regular Button">
            Regular
          </Button>
          <Button tabIndex={0} aria-label="Regular Button">
            Regular
          </Button>
        </ButtonGroup>
      </div>
      <div>
        <ButtonGroup size="small">
          <Button tabIndex={0} aria-label="Small Button">
            Small
          </Button>
          <Button tabIndex={0} aria-label="Small Button">
            Small
          </Button>
        </ButtonGroup>
      </div>
    </>
  ),
};

export const Gap: Story = {
  render: () => (
    <ButtonGroup gap={12}>
      <Button tabIndex={0} aria-label="Button 1">
        Button 1
      </Button>
      <Button tabIndex={0} aria-label="Button 2">
        Button 2
      </Button>
      <Button tabIndex={0} aria-label="Button 3">
        Button 3
      </Button>
    </ButtonGroup>
  ),
};

export const FullWidth: Story = {
  render: () => (
    <div style={{ width: 400 }}>
      <ButtonGroup fullWidth>
        <Button tabIndex={0} aria-label="Full Width 1">
          Full Width 1
        </Button>
        <Button tabIndex={0} aria-label="Full Width 2">
          Full Width 2
        </Button>
        <Button tabIndex={0} aria-label="Full Width 3">
          Full Width 3
        </Button>
      </ButtonGroup>
    </div>
  ),
};

export const Disabled: Story = {
  render: () => (
    <ButtonGroup disabled>
      <Button tabIndex={0} aria-label="Disabled 1">
        Disabled 1
      </Button>
      <Button tabIndex={0} aria-label="Disabled 2">
        Disabled 2
      </Button>
      <Button tabIndex={0} aria-label="Disabled 3">
        Disabled 3
      </Button>
    </ButtonGroup>
  ),
};

export const MixedProps: Story = {
  render: () => (
    <ButtonGroup variant="secondary" size="regular">
      <Button tabIndex={0} aria-label="Enabled Button">
        Enabled
      </Button>
      <Button tabIndex={0} aria-label="Individually Disabled Button" disabled>
        Individually Disabled
      </Button>
      <Button tabIndex={0} aria-label="Danger Button" variant="danger">
        Danger
      </Button>
    </ButtonGroup>
  ),
};
