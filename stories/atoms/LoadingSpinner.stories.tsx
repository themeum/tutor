import LoadingSpinner, {
  FullscreenLoadingSpinner,
  GradientLoadingSpinner,
  LoadingOverlay,
  LoadingSection,
} from '@TutorShared/atoms/LoadingSpinner';
import type { Meta, StoryObj } from 'storybook-react-rsbuild';

const meta = {
  title: 'Atoms/LoadingSpinner',
  component: LoadingSpinner,
  tags: ['autodocs'],
  parameters: {
    layout: 'centered',
    docs: {
      description: {
        component:
          'LoadingSpinner is a flexible, accessible SVG spinner for indicating loading states. It supports custom size, color, and gradient variants. Use for overlays, sections, or fullscreen loading feedback.',
      },
    },
  },
  argTypes: {
    size: {
      control: 'number',
      description: 'Size of the spinner in pixels.',
      defaultValue: 30,
    },
    color: {
      control: 'color',
      description: 'Stroke color of the spinner.',
      defaultValue: '#D1D5DB',
    },
  },
} satisfies Meta<typeof LoadingSpinner>;

export default meta;

type Story = StoryObj<typeof meta>;

export const Default = {
  args: {
    size: 30,
    color: '#D1D5DB',
  },
  render: (args) => <LoadingSpinner {...args} aria-label="Loading" />,
} satisfies Story;

export const CustomSize = {
  args: {
    size: 60,
    color: '#D1D5DB',
  },
  render: (args) => <LoadingSpinner {...args} aria-label="Loading large" />,
} satisfies Story;

export const CustomColor = {
  args: {
    size: 40,
    color: '#1976d2',
  },
  render: (args) => <LoadingSpinner {...args} aria-label="Loading blue" />,
} satisfies Story;

export const Overlay = {
  render: () => (
    <div
      style={{
        position: 'relative',
        width: 200,
        height: 120,
        background: '#f0f4ff',
        borderRadius: 8,
        overflow: 'hidden',
      }}
    >
      <LoadingOverlay />
      <span
        style={{
          position: 'absolute',
          transform: 'translate(-50%, -50%)',
          top: '50%',
          left: '50%',
          fontSize: 12,
          color: '#888',
        }}
      >
        Loading overlay will be on top of this content
      </span>
    </div>
  ),
} satisfies Story;

export const Section = {
  render: () => (
    <div
      style={{
        width: 300,
        height: 100,
        border: '1px solid #e0e0e0',
        borderRadius: 8,
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        background: '#fff',
      }}
    >
      <LoadingSection />
    </div>
  ),
} satisfies Story;

export const Fullscreen = {
  render: () => <FullscreenLoadingSpinner />,
} satisfies Story;

export const Gradient = {
  render: () => <GradientLoadingSpinner size={48} aria-label="Gradient loading" />,
} satisfies Story;
