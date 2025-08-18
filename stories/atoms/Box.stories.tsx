import { css } from '@emotion/react';
import { Box, BoxSubtitle, BoxTitle } from '@TutorShared/atoms/Box';
import type { Meta, StoryObj } from 'storybook-react-rsbuild';

const meta = {
  title: 'Atoms/Box',
  component: Box,
  parameters: {
    layout: 'centered',
    docs: {
      description: {
        component:
          'A versatile Box component that provides consistent spacing, background, and optional border styling. It can be used to wrap content and apply custom styles using Emotion CSS.',
      },
    },
  },
  tags: ['autodocs'],
  argTypes: {
    bordered: {
      control: { type: 'boolean' },
      description: 'Whether the box should have a border.',
      defaultValue: false,
    },
    wrapperCss: {
      control: false,
      description: 'Custom Emotion CSS styles for the box wrapper.',
    },
  },
  args: {
    bordered: false,
  },
  render: (args) => (
    <Box {...args}>
      <div tabIndex={0} aria-label="Box Content">
        <p>
          This is a simple Box component. You can use it to wrap content and apply consistent spacing and background.
        </p>
      </div>
    </Box>
  ),
} satisfies Meta<typeof Box>;

export default meta;

type Story = StoryObj<typeof meta>;

export const Default = {} satisfies Story;

export const Bordered = {
  args: {
    bordered: true,
  },
  render: (args) => (
    <Box {...args}>
      <div tabIndex={0} aria-label="Bordered Box Content">
        <p>
          This Box has a border. Use the <code>bordered</code> prop to enable it.
        </p>
      </div>
    </Box>
  ),
} satisfies Story;

export const CustomWrapperCss = {
  args: {
    bordered: true,
    wrapperCss: css`
      background: #f0f4ff;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    `,
  },
  render: (args) => (
    <Box {...args}>
      <div tabIndex={0} aria-label="Custom Styled Box Content">
        <p>
          This Box uses custom Emotion CSS via the <code>wrapperCss</code> prop.
        </p>
      </div>
    </Box>
  ),
} satisfies Story;

export const WithTitle = {
  args: {
    bordered: true,
  },
  render: (args) => (
    <Box {...args}>
      <BoxTitle tabIndex={0} aria-label="Box Title">
        Box Title
      </BoxTitle>
      <div tabIndex={0} aria-label="Box Content">
        <p>Box with a title. The title is accessible and keyboard focusable.</p>
      </div>
    </Box>
  ),
} satisfies Story;

export const WithTitleAndSubtitle = {
  args: {
    bordered: true,
  },
  render: (args) => (
    <Box {...args}>
      <BoxTitle tabIndex={0} aria-label="Box Title">
        Box Title
      </BoxTitle>
      <BoxSubtitle tabIndex={0} aria-label="Box Subtitle">
        Box Subtitle
      </BoxSubtitle>
      <div tabIndex={0} aria-label="Box Content">
        <p>Box with both a title and a subtitle. Both are accessible and keyboard focusable.</p>
      </div>
    </Box>
  ),
} satisfies Story;

export const TitleWithTooltip = {
  args: {
    bordered: true,
  },
  render: (args) => (
    <Box {...args}>
      <BoxTitle tooltip="More info about this box" tabIndex={0} aria-label="Box Title with Tooltip">
        Box Title
      </BoxTitle>
      <div tabIndex={0} aria-label="Box Content">
        <p>Hover or focus the info icon to see the tooltip.</p>
      </div>
    </Box>
  ),
} satisfies Story;

export const TitleWithSeparator = {
  args: {
    bordered: true,
  },
  render: (args) => (
    <Box {...args}>
      <BoxTitle separator tabIndex={0} aria-label="Box Title with Separator">
        Box Title
      </BoxTitle>
      <div tabIndex={0} aria-label="Box Content">
        <p>
          The title has a separator line below it. Use the <code>separator</code> prop.
        </p>
      </div>
    </Box>
  ),
} satisfies Story;

export const AccessibleBox = {
  args: {
    bordered: true,
    wrapperCss: css`
      background: #e3fcec;
      border-radius: 8px;
      padding: 24px;
    `,
  },
  render: (args) => (
    <Box {...args}>
      <BoxTitle tabIndex={0} aria-label="Accessible Box Title">
        Accessible Box Title
      </BoxTitle>
      <BoxSubtitle tabIndex={0} aria-label="Accessible Box Subtitle">
        Accessible Box Subtitle
      </BoxSubtitle>
      <div tabIndex={0} aria-label="Accessible Box Content">
        <p>
          This Box is fully accessible. All elements are keyboard focusable and have descriptive aria-labels for screen
          readers.
        </p>
      </div>
    </Box>
  ),
} satisfies Story;
