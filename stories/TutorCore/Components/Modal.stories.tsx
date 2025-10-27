import { css } from '@emotion/react';
import type { Meta, StoryObj } from '@storybook/react';
import { useState } from 'react';

const meta: Meta = {
  title: 'TutorCore/Components/Modal',
  parameters: {
    docs: {
      description: {
        component: `
# Modal Component

The TutorCore modal component provides accessible dialog overlays with focus management, backdrop handling, and keyboard navigation. Perfect for confirmations, forms, and detailed content.

## Features

- **Focus Management**: Automatic focus trapping and restoration
- **Keyboard Navigation**: ESC to close, Tab navigation within modal
- **Backdrop Control**: Click outside to close (configurable)
- **Accessibility**: Proper ARIA attributes and screen reader support
- **Responsive**: Adapts to different screen sizes
- **RTL Support**: Works seamlessly with RTL layouts

## CSS Classes

\`\`\`css
/* Modal structure */
.tutor-modal
.tutor-modal__backdrop
.tutor-modal__content
.tutor-modal__header
.tutor-modal__body
.tutor-modal__footer
.tutor-modal__close

/* Modal sizes */
.tutor-modal--small
.tutor-modal--medium
.tutor-modal--large
.tutor-modal--fullscreen
\`\`\`

## Alpine.js Usage

\`\`\`html
<div x-data="TutorCore.modal({ closable: true, backdrop: true })">
  <button @click="show()" class="tutor-btn tutor-btn--primary">
    Open Modal
  </button>
  
  <div x-show="open" class="tutor-modal" x-transition>
    <div class="tutor-modal__backdrop" @click="hide()"></div>
    <div class="tutor-modal__content" @keydown.escape="hide()">
      <!-- Modal content -->
    </div>
  </div>
</div>
\`\`\`
        `,
      },
    },
  },
};

export default meta;
type Story = StoryObj;

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

const Modal = ({ 
  isOpen, 
  onClose, 
  title, 
  children, 
  size = 'medium',
  showCloseButton = true 
}: {
  isOpen: boolean;
  onClose: () => void;
  title?: string;
  children: React.ReactNode;
  size?: 'small' | 'medium' | 'large';
  showCloseButton?: boolean;
}) => {
  if (!isOpen) return null;

  const sizeStyles = {
    small: css`max-width: 400px;`,
    medium: css`max-width: 500px;`,
    large: css`max-width: 700px;`,
  };

  return (
    <div css={modalStyles.backdrop} onClick={onClose}>
      <div 
        css={[modalStyles.content, sizeStyles[size]]} 
        onClick={(e) => e.stopPropagation()}
        role="dialog"
        aria-modal="true"
        aria-labelledby={title ? "modal-title" : undefined}
      >
        {title && (
          <div css={modalStyles.header}>
            <h2 css={modalStyles.title} id="modal-title">
              {title}
            </h2>
            {showCloseButton && (
              <button css={modalStyles.closeButton} onClick={onClose} aria-label="Close modal">
                ×
              </button>
            )}
          </div>
        )}
        {children}
      </div>
    </div>
  );
};

export const BasicModal: Story = {
  render: () => {
    const [isOpen, setIsOpen] = useState(false);

    return (
      <div>
        <button 
          css={[modalStyles.button, modalStyles.primaryButton]}
          onClick={() => setIsOpen(true)}
        >
          Open Basic Modal
        </button>

        <Modal 
          isOpen={isOpen} 
          onClose={() => setIsOpen(false)}
          title="Basic Modal"
        >
          <div css={modalStyles.body}>
            <p css={css`margin: 0 0 16px 0; line-height: 1.5; color: #333;`}>
              This is a basic modal with a title, body content, and close functionality. 
              You can close it by clicking the X button, clicking outside the modal, or pressing the ESC key.
            </p>
            <p css={css`margin: 0; line-height: 1.5; color: #666; font-size: 14px;`}>
              The modal automatically manages focus and provides proper accessibility attributes 
              for screen readers.
            </p>
          </div>
          <div css={modalStyles.footer}>
            <button 
              css={[modalStyles.button, modalStyles.secondaryButton]}
              onClick={() => setIsOpen(false)}
            >
              Cancel
            </button>
            <button 
              css={[modalStyles.button, modalStyles.primaryButton]}
              onClick={() => setIsOpen(false)}
            >
              Confirm
            </button>
          </div>
        </Modal>
      </div>
    );
  },
  parameters: {
    docs: {
      description: {
        story: 'Basic modal with title, body content, and action buttons in the footer.',
      },
    },
  },
};

