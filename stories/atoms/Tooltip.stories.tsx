/** @jsxImportSource @emotion/react */
import { css } from '@emotion/react';
import Button from '@TutorShared/atoms/Button';
import Tooltip from '@TutorShared/atoms/Tooltip';
import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import type { Meta, StoryObj } from 'storybook-react-rsbuild';

const meta = {
  title: 'Atoms/Tooltip',
  component: Tooltip,
  parameters: {
    layout: 'centered',
    docs: {
      description: {
        component: 'A tooltip component that displays contextual information on hover.',
      },
    },
  },
  tags: ['autodocs'],
  argTypes: {
    content: {
      control: 'text',
      description: 'The content to display in the tooltip',
    },
    placement: {
      control: 'select',
      options: ['top', 'right', 'bottom', 'left'],
      description: 'Position of the tooltip relative to the trigger element',
    },
    allowHTML: {
      control: 'boolean',
      description: 'Whether to allow HTML content in the tooltip',
    },
    hideOnClick: {
      control: 'boolean',
      description: 'Whether to hide tooltip when clicked',
    },
    delay: {
      control: 'number',
      description: 'Delay in milliseconds before showing the tooltip',
    },
    disabled: {
      control: 'boolean',
      description: 'Whether the tooltip is disabled',
    },
    visible: {
      control: 'boolean',
      description: 'Whether the tooltip is always visible',
    },
  },
  args: {
    content: 'This is a tooltip',
    placement: 'top',
    delay: 0,
    children: <Button variant="primary">Hover me</Button>,
  },
  render: (args) => <Tooltip {...args}>{args.children}</Tooltip>,
} satisfies Meta<typeof Tooltip>;

export default meta;
type Story = StoryObj<typeof meta>;

export const Default = {} satisfies Story;

export const Placements = {
  render: () => (
    <div css={templateStyles.placementGrid}>
      <Tooltip content="Tooltip on top" placement="top">
        <Button>Top</Button>
      </Tooltip>
      <Tooltip content="Tooltip on right" placement="right">
        <Button>Right</Button>
      </Tooltip>
      <Tooltip content="Tooltip on bottom" placement="bottom">
        <Button>Bottom</Button>
      </Tooltip>
      <Tooltip content="Tooltip on left" placement="left">
        <Button>Left</Button>
      </Tooltip>
    </div>
  ),
  parameters: {
    docs: {
      description: {
        story: 'Tooltip can be positioned on any side of the trigger element.',
      },
    },
  },
} satisfies Story;

export const WithHTMLContent = {
  args: {
    content: '<strong>Bold text</strong><br/>Line break<br/><em>Italic text</em>',
    allowHTML: true,
    placement: 'top',
  },
  parameters: {
    docs: {
      description: {
        story: 'Tooltip can display HTML content when allowHTML is enabled.',
      },
    },
  },
} satisfies Story;

export const ReactNodeContent = {
  args: {
    content: (
      <div>
        <div
          style={{
            fontWeight: 'bold',
            marginBottom: spacing[4],
            color: colorTokens.text.white,
          }}
        >
          Custom Content
        </div>
        <div
          style={{
            fontSize: '12px',
            opacity: 0.8,
            color: colorTokens.text.white,
          }}
        >
          This is a React node
        </div>
      </div>
    ),
    placement: 'top',
  },
  parameters: {
    docs: {
      description: {
        story: 'Tooltip content can be a React node for more complex layouts.',
      },
    },
  },
} satisfies Story;

export const WithDelay = {
  args: {
    content: 'This tooltip appears after 1 second',
    delay: 1000,
    placement: 'top',
  },
  parameters: {
    docs: {
      description: {
        story: 'Tooltip can have a custom delay before appearing.',
      },
    },
  },
} satisfies Story;

export const AlwaysVisible = {
  args: {
    content: 'This tooltip is always visible',
    visible: true,
    placement: 'top',
  },
  parameters: {
    docs: {
      description: {
        story: 'Tooltip can be forced to always be visible.',
      },
    },
  },
} satisfies Story;

export const HideOnClick = {
  args: {
    content: 'Click the button to hide this tooltip',
    hideOnClick: true,
    placement: 'top',
  },
  parameters: {
    docs: {
      description: {
        story: 'Tooltip can be configured to hide when the trigger is clicked.',
      },
    },
  },
} satisfies Story;

