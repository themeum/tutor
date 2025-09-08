import { css } from '@emotion/react';
import Button from '@TutorShared/atoms/Button';
import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import { AnimationType } from '@TutorShared/hooks/useAnimation';
import type { PopoverPlacement } from '@TutorShared/hooks/usePortalPopover';
import Popover from '@TutorShared/molecules/Popover';
import { createRef, useMemo, useRef, useState } from 'react';
import { type Meta, type StoryObj } from 'storybook-react-rsbuild';

const placements: PopoverPlacement[] = [
  'topLeft',
  'top',
  'topRight',
  'leftTop',
  'left',
  'leftBottom',
  'rightTop',
  'right',
  'rightBottom',
  'bottomLeft',
  'bottom',
  'bottomRight',
  'middle',
  'absoluteCenter',
];

const meta = {
  title: 'Molecules/Popover',
  component: Popover,
  parameters: {
    layout: 'centered',
    docs: {
      description: {
        component:
          'Popover is a flexible popover component that supports custom placement, animation, arrow, and accessibility features. It uses a portal and can be anchored to any trigger element.',
      },
    },
  },
  tags: ['autodocs'],
  argTypes: {
    placement: {
      control: 'select',
      options: placements,
      description: 'Popover placement relative to the trigger.',
      defaultValue: 'bottom',
    },
    isOpen: {
      control: 'boolean',
      description: 'Whether the popover is open.',
      defaultValue: false,
    },
    gap: {
      control: 'number',
      description: 'Gap (px) between trigger and popover.',
      defaultValue: 8,
    },
    maxWidth: {
      control: 'text',
      description: 'Max width of the popover.',
      defaultValue: '240px',
    },
    closeOnEscape: {
      control: 'boolean',
      description: 'Close popover on Escape key.',
      defaultValue: true,
    },
    animationType: {
      control: { type: 'select' },
      options: Object.keys(AnimationType).filter((key) => isNaN(Number(key))),
      description: 'Animation type for popover entrance/exit',
      defaultValue: AnimationType.slideLeft,
    },
    arrow: {
      control: 'boolean',
      description: 'Show arrow on popover.',
      defaultValue: false,
    },
    autoAdjustOverflow: {
      control: 'boolean',
      description: 'Automatically adjust popover position to prevent overflow.',
      defaultValue: true,
    },
    positionModifier: {
      control: 'object',
      description: 'Position modifier for the popover.',
      defaultValue: {
        top: 0,
        left: 0,
      },
    },
    triggerRef: { control: false },
    children: { control: false },
    closePopover: { control: false },
  },
  args: {
    placement: 'bottom',
    gap: 8,
    maxWidth: '240px',
    closeOnEscape: true,
    animationType: AnimationType.slideLeft,
    arrow: true,
    isOpen: undefined,
    triggerRef: undefined,
    closePopover: undefined,
    children: undefined,
  },
  render: (args) => {
    const triggerRef = useRef<HTMLButtonElement>(null);
    const [isOpen, setOpen] = useState(args.isOpen);

    const handleTogglePopover = () => setOpen((prev) => !prev);
    const handleClosePopover = () => setOpen(false);

    const animationTypeValue: AnimationType =
      typeof args.animationType === 'string'
        ? (AnimationType[args.animationType as keyof typeof AnimationType] ?? AnimationType.slideLeft)
        : AnimationType.slideLeft;

    return (
      <div css={templateStyles.container}>
        <Button ref={triggerRef} onClick={handleTogglePopover} aria-expanded={isOpen} aria-haspopup="true">
          Toggle Popover
        </Button>

        <Popover
          {...args}
          triggerRef={triggerRef}
          animationType={animationTypeValue}
          isOpen={isOpen}
          closePopover={handleClosePopover}
        >
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
  },
} satisfies Meta<typeof Popover>;

export default meta;

type Story = StoryObj<typeof meta>;

export const Default = {
  args: {
    placement: 'bottom',
    gap: 8,
    maxWidth: '240px',
    closeOnEscape: true,
    animationType: AnimationType.slideLeft,
    arrow: true,
  },
} satisfies Story;

export const AllPlacements = {
  render: (args) => {
    const triggerRefs = useMemo(
      () =>
        placements.reduce(
          (acc, placement) => ({
            ...acc,
            [placement]: createRef<HTMLButtonElement>(),
          }),
          {} as Record<PopoverPlacement, React.RefObject<HTMLButtonElement>>,
        ),
      [],
    );
    const [openPopovers, setOpenPopovers] = useState<Record<string, boolean>>({});

    const animationTypeValue: AnimationType =
      typeof args.animationType === 'string'
        ? (AnimationType[args.animationType as keyof typeof AnimationType] ?? AnimationType.slideLeft)
        : AnimationType.slideLeft;

    const handleClosePopover = () => setOpenPopovers({});

    return (
      <div
        css={css`
          display: grid;
          grid-template-columns: repeat(5, 1fr);
          grid-template-rows: repeat(5, 1fr);
          gap: 16px;

          button:nth-child(1) {
            grid-column: 2;
            grid-row: 1;
          }

          button:nth-child(2) {
            grid-column: 3;
            grid-row: 1;
          }

          button:nth-child(3) {
            grid-column: 4;
            grid-row: 1;
          }

          button:nth-child(4) {
            grid-column: 1;
            grid-row: 2;
          }

          button:nth-child(5) {
            grid-column: 1;
            grid-row: 3;
          }

          button:nth-child(6) {
            grid-column: 1;
            grid-row: 4;
          }

          button:nth-child(7) {
            grid-column: 5;
            grid-row: 2;
          }

          button:nth-child(8) {
            grid-column: 5;
            grid-row: 3;
          }

          button:nth-child(9) {
            grid-column: 5;
            grid-row: 4;
          }

          button:nth-child(10) {
            grid-column: 2;
            grid-row: 5;
          }

          button:nth-child(11) {
            grid-column: 3;
            grid-row: 5;
          }

          button:nth-child(12) {
            grid-column: 4;
            grid-row: 5;
          }

          button:nth-child(13) {
            grid-column: 2 / 6;
            grid-row: 3;
            justify-self: center;
            margin-right: 50%;
          }

          button:nth-child(14) {
            grid-column: 4 / 6;
            grid-row: 3;
            justify-self: center;
            margin-right: 100%;
          }
        `}
      >
        {placements.map((placement) => (
          <>
            <Button
              ref={triggerRefs[placement]}
              onClick={() => {
                setOpenPopovers((prev) => ({
                  ...prev,
                  [placement]: !prev[placement],
                }));
              }}
            >
              {placement}
            </Button>

            <Popover
              {...args}
              placement={placement}
              triggerRef={triggerRefs[placement]}
              animationType={animationTypeValue}
              isOpen={!!openPopovers[placement]}
              closePopover={handleClosePopover}
            >
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
          </>
        ))}
      </div>
    );
  },
} satisfies Story;

export const NoArrow = {
  args: {
    ...Default.args,
    arrow: false,
  },
} satisfies Story;

export const TopPlacement = {
  args: {
    ...Default.args,
    placement: 'top',
  },
} satisfies Story;

export const RightPlacement = {
  args: {
    ...Default.args,
    placement: 'right',
  },
} satisfies Story;

export const LeftPlacement = {
  args: {
    ...Default.args,
    placement: 'left',
  },
} satisfies Story;

export const TopLeftPlacement = {
  args: {
    ...Default.args,
    placement: 'topLeft',
  },
} satisfies Story;

export const TopRightPlacement = {
  args: {
    ...Default.args,
    placement: 'topRight',
  },
} satisfies Story;

export const RightTopPlacement = {
  args: {
    ...Default.args,
    placement: 'rightTop',
  },
} satisfies Story;

export const RightBottomPlacement = {
  args: {
    ...Default.args,
    placement: 'rightBottom',
  },
} satisfies Story;

export const BottomLeftPlacement = {
  args: {
    ...Default.args,
    placement: 'bottomLeft',
  },
} satisfies Story;

export const BottomRightPlacement = {
  args: {
    ...Default.args,
    placement: 'bottomRight',
  },
} satisfies Story;

export const LeftTopPlacement = {
  args: {
    ...Default.args,
    placement: 'leftTop',
  },
} satisfies Story;

export const LeftBottomPlacement = {
  args: {
    ...Default.args,
    placement: 'leftBottom',
  },
} satisfies Story;

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
