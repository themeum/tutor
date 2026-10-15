import type { CSSProperties } from 'react';
import type { Meta, StoryObj } from 'storybook-react-rsbuild';

import '../../assets/css/tutor-core.min.css';
import '../../assets/css/tutor-kids.min.css';

const variants = [
  { label: 'Primary', className: 'tutor-btn-primary' },
  { label: 'Primary Soft', className: 'tutor-btn-primary-soft' },
  { label: 'Destructive', className: 'tutor-btn-destructive' },
  { label: 'Destructive Soft', className: 'tutor-btn-destructive-soft' },
  { label: 'Secondary', className: 'tutor-btn-secondary' },
  { label: 'Outline', className: 'tutor-btn-outline' },
  { label: 'Ghost', className: 'tutor-btn-ghost' },
  { label: 'Ghost Brand', className: 'tutor-btn-ghost-brand' },
  { label: 'Link', className: 'tutor-btn-link' },
  { label: 'Link Gray', className: 'tutor-btn-link-gray' },
  { label: 'Link Destructive', className: 'tutor-btn-link-destructive' },
];

const sizes = [
  { label: 'X-Small', className: 'tutor-btn-x-small' },
  { label: 'Small', className: 'tutor-btn-small' },
  { label: 'Medium', className: 'tutor-btn-medium' },
  { label: 'Large', className: 'tutor-btn-large' },
];

const wrapperStyle: CSSProperties = {
  padding: '24px',
  borderRadius: '16px',
  background: 'var(--tutor-surface-base)',
  border: '1px solid var(--tutor-border-idle)',
};

const gridStyle: CSSProperties = {
  display: 'grid',
  gridTemplateColumns: 'repeat(auto-fit, minmax(180px, 1fr))',
  gap: '12px',
  alignItems: 'center',
};

const rowStyle: CSSProperties = {
  display: 'flex',
  flexWrap: 'wrap',
  gap: '12px',
  alignItems: 'center',
};

const labelStyle: CSSProperties = {
  fontSize: '14px',
  fontWeight: 600,
  color: 'var(--tutor-text-secondary)',
};

const iconStyle: CSSProperties = {
  width: '20px',
  height: '20px',
  display: 'block',
};

const iconSvg = (
  <svg viewBox="0 0 24 24" aria-hidden="true" style={iconStyle}>
    <path
      fill="currentColor"
      d="M12 4l2.47 4.99 5.5.8-3.98 3.88.94 5.48L12 16.9 7.07 19.15l.94-5.48-3.98-3.88 5.5-.8L12 4z"
    />
  </svg>
);

type PlaygroundArgs = {
  variant: string;
  size: string;
  uiMode: 'default' | 'kids';
  theme: 'light' | 'dark';
  disabled: boolean;
  iconOnly: boolean;
};

const meta = {
  title: 'Core/Button',
  parameters: {
    layout: 'centered',
    docs: {
      description: {
        component: 'Core (SCSS) button styles from assets/core/scss/components/_button.scss.',
      },
    },
  },
  tags: ['autodocs'],
  argTypes: {
    variant: {
      control: 'select',
      options: variants.map((variant) => variant.className),
      description: 'Button variant class name.',
    },
    size: {
      control: 'select',
      options: sizes.map((size) => size.className),
      description: 'Button size class name.',
    },
    uiMode: {
      control: 'radio',
      options: ['default', 'kids'],
      description: 'UI mode data attribute.',
    },
    theme: {
      control: 'radio',
      options: ['light', 'dark'],
      description: 'Theme data attribute.',
    },
    disabled: {
      control: 'boolean',
      description: 'Disables the button.',
    },
    iconOnly: {
      control: 'boolean',
      description: 'Renders as icon-only button.',
    },
  },
  args: {
    variant: 'tutor-btn-primary',
    size: 'tutor-btn-large',
    uiMode: 'kids',
    theme: 'light',
    disabled: false,
    iconOnly: false,
  },
  render: (args: PlaygroundArgs) => {
    const dataTutorUi = args.uiMode === 'kids' ? 'kids' : undefined;
    const isIconOnly = args.iconOnly;
    return (
      <div data-theme={args.theme} data-tutor-ui={dataTutorUi} style={wrapperStyle}>
        <button
          className={`tutor-btn ${args.variant} ${args.size}${isIconOnly ? ' tutor-btn-icon' : ''}`}
          disabled={args.disabled}
          aria-label={isIconOnly ? 'Icon button' : undefined}
        >
          {isIconOnly ? iconSvg : 'Core Button'}
        </button>
      </div>
    );
  },
} satisfies Meta<PlaygroundArgs>;

