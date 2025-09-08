import SVGIcon from '@TutorShared/atoms/SVGIcon';
import { AnimationType } from '@TutorShared/hooks/useAnimation';
import { POPOVER_PLACEMENTS } from '@TutorShared/hooks/usePortalPopover';
import ThreeDots from '@TutorShared/molecules/ThreeDots';
import { useState } from 'react';
import type { Meta, StoryObj } from 'storybook-react-rsbuild';

const meta = {
  title: 'Molecules/ThreeDots',
  component: ThreeDots,
  tags: ['autodocs'],
  parameters: {
    layout: 'centered',
    docs: {
      description: {
        component:
          'ThreeDots is a popover menu triggered by a three-dots button. Supports horizontal/vertical orientation, custom options, inverse style, arrow position, animation, and accessibility.',
      },
    },
  },
  argTypes: {
    isOpen: {
      control: 'boolean',
      description: 'Whether the popover is open.',
      defaultValue: false,
    },
    placement: {
      control: 'select',
      options: Object.values(POPOVER_PLACEMENTS),
      description: 'Arrow position for popover.',
      defaultValue: 'top',
    },
    animationType: {
      control: 'select',
      options: Object.keys(AnimationType).filter((key) => isNaN(Number(key))),
      description: 'Popover animation type.',
      defaultValue: AnimationType.slideLeft,
    },
    dotsOrientation: {
      control: 'select',
      options: ['horizontal', 'vertical'],
      description: 'Orientation of dots icon.',
      defaultValue: 'horizontal',
    },
    maxWidth: {
      control: 'text',
      description: 'Max width of the popover.',
      defaultValue: '148px',
    },
    isInverse: {
      control: 'boolean',
      description: 'Inverse style for button.',
      defaultValue: false,
    },
    arrow: {
      control: 'boolean',
      description: 'Show the popover arrow.',
      defaultValue: false,
    },
    size: {
      control: 'select',
      options: ['small', 'medium'],
      description: 'Size of the button and options.',
      defaultValue: 'medium',
    },
    disabled: {
      control: 'boolean',
      description: 'Disable the trigger button.',
      defaultValue: false,
    },
    closeOnEscape: {
      control: 'boolean',
      description: 'Close popover on Escape key.',
      defaultValue: true,
    },
    wrapperCss: {
      control: false,
      description: 'Custom Emotion CSS for wrapper.',
    },
    onClick: { control: false },
    closePopover: { control: false },
    children: { control: false },
  },
  args: {
    isOpen: false,
    placement: POPOVER_PLACEMENTS.BOTTOM_RIGHT,
    animationType: AnimationType.slideUp,
    dotsOrientation: 'horizontal',
    maxWidth: '148px',
    isInverse: false,
    arrow: false,
    size: 'medium',
    disabled: false,
    closeOnEscape: true,
    wrapperCss: undefined,
    closePopover: () => {},
    onClick: () => {},
    children: (
      <>
        <ThreeDots.Option
          text="Edit"
          icon={<SVGIcon name="edit" width={18} height={18} />}
          aria-label="Edit"
          onClick={() => alert('Edit clicked')}
        />
        <ThreeDots.Option
          text="Duplicate"
          icon={<SVGIcon name="copy" width={18} height={18} />}
          aria-label="Duplicate"
          onClick={() => alert('Duplicate clicked')}
        />
        <ThreeDots.Option
          text="Delete"
          icon={<SVGIcon name="delete" width={18} height={18} />}
          isTrash
          aria-label="Delete"
          onClick={() => alert('Delete clicked')}
        />
      </>
    ),
  },
  render: (args) => {
    const [isOpen, setIsOpen] = useState(false);

    const handleClickButton = () => setIsOpen((open) => !open);
    const handleClosePopover = () => setIsOpen(false);

    const animationTypeValue: AnimationType =
      typeof args.animationType === 'string'
        ? (AnimationType[args.animationType as keyof typeof AnimationType] ?? AnimationType.slideLeft)
        : AnimationType.slideLeft;

    return (
      <ThreeDots
        {...args}
        isOpen={isOpen}
        onClick={handleClickButton}
        closePopover={handleClosePopover}
        animationType={animationTypeValue}
      >
        {args.children}
      </ThreeDots>
    );
  },
} satisfies Meta<typeof ThreeDots>;
export default meta;

type Story = StoryObj<typeof meta>;

export const Default = {} satisfies Story;

export const Vertical = {
  args: {
    dotsOrientation: 'vertical',
  },
} satisfies Story;

export const Inverse = {
  args: {
    isInverse: true,
  },
} satisfies Story;

export const SmallSize = {
  args: {
    size: 'small',
  },
} satisfies Story;

export const CustomMaxWidth = {
  args: {
    maxWidth: '220px',
  },
} satisfies Story;

export const HideArrow = {
  args: {
    arrow: false,
  },
} satisfies Story;

export const Disabled = {
  args: {
    disabled: true,
  },
} satisfies Story;