export const Disabled = {
  args: {
    content: 'This tooltip will not show',
    disabled: true,
    placement: 'top',
  },
  parameters: {
    docs: {
      description: {
        story: 'Tooltip can be disabled, preventing it from showing.',
      },
    },
  },
} satisfies Story;

export const LongContent = {
  args: {
    content:
      'This is a very long tooltip content that demonstrates how the tooltip handles longer text. It should wrap appropriately and maintain good readability.',
    placement: 'top',
  },
  parameters: {
    docs: {
      description: {
        story: 'Tooltip handles long content gracefully with proper text wrapping.',
      },
    },
  },
} satisfies Story;

export const WithIcon = {
  render: () => (
    <div css={templateStyles.container}>
      <Tooltip content="Helpful information about this feature" placement="top">
        <span css={templateStyles.helpIcon} tabIndex={0} role="button" aria-label="More information">
          ?
        </span>
      </Tooltip>
    </div>
  ),
  parameters: {
    docs: {
      description: {
        story: 'Tooltip can be used with icons or other UI elements.',
      },
    },
  },
} satisfies Story;

export const VariantButtons = {
  render: () => (
    <div css={templateStyles.variantGrid}>
      <Tooltip content="Primary button tooltip" placement="top">
        <Button variant="primary">Primary</Button>
      </Tooltip>
      <Tooltip content="Secondary button tooltip" placement="top">
        <Button variant="secondary">Secondary</Button>
      </Tooltip>
      <Tooltip content="Tertiary button tooltip" placement="top">
        <Button variant="tertiary">Tertiary</Button>
      </Tooltip>
      <Tooltip content="Danger button tooltip" placement="top">
        <Button variant="danger">Danger</Button>
      </Tooltip>
      <Tooltip content="Text button tooltip" placement="top">
        <Button variant="text">Text</Button>
      </Tooltip>
    </div>
  ),
  parameters: {
    docs: {
      description: {
        story: 'Tooltip works with all button variants.',
      },
    },
  },
} satisfies Story;

export const ButtonSizes = {
  render: () => (
    <div css={templateStyles.sizeGrid}>
      <Tooltip content="Large button tooltip" placement="top">
        <Button size="large">Large</Button>
      </Tooltip>
      <Tooltip content="Regular button tooltip" placement="top">
        <Button size="regular">Regular</Button>
      </Tooltip>
      <Tooltip content="Small button tooltip" placement="top">
        <Button size="small">Small</Button>
      </Tooltip>
    </div>
  ),
  parameters: {
    docs: {
      description: {
        story: 'Tooltip works with all button sizes.',
      },
    },
  },
} satisfies Story;

const templateStyles = {
  container: css`
    display: flex;
    justify-content: center;
    align-items: center;
    padding: ${spacing[32]} satisfies Story;
    ${typography.body()}
  `,
  placementGrid: css`
    display: grid;
    gap: ${spacing[32]} satisfies Story;
    grid-template-columns: repeat(2, 1fr);
    padding: ${spacing[48]} satisfies Story;
    ${typography.body()}
  `,
  variantGrid: css`
    display: flex;
    gap: ${spacing[16]} satisfies Story;
    flex-wrap: wrap;
    justify-content: center;
    align-items: center;
    padding: ${spacing[32]} satisfies Story;
    ${typography.body()}
  `,
  sizeGrid: css`
    display: flex;
    gap: ${spacing[16]} satisfies Story;
    align-items: center;
    justify-content: center;
    padding: ${spacing[32]} satisfies Story;
    ${typography.body()}
  `,
  customContentTitle: css`
    font-weight: 600;
    margin-bottom: ${spacing[4]} satisfies Story;
    color: ${colorTokens.text.white} satisfies Story;
  `,
  customContentSubtitle: css`
    font-size: 12px;
    opacity: 0.8;
    color: ${colorTokens.text.white} satisfies Story;
  `,
  helpIcon: css`
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 20px;
    height: 20px;
    color: ${colorTokens.text.primary} satisfies Story;
    border-radius: ${borderRadius[8]} satisfies Story;
    font-size: 12px;
    font-weight: 600;
    cursor: help;
    border: 1px solid ${colorTokens.stroke.default} satisfies Story;
    transition: all 150ms ease-in-out;

    &:hover {
      background-color: ${colorTokens.background.hover} satisfies Story;
      border-color: ${colorTokens.stroke.hover} satisfies Story;
    }

    &:focus {
      outline: 2px solid ${colorTokens.stroke.brand} satisfies Story;
      outline-offset: 1px;
    }
  `,
};