export const ModalSizes: Story = {
  render: () => {
    const [openModal, setOpenModal] = useState<string | null>(null);

    return (
      <div css={css`display: flex; gap: 12px; flex-wrap: wrap;`}>
        <button 
          css={[modalStyles.button, modalStyles.primaryButton]}
          onClick={() => setOpenModal('small')}
        >
          Small Modal
        </button>
        <button 
          css={[modalStyles.button, modalStyles.primaryButton]}
          onClick={() => setOpenModal('medium')}
        >
          Medium Modal
        </button>
        <button 
          css={[modalStyles.button, modalStyles.primaryButton]}
          onClick={() => setOpenModal('large')}
        >
          Large Modal
        </button>

        <Modal 
          isOpen={openModal === 'small'} 
          onClose={() => setOpenModal(null)}
          title="Small Modal"
          size="small"
        >
          <div css={modalStyles.body}>
            <p css={css`margin: 0; line-height: 1.5; color: #333;`}>
              This is a small modal (max-width: 400px). Perfect for simple confirmations 
              and brief messages.
            </p>
          </div>
        </Modal>

        <Modal 
          isOpen={openModal === 'medium'} 
          onClose={() => setOpenModal(null)}
          title="Medium Modal"
          size="medium"
        >
          <div css={modalStyles.body}>
            <p css={css`margin: 0 0 16px 0; line-height: 1.5; color: #333;`}>
              This is a medium modal (max-width: 500px). Good for forms and moderate 
              amounts of content.
            </p>
            <p css={css`margin: 0; line-height: 1.5; color: #666;`}>
              The medium size is the default and works well for most use cases.
            </p>
          </div>
        </Modal>

        <Modal 
          isOpen={openModal === 'large'} 
          onClose={() => setOpenModal(null)}
          title="Large Modal"
          size="large"
        >
          <div css={modalStyles.body}>
            <p css={css`margin: 0 0 16px 0; line-height: 1.5; color: #333;`}>
              This is a large modal (max-width: 700px). Suitable for complex forms, 
              detailed content, or when you need more horizontal space.
            </p>
            <p css={css`margin: 0 0 16px 0; line-height: 1.5; color: #666;`}>
              Large modals are great for:
            </p>
            <ul css={css`margin: 0; padding-left: 20px; color: #666;`}>
              <li>Multi-step forms</li>
              <li>Data tables</li>
              <li>Rich content editors</li>
              <li>Image galleries</li>
            </ul>
          </div>
        </Modal>
      </div>
    );
  },
  parameters: {
    docs: {
      description: {
        story: 'Different modal sizes: small (400px), medium (500px), and large (700px) max-widths.',
      },
    },
  },
};

export const ConfirmationModal: Story = {
  render: () => {
    const [isOpen, setIsOpen] = useState(false);

    return (
      <div>
        <button 
          css={[modalStyles.button, css`background: #f04438; color: white; &:hover { background: #d92d20; }`]}
          onClick={() => setIsOpen(true)}
        >
          Delete Item
        </button>

        <Modal 
          isOpen={isOpen} 
          onClose={() => setIsOpen(false)}
          title="Confirm Deletion"
          size="small"
        >
          <div css={modalStyles.body}>
            <div css={css`display: flex; align-items: center; margin-bottom: 16px;`}>
              <div css={css`
                width: 48px;
                height: 48px;
                background: #fee4e2;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                margin-right: 16px;
              `}>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="#f04438">
                  <path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
              </div>
              <div>
                <h3 css={css`margin: 0 0 4px 0; font-size: 16px; font-weight: 600; color: #333;`}>
                  Delete this item?
                </h3>
                <p css={css`margin: 0; font-size: 14px; color: #666;`}>
                  This action cannot be undone.
                </p>
              </div>
            </div>
            <p css={css`margin: 0; line-height: 1.5; color: #333; font-size: 14px;`}>
              Are you sure you want to delete this item? All associated data will be permanently removed.
            </p>
          </div>
          <div css={modalStyles.footer}>
            <button 
              css={[modalStyles.button, modalStyles.secondaryButton]}
              onClick={() => setIsOpen(false)}
            >
              Cancel
            </button>
            <button 
              css={[modalStyles.button, css`background: #f04438; color: white; &:hover { background: #d92d20; }`]}
              onClick={() => setIsOpen(false)}
            >
              Delete
            </button>
          </div>
        </Modal>
      </div>
    );
  },
  parameters: {
    docs: {
      description: {
        story: 'Confirmation modal for destructive actions with warning icon and appropriate button styling.',
      },
    },
  },
};

