import SVGIcon from '@TutorShared/atoms/SVGIcon';
import { icons, type IconCollection } from '@TutorShared/icons/types';
import { type Meta, type StoryObj } from 'storybook-react-rsbuild';

const meta: Meta<typeof SVGIcon> = {
  title: 'Shared/Atoms/SVGIcon',
  component: SVGIcon,
  parameters: {
    layout: 'centered',
    docs: {
      description: {
        component: 'A versatile SVG icon component that supports dynamic loading and colorization.',
      },
    },
  },
  tags: ['autodocs'],
  argTypes: {
    name: {
      control: 'text',
      description: 'Name of the icon from IconCollection',
    },
    width: {
      control: 'number',
      defaultValue: 24,
    },
    height: {
      control: 'number',
      defaultValue: 24,
    },
    isColorIcon: {
      control: 'boolean',
      defaultValue: false,
    },
    style: {
      control: false,
    },
  },
};

export default meta;

type Story = StoryObj<typeof SVGIcon>;

export const Default: Story = {
  args: {
    name: 'plus',
    width: 24,
    height: 24,
    isColorIcon: false,
  },
};

export const ColorIcon: Story = {
  args: {
    name: 'googleMeetColorize',
    width: 32,
    height: 32,
    isColorIcon: true,
  },
};

export const CustomSize: Story = {
  args: {
    name: 'alert',
    width: 48,
    height: 48,
    isColorIcon: false,
  },
};

export const AllIcons: Story = {
  render: (args) => {
    return (
      <div
        style={{
          display: 'grid',
          gridTemplateColumns: 'repeat(auto-fill, minmax(100px, 1fr))',
          gap: '32px',
          width: '100vw',
        }}
      >
        {icons.map((iconName) => (
          <div key={iconName} style={{ display: 'flex', flexDirection: 'column', alignItems: 'center' }}>
            <SVGIcon name={iconName as IconCollection} height={args.height} width={args.width} />
            <span style={{ marginTop: 8 }}>{iconName}</span>
          </div>
        ))}
      </div>
    );
  },
};
