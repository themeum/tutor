import { css } from '@emotion/react';
import type { Meta, StoryObj } from '@storybook/react';
import { useEffect, useRef, useState } from 'react';

const meta: Meta = {
  title: 'TutorCore/Alpine/Modal',
  parameters: {
    docs: {
      description: {
        component: `
# Alpine.js Modal Component

The TutorCore Alpine.js modal component provides accessible dialog overlays with automatic focus management, keyboard navigation, and backdrop handling. Built with TypeScript support and RTL compatibility.

## Features

- **Focus Management**: Automatic focus trapping and restoration
- **Keyboard Navigation**: ESC to close, Tab navigation within modal
- **Backdrop Control**: Click outside to close (configurable)
- **Accessibility**: Proper ARIA attributes and screen reader support
- **TypeScript**: Full type definitions and configuration options
- **RTL Support**: Works seamlessly with RTL layouts

## Configuration

\`\`\`typescript
interface ModalConfig {
  closable?: boolean;        // Allow closing with ESC/backdrop (default: true)
  backdrop?: boolean;        // Show backdrop overlay (default: true)
  keyboard?: boolean;        // Enable ESC key to close (default: true)
  size?: 'small' | 'medium' | 'large' | 'fullscreen';
  animation?: 'fade' | 'slide' | 'zoom';
}
\`\`\`

## Usage

\`\`\`html
<div x-data="TutorCore.modal({ closable: true, backdrop: true })">
  <button @click="show()" class="tutor-btn tutor-btn--primary">
    Open Modal
  </button>
  
  <div x-show="open" class="tutor-modal" x-transition>
    <div class="tutor-modal__backdrop" @click="hide()"></div>
    <div class="tutor-modal__content" @keydown.escape="hide()">
      <div class="tutor-modal__header">
        <h3>Modal Title</h3>
        <button @click="hide()" class="tutor-modal__close">&times;</button>
      </div>
      <div class="tutor-modal__body">
        <p>Modal content goes here...</p>
      </div>
      <div class="tutor-modal__footer">
        <button @click="hide()" class="tutor-btn tutor-btn--secondary">Cancel</button>
        <button class="tutor-btn tutor-btn--primary">Confirm</button>
      </div>
    </div>
  </div>
</div>
\`\`\`

## Methods

- \`show()\`: Show the modal
- \`hide()\`: Hide the modal
- \`handleKeydown(event)\`: Handle ESC key press
- \`handleBackdropClick()\`: Handle backdrop clicks
- \`trapFocus()\`: Manage focus within modal
- \`releaseFocus()\`: Restore focus when closing
        `,
      },
    },
  },
};

export default meta;
type Story = StoryObj;

// Mock Alpine.js modal functionality
const useModal = (config: { closable?: boolean; backdrop?: boolean; keyboard?: boolean } = {}) => {
  const [isOpen, setIsOpen] = useState(false);
  const [previousFocus, setPreviousFocus] = useState<HTMLElement | null>(null);
  const modalRef = useRef<HTMLDivElement>(null);

  const show = () => {
    setPreviousFocus(document.activeElement as HTMLElement);
    setIsOpen(true);
  };

  const hide = () => {
    if (config.closable !== false) {
      setIsOpen(false);
      // Restore focus after a brief delay to allow for transitions
      setTimeout(() => {
        if (previousFocus) {
          previousFocus.focus();
        }
      }, 100);
    }
  };

  const handleBackdropClick = () => {
    if (config.backdrop !== false && config.closable !== false) {
      hide();
    }
  };

  useEffect(() => {
    const handleKeydown = (event: KeyboardEvent) => {
      if (event.key === 'Escape' && config.keyboard !== false && config.closable !== false) {
        hide();
      }
      
      // Focus trapping
      if (isOpen && event.key === 'Tab' && modalRef.current) {
        const focusableElements = modalRef.current.querySelectorAll(
          'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
        );
        const firstElement = focusableElements[0] as HTMLElement;
        const lastElement = focusableElements[focusableElements.length - 1] as HTMLElement;

        if (event.shiftKey) {
          if (document.activeElement === firstElement) {
            event.preventDefault();
            lastElement.focus();
          }
        } else {
          if (document.activeElement === lastElement) {
            event.preventDefault();
            firstElement.focus();
          }
        }
      }
    };

    if (isOpen) {
      document.addEventListener('keydown', handleKeydown);
      // Focus first focusable element
      setTimeout(() => {
        if (modalRef.current) {
          const firstFocusable = modalRef.current.querySelector(
            'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
          ) as HTMLElement;
          if (firstFocusable) {
            firstFocusable.focus();
          }
        }
      }, 100);
    }

    return () => {
      document.removeEventListener('keydown', handleKeydown);
    };
  }, [isOpen, config]);

  return { isOpen, show, hide, handleBackdropClick, modalRef };
};

