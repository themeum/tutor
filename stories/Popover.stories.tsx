/** @jsxImportSource @emotion/react */
import { css } from '@emotion/react';
import { useRef, useState } from 'react';
import type { Meta, StoryObj } from 'storybook-react-rsbuild';
import Button from '../assets/react/v3/shared/atoms/Button';
import { borderRadius, colorTokens, spacing } from '../assets/react/v3/shared/config/styles';
import { AnimationType } from '../assets/react/v3/shared/hooks/useAnimation';
import Popover from '../assets/react/v3/shared/molecules/Popover';

const meta: Meta<typeof Popover> = {
  title: 'Shared/Molecules/Popover',
  component: Popover,
  parameters: {
    layout: 'centered',
    docs: {
      description: {
        component:
          'A versatile popover component that can display rich content, supports various animations, and can be customized with different arrow positions and styles.',
      },
    },
  },
  tags: ['autodocs'],
  argTypes: {
    arrow: {
      control: { type: 'select' },
      options: ['left', 'right', 'top', 'bottom', 'middle', 'auto', 'absoluteCenter'],
      description: 'Position of the arrow relative to the popover',
    },
    gap: {
      control: { type: 'number', min: 0, max: 50 },
      description: 'Gap between trigger and popover in pixels',
    },
    maxWidth: {
      control: { type: 'text' },
      description: 'Maximum width of the popover',
    },
    closeOnEscape: {
      control: { type: 'boolean' },
      description: 'Whether to close popover on Escape key',
    },
    animationType: {
      control: { type: 'select' },
      options: [
        AnimationType.slideDown,
        AnimationType.slideUp,
        AnimationType.slideLeft,
        AnimationType.slideRight,
        AnimationType.collapseExpand,
        AnimationType.zoomIn,
        AnimationType.zoomOut,
        AnimationType.fadeIn,
      ],
      description: 'Animation type for popover entrance/exit',
    },
    hideArrow: {
      control: { type: 'boolean' },
      description: 'Hide the arrow indicator',
    },
  },
};

export default meta;
type Story = StoryObj<typeof meta>;

// eslint-disable-next-line @typescript-eslint/no-explicit-any
const PopoverTemplate = (args: any) => {
  const [isOpen, setIsOpen] = useState(false);
  const triggerRef = useRef<HTMLButtonElement>(null);

  const handleTogglePopover = () => {
    setIsOpen(!isOpen);
  };

  const handleClosePopover = () => {
    setIsOpen(false);
  };

  return (
    <div css={templateStyles.container}>
      <Button ref={triggerRef} onClick={handleTogglePopover} aria-expanded={isOpen} aria-haspopup="true">
        Toggle Popover
      </Button>

      <Popover {...args} triggerRef={triggerRef} isOpen={isOpen} closePopover={handleClosePopover}>
        <div css={templateStyles.popoverContent}>
          <h3 css={templateStyles.popoverTitle}>Popover Title</h3>
          <p css={templateStyles.popoverText}>
            This is a sample popover content. You can put any React components here.
          </p>
          <Button onClick={handleClosePopover} variant="danger" size="small">
            Close
          </Button>
        </div>
      </Popover>
    </div>
  );
};

export const Default: Story = {
  render: PopoverTemplate,
  args: {
    arrow: 'top',
    gap: 8,
    closeOnEscape: true,
    animationType: AnimationType.slideLeft,
    hideArrow: false,
  },
};

export const ArrowLeft: Story = {
  render: PopoverTemplate,
  args: {
    arrow: 'left',
    gap: 8,
    closeOnEscape: true,
    animationType: AnimationType.slideRight,
  },
};

export const ArrowRight: Story = {
  render: PopoverTemplate,
  args: {
    arrow: 'right',
    gap: 8,
    closeOnEscape: true,
    animationType: AnimationType.slideLeft,
  },
};

export const ArrowBottom: Story = {
  render: PopoverTemplate,
  args: {
    arrow: 'bottom',
    gap: 8,
    closeOnEscape: true,
    animationType: AnimationType.slideUp,
  },
};

export const ArrowTop: Story = {
  render: PopoverTemplate,
  args: {
    arrow: 'top',
    gap: 8,
    closeOnEscape: true,
    animationType: AnimationType.slideDown,
  },
};

export const ArrowMiddle: Story = {
  render: PopoverTemplate,
  args: {
    arrow: 'middle',
    gap: 8,
    closeOnEscape: true,
    animationType: AnimationType.slideDown,
  },
};

export const ArrowAbsoluteCenter: Story = {
  render: PopoverTemplate,
  args: {
    arrow: 'absoluteCenter',
    gap: 8,
    closeOnEscape: true,
    animationType: AnimationType.slideDown,
  },
};

export const NoArrow: Story = {
  render: PopoverTemplate,
  args: {
    hideArrow: true,
    gap: 8,
    closeOnEscape: true,
    animationType: AnimationType.fadeIn,
  },
};

export const CustomWidth: Story = {
  render: PopoverTemplate,
  args: {
    arrow: 'top',
    gap: 12,
    maxWidth: '300px',
    closeOnEscape: true,
    animationType: AnimationType.slideDown,
  },
};

export const FadeAnimation: Story = {
  render: PopoverTemplate,
  args: {
    arrow: 'bottom',
    gap: 8,
    closeOnEscape: true,
    animationType: AnimationType.fadeIn,
  },
};

export const LargeGap: Story = {
  render: PopoverTemplate,
  args: {
    arrow: 'top',
    gap: 20,
    closeOnEscape: true,
    animationType: AnimationType.slideDown,
  },
};

export const CloseOnEscape: Story = {
  render: PopoverTemplate,
  args: {
    arrow: 'top',
    gap: 8,
    closeOnEscape: false,
    animationType: AnimationType.slideDown,
  },
};

// eslint-disable-next-line @typescript-eslint/no-explicit-any
const RichContentTemplate = (args: any) => {
  const [isOpen, setIsOpen] = useState(false);
  const triggerRef = useRef<HTMLButtonElement>(null);

  const handleTogglePopover = () => {
    setIsOpen(!isOpen);
  };

  const handleClosePopover = () => {
    setIsOpen(false);
  };

  return (
    <div css={templateStyles.container}>
      <Button ref={triggerRef} onClick={handleTogglePopover} aria-expanded={isOpen} aria-haspopup="true">
        Open Rich Content Popover
      </Button>

      <Popover {...args} triggerRef={triggerRef} isOpen={isOpen} closePopover={handleClosePopover}>
        <div css={templateStyles.richContent}>
          <div css={templateStyles.header}>
            <h3 css={templateStyles.richTitle}>Course Options</h3>
            <Button onClick={handleClosePopover} variant="danger" size="small" css={templateStyles.headerCloseButton}>
              &times;
            </Button>
          </div>
          <div css={templateStyles.menuList}>
            <button css={templateStyles.menuItem}>
              <span>📚</span>
              View Course Details
            </button>
            <button css={templateStyles.menuItem}>
              <span>📝</span>
              Edit Course
            </button>
            <button css={templateStyles.menuItem}>
              <span>👥</span>
              Manage Students
            </button>
            <button css={templateStyles.menuItem}>
              <span>📊</span>
              View Analytics
            </button>
            <hr css={templateStyles.divider} />
            <button css={[templateStyles.menuItem, templateStyles.dangerItem]}>
              <span>🗑️</span>
              Delete Course
            </button>
          </div>
        </div>
      </Popover>
    </div>
  );
};

export const RichContent: Story = {
  render: RichContentTemplate,
  args: {
    arrow: 'top',
    gap: 8,
    maxWidth: '250px',
    closeOnEscape: true,
    animationType: AnimationType.slideDown,
  },
};

const templateStyles = {
  container: css`
    display: flex;
    justify-content: center;
    align-items: center;
    padding: ${spacing[32]};
  `,
  popoverContent: css`
    padding: ${spacing[16]};
  `,
  popoverTitle: css`
    margin: 0 0 ${spacing[8]} 0;
    font-size: 16px;
    font-weight: 600;
    color: ${colorTokens.text.title};
  `,
  popoverText: css`
    margin: 0 0 ${spacing[12]} 0;
    font-size: 14px;
    line-height: 1.5;
    color: ${colorTokens.text.primary};
  `,
  closeButton: css`
    background-color: ${colorTokens.action.secondary.default};
    color: ${colorTokens.text.primary};
    border: none;
    padding: ${spacing[8]} ${spacing[12]};
    border-radius: ${borderRadius[4]};
    cursor: pointer;
    font-size: 12px;

    &:hover {
      background-color: ${colorTokens.action.secondary.hover};
    }
  `,
  richContent: css`
    min-width: 220px;
  `,
  header: css`
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: ${spacing[12]} ${spacing[16]} ${spacing[8]} ${spacing[16]};
    border-bottom: 1px solid ${colorTokens.stroke.divider};
  `,
  richTitle: css`
    margin: 0;
    font-size: 14px;
    font-weight: 600;
    color: ${colorTokens.text.title};
  `,
  headerCloseButton: css`
    background: none;
    border: none;
    font-size: 18px;
    cursor: pointer;
    padding: ${spacing[4]};
    line-height: 1;
    color: ${colorTokens.text.hints};

    &:hover {
      color: ${colorTokens.text.primary};
    }
  `,
  menuList: css`
    padding: ${spacing[8]} 0;
  `,
  menuItem: css`
    display: flex;
    align-items: center;
    gap: ${spacing[8]};
    width: 100%;
    padding: ${spacing[8]} ${spacing[16]};
    background: none;
    border: none;
    font-size: 14px;
    color: ${colorTokens.text.primary};
    cursor: pointer;
    text-align: left;

    &:hover {
      background-color: ${colorTokens.background.hover};
    }

    span {
      font-size: 16px;
    }
  `,
  dangerItem: css`
    color: ${colorTokens.text.error};

    &:hover {
      background-color: ${colorTokens.text.error};
    }
  `,
  divider: css`
    margin: ${spacing[8]} ${spacing[16]};
    border: none;
    border-top: 1px solid ${colorTokens.stroke.divider};
  `,
};
