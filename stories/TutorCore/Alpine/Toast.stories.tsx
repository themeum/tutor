import { css } from '@emotion/react';
import type { Meta, StoryObj } from '@storybook/react';
import { useState } from 'react';

const meta: Meta = {
  title: 'TutorCore/Alpine/Toast',
  parameters: {
    docs: {
      description: {
        component: `
# Alpine.js Toast Component

The TutorCore Alpine.js toast component provides non-intrusive notifications with automatic dismissal, stacking, and multiple types. Perfect for user feedback and status updates.

## Features

- **Multiple Types**: Success, error, warning, info notifications
- **Auto Dismiss**: Configurable timeout with pause on hover
- **Stacking**: Multiple toasts stack vertically
- **Positioning**: Configurable position (top/bottom, left/right/center)
- **Interactive**: Click to dismiss, pause on hover
- **Accessibility**: Proper ARIA attributes and announcements

## Configuration

\`\`\`typescript
interface ToastConfig {
  type?: 'success' | 'error' | 'warning' | 'info';
  duration?: number;           // Auto-dismiss timeout (default: 5000ms)
  position?: 'top-right' | 'top-left' | 'bottom-right' | 'bottom-left' | 'top-center' | 'bottom-center';
  closable?: boolean;          // Show close button (default: true)
  icon?: boolean;             // Show type icon (default: true)
}
\`\`\`

## Usage

\`\`\`html
<div x-data="TutorCore.toast()">
  <button @click="success('Operation completed!')" class="tutor-btn">
    Success Toast
  </button>
  
  <div class="tutor-toast-container">
    <template x-for="toast in toasts" :key="toast.id">
      <div class="tutor-toast" :class="\`tutor-toast--\${toast.type}\`" x-transition>
        <span x-text="toast.message"></span>
        <button @click="remove(toast.id)" class="tutor-toast__close">&times;</button>
      </div>
    </template>
  </div>
</div>
\`\`\`

## Methods

- \`show(message, config)\`: Display a toast notification
- \`success(message)\`: Show success toast
- \`error(message)\`: Show error toast  
- \`warning(message)\`: Show warning toast
- \`info(message)\`: Show info toast
- \`remove(id)\`: Remove specific toast
- \`clear()\`: Remove all toasts
        `,
      },
    },
  },
};

export default meta;
type Story = StoryObj;
interface Toast {
  id: number;
  message: string;
  type: 'success' | 'error' | 'warning' | 'info';
  duration: number;
}

// Mock Alpine.js toast functionality
const useToast = () => {
  const [toasts, setToasts] = useState<Toast[]>([]);

  const show = (message: string, config: { type?: Toast['type']; duration?: number } = {}) => {
    const toast: Toast = {
      id: Date.now() + Math.random(),
      message,
      type: config.type || 'info',
      duration: config.duration || 5000,
    };

    setToasts(prev => [...prev, toast]);

    // Auto remove after duration
    setTimeout(() => {
      remove(toast.id);
    }, toast.duration);
  };

  const remove = (id: number) => {
    setToasts(prev => prev.filter(toast => toast.id !== id));
  };

  const clear = () => {
    setToasts([]);
  };

  const success = (message: string) => show(message, { type: 'success' });
  const error = (message: string) => show(message, { type: 'error' });
  const warning = (message: string) => show(message, { type: 'warning' });
  const info = (message: string) => show(message, { type: 'info' });

  return { toasts, show, remove, clear, success, error, warning, info };
};

