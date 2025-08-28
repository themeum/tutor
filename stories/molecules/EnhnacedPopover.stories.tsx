import { css } from '@emotion/react';
import Button from '@TutorShared/atoms/Button';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import { AnimationType } from '@TutorShared/hooks/useAnimation';
import type { PopoverPlacement } from '@TutorShared/hooks/useEnhancedPortalPopover';
import EnhancedPopover from '@TutorShared/molecules/EnhancedPopover';
import { createRef, useEffect, useMemo, useRef, useState } from 'react';
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
];

const meta = {
  title: 'Molecules/EnhancedPopover',
  component: EnhancedPopover,
  parameters: {
    layout: 'centered',
    docs: {
      description: {
        component:
          'EnhancedPopover is a flexible popover component that supports custom placement, animation, arrow, and accessibility features. It uses a portal and can be anchored to any trigger element.',
      },
    },
  },
  tags: ['autodocs'],
  argTypes: {
    placement: {
      control: 'select',
      options: [...placements, 'middle', 'absoluteCenter'],
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
      control: 'select',
      options: Object.values(AnimationType),
      description: 'Popover animation type.',
      defaultValue: AnimationType.slideLeft,
    },
    arrow: {
      control: 'boolean',
      description: 'Show arrow on popover.',
      defaultValue: true,
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
    const buttonRef = useRef<HTMLButtonElement>(null);
    const [open, setOpen] = useState(args.isOpen);

    const handleToggle = () => setOpen((prev) => !prev);
    const handleClose = () => setOpen(false);

    return (
      <div>
        <Button
          ref={buttonRef}
          onClick={handleToggle}
          isIconOnly
          icon={<SVGIcon name="plus" width={24} height={24} />}
          aria-label="Open Popover"
        />
        <EnhancedPopover {...args} isOpen={open} triggerRef={buttonRef} closePopover={handleClose}>
          <div
            id="enhanced-popover"
            role="dialog"
            aria-modal="true"
            tabIndex={-1}
            style={{ padding: 24, minWidth: 180, textAlign: 'center' }}
          >
            <strong>Popover Content</strong>
            <div style={{ marginTop: 8 }}>You can put any content here.</div>
            <Button variant="danger" size="small" onClick={handleClose} style={{ marginTop: 16 }}>
              Close
            </Button>
          </div>
        </EnhancedPopover>
      </div>
    );
  },
} satisfies Meta<typeof EnhancedPopover>;

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
    const buttonRefs = useMemo(
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

    const handleClose = () => setOpenPopovers({});

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
        `}
      >
        {placements.map((placement) => (
          <>
            <Button
              ref={buttonRefs[placement]}
              onClick={() => {
                setOpenPopovers((prev) => ({
                  ...prev,
                  [placement]: !prev[placement],
                }));
              }}
            >
              Popover {placement}
            </Button>

            <EnhancedPopover
              {...args}
              isOpen={openPopovers[placement]}
              placement={placement}
              triggerRef={buttonRefs[placement]}
              closePopover={handleClose}
            >
              <div
                id={`enhanced-popover-${placement}`}
                role="dialog"
                aria-modal="true"
                tabIndex={-1}
                style={{ padding: 24, minWidth: 180, textAlign: 'center' }}
              >
                <strong>Popover Content</strong>
                <div style={{ marginTop: 8 }}>You can put any content here.</div>
                <Button variant="secondary" size="small" onClick={handleClose} style={{ marginTop: 16 }}>
                  Close
                </Button>
              </div>
            </EnhancedPopover>
          </>
        ))}
      </div>
    );
  },
} satisfies Story;

// Scrollable container to demonstrate auto-shifting
export const AutoShift = {
  render: () => {
    const buttonRef = useRef<HTMLButtonElement>(null);
    const [isOpen, setIsOpen] = useState(false);

    useEffect(() => {
      if (buttonRef.current) {
        // scroll to center
        buttonRef.current.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
    }, []);

    return (
      <div
        style={{
          width: '400px',
          height: '400px',
          maxHeight: '300px',
          overflowY: 'auto',
          border: '1px solid #ccc',
        }}
      >
        <div
          style={{
            height: '100vh',
            width: '100vw',
            borderRadius: 4,
            padding: 8,
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
          }}
        >
          <Button ref={buttonRef} onClick={() => setIsOpen(true)}>
            Open Popover
          </Button>

          <EnhancedPopover
            triggerRef={buttonRef}
            placement="bottom"
            isOpen={isOpen}
            closePopover={() => setIsOpen(false)}
            autoAdjustOverflow={true}
            maxWidth="200px"
          >
            <div
              id={`enhanced-popover`}
              role="dialog"
              aria-modal="true"
              tabIndex={-1}
              style={{ padding: 24, minWidth: 180, textAlign: 'center' }}
            >
              <strong>Popover Content</strong>
              <div style={{ marginTop: 8 }}>You can put any content here.</div>
              <Button variant="secondary" size="small" onClick={() => setIsOpen(false)} style={{ marginTop: 16 }}>
                Close
              </Button>
            </div>
          </EnhancedPopover>
        </div>
      </div>
    );
  },
};

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

export const NoArrow = {
  args: {
    ...Default.args,
    arrow: false,
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