export default meta;

type Story = StoryObj<typeof meta>;

export const Playground = {} satisfies Story;

export const AllVariants = {
  parameters: { controls: { disable: true } },
  render: () => (
    <div data-theme="light" style={wrapperStyle}>
      <div style={gridStyle}>
        {variants.map((variant) => (
          <button key={variant.className} className={`tutor-btn ${variant.className} tutor-btn-medium`}>
            {variant.label}
          </button>
        ))}
      </div>
    </div>
  ),
} satisfies Story;

export const Sizes = {
  parameters: { controls: { disable: true } },
  render: () => (
    <div data-theme="light" style={wrapperStyle}>
      <div style={rowStyle}>
        {sizes.map((size) => (
          <button key={size.className} className={`tutor-btn tutor-btn-primary ${size.className}`}>
            {size.label}
          </button>
        ))}
      </div>
    </div>
  ),
} satisfies Story;

export const Themes = {
  parameters: { controls: { disable: true } },
  render: () => (
    <div style={rowStyle}>
      <div data-theme="light" style={{ ...wrapperStyle, display: 'grid', gap: '12px' }}>
        <div style={labelStyle}>Light Theme</div>
        <div style={gridStyle}>
          {variants.slice(0, 4).map((variant) => (
            <button key={variant.className} className={`tutor-btn ${variant.className} tutor-btn-medium`}>
              {variant.label}
            </button>
          ))}
        </div>
      </div>
      <div data-theme="dark" style={{ ...wrapperStyle, display: 'grid', gap: '12px' }}>
        <div style={labelStyle}>Dark Theme</div>
        <div style={gridStyle}>
          {variants.slice(0, 4).map((variant) => (
            <button key={variant.className} className={`tutor-btn ${variant.className} tutor-btn-medium`}>
              {variant.label}
            </button>
          ))}
        </div>
      </div>
    </div>
  ),
} satisfies Story;

export const UIModes = {
  parameters: { controls: { disable: true } },
  render: () => (
    <div style={rowStyle}>
      <div data-theme="light" style={{ ...wrapperStyle, display: 'grid', gap: '12px' }}>
        <div style={labelStyle}>Default UI</div>
        <div style={gridStyle}>
          {variants.slice(0, 4).map((variant) => (
            <button key={variant.className} className={`tutor-btn ${variant.className} tutor-btn-medium`}>
              {variant.label}
            </button>
          ))}
        </div>
      </div>
      <div data-theme="light" data-tutor-ui="kids" style={{ ...wrapperStyle, display: 'grid', gap: '12px' }}>
        <div style={labelStyle}>Kids UI</div>
        <div style={gridStyle}>
          {variants.slice(0, 4).map((variant) => (
            <button key={variant.className} className={`tutor-btn ${variant.className} tutor-btn-medium`}>
              {variant.label}
            </button>
          ))}
        </div>
      </div>
    </div>
  ),
} satisfies Story;

export const Disabled = {
  parameters: { controls: { disable: true } },
  render: () => (
    <div data-theme="light" style={wrapperStyle}>
      <div style={gridStyle}>
        {variants.map((variant) => (
          <button key={variant.className} className={`tutor-btn ${variant.className} tutor-btn-medium`} disabled>
            {variant.label}
          </button>
        ))}
      </div>
    </div>
  ),
} satisfies Story;

export const IconOnly = {
  parameters: { controls: { disable: true } },
  render: () => (
    <div data-theme="light" style={wrapperStyle}>
      <div style={rowStyle}>
        {sizes.map((size) => (
          <button
            key={size.className}
            className={`tutor-btn tutor-btn-primary tutor-btn-icon ${size.className}`}
            aria-label={`Icon button ${size.label}`}
          >
            {iconSvg}
          </button>
        ))}
      </div>
    </div>
  ),
} satisfies Story;