const toastStyles = {
  container: css`
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 1000;
    display: flex;
    flex-direction: column;
    gap: 8px;
    max-width: 400px;
    pointer-events: none;
  `,
  
  toast: css`
    padding: 12px 16px;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 14px;
    font-weight: 500;
    pointer-events: auto;
    animation: slideIn 0.3s ease-out;
    
    @keyframes slideIn {
      from {
        opacity: 0;
        transform: translateX(100%);
      }
      to {
        opacity: 1;
        transform: translateX(0);
      }
    }
  `,
  
  success: css`
    background: #e3fbcc;
    color: #2b5314;
    border-left: 4px solid #66c61c;
  `,
  
  error: css`
    background: #fee4e2;
    color: #7a271a;
    border-left: 4px solid #f04438;
  `,
  
  warning: css`
    background: #fef0c7;
    color: #7a2e0e;
    border-left: 4px solid #f79009;
  `,
  
  info: css`
    background: #f6f8fe;
    color: #4979e8;
    border-left: 4px solid #4979e8;
  `,
  
  icon: css`
    flex-shrink: 0;
  `,
  
  message: css`
    flex: 1;
    line-height: 1.4;
  `,
  
  closeButton: css`
    background: none;
    border: none;
    font-size: 18px;
    cursor: pointer;
    padding: 0;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    transition: background-color 0.2s ease;
    color: currentColor;
    opacity: 0.7;
    
    &:hover {
      opacity: 1;
      background: rgba(0, 0, 0, 0.1);
    }
  `,
  
  button: css`
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    margin-right: 8px;
    
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
  
  successButton: css`
    background: #66c61c;
    color: white;
    
    &:hover {
      background: #4ca30d;
    }
  `,
  
  warningButton: css`
    background: #f79009;
    color: white;
    
    &:hover {
      background: #dc6803;
    }
  `,
  
  errorButton: css`
    background: #f04438;
    color: white;
    
    &:hover {
      background: #d92d20;
    }
  `,
};

const ToastIcon = ({ type }: { type: Toast['type'] }) => {
  const icons = {
    success: (
      <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
        <path d="M10 0a10 10 0 100 20 10 10 0 000-20zm4 7l-5 5-3-3 1.5-1.5L9 7l3.5-3.5L14 7z"/>
      </svg>
    ),
    error: (
      <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
        <path d="M10 0a10 10 0 100 20 10 10 0 000-20zM9 5h2v6H9V5zm0 7h2v2H9v-2z"/>
      </svg>
    ),
    warning: (
      <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
        <path d="M10 0L0 18h20L10 0zm0 6l1 6H9l1-6zm0 8a1 1 0 100 2 1 1 0 000-2z"/>
      </svg>
    ),
    info: (
      <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
        <path d="M10 0a10 10 0 100 20 10 10 0 000-20zm0 6a1 1 0 100-2 1 1 0 000 2zm1 2H9v6h2V8z"/>
      </svg>
    ),
  };

  return <div css={toastStyles.icon}>{icons[type]}</div>;
};

const ToastItem = ({ toast, onRemove }: { toast: Toast; onRemove: (id: number) => void }) => {
  const typeStyles = {
    success: toastStyles.success,
    error: toastStyles.error,
    warning: toastStyles.warning,
    info: toastStyles.info,
  };

  return (
    <div css={[toastStyles.toast, typeStyles[toast.type]]}>
      <ToastIcon type={toast.type} />
      <div css={toastStyles.message}>{toast.message}</div>
      <button 
        css={toastStyles.closeButton}
        onClick={() => onRemove(toast.id)}
        aria-label="Close notification"
      >
        ×
      </button>
    </div>
  );
};

const ToastContainer = ({ toasts, onRemove }: { toasts: Toast[]; onRemove: (id: number) => void }) => (
  <div css={toastStyles.container}>
    {toasts.map(toast => (
      <ToastItem key={toast.id} toast={toast} onRemove={onRemove} />
    ))}
  </div>
);

export const BasicToasts: Story = {
  render: () => {
    const { toasts, success, error, warning, info, clear, remove } = useToast();

    return (
      <div>
        <div css={css`margin-bottom: 24px;`}>
          <h3 css={css`margin: 0 0 16px 0; font-size: 16px; font-weight: 600;`}>
            Toast Notifications
          </h3>
          <div css={css`display: flex; flex-wrap: wrap; gap: 8px;`}>
            <button 
              css={[toastStyles.button, toastStyles.successButton]}
              onClick={() => success('Operation completed successfully!')}
            >
              Success Toast
            </button>
            <button 
              css={[toastStyles.button, toastStyles.errorButton]}
              onClick={() => error('Something went wrong. Please try again.')}
            >
              Error Toast
            </button>
            <button 
              css={[toastStyles.button, toastStyles.warningButton]}
              onClick={() => warning('Please review your input before proceeding.')}
            >
              Warning Toast
            </button>
            <button 
              css={[toastStyles.button, toastStyles.primaryButton]}
              onClick={() => info('Here is some helpful information for you.')}
            >
              Info Toast
            </button>
          </div>
          
          {toasts.length > 0 && (
            <div css={css`margin-top: 16px;`}>
              <button 
                css={[toastStyles.button, css`background: #f5f5f6; color: #333; border: 1px solid #cecfd2;`]}
                onClick={clear}
              >
                Clear All ({toasts.length})
              </button>
            </div>
          )}
        </div>

        <ToastContainer toasts={toasts} onRemove={remove} />
      </div>
    );
  },
  parameters: {
    docs: {
      description: {
        story: 'Basic toast notifications with different types: success, error, warning, and info.',
      },
    },
  },
};

export const ToastVariations: Story = {
  render: () => {
    const { toasts, show, remove } = useToast();

    const showCustomToast = (type: Toast['type'], message: string, duration?: number) => {
      show(message, { type, duration });
    };

    return (
      <div>
        <div css={css`margin-bottom: 24px;`}>
          <h3 css={css`margin: 0 0 16px 0; font-size: 16px; font-weight: 600;`}>
            Toast Variations
          </h3>
          
          <div css={css`display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;`}>
            <div>
              <h4 css={css`margin: 0 0 8px 0; font-size: 14px; font-weight: 600;`}>
                Duration Variants
              </h4>
              <div css={css`display: flex; flex-direction: column; gap: 4px;`}>
                <button 
                  css={[toastStyles.button, toastStyles.primaryButton, css`width: 100%; font-size: 12px;`]}
                  onClick={() => showCustomToast('info', 'Quick message (2s)', 2000)}
                >
                  Short (2s)
                </button>
                <button 
                  css={[toastStyles.button, toastStyles.primaryButton, css`width: 100%; font-size: 12px;`]}
                  onClick={() => showCustomToast('info', 'Standard message (5s)', 5000)}
                >
                  Standard (5s)
                </button>
                <button 
                  css={[toastStyles.button, toastStyles.primaryButton, css`width: 100%; font-size: 12px;`]}
                  onClick={() => showCustomToast('info', 'Long message (10s)', 10000)}
                >
                  Long (10s)
                </button>
              </div>
            </div>

            <div>
              <h4 css={css`margin: 0 0 8px 0; font-size: 14px; font-weight: 600;`}>
                Message Length
              </h4>
              <div css={css`display: flex; flex-direction: column; gap: 4px;`}>
                <button 
                  css={[toastStyles.button, toastStyles.successButton, css`width: 100%; font-size: 12px;`]}
                  onClick={() => showCustomToast('success', 'Saved!')}
                >
                  Short Message
                </button>
                <button 
                  css={[toastStyles.button, toastStyles.warningButton, css`width: 100%; font-size: 12px;`]}
                  onClick={() => showCustomToast('warning', 'This is a longer message that provides more detailed information about the current situation.')}
                >
                  Long Message
                </button>
              </div>
            </div>

            <div>
              <h4 css={css`margin: 0 0 8px 0; font-size: 14px; font-weight: 600;`}>
                Multiple Toasts
              </h4>
              <div css={css`display: flex; flex-direction: column; gap: 4px;`}>
                <button 
                  css={[toastStyles.button, toastStyles.primaryButton, css`width: 100%; font-size: 12px;`]}
                  onClick={() => {
                    showCustomToast('success', 'First notification');
                    setTimeout(() => showCustomToast('info', 'Second notification'), 500);
                    setTimeout(() => showCustomToast('warning', 'Third notification'), 1000);
                  }}
                >
                  Show Stack
                </button>
              </div>
            </div>
          </div>
        </div>

        <ToastContainer toasts={toasts} onRemove={remove} />
      </div>
    );
  },
  parameters: {
    docs: {
      description: {
        story: 'Toast variations showing different durations, message lengths, and stacking behavior.',
      },
    },
  },
};

