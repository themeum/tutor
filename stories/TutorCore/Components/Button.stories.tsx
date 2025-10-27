import { css } from '@emotion/react';
import type { Meta, StoryObj } from '@storybook/react';

const meta: Meta = {
  title: 'TutorCore/Components/Button',
  parameters: {
    docs: {
      description: {
        component: `
# Button Component

The TutorCore button component provides a comprehensive set of button styles with multiple variants, sizes, and states. All buttons are fully accessible and support RTL layouts.

## Features

- **Multiple Variants**: Primary, Secondary, Outline, Ghost
- **Three Sizes**: Small, Medium, Large
- **Icon Support**: Leading, trailing, or icon-only buttons
- **Loading States**: Built-in loading spinner
- **RTL Support**: Automatic icon positioning for RTL layouts
- **Accessibility**: Proper ARIA attributes and keyboard navigation

## CSS Classes

\`\`\`css
/* Base button class */
.tutor-btn

/* Button variants */
.tutor-btn--primary
.tutor-btn--secondary  
.tutor-btn--outline
.tutor-btn--ghost

/* Button sizes */
.tutor-btn--small
.tutor-btn--medium
.tutor-btn--large

/* Button states */
.tutor-btn--loading
.tutor-btn--block
.tutor-btn--icon
.tutor-btn--fab
\`\`\`
        `,
      },
    },
  },
};

export default meta;
type Story = StoryObj;

// Mock TutorCore styles for demonstration
const buttonStyles = {
  base: css`
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 10px 16px;
    border: 1px solid transparent;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    line-height: 1.4;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s ease;
    min-height: 40px;
    
    &:disabled {
      opacity: 0.6;
      cursor: not-allowed;
    }
    
    &:focus {
      outline: 2px solid #4979e8;
      outline-offset: 2px;
    }
  `,
  
  primary: css`
    background: #4979e8;
    color: white;
    border-color: #4979e8;
    
    &:hover:not(:disabled) {
      background: #3e64de;
      border-color: #3e64de;
    }
    
    &:active:not(:disabled) {
      background: #2b49ca;
      border-color: #2b49ca;
    }
  `,
  
  secondary: css`
    background: #f5f5f6;
    color: #333741;
    border-color: #cecfd2;
    
    &:hover:not(:disabled) {
      background: #ececed;
      border-color: #94969c;
    }
    
    &:active:not(:disabled) {
      background: #e0e0e0;
    }
  `,
  
  outline: css`
    background: transparent;
    color: #4979e8;
    border-color: #4979e8;
    
    &:hover:not(:disabled) {
      background: #f6f8fe;
      color: #3e64de;
      border-color: #3e64de;
    }
    
    &:active:not(:disabled) {
      background: #e4ebfc;
    }
  `,
  
  ghost: css`
    background: transparent;
    color: #4979e8;
    border-color: transparent;
    
    &:hover:not(:disabled) {
      background: #f6f8fe;
      color: #3e64de;
    }
    
    &:active:not(:disabled) {
      background: #e4ebfc;
    }
  `,
  
  small: css`
    padding: 6px 12px;
    font-size: 12px;
    min-height: 32px;
  `,
  
  large: css`
    padding: 14px 24px;
    font-size: 16px;
    min-height: 48px;
  `,
  
  block: css`
    width: 100%;
  `,
  
  loading: css`
    position: relative;
    color: transparent !important;
    
    &::after {
      content: '';
      position: absolute;
      width: 16px;
      height: 16px;
      border: 2px solid currentColor;
      border-radius: 50%;
      border-top-color: transparent;
      animation: spin 1s linear infinite;
      color: inherit;
    }
    
    @keyframes spin {
      to {
        transform: rotate(360deg);
      }
    }
  `,
};

const Button = ({ 
  variant = 'primary', 
  size = 'medium', 
  children, 
  loading = false,
  block = false,
  disabled = false,
  icon,
  iconPosition = 'leading',
  ...props 
}: {
  variant?: 'primary' | 'secondary' | 'outline' | 'ghost';
  size?: 'small' | 'medium' | 'large';
  children: React.ReactNode;
  loading?: boolean;
  block?: boolean;
  disabled?: boolean;
  icon?: React.ReactNode;
  iconPosition?: 'leading' | 'trailing';
  onClick?: () => void;
}) => (
  <button
    css={[
      buttonStyles.base,
      buttonStyles[variant],
      size !== 'medium' && buttonStyles[size],
      block && buttonStyles.block,
      loading && buttonStyles.loading,
    ]}
    disabled={disabled || loading}
    {...props}
  >
    {icon && iconPosition === 'leading' && icon}
    {children}
    {icon && iconPosition === 'trailing' && icon}
  </button>
);

const IconChevronDown = () => (
  <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
    <path d="M4 6l4 4 4-4" stroke="currentColor" strokeWidth="2" fill="none" strokeLinecap="round" strokeLinejoin="round"/>
  </svg>
);

const IconPlus = () => (
  <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
    <path d="M8 2v12M2 8h12" stroke="currentColor" strokeWidth="2" strokeLinecap="round"/>
  </svg>
);

export const Variants: Story = {
  render: () => (
    <div css={css`display: flex; gap: 12px; flex-wrap: wrap; align-items: center;`}>
      <Button variant="primary">Primary Button</Button>
      <Button variant="secondary">Secondary Button</Button>
      <Button variant="outline">Outline Button</Button>
      <Button variant="ghost">Ghost Button</Button>
    </div>
  ),
  parameters: {
    docs: {
      description: {
        story: 'Four button variants: Primary for main actions, Secondary for supporting actions, Outline for alternative actions, and Ghost for subtle actions.',
      },
    },
  },
};

export const Sizes: Story = {
  render: () => (
    <div css={css`display: flex; gap: 12px; flex-wrap: wrap; align-items: center;`}>
      <Button size="small">Small Button</Button>
      <Button size="medium">Medium Button</Button>
      <Button size="large">Large Button</Button>
    </div>
  ),
  parameters: {
    docs: {
      description: {
        story: 'Three button sizes: Small (32px), Medium (40px), and Large (48px) heights.',
      },
    },
  },
};

export const WithIcons: Story = {
  render: () => (
    <div css={css`display: flex; gap: 12px; flex-wrap: wrap; align-items: center;`}>
      <Button icon={<IconPlus />} iconPosition="leading">
        Add Item
      </Button>
      <Button variant="outline" icon={<IconChevronDown />} iconPosition="trailing">
        Options
      </Button>
      <Button variant="secondary" icon={<IconPlus />} iconPosition="leading">
        Create New
      </Button>
    </div>
  ),
  parameters: {
    docs: {
      description: {
        story: 'Buttons with icons in leading or trailing positions. Icons automatically adapt to RTL layouts.',
      },
    },
  },
};

export const States: Story = {
  render: () => (
    <div css={css`display: flex; gap: 12px; flex-wrap: wrap; align-items: center;`}>
      <Button>Normal State</Button>
      <Button disabled>Disabled State</Button>
      <Button loading>Loading State</Button>
    </div>
  ),
  parameters: {
    docs: {
      description: {
        story: 'Button states: Normal, Disabled (reduced opacity), and Loading (with spinner).',
      },
    },
  },
};

export const BlockButtons: Story = {
  render: () => (
    <div css={css`display: flex; flex-direction: column; gap: 12px; max-width: 400px;`}>
      <Button block>Full Width Primary</Button>
      <Button variant="secondary" block>Full Width Secondary</Button>
      <Button variant="outline" block>Full Width Outline</Button>
    </div>
  ),
  parameters: {
    docs: {
      description: {
        story: 'Block buttons that take the full width of their container.',
      },
    },
  },
};

export const ButtonGroups: Story = {
  render: () => (
    <div css={css`display: flex; flex-direction: column; gap: 24px;`}>
      <div>
        <h4 css={css`margin: 0 0 12px 0; font-size: 14px; font-weight: 600;`}>
          Horizontal Button Group
        </h4>
        <div css={css`
          display: flex;
          
          button {
            border-radius: 0;
            margin-left: -1px;
            
            &:first-of-type {
              border-top-left-radius: 6px;
              border-bottom-left-radius: 6px;
              margin-left: 0;
            }
            
            &:last-of-type {
              border-top-right-radius: 6px;
              border-bottom-right-radius: 6px;
            }
            
            &:focus {
              z-index: 1;
              position: relative;
            }
          }
        `}>
          <Button variant="outline">Left</Button>
          <Button variant="outline">Center</Button>
          <Button variant="outline">Right</Button>
        </div>
      </div>
      
      <div>
        <h4 css={css`margin: 0 0 12px 0; font-size: 14px; font-weight: 600;`}>
          Vertical Button Group
        </h4>
        <div css={css`
          display: flex;
          flex-direction: column;
          width: fit-content;
          
          button {
            border-radius: 0;
            margin-top: -1px;
            
            &:first-of-type {
              border-top-left-radius: 6px;
              border-top-right-radius: 6px;
              margin-top: 0;
            }
            
            &:last-of-type {
              border-bottom-left-radius: 6px;
              border-bottom-right-radius: 6px;
            }
            
            &:focus {
              z-index: 1;
              position: relative;
            }
          }
        `}>
          <Button variant="outline">Top</Button>
          <Button variant="outline">Middle</Button>
          <Button variant="outline">Bottom</Button>
        </div>
      </div>
    </div>
  ),
  parameters: {
    docs: {
      description: {
        story: 'Button groups for related actions, available in horizontal and vertical layouts.',
      },
    },
  },
};

export const IconOnlyButtons: Story = {
  render: () => (
    <div css={css`display: flex; gap: 12px; flex-wrap: wrap; align-items: center;`}>
      <button
        css={[
          buttonStyles.base,
          buttonStyles.primary,
          css`
            padding: 10px;
            min-width: 40px;
            min-height: 40px;
          `
        ]}
        aria-label="Add item"
      >
        <IconPlus />
      </button>
      
      <button
        css={[
          buttonStyles.base,
          buttonStyles.secondary,
          css`
            padding: 8px;
            min-width: 32px;
            min-height: 32px;
          `
        ]}
        aria-label="Options"
      >
        <IconChevronDown />
      </button>
      
      <button
        css={[
          buttonStyles.base,
          buttonStyles.outline,
          css`
            padding: 12px;
            min-width: 48px;
            min-height: 48px;
          `
        ]}
        aria-label="Create new"
      >
        <IconPlus />
      </button>
    </div>
  ),
  parameters: {
    docs: {
      description: {
        story: 'Icon-only buttons for compact interfaces. Always include aria-label for accessibility.',
      },
    },
  },
};

export const FloatingActionButtons: Story = {
  render: () => (
    <div css={css`display: flex; gap: 16px; flex-wrap: wrap; align-items: center;`}>
      <button
        css={[
          buttonStyles.base,
          buttonStyles.primary,
          css`
            width: 56px;
            height: 56px;
            border-radius: 50%;
            padding: 0;
            box-shadow: 0 4px 12px rgba(73, 121, 232, 0.3);
            
            &:hover:not(:disabled) {
              box-shadow: 0 6px 16px rgba(73, 121, 232, 0.4);
              transform: translateY(-1px);
            }
          `
        ]}
        aria-label="Add new item"
      >
        <IconPlus />
      </button>
      
      <button
        css={[
          buttonStyles.base,
          buttonStyles.primary,
          css`
            width: 40px;
            height: 40px;
            border-radius: 50%;
            padding: 0;
            box-shadow: 0 2px 8px rgba(73, 121, 232, 0.3);
            
            &:hover:not(:disabled) {
              box-shadow: 0 4px 12px rgba(73, 121, 232, 0.4);
              transform: translateY(-1px);
            }
          `
        ]}
        aria-label="Small FAB"
      >
        <IconPlus />
      </button>
      
      <button
        css={[
          buttonStyles.base,
          buttonStyles.primary,
          css`
            width: 72px;
            height: 72px;
            border-radius: 50%;
            padding: 0;
            box-shadow: 0 6px 16px rgba(73, 121, 232, 0.3);
            
            &:hover:not(:disabled) {
              box-shadow: 0 8px 20px rgba(73, 121, 232, 0.4);
              transform: translateY(-2px);
            }
          `
        ]}
        aria-label="Large FAB"
      >
        <IconPlus />
      </button>
    </div>
  ),
  parameters: {
    docs: {
      description: {
        story: 'Floating Action Buttons (FABs) in different sizes with elevation and hover effects.',
      },
    },
  },
};

export const RTLSupport: Story = {
  render: () => (
    <div css={css`display: flex; flex-direction: column; gap: 24px;`}>
      <div>
        <h4 css={css`margin: 0 0 12px 0; font-size: 14px; font-weight: 600;`}>
          LTR (Left-to-Right)
        </h4>
        <div css={css`display: flex; gap: 12px; direction: ltr;`}>
          <Button icon={<IconPlus />} iconPosition="leading">
            Add Item
          </Button>
          <Button variant="outline" icon={<IconChevronDown />} iconPosition="trailing">
            Options
          </Button>
        </div>
      </div>
      
      <div>
        <h4 css={css`margin: 0 0 12px 0; font-size: 14px; font-weight: 600;`}>
          RTL (Right-to-Left)
        </h4>
        <div css={css`display: flex; gap: 12px; direction: rtl;`}>
          <Button icon={<IconPlus />} iconPosition="leading">
            إضافة عنصر
          </Button>
          <Button variant="outline" icon={<IconChevronDown />} iconPosition="trailing">
            خيارات
          </Button>
        </div>
      </div>
    </div>
  ),
  parameters: {
    docs: {
      description: {
        story: 'Buttons automatically adapt to RTL layouts. Icon positions and spacing adjust based on text direction.',
      },
    },
  },
};

export const AllExamples: Story = {
  render: () => (
    <div css={css`display: flex; flex-direction: column; gap: 32px;`}>
      <section>
        <h3 css={css`margin: 0 0 16px 0; font-size: 18px; font-weight: 600;`}>
          Button Variants
        </h3>
        <div css={css`display: flex; gap: 12px; flex-wrap: wrap;`}>
          <Button variant="primary">Primary</Button>
          <Button variant="secondary">Secondary</Button>
          <Button variant="outline">Outline</Button>
          <Button variant="ghost">Ghost</Button>
        </div>
      </section>
      
      <section>
        <h3 css={css`margin: 0 0 16px 0; font-size: 18px; font-weight: 600;`}>
          Button Sizes
        </h3>
        <div css={css`display: flex; gap: 12px; flex-wrap: wrap; align-items: center;`}>
          <Button size="small">Small</Button>
          <Button size="medium">Medium</Button>
          <Button size="large">Large</Button>
        </div>
      </section>
      
      <section>
        <h3 css={css`margin: 0 0 16px 0; font-size: 18px; font-weight: 600;`}>
          Buttons with Icons
        </h3>
        <div css={css`display: flex; gap: 12px; flex-wrap: wrap;`}>
          <Button icon={<IconPlus />}>Add New</Button>
          <Button variant="outline" icon={<IconChevronDown />} iconPosition="trailing">
            Dropdown
          </Button>
        </div>
      </section>
      
      <section>
        <h3 css={css`margin: 0 0 16px 0; font-size: 18px; font-weight: 600;`}>
          Button States
        </h3>
        <div css={css`display: flex; gap: 12px; flex-wrap: wrap;`}>
          <Button>Normal</Button>
          <Button disabled>Disabled</Button>
          <Button loading>Loading</Button>
        </div>
      </section>
    </div>
  ),
  parameters: {
    docs: {
      description: {
        story: 'Comprehensive showcase of all button variants, sizes, and states available in TutorCore.',
      },
    },
  },
};