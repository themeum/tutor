import Button from '@TutorShared/atoms/Button';
import { AnimationType } from '@TutorShared/hooks/useAnimation';
import ConfirmationPopover from '@TutorShared/molecules/ConfirmationPopover';
import { useRef, useState } from 'react';
import type { Meta, StoryObj } from 'storybook-react-rsbuild';

const meta: Meta<typeof ConfirmationPopover> = {
  title: 'Molecules/ConfirmationPopover',
  component: ConfirmationPopover,
  tags: ['autodocs'],
  parameters: {
    layout: 'centered',
    docs: {
      description: {
        component:
          'ConfirmationPopover displays a popover for confirming or cancelling an action. It supports custom buttons, loading state, arrow, animation, and accessibility features.',
      },
    },
  },
  argTypes: {
    title: {
      control: 'text',
      description: 'Title of the popover.',
      defaultValue: 'Are you sure?',
    },
    message: {
      control: 'text',
      description: 'Message or description in the popover.',
      defaultValue: 'This action cannot be undone.',
    },
    isOpen: {
      control: 'boolean',
      description: 'Whether the popover is open.',
      defaultValue: true,
    },
    isLoading: {
      control: 'boolean',
      description: 'Show loading spinner on confirm button.',
      defaultValue: false,
    },
    hideArrow: {
      control: 'boolean',
      description: 'Hide the popover arrow.',
      defaultValue: false,
    },
    confirmButton: {
      control: false,
      description: 'Custom confirm button props.',
    },
    cancelButton: {
      control: false,
      description: 'Custom cancel button props.',
    },
    animationType: {
      control: 'select',
      options: Object.keys(AnimationType).filter((key) => isNaN(Number(key))),
      description: 'Popover animation type.',
      defaultValue: AnimationType.slideLeft,
    },
    maxWidth: {
      control: 'text',
      description: 'Maximum width of the popover.',
      defaultValue: '',
    },
    gap: {
      control: 'number',
      description: 'Gap between trigger and popover.',
      defaultValue: 8,
    },
    triggerRef: { control: false },
    closePopover: { control: false },
    onConfirmation: { control: false },
    onCancel: { control: false },
    positionModifier: { control: false },
    arrow: {
      control: 'select',
      options: ['top', 'bottom', 'left', 'right', 'middle'],
      description: 'Position of the popover arrow.',
      defaultValue: 'top',
    },
  },
  args: {
    title: 'Are you sure?',
    message: 'This action cannot be undone.',
    isOpen: true,
    isLoading: false,
    hideArrow: false,
    animationType: AnimationType.slideLeft,
    maxWidth: '300px',
    gap: 8,
  },
  render: (args) => {
    const triggerRef = useRef<HTMLButtonElement>(null);
    const [isOpen, setIsOpen] = useState(false);
    const [isLoading, setIsLoading] = useState(false);

    const animationTypeValue: AnimationType =
      typeof args.animationType === 'string'
        ? (AnimationType[args.animationType as keyof typeof AnimationType] ?? AnimationType.slideLeft)
        : AnimationType.slideLeft;

    const handleOpenPopover = () => setIsOpen(true);
    const handleClosePopover = () => setIsOpen(false);

    const handleConfirm = () => {
      setIsLoading(true);
      setTimeout(() => {
        setIsLoading(false);
        setIsOpen(false);
      }, 1200);
    };

    const handleCancel = () => {
      setIsOpen(false);
    };

    return (
      <>
        <Button
          ref={triggerRef}
          variant="primary"
          aria-label="Open Confirmation Popover"
          tabIndex={0}
          onClick={handleOpenPopover}
          onKeyDown={(event) => {
            if (event.key === 'Enter' || event.key === ' ') {
              handleOpenPopover();
            }
          }}
        >
          Show Confirmation Popover
        </Button>
        <ConfirmationPopover
          {...args}
          title="Caution!"
          message="This action will permanently delete your data. Are you sure you want to proceed?"
          triggerRef={triggerRef}
          animationType={animationTypeValue}
          isOpen={isOpen}
          isLoading={isLoading}
          closePopover={handleClosePopover}
          onConfirmation={handleConfirm}
          onCancel={handleCancel}
        />
      </>
    );
  },
};
export default meta;

type Story = StoryObj<typeof meta>;

export const Default: Story = {};

export const Loading: Story = {
  render: (args) => {
    const triggerRef = useRef<HTMLButtonElement>(null);
    const [isOpen, setIsOpen] = useState(false);

    const handleOpenPopover = () => setIsOpen(true);
    const handleClosePopover = () => setIsOpen(false);

    return (
      <>
        <Button
          ref={triggerRef}
          variant="primary"
          aria-label="Open Confirmation Popover"
          tabIndex={0}
          onClick={handleOpenPopover}
          onKeyDown={(event) => {
            if (event.key === 'Enter' || event.key === ' ') {
              handleOpenPopover();
            }
          }}
        >
          Show Confirmation Popover
        </Button>
        <ConfirmationPopover
          {...args}
          triggerRef={triggerRef}
          isOpen={isOpen}
          isLoading={true}
          closePopover={handleClosePopover}
          onConfirmation={handleClosePopover}
          onCancel={handleClosePopover}
        />
      </>
    );
  },
};

export const DeleteAction: Story = {
  args: {
    confirmButton: {
      text: 'Delete',
      variant: 'danger',
      isDelete: true,
    },
    cancelButton: {
      text: 'Keep',
      variant: 'secondary',
    },
    title: 'Delete Item?',
    message: 'Are you sure you want to delete this item? This cannot be undone.',
  },
};

export const CustomButtons: Story = {
  args: {
    confirmButton: {
      text: 'Yes, Proceed',
      variant: 'primary',
    },
    cancelButton: {
      text: 'No, Cancel',
      variant: 'tertiary',
    },
    title: 'Proceed with Action?',
    message: 'Do you want to continue with this operation?',
  },
};

export const HideArrow: Story = {
  args: {
    hideArrow: true,
  },
};

export const CustomAnimation: Story = {
  args: {
    animationType: AnimationType.slideUp,
  },
};

export const CustomMaxWidth: Story = {
  args: {
    maxWidth: '320px',
  },
};