const modalStyles = {
  backdrop: css`
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
    animation: backdropFadeIn 0.2s ease-out;
    
    @keyframes backdropFadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }
  `,
  
  content: css`
    background: white;
    border-radius: 8px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    max-width: 500px;
    width: 100%;
    max-height: 90vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    animation: modalEnter 0.2s ease-out;
    
    @keyframes modalEnter {
      from {
        opacity: 0;
        transform: scale(0.95) translateY(-10px);
      }
      to {
        opacity: 1;
        transform: scale(1) translateY(0);
      }
    }
  `,
  
  header: css`
    padding: 20px 20px 0 20px;
    border-bottom: 1px solid #e0e0e0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-shrink: 0;
  `,
  
  title: css`
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: #333741;
  `,
  
  closeButton: css`
    background: none;
    border: none;
    font-size: 24px;
    color: #61646c;
    cursor: pointer;
    padding: 4px;
    border-radius: 4px;
    transition: all 0.2s ease;
    
    &:hover {
      background: #f5f5f6;
      color: #333741;
    }
    
    &:focus {
      outline: 2px solid #4979e8;
      outline-offset: 2px;
    }
  `,
  
  body: css`
    padding: 20px;
    flex: 1;
    overflow-y: auto;
  `,
  
  footer: css`
    padding: 0 20px 20px 20px;
    border-top: 1px solid #e0e0e0;
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    flex-shrink: 0;
  `,
  
  button: css`
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    border: none;
    
    &:focus {
      outline: 2px solid #4979e8;
      outline-offset: 2px;
    }
  `,
  
  primaryButton: css`
    background: #4979e8;
    color: white;
    
    &:hover {
      background: #3e64de;
    }
  `,
  
  secondaryButton: css`
    background: #f5f5f6;
    color: #333741;
    border: 1px solid #cecfd2;
    
    &:hover {
      background: #ececed;
    }
  `,
};

const AlpineModal = ({ 
  trigger, 
  title, 
  children, 
  config = {},
  size = 'medium' 
}: {
  trigger: React.ReactNode;
  title?: string;
  children: React.ReactNode;
  config?: { closable?: boolean; backdrop?: boolean; keyboard?: boolean };
  size?: 'small' | 'medium' | 'large';
}) => {
  const { isOpen, show, hide, handleBackdropClick, modalRef } = useModal(config);

  const sizeStyles = {
    small: css`max-width: 400px;`,
    medium: css`max-width: 500px;`,
    large: css`max-width: 700px;`,
  };

  return (
    <div>
      <div onClick={show}>
        {trigger}
      </div>

      {isOpen && (
        <div css={modalStyles.backdrop} onClick={handleBackdropClick}>
          <div 
            css={[modalStyles.content, sizeStyles[size]]} 
            onClick={(e) => e.stopPropagation()}
            ref={modalRef}
            role="dialog"
            aria-modal="true"
            aria-labelledby={title ? "modal-title" : undefined}
          >
            {title && (
              <div css={modalStyles.header}>
                <h2 css={modalStyles.title} id="modal-title">
                  {title}
                </h2>
                <button css={modalStyles.closeButton} onClick={hide} aria-label="Close modal">
                  ×
                </button>
              </div>
            )}
            {children}
          </div>
        </div>
      )}
    </div>
  );
};

export const BasicAlpineModal: Story = {
  render: () => (
    <AlpineModal 
      trigger={
        <button css={[modalStyles.button, modalStyles.primaryButton]}>
          Open Alpine Modal
        </button>
      }
      title="Alpine.js Modal"
      config={{ closable: true, backdrop: true, keyboard: true }}
    >
      <div css={modalStyles.body}>
        <p css={css`margin: 0 0 16px 0; line-height: 1.5; color: #333;`}>
          This modal is powered by Alpine.js with TutorCore integration. It includes:
        </p>
        <ul css={css`margin: 0 0 16px 0; padding-left: 20px; color: #333;`}>
          <li>Automatic focus management</li>
          <li>Keyboard navigation (Tab, Shift+Tab, ESC)</li>
          <li>Click outside to close</li>
          <li>Proper ARIA attributes</li>
          <li>TypeScript configuration</li>
        </ul>
        <p css={css`margin: 0; line-height: 1.5; color: #666; font-size: 14px;`}>
          Try pressing Tab to navigate between focusable elements, or ESC to close.
        </p>
      </div>
      <div css={modalStyles.footer}>
        <button css={[modalStyles.button, modalStyles.secondaryButton]}>
          Cancel
        </button>
        <button css={[modalStyles.button, modalStyles.primaryButton]}>
          Confirm
        </button>
      </div>
    </AlpineModal>
  ),
  parameters: {
    docs: {
      description: {
        story: 'Basic Alpine.js modal with focus management, keyboard navigation, and accessibility features.',
      },
    },
  },
};

export const ModalConfigurations: Story = {
  render: () => (
    <div css={css`display: flex; gap: 12px; flex-wrap: wrap;`}>
      <AlpineModal 
        trigger={
          <button css={[modalStyles.button, modalStyles.primaryButton]}>
            Closable Modal
          </button>
        }
        title="Closable Modal"
        config={{ closable: true, backdrop: true, keyboard: true }}
      >
        <div css={modalStyles.body}>
          <p css={css`margin: 0; line-height: 1.5; color: #333;`}>
            This modal can be closed by clicking outside, pressing ESC, or using the close button.
          </p>
        </div>
      </AlpineModal>

      <AlpineModal 
        trigger={
          <button css={[modalStyles.button, modalStyles.primaryButton]}>
            Non-Closable Modal
          </button>
        }
        title="Non-Closable Modal"
        config={{ closable: false, backdrop: true, keyboard: false }}
      >
        <div css={modalStyles.body}>
          <p css={css`margin: 0 0 16px 0; line-height: 1.5; color: #333;`}>
            This modal cannot be closed by clicking outside or pressing ESC. 
            You must use the action buttons.
          </p>
        </div>
        <div css={modalStyles.footer}>
          <button css={[modalStyles.button, modalStyles.secondaryButton]}>
            Cancel
          </button>
          <button css={[modalStyles.button, modalStyles.primaryButton]}>
            Confirm
          </button>
        </div>
      </AlpineModal>

      <AlpineModal 
        trigger={
          <button css={[modalStyles.button, modalStyles.primaryButton]}>
            No Backdrop Modal
          </button>
        }
        title="No Backdrop Modal"
        config={{ closable: true, backdrop: false, keyboard: true }}
      >
        <div css={modalStyles.body}>
          <p css={css`margin: 0; line-height: 1.5; color: #333;`}>
            This modal has no backdrop overlay. You can still close it with ESC or the close button.
          </p>
        </div>
      </AlpineModal>
    </div>
  ),
  parameters: {
    docs: {
      description: {
        story: 'Different modal configurations showing closable, non-closable, and no-backdrop variants.',
      },
    },
  },
};

export const ModalSizes: Story = {
  render: () => (
    <div css={css`display: flex; gap: 12px; flex-wrap: wrap;`}>
      <AlpineModal 
        trigger={
          <button css={[modalStyles.button, modalStyles.primaryButton]}>
            Small Modal
          </button>
        }
        title="Small Modal"
        size="small"
      >
        <div css={modalStyles.body}>
          <p css={css`margin: 0; line-height: 1.5; color: #333;`}>
            Small modal (400px max-width) for simple confirmations and brief messages.
          </p>
        </div>
      </AlpineModal>

      <AlpineModal 
        trigger={
          <button css={[modalStyles.button, modalStyles.primaryButton]}>
            Medium Modal
          </button>
        }
        title="Medium Modal"
        size="medium"
      >
        <div css={modalStyles.body}>
          <p css={css`margin: 0; line-height: 1.5; color: #333;`}>
            Medium modal (500px max-width) - the default size, good for forms and moderate content.
          </p>
        </div>
      </AlpineModal>

      <AlpineModal 
        trigger={
          <button css={[modalStyles.button, modalStyles.primaryButton]}>
            Large Modal
          </button>
        }
        title="Large Modal"
        size="large"
      >
        <div css={modalStyles.body}>
          <p css={css`margin: 0; line-height: 1.5; color: #333;`}>
            Large modal (700px max-width) suitable for complex forms, detailed content, 
            or when you need more horizontal space.
          </p>
        </div>
      </AlpineModal>
    </div>
  ),
  parameters: {
    docs: {
      description: {
        story: 'Alpine.js modals in different sizes: small (400px), medium (500px), and large (700px).',
      },
    },
  },
};

export const InteractiveFormModal: Story = {
  render: () => {
    const [formData, setFormData] = useState({ name: '', email: '', role: '' });

    return (
      <AlpineModal 
        trigger={
          <button css={[modalStyles.button, modalStyles.primaryButton]}>
            Open Form Modal
          </button>
        }
        title="User Registration"
        size="medium"
      >
        <div css={modalStyles.body}>
          <form css={css`display: flex; flex-direction: column; gap: 16px;`}>
            <div>
              <label css={css`
                display: block;
                margin-bottom: 6px;
                font-size: 14px;
                font-weight: 500;
                color: #333;
              `}>
                Full Name
              </label>
              <input 
                type="text"
                value={formData.name}
                onChange={(e) => setFormData(prev => ({ ...prev, name: e.target.value }))}
                css={css`
                  width: 100%;
                  padding: 10px 12px;
                  border: 1px solid #cecfd2;
                  border-radius: 6px;
                  font-size: 14px;
                  
                  &:focus {
                    outline: none;
                    border-color: #4979e8;
                    box-shadow: 0 0 0 3px rgba(73, 121, 232, 0.1);
                  }
                `}
                placeholder="Enter your full name"
              />
            </div>
            
            <div>
              <label css={css`
                display: block;
                margin-bottom: 6px;
                font-size: 14px;
                font-weight: 500;
                color: #333;
              `}>
                Email Address
              </label>
              <input 
                type="email"
                value={formData.email}
                onChange={(e) => setFormData(prev => ({ ...prev, email: e.target.value }))}
                css={css`
                  width: 100%;
                  padding: 10px 12px;
                  border: 1px solid #cecfd2;
                  border-radius: 6px;
                  font-size: 14px;
                  
                  &:focus {
                    outline: none;
                    border-color: #4979e8;
                    box-shadow: 0 0 0 3px rgba(73, 121, 232, 0.1);
                  }
                `}
                placeholder="Enter your email"
              />
            </div>
            
            <div>
              <label css={css`
                display: block;
                margin-bottom: 6px;
                font-size: 14px;
                font-weight: 500;
                color: #333;
              `}>
                Role
              </label>
              <select 
                value={formData.role}
                onChange={(e) => setFormData(prev => ({ ...prev, role: e.target.value }))}
                css={css`
                  width: 100%;
                  padding: 10px 12px;
                  border: 1px solid #cecfd2;
                  border-radius: 6px;
                  font-size: 14px;
                  background: white;
                  
                  &:focus {
                    outline: none;
                    border-color: #4979e8;
                    box-shadow: 0 0 0 3px rgba(73, 121, 232, 0.1);
                  }
                `}
              >
                <option value="">Select a role</option>
                <option value="admin">Administrator</option>
                <option value="editor">Editor</option>
                <option value="viewer">Viewer</option>
              </select>
            </div>
          </form>
          
          {(formData.name || formData.email || formData.role) && (
            <div css={css`
              margin-top: 16px;
              padding: 12px;
              background: #f0f8ff;
              border-radius: 6px;
              border: 1px solid #4979e8;
            `}>
              <h4 css={css`margin: 0 0 8px 0; font-size: 14px; font-weight: 600;`}>
                Form Data (Alpine.js State)
              </h4>
              <pre css={css`
                margin: 0;
                font-size: 12px;
                color: #333;
                font-family: monospace;
              `}>
                {JSON.stringify(formData, null, 2)}
              </pre>
            </div>
          )}
        </div>
        <div css={modalStyles.footer}>
          <button 
            css={[modalStyles.button, modalStyles.secondaryButton]}
            onClick={() => setFormData({ name: '', email: '', role: '' })}
          >
            Reset
          </button>
          <button css={[modalStyles.button, modalStyles.primaryButton]}>
            Register
          </button>
        </div>
      </AlpineModal>
    );
  },
  parameters: {
    docs: {
      description: {
        story: 'Interactive form modal demonstrating Alpine.js state management and form handling within a modal.',
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
        Alpine.js Modal Implementation
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
{`<!-- Basic Modal -->
<div x-data="TutorCore.modal({ closable: true, backdrop: true })">
  <button @click="show()" class="tutor-btn tutor-btn--primary">
    Open Modal
  </button>
  
  <div x-show="open" class="tutor-modal" x-transition>
    <div class="tutor-modal__backdrop" @click="hide()"></div>
    <div class="tutor-modal__content" @keydown.escape="hide()">
      <div class="tutor-modal__header">
        <h3>Modal Title</h3>
        <button @click="hide()" class="tutor-modal__close">&times;</button>
      </div>
      <div class="tutor-modal__body">
        <p>Modal content goes here...</p>
      </div>
      <div class="tutor-modal__footer">
        <button @click="hide()" class="tutor-btn tutor-btn--secondary">
          Cancel
        </button>
        <button class="tutor-btn tutor-btn--primary">Confirm</button>
      </div>
    </div>
  </div>
</div>

<!-- Configuration Options -->
<div x-data="TutorCore.modal({ 
  closable: false,     // Disable ESC/backdrop closing
  backdrop: true,      // Show backdrop overlay
  keyboard: false,     // Disable ESC key
  size: 'large'        // Modal size
})">
  <!-- Modal content -->
</div>

<!-- TypeScript Usage -->
<script>
document.addEventListener('alpine:init', () => {
  Alpine.data('customModal', () => TutorCore.modal({
    closable: true,
    backdrop: true,
    keyboard: true
  }));
});
</script>`}
      </pre>
    </div>
  ),
  parameters: {
    docs: {
      description: {
        story: 'Complete implementation example showing how to use TutorCore modal with Alpine.js.',
      },
    },
  },
};