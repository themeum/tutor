import { css } from '@emotion/react';
import type { Meta, StoryObj } from '@storybook/react';
import { useEffect, useRef, useState } from 'react';

const meta: Meta = {
  title: 'TutorCore/Alpine/Dropdown',
  parameters: {
    docs: {
      description: {
        component: `
# Dropdown Component

The TutorCore dropdown component provides a flexible and accessible dropdown menu with RTL support, keyboard navigation, and customizable positioning.

## Features

- **RTL Support**: Automatic positioning adaptation for RTL layouts
- **Keyboard Navigation**: Arrow keys, Enter, Escape support
- **Click Outside**: Closes when clicking outside the dropdown
- **Positioning**: Multiple placement options with collision detection
- **Accessibility**: Proper ARIA attributes and focus management

## Configuration

\`\`\`typescript
interface DropdownConfig {
  placement?: 'bottom-start' | 'bottom-end' | 'top-start' | 'top-end';
  offset?: number;
  closeOnClickOutside?: boolean;
}
\`\`\`

## Usage

\`\`\`html
<div x-data="TutorCore.dropdown({ placement: 'bottom-start' })">
  <button @click="toggle()" class="tutor-btn tutor-btn--primary">
    Options <i class="tutor-icon-chevron-down"></i>
  </button>
  <div x-show="open" @click.outside="close()" class="tutor-dropdown__menu">
    <a href="#" class="tutor-dropdown__item">Profile</a>
    <a href="#" class="tutor-dropdown__item">Settings</a>
    <a href="#" class="tutor-dropdown__item">Logout</a>
  </div>
</div>
\`\`\`

## Methods

- \`toggle()\`: Toggle dropdown visibility
- \`open()\`: Show dropdown
- \`close()\`: Hide dropdown
- \`handleKeydown(event)\`: Handle keyboard navigation
        `,
      },
    },
  },
};

export default meta;
type Story = StoryObj;

// Mock Alpine.js dropdown functionality
const useDropdown = (config: { placement?: string; closeOnClickOutside?: boolean } = {}) => {
  const [isOpen, setIsOpen] = useState(false);
  const dropdownRef = useRef<HTMLDivElement>(null);

  const toggle = () => setIsOpen(!isOpen);
  const open = () => setIsOpen(true);
  const close = () => setIsOpen(false);

  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (config.closeOnClickOutside !== false && dropdownRef.current && !dropdownRef.current.contains(event.target as Node)) {
        close();
      }
    };

    const handleKeydown = (event: KeyboardEvent) => {
      if (event.key === 'Escape') {
        close();
      }
    };

    if (isOpen) {
      document.addEventListener('mousedown', handleClickOutside);
      document.addEventListener('keydown', handleKeydown);
    }

    return () => {
      document.removeEventListener('mousedown', handleClickOutside);
      document.removeEventListener('keydown', handleKeydown);
    };
  }, [isOpen, config.closeOnClickOutside]);

  return { isOpen, toggle, open, close, dropdownRef };
};

const dropdownStyles = {
  container: css`
    position: relative;
    display: inline-block;
  `,
  
  trigger: css`
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    background: #4979e8;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    
    &:hover {
      background: #3e64de;
    }
    
    &:focus {
      outline: 2px solid #4979e8;
      outline-offset: 2px;
    }
  `,
  
  menu: css`
    position: absolute;
    top: 100%;
    left: 0;
    z-index: 1000;
    min-width: 200px;
    margin-top: 4px;
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    padding: 8px 0;
    animation: fadeIn 0.15s ease-out;
    
    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(-4px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
  `,
  
  item: css`
    display: block;
    width: 100%;
    padding: 10px 16px;
    color: #333;
    text-decoration: none;
    font-size: 14px;
    border: none;
    background: none;
    text-align: left;
    cursor: pointer;
    transition: background-color 0.15s ease;
    
    &:hover {
      background: #f5f5f6;
    }
    
    &:focus {
      background: #f5f5f6;
      outline: none;
    }
    
    &:active {
      background: #ececed;
    }
  `,
  
  divider: css`
    height: 1px;
    background: #e0e0e0;
    margin: 8px 0;
  `,
};

const ChevronDownIcon = ({ isOpen }: { isOpen: boolean }) => (
  <svg 
    width="16" 
    height="16" 
    viewBox="0 0 16 16" 
    fill="currentColor"
    css={css`
      transition: transform 0.2s ease;
      transform: ${isOpen ? 'rotate(180deg)' : 'rotate(0deg)'};
    `}
  >
    <path d="M4 6l4 4 4-4" stroke="currentColor" strokeWidth="2" fill="none" strokeLinecap="round" strokeLinejoin="round"/>
  </svg>
);

const Dropdown = ({ 
  children, 
  trigger, 
  placement = 'bottom-start',
  closeOnClickOutside = true 
}: {
  children: React.ReactNode;
  trigger: React.ReactNode;
  placement?: string;
  closeOnClickOutside?: boolean;
}) => {
  const { isOpen, toggle, dropdownRef } = useDropdown({ placement, closeOnClickOutside });

  return (
    <div css={dropdownStyles.container} ref={dropdownRef}>
      <button css={dropdownStyles.trigger} onClick={toggle}>
        {trigger}
      </button>
      {isOpen && (
        <div css={dropdownStyles.menu}>
          {children}
        </div>
      )}
    </div>
  );
};

export const BasicDropdown: Story = {
  render: () => (
    <Dropdown 
      trigger={
        <>
          Options <ChevronDownIcon isOpen={false} />
        </>
      }
    >
      <button css={dropdownStyles.item}>Profile</button>
      <button css={dropdownStyles.item}>Settings</button>
      <div css={dropdownStyles.divider} />
      <button css={dropdownStyles.item}>Logout</button>
    </Dropdown>
  ),
  parameters: {
    docs: {
      description: {
        story: 'Basic dropdown with menu items and a divider. Click outside to close.',
      },
    },
  },
};

export const DropdownVariants: Story = {
  render: () => (
    <div css={css`display: flex; gap: 16px; flex-wrap: wrap;`}>
      <Dropdown 
        trigger={
          <>
            Primary Menu <ChevronDownIcon isOpen={false} />
          </>
        }
      >
        <button css={dropdownStyles.item}>Dashboard</button>
        <button css={dropdownStyles.item}>Analytics</button>
        <button css={dropdownStyles.item}>Reports</button>
      </Dropdown>

      <div css={dropdownStyles.container}>
        <button 
          css={[
            dropdownStyles.trigger,
            css`
              background: #f5f5f6;
              color: #333;
              border: 1px solid #cecfd2;
              
              &:hover {
                background: #ececed;
              }
            `
          ]}
        >
          Secondary Menu <ChevronDownIcon isOpen={false} />
        </button>
      </div>

      <div css={dropdownStyles.container}>
        <button 
          css={[
            dropdownStyles.trigger,
            css`
              background: transparent;
              color: #4979e8;
              border: 1px solid #4979e8;
              
              &:hover {
                background: #f6f8fe;
              }
            `
          ]}
        >
          Outline Menu <ChevronDownIcon isOpen={false} />
        </button>
      </div>
    </div>
  ),
  parameters: {
    docs: {
      description: {
        story: 'Dropdown triggers with different button styles: primary, secondary, and outline.',
      },
    },
  },
};

export const DropdownWithIcons: Story = {
  render: () => (
    <Dropdown 
      trigger={
        <>
          User Menu <ChevronDownIcon isOpen={false} />
        </>
      }
    >
      <button css={[dropdownStyles.item, css`display: flex; align-items: center; gap: 12px;`]}>
        <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
          <circle cx="8" cy="8" r="3" stroke="currentColor" strokeWidth="2" fill="none"/>
          <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0z" stroke="currentColor" strokeWidth="2" fill="none"/>
        </svg>
        Profile
      </button>
      <button css={[dropdownStyles.item, css`display: flex; align-items: center; gap: 12px;`]}>
        <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
          <circle cx="8" cy="8" r="6" stroke="currentColor" strokeWidth="2" fill="none"/>
          <path d="M6 8l2 2 4-4" stroke="currentColor" strokeWidth="2" fill="none"/>
        </svg>
        Settings
      </button>
      <div css={dropdownStyles.divider} />
      <button css={[dropdownStyles.item, css`display: flex; align-items: center; gap: 12px; color: #d92d20;`]}>
        <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
          <path d="M6 2h4v2H6V2zM4 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2h2v8a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6h2z" stroke="currentColor" strokeWidth="2" fill="none"/>
        </svg>
        Logout
      </button>
    </Dropdown>
  ),
  parameters: {
    docs: {
      description: {
        story: 'Dropdown menu items with icons. The logout item uses error color for destructive actions.',
      },
    },
  },
};

export const NestedDropdown: Story = {
  render: () => {
    const [submenuOpen, setSubmenuOpen] = useState(false);
    
    return (
      <Dropdown 
        trigger={
          <>
            Main Menu <ChevronDownIcon isOpen={false} />
          </>
        }
      >
        <button css={dropdownStyles.item}>Dashboard</button>
        <div 
          css={css`position: relative;`}
          onMouseEnter={() => setSubmenuOpen(true)}
          onMouseLeave={() => setSubmenuOpen(false)}
        >
          <button css={[dropdownStyles.item, css`display: flex; align-items: center; justify-content: space-between;`]}>
            Products
            <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
              <path d="M6 4l4 4-4 4" stroke="currentColor" strokeWidth="2" fill="none" strokeLinecap="round" strokeLinejoin="round"/>
            </svg>
          </button>
          {submenuOpen && (
            <div css={[
              dropdownStyles.menu,
              css`
                position: absolute;
                top: 0;
                left: 100%;
                margin-top: 0;
                margin-left: 4px;
              `
            ]}>
              <button css={dropdownStyles.item}>All Products</button>
              <button css={dropdownStyles.item}>Categories</button>
              <button css={dropdownStyles.item}>Inventory</button>
            </div>
          )}
        </div>
        <button css={dropdownStyles.item}>Orders</button>
        <button css={dropdownStyles.item}>Customers</button>
      </Dropdown>
    );
  },
  parameters: {
    docs: {
      description: {
        story: 'Nested dropdown with submenu that appears on hover. Useful for complex navigation structures.',
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
        <div css={css`direction: ltr;`}>
          <Dropdown 
            trigger={
              <>
                Options <ChevronDownIcon isOpen={false} />
              </>
            }
          >
            <button css={dropdownStyles.item}>Profile</button>
            <button css={dropdownStyles.item}>Settings</button>
            <button css={dropdownStyles.item}>Logout</button>
          </Dropdown>
        </div>
      </div>
      
      <div>
        <h4 css={css`margin: 0 0 12px 0; font-size: 14px; font-weight: 600;`}>
          RTL (Right-to-Left)
        </h4>
        <div css={css`direction: rtl;`}>
          <div css={[
            dropdownStyles.container,
            css`
              .dropdown-menu {
                left: auto;
                right: 0;
              }
            `
          ]}>
            <Dropdown 
              trigger={
                <>
                  خيارات <ChevronDownIcon isOpen={false} />
                </>
              }
            >
              <button css={dropdownStyles.item}>الملف الشخصي</button>
              <button css={dropdownStyles.item}>الإعدادات</button>
              <button css={dropdownStyles.item}>تسجيل الخروج</button>
            </Dropdown>
          </div>
        </div>
      </div>
    </div>
  ),
  parameters: {
    docs: {
      description: {
        story: 'Dropdown automatically adapts to RTL layouts. Menu positioning and text alignment adjust based on direction.',
      },
    },
  },
};

export const DropdownPlacements: Story = {
  render: () => (
    <div css={css`
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 40px;
      padding: 40px;
      min-height: 300px;
    `}>
      <div css={css`display: flex; justify-content: flex-start; align-items: flex-start;`}>
        <Dropdown 
          trigger={
            <>
              Bottom Start <ChevronDownIcon isOpen={false} />
            </>
          }
        >
          <button css={dropdownStyles.item}>Item 1</button>
          <button css={dropdownStyles.item}>Item 2</button>
          <button css={dropdownStyles.item}>Item 3</button>
        </Dropdown>
      </div>
      
      <div css={css`display: flex; justify-content: flex-end; align-items: flex-start;`}>
        <div css={[
          dropdownStyles.container,
          css`
            .dropdown-menu {
              left: auto;
              right: 0;
            }
          `
        ]}>
          <Dropdown 
            trigger={
              <>
                Bottom End <ChevronDownIcon isOpen={false} />
              </>
            }
          >
            <button css={dropdownStyles.item}>Item 1</button>
            <button css={dropdownStyles.item}>Item 2</button>
            <button css={dropdownStyles.item}>Item 3</button>
          </Dropdown>
        </div>
      </div>
      
      <div css={css`display: flex; justify-content: flex-start; align-items: flex-end;`}>
        <div css={[
          dropdownStyles.container,
          css`
            .dropdown-menu {
              top: auto;
              bottom: 100%;
              margin-top: 0;
              margin-bottom: 4px;
            }
          `
        ]}>
          <Dropdown 
            trigger={
              <>
                Top Start <ChevronDownIcon isOpen={false} />
              </>
            }
          >
            <button css={dropdownStyles.item}>Item 1</button>
            <button css={dropdownStyles.item}>Item 2</button>
            <button css={dropdownStyles.item}>Item 3</button>
          </Dropdown>
        </div>
      </div>
      
      <div css={css`display: flex; justify-content: flex-end; align-items: flex-end;`}>
        <div css={[
          dropdownStyles.container,
          css`
            .dropdown-menu {
              top: auto;
              bottom: 100%;
              left: auto;
              right: 0;
              margin-top: 0;
              margin-bottom: 4px;
            }
          `
        ]}>
          <Dropdown 
            trigger={
              <>
                Top End <ChevronDownIcon isOpen={false} />
              </>
            }
          >
            <button css={dropdownStyles.item}>Item 1</button>
            <button css={dropdownStyles.item}>Item 2</button>
            <button css={dropdownStyles.item}>Item 3</button>
          </Dropdown>
        </div>
      </div>
    </div>
  ),
  parameters: {
    docs: {
      description: {
        story: 'Different dropdown placements: bottom-start, bottom-end, top-start, and top-end. Useful for different layout contexts.',
      },
    },
  },
};

export const CodeExample: Story = {
  render: () => (
    <div css={css`
      padding: 24px;
      background: #f8f9fa;
      border-radius: 8px;
      border: 1px solid #e0e0e0;
    `}>
      <h3 css={css`margin: 0 0 16px 0; font-size: 16px; font-weight: 600;`}>
        HTML Implementation
      </h3>
      <pre css={css`
        background: #fff;
        padding: 16px;
        border-radius: 6px;
        border: 1px solid #e0e0e0;
        font-size: 12px;
        line-height: 1.5;
        overflow-x: auto;
        margin: 0;
        color: #333;
      `}>
{`<!-- Basic Dropdown -->
<div x-data="TutorCore.dropdown({ placement: 'bottom-start' })">
  <button @click="toggle()" class="tutor-btn tutor-btn--primary">
    Options <i class="tutor-icon-chevron-down"></i>
  </button>
  <div x-show="open" @click.outside="close()" class="tutor-dropdown__menu">
    <a href="#" class="tutor-dropdown__item">Profile</a>
    <a href="#" class="tutor-dropdown__item">Settings</a>
    <div class="tutor-dropdown__divider"></div>
    <a href="#" class="tutor-dropdown__item">Logout</a>
  </div>
</div>

<!-- With Configuration -->
<div x-data="TutorCore.dropdown({ 
  placement: 'top-end', 
  offset: 8,
  closeOnClickOutside: true 
})">
  <!-- Dropdown content -->
</div>`}
      </pre>
    </div>
  ),
  parameters: {
    docs: {
      description: {
        story: 'Complete HTML implementation example showing how to use the TutorCore dropdown component with Alpine.js.',
      },
    },
  },
};