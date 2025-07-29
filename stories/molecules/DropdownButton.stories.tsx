import SVGIcon from '@TutorShared/atoms/SVGIcon';
import { AnimationType } from '@TutorShared/hooks/useAnimation';
import DropdownButton from '@TutorShared/molecules/DropdownButton';
import type { Meta, StoryObj } from 'storybook-react-rsbuild';

const meta: Meta<typeof DropdownButton> = {
  title: 'Molecules/DropdownButton',
  component: DropdownButton,
  tags: ['autodocs'],
  parameters: {
    layout: 'centered',
    docs: {
      description: {
        component:
          'DropdownButton is a split button with a dropdown menu for additional actions. Supports custom icons, variants, sizes, loading, disabled state, and accessibility features.',
      },
    },
  },
  argTypes: {
    text: {
      control: 'text',
      description: 'Main button text.',
      defaultValue: 'Actions',
    },
    variant: {
      control: 'select',
      options: ['primary', 'secondary', 'tertiary', 'danger', 'text'],
      description: 'Button variant.',
      defaultValue: 'primary',
    },
    size: {
      control: 'select',
      options: ['regular', 'small', 'large'],
      description: 'Button size.',
      defaultValue: 'regular',
    },
    icon: {
      control: false,
      description: 'Optional icon for the button.',
    },
    iconPosition: {
      control: 'select',
      options: ['left', 'right'],
      description: 'Position of the icon.',
      defaultValue: 'left',
    },
    disabled: {
      control: 'boolean',
      description: 'Disable the main button.',
      defaultValue: false,
    },
    loading: {
      control: 'boolean',
      description: 'Show loading spinner on main button.',
      defaultValue: false,
    },
    dropdownMaxWidth: {
      control: 'text',
      description: 'Max width of the dropdown.',
      defaultValue: '140px',
    },
    disabledDropdown: {
      control: 'boolean',
      description: 'Disable the dropdown trigger.',
      defaultValue: false,
    },
    arrowPosition: {
      control: 'select',
      options: ['top', 'bottom', 'left', 'right'],
      description: 'Arrow position for dropdown.',
      defaultValue: 'top',
    },
    animationType: {
      control: 'select',
      options: Object.values(AnimationType),
      description: 'Dropdown animation type.',
      defaultValue: 'slideUp',
    },
    buttonCss: { control: false },
    buttonContentCss: { control: false },
    children: { control: false },
    onClick: {
      control: false,
      description: 'Function to call when the main button is clicked.',
      action: 'clicked',
    },
    tabIndex: {
      control: 'number',
      description: 'Tab index for accessibility.',
      defaultValue: 0,
    },
  },
  args: {
    text: 'Actions',
    variant: 'primary',
    size: 'regular',
    icon: undefined,
    iconPosition: 'left',
    disabled: false,
    loading: false,
    dropdownMaxWidth: '140px',
    disabledDropdown: false,
    arrowPosition: 'top',
    animationType: AnimationType.slideUp,
    tabIndex: 0,
    children: (
      <>
        <DropdownButton.Item
          text="Edit"
          icon={<SVGIcon name="edit" width={20} height={20} />}
          aria-label="Edit"
          onClick={() => alert('Edit clicked')}
        />
        <DropdownButton.Item
          text="Duplicate"
          icon={<SVGIcon name="copy" width={20} height={20} />}
          aria-label="Duplicate"
          onClick={() => alert('Duplicate clicked')}
        />
        <DropdownButton.Item
          text="Delete"
          icon={<SVGIcon name="delete" width={20} height={20} />}
          isDanger
          aria-label="Delete"
          onClick={() => alert('Delete clicked')}
        />
      </>
    ),
  },
  render: (args) => <DropdownButton {...args} />,
};
export default meta;

type Story = StoryObj<typeof meta>;

export const Default: Story = {};

export const WithIcon: Story = {
  args: {
    icon: <SVGIcon name="star" width={18} height={18} />,
    text: 'Starred',
  },
};

export const DangerOption: Story = {
  args: {
    children: (
      <>
        <DropdownButton.Item
          text="Archive"
          icon={<SVGIcon name="archive" width={18} height={18} />}
          aria-label="Archive"
          onClick={() => alert('Archive clicked')}
        />
        <DropdownButton.Item
          text="Delete"
          icon={<SVGIcon name="delete" width={18} height={18} />}
          isDanger
          aria-label="Delete"
          onClick={() => alert('Delete clicked')}
        />
      </>
    ),
  },
};

export const Disabled: Story = {
  args: {
    disabled: true,
  },
};

export const Loading: Story = {
  args: {
    loading: true,
  },
};

export const Small: Story = {
  args: {
    size: 'small',
  },
};

export const Large: Story = {
  args: {
    size: 'large',
  },
};

export const CustomDropdownWidth: Story = {
  args: {
    dropdownMaxWidth: '240px',
  },
};
