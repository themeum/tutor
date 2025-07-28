import { css } from '@emotion/react';
import { Box, BoxSubtitle, BoxTitle } from '@TutorShared/atoms/Box';
import type { Meta, StoryObj } from 'storybook-react-rsbuild';

const meta: Meta<typeof Box> = {
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
};
export default meta;

type Story = StoryObj<typeof meta>;

const boxContent = (
  <div>
    <p>This is a simple Box component. You can use it to wrap content and apply consistent spacing and background.</p>
  </div>
);

export const Default: Story = {
  render: (args) => <Box {...args}>{boxContent}</Box>,
};

export const Bordered: Story = {
  render: () => (
    <Box bordered>
      <p>
        This Box has a border. Use the <code>bordered</code> prop to enable it.
      </p>
    </Box>
  ),
};

export const CustomWrapperCss: Story = {
  name: 'Custom Wrapper CSS',
  render: () => (
    <Box
      bordered
      wrapperCss={css`
        background: #f0f4ff;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
      `}
    >
      <p>
        This Box uses custom Emotion CSS via the <code>wrapperCss</code> prop.
      </p>
    </Box>
  ),
};

export const WithTitle: Story = {
  render: () => (
    <Box bordered>
      <BoxTitle tabIndex={0} aria-label="Box Title">
        Box Title
      </BoxTitle>
      <div>
        <p>Box with a title. The title is accessible and keyboard focusable.</p>
      </div>
    </Box>
  ),
};

export const WithTitleAndSubtitle: Story = {
  render: () => (
    <Box bordered>
      <BoxTitle tabIndex={0} aria-label="Box Title">
        Box Title
      </BoxTitle>
      <BoxSubtitle tabIndex={0} aria-label="Box Subtitle">
        Box Subtitle
      </BoxSubtitle>
      <div>
        <p>Box with both a title and a subtitle. Both are accessible and keyboard focusable.</p>
      </div>
    </Box>
  ),
};

export const TitleWithTooltip: Story = {
  render: () => (
    <Box bordered>
      <BoxTitle tooltip="More info about this box" tabIndex={0} aria-label="Box Title with Tooltip">
        Box Title
      </BoxTitle>
      <div>
        <p>Hover or focus the info icon to see the tooltip.</p>
      </div>
    </Box>
  ),
};

export const TitleWithSeparator: Story = {
  render: () => (
    <Box bordered>
      <BoxTitle separator tabIndex={0} aria-label="Box Title with Separator">
        Box Title
      </BoxTitle>
      <div>
        <p>
          The title has a separator line below it. Use the <code>separator</code> prop.
        </p>
      </div>
    </Box>
  ),
};
