import SVGIcon from '@TutorShared/atoms/SVGIcon';
import { icons, type IconCollection } from '@TutorShared/icons/types';
import { type Meta, type StoryObj } from 'storybook-react-rsbuild';

const meta = {
  title: 'Atoms/SVGIcon',
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
  args: {
    name: 'plus',
    width: 24,
    height: 24,
    isColorIcon: false,
  },
  render: (args) => <SVGIcon {...args} aria-label={args.name} />,
} satisfies Meta<typeof SVGIcon>;

export default meta;

type Story = StoryObj<typeof SVGIcon>;

export const Default = {
  args: {
    name: 'plus',
  },
} satisfies Story;

export const ColorIcon = {
  args: {
    name: 'googleMeetColorize',
    width: 32,
    height: 32,
  },
} satisfies Story;

export const CustomSize = {
  args: {
    name: 'alert',
    width: 48,
    height: 48,
  },
} satisfies Story;

export const AllIcons = {
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
} satisfies Story;