export const ToastPositions: Story = {
  render: () => {
    const [position, setPosition] = useState<'top-right' | 'top-left' | 'bottom-right' | 'bottom-left'>('top-right');
    const { toasts, success, remove } = useToast();

    const positionStyles = {
      'top-right': css`top: 20px; right: 20px;`,
      'top-left': css`top: 20px; left: 20px;`,
      'bottom-right': css`bottom: 20px; right: 20px;`,
      'bottom-left': css`bottom: 20px; left: 20px;`,
    };

    return (
      <div>
        <div css={css`margin-bottom: 24px;`}>
          <h3 css={css`margin: 0 0 16px 0; font-size: 16px; font-weight: 600;`}>
            Toast Positions
          </h3>
          
          <div css={css`margin-bottom: 16px;`}>
            <label css={css`display: block; margin-bottom: 8px; font-size: 14px; font-weight: 500;`}>
              Position:
            </label>
            <select 
              value={position}
              onChange={(e) => setPosition(e.target.value as any)}
              css={css`
                padding: 8px 12px;
                border: 1px solid #cecfd2;
                border-radius: 6px;
                font-size: 14px;
                background: white;
              `}
            >
              <option value="top-right">Top Right</option>
              <option value="top-left">Top Left</option>
              <option value="bottom-right">Bottom Right</option>
              <option value="bottom-left">Bottom Left</option>
            </select>
          </div>
          
          <button 
            css={[toastStyles.button, toastStyles.successButton]}
            onClick={() => success(`Toast positioned at ${position.replace('-', ' ')}`)}
          >
            Show Toast at {position.replace('-', ' ')}
          </button>
        </div>

        <div css={[toastStyles.container, positionStyles[position]]}>
          {toasts.map(toast => (
            <ToastItem key={toast.id} toast={toast} onRemove={remove} />
          ))}
        </div>
      </div>
    );
  },
  parameters: {
    docs: {
      description: {
        story: 'Toast positioning options: top-right, top-left, bottom-right, and bottom-left.',
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
        Alpine.js Toast Implementation
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
{`<!-- Toast System -->
<div x-data="TutorCore.toast()">
  <!-- Trigger Buttons -->
  <button @click="success('Operation completed!')" class="tutor-btn tutor-btn--success">
    Success
  </button>
  <button @click="error('Something went wrong!')" class="tutor-btn tutor-btn--error">
    Error
  </button>
  <button @click="warning('Please check your input!')" class="tutor-btn tutor-btn--warning">
    Warning
  </button>
  <button @click="info('Here is some information.')" class="tutor-btn tutor-btn--primary">
    Info
  </button>

  <!-- Toast Container -->
  <div class="tutor-toast-container">
    <template x-for="toast in toasts" :key="toast.id">
      <div 
        class="tutor-toast" 
        :class="\`tutor-toast--\${toast.type}\`" 
        x-transition
        @mouseenter="pauseTimer(toast.id)"
        @mouseleave="resumeTimer(toast.id)"
      >
        <div class="tutor-toast__icon">
          <!-- Icon based on toast.type -->
        </div>
        <span class="tutor-toast__message" x-text="toast.message"></span>
        <button @click="remove(toast.id)" class="tutor-toast__close">
          &times;
        </button>
      </div>
    </template>
  </div>
</div>

<!-- Custom Configuration -->
<div x-data="TutorCore.toast()">
  <button @click="show('Custom toast', { 
    type: 'success', 
    duration: 3000,
    position: 'bottom-right'
  })">
    Custom Toast
  </button>
</div>`}
      </pre>
    </div>
  ),
  parameters: {
    docs: {
      description: {
        story: 'Complete implementation example showing how to use TutorCore toast with Alpine.js.',
      },
    },
  },
};