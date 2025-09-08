import { css } from '@emotion/react';
import Button from '@TutorShared/atoms/Button';
import ButtonGroup from '@TutorShared/atoms/ButtonGroup';
import type { Meta, StoryObj } from 'storybook-react-rsbuild';

const meta = {
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
  render: (args) => (
    <ButtonGroup {...args} aria-label="Default Button Group">
      {['First', 'Second', 'Third'].map((label) => (
        <Button
          key={label}
          tabIndex={0}
          aria-label={`${label} Button`}
          onClick={() => alert(`${label} clicked`)}
          onKeyDown={(event) => {
            if (event.key === 'Enter' || event.key === ' ') {
              event.preventDefault();
              alert(`${label} clicked`);
            }
          }}
        >
          {label}
        </Button>
      ))}
    </ButtonGroup>
  ),
} satisfies Meta<typeof ButtonGroup>;

export default meta;

type Story = StoryObj<typeof meta>;

const buttonGroupContainerCss = css`
  margin-bottom: 20px;
  padding-bottom: 12px;
  border-bottom: 1px solid #e0e0e0;
`;

import type { ButtonSize, ButtonVariant } from '@TutorShared/atoms/Button';

const renderButtons = (labels: string[], variant?: ButtonVariant) =>
  labels.map((label) => (
    <Button
      key={label}
      tabIndex={0}
      aria-label={`${label} Button`}
      variant={variant}
      onClick={() => alert(`${label} clicked`)}
      onKeyDown={(event) => {
        if (event.key === 'Enter' || event.key === ' ') {
          event.preventDefault();
          alert(`${label} clicked`);
        }
      }}
    >
      {label}
    </Button>
  ));

export const Default: Story = {};

export const Variants: Story = {
  render: () => (
    <div>
      {(['primary', 'secondary', 'tertiary', 'danger', 'text', 'WP'] as ButtonVariant[]).map(
        (variant: ButtonVariant) => (
          <div key={variant} css={buttonGroupContainerCss}>
            <ButtonGroup variant={variant} aria-label={`${variant} Button Group`}>
              {renderButtons([variant, variant], variant)}
            </ButtonGroup>
          </div>
        ),
      )}
    </div>
  ),
};

export const Sizes: Story = {
  render: () => (
    <div>
      {(['large', 'regular', 'small'] as ButtonSize[]).map((size) => (
        <div key={size} css={buttonGroupContainerCss}>
          <ButtonGroup size={size} aria-label={`${size} Button Group`}>
            {renderButtons([size, size])}
          </ButtonGroup>
        </div>
      ))}
    </div>
  ),
};

export const Gap: Story = {
  args: {
    gap: 12,
  },
  render: (args) => (
    <ButtonGroup {...args} aria-label="Gap Button Group">
      {renderButtons(['Button 1', 'Button 2', 'Button 3'])}
    </ButtonGroup>
  ),
};

export const FullWidth: Story = {
  args: {
    fullWidth: true,
  },
  render: (args) => (
    <div
      css={css`
        width: 400px;
      `}
    >
      <ButtonGroup {...args} aria-label="Full Width Button Group">
        {renderButtons(['Full Width 1', 'Full Width 2', 'Full Width 3'])}
      </ButtonGroup>
    </div>
  ),
};

export const Disabled: Story = {
  args: {
    disabled: true,
  },
  render: (args) => (
    <ButtonGroup {...args} aria-label="Disabled Button Group">
      {renderButtons(['Disabled 1', 'Disabled 2', 'Disabled 3'])}
    </ButtonGroup>
  ),
};

export const MixedProps: Story = {
  render: () => (
    <ButtonGroup variant="secondary" size="regular" aria-label="Mixed Props Button Group">
      <Button
        tabIndex={0}
        aria-label="Enabled Button"
        onClick={() => alert('Enabled clicked')}
        onKeyDown={(event) => {
          if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            alert('Enabled clicked');
          }
        }}
      >
        Enabled
      </Button>
      <Button
        tabIndex={0}
        aria-label="Individually Disabled Button"
        disabled
        onClick={() => alert('Individually Disabled clicked')}
        onKeyDown={(event) => {
          if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            alert('Individually Disabled clicked');
          }
        }}
      >
        Individually Disabled
      </Button>
      <Button
        tabIndex={0}
        aria-label="Danger Button"
        variant="danger"
        onClick={() => alert('Danger clicked')}
        onKeyDown={(event) => {
          if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            alert('Danger clicked');
          }
        }}
      >
        Danger
      </Button>
    </ButtonGroup>
  ),
};

export const AccessibleGroup: Story = {
  render: () => (
    <ButtonGroup gap={8} aria-label="Accessible Button Group">
      <Button
        tabIndex={0}
        aria-label="Accessible Button 1"
        onClick={() => alert('Accessible Button 1 clicked')}
        onKeyDown={(event) => {
          if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            alert('Accessible Button 1 clicked');
          }
        }}
      >
        Accessible 1
      </Button>
      <Button
        tabIndex={0}
        aria-label="Accessible Button 2"
        onClick={() => alert('Accessible Button 2 clicked')}
        onKeyDown={(event) => {
          if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            alert('Accessible Button 2 clicked');
          }
        }}
      >
        Accessible 2
      </Button>
      <Button
        tabIndex={0}
        aria-label="Accessible Button 3"
        onClick={() => alert('Accessible Button 3 clicked')}
        onKeyDown={(event) => {
          if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            alert('Accessible Button 3 clicked');
          }
        }}
      >
        Accessible 3
      </Button>
    </ButtonGroup>
  ),
};
