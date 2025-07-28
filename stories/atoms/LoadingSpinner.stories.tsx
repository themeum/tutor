import LoadingSpinner, {
  FullscreenLoadingSpinner,
  GradientLoadingSpinner,
  LoadingOverlay,
  LoadingSection,
} from '@TutorShared/atoms/LoadingSpinner';
import type { Meta, StoryObj } from 'storybook-react-rsbuild';

const meta: Meta<typeof LoadingSpinner> = {
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
};
export default meta;

type Story = StoryObj<typeof meta>;

export const Default: Story = {
  args: {
    size: 30,
    color: '#D1D5DB',
  },
  render: (args) => <LoadingSpinner {...args} aria-label="Loading" />,
};

export const CustomSize: Story = {
  args: {
    size: 60,
    color: '#D1D5DB',
  },
  render: (args) => <LoadingSpinner {...args} aria-label="Loading large" />,
};

export const CustomColor: Story = {
  args: {
    size: 40,
    color: '#1976d2',
  },
  render: (args) => <LoadingSpinner {...args} aria-label="Loading blue" />,
};

export const Overlay: Story = {
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
          bottom: 8,
          left: 16,
          fontSize: 12,
          color: '#888',
        }}
      >
        Loading overlay
      </span>
    </div>
  ),
};

export const Section: Story = {
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
};

export const Fullscreen: Story = {
  render: () => <FullscreenLoadingSpinner />,
};

export const Gradient: Story = {
  render: () => <GradientLoadingSpinner size={48} aria-label="Gradient loading" />,
};