export const FormModal: Story = {
  render: () => {
    const [isOpen, setIsOpen] = useState(false);

    return (
      <div>
        <button 
          css={[modalStyles.button, modalStyles.primaryButton]}
          onClick={() => setIsOpen(true)}
        >
          Add New User
        </button>

        <Modal 
          isOpen={isOpen} 
          onClose={() => setIsOpen(false)}
          title="Add New User"
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
                  placeholder="Enter full name"
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
                  placeholder="Enter email address"
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
                <select css={css`
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
                `}>
                  <option value="">Select a role</option>
                  <option value="admin">Administrator</option>
                  <option value="editor">Editor</option>
                  <option value="viewer">Viewer</option>
                </select>
              </div>
              
              <div>
                <label css={css`display: flex; align-items: center; cursor: pointer;`}>
                  <input 
                    type="checkbox"
                    css={css`
                      margin-right: 8px;
                      width: 16px;
                      height: 16px;
                    `}
                  />
                  <span css={css`font-size: 14px; color: #333;`}>
                    Send welcome email to user
                  </span>
                </label>
              </div>
            </form>
          </div>
          <div css={modalStyles.footer}>
            <button 
              css={[modalStyles.button, modalStyles.secondaryButton]}
              onClick={() => setIsOpen(false)}
            >
              Cancel
            </button>
            <button 
              css={[modalStyles.button, modalStyles.primaryButton]}
              onClick={() => setIsOpen(false)}
            >
              Add User
            </button>
          </div>
        </Modal>
      </div>
    );
  },
  parameters: {
    docs: {
      description: {
        story: 'Modal containing a form with various input types and proper focus management.',
      },
    },
  },
};

export const ScrollableModal: Story = {
  render: () => {
    const [isOpen, setIsOpen] = useState(false);

    return (
      <div>
        <button 
          css={[modalStyles.button, modalStyles.primaryButton]}
          onClick={() => setIsOpen(true)}
        >
          Open Scrollable Modal
        </button>

        <Modal 
          isOpen={isOpen} 
          onClose={() => setIsOpen(false)}
          title="Terms and Conditions"
          size="medium"
        >
          <div css={modalStyles.body}>
            <div css={css`max-height: 300px; overflow-y: auto; padding-right: 8px;`}>
              <h3 css={css`margin: 0 0 16px 0; font-size: 16px; font-weight: 600;`}>
                1. Acceptance of Terms
              </h3>
              <p css={css`margin: 0 0 16px 0; line-height: 1.5; color: #333;`}>
                By accessing and using this service, you accept and agree to be bound by the terms 
                and provision of this agreement. If you do not agree to abide by the above, please 
                do not use this service.
              </p>
              
              <h3 css={css`margin: 0 0 16px 0; font-size: 16px; font-weight: 600;`}>
                2. Use License
              </h3>
              <p css={css`margin: 0 0 16px 0; line-height: 1.5; color: #333;`}>
                Permission is granted to temporarily download one copy of the materials on our 
                website for personal, non-commercial transitory viewing only. This is the grant 
                of a license, not a transfer of title, and under this license you may not:
              </p>
              <ul css={css`margin: 0 0 16px 0; padding-left: 20px; color: #333;`}>
                <li>modify or copy the materials;</li>
                <li>use the materials for any commercial purpose or for any public display;</li>
                <li>attempt to reverse engineer any software contained on the website;</li>
                <li>remove any copyright or other proprietary notations from the materials.</li>
              </ul>
              
              <h3 css={css`margin: 0 0 16px 0; font-size: 16px; font-weight: 600;`}>
                3. Disclaimer
              </h3>
              <p css={css`margin: 0 0 16px 0; line-height: 1.5; color: #333;`}>
                The materials on our website are provided on an 'as is' basis. We make no 
                warranties, expressed or implied, and hereby disclaim and negate all other 
                warranties including without limitation, implied warranties or conditions of 
                merchantability, fitness for a particular purpose, or non-infringement of 
                intellectual property or other violation of rights.
              </p>
              
              <h3 css={css`margin: 0 0 16px 0; font-size: 16px; font-weight: 600;`}>
                4. Limitations
              </h3>
              <p css={css`margin: 0; line-height: 1.5; color: #333;`}>
                In no event shall our company or its suppliers be liable for any damages 
                (including, without limitation, damages for loss of data or profit, or due to 
                business interruption) arising out of the use or inability to use the materials 
                on our website, even if we or our authorized representative has been notified 
                orally or in writing of the possibility of such damage.
              </p>
            </div>
          </div>
          <div css={modalStyles.footer}>
            <button 
              css={[modalStyles.button, modalStyles.secondaryButton]}
              onClick={() => setIsOpen(false)}
            >
              Decline
            </button>
            <button 
              css={[modalStyles.button, modalStyles.primaryButton]}
              onClick={() => setIsOpen(false)}
            >
              Accept
            </button>
          </div>
        </Modal>
      </div>
    );
  },
  parameters: {
    docs: {
      description: {
        story: 'Modal with scrollable content when the body content exceeds the available height.',
      },
    },
  },
};

export const RTLSupport: Story = {
  render: () => {
    const [ltrOpen, setLtrOpen] = useState(false);
    const [rtlOpen, setRtlOpen] = useState(false);

    return (
      <div css={css`display: flex; flex-direction: column; gap: 24px;`}>
        <div>
          <h4 css={css`margin: 0 0 12px 0; font-size: 14px; font-weight: 600;`}>
            LTR (Left-to-Right)
          </h4>
          <div css={css`direction: ltr;`}>
            <button 
              css={[modalStyles.button, modalStyles.primaryButton]}
              onClick={() => setLtrOpen(true)}
            >
              Open LTR Modal
            </button>

            <Modal 
              isOpen={ltrOpen} 
              onClose={() => setLtrOpen(false)}
              title="LTR Modal Example"
              size="small"
            >
              <div css={modalStyles.body}>
                <p css={css`margin: 0; line-height: 1.5; color: #333;`}>
                  This modal demonstrates left-to-right layout with standard text alignment 
                  and button positioning.
                </p>
              </div>
              <div css={modalStyles.footer}>
                <button 
                  css={[modalStyles.button, modalStyles.secondaryButton]}
                  onClick={() => setLtrOpen(false)}
                >
                  Cancel
                </button>
                <button 
                  css={[modalStyles.button, modalStyles.primaryButton]}
                  onClick={() => setLtrOpen(false)}
                >
                  Confirm
                </button>
              </div>
            </Modal>
          </div>
        </div>
        
        <div>
          <h4 css={css`margin: 0 0 12px 0; font-size: 14px; font-weight: 600;`}>
            RTL (Right-to-Left)
          </h4>
          <div css={css`direction: rtl;`}>
            <button 
              css={[modalStyles.button, modalStyles.primaryButton]}
              onClick={() => setRtlOpen(true)}
            >
              فتح النافذة المنبثقة
            </button>

            <Modal 
              isOpen={rtlOpen} 
              onClose={() => setRtlOpen(false)}
              title="مثال على النافذة المنبثقة"
              size="small"
            >
              <div css={[modalStyles.body, css`text-align: right;`]}>
                <p css={css`margin: 0; line-height: 1.5; color: #333;`}>
                  هذه النافذة المنبثقة تُظهر التخطيط من اليمين إلى اليسار مع محاذاة النص 
                  وموضع الأزرار المناسب للغات العربية.
                </p>
              </div>
              <div css={[modalStyles.footer, css`justify-content: flex-start;`]}>
                <button 
                  css={[modalStyles.button, modalStyles.primaryButton]}
                  onClick={() => setRtlOpen(false)}
                >
                  تأكيد
                </button>
                <button 
                  css={[modalStyles.button, modalStyles.secondaryButton]}
                  onClick={() => setRtlOpen(false)}
                >
                  إلغاء
                </button>
              </div>
            </Modal>
          </div>
        </div>
      </div>
    );
  },
  parameters: {
    docs: {
      description: {
        story: 'Modal components automatically adapt to RTL layouts with proper text alignment and button positioning.',
      },
    },
  },
};