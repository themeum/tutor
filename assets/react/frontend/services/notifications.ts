// Notifications Service
// Toast notifications and alert system

interface NotificationOptions {
  duration?: number;
  position?: 'top-right' | 'top-left' | 'bottom-right' | 'bottom-left' | 'top-center' | 'bottom-center';
  closable?: boolean;
  persistent?: boolean;
  onClick?: () => void;
  onClose?: () => void;
}

interface Notification {
  id: string;
  type: 'success' | 'error' | 'warning' | 'info';
  message: string;
  options: NotificationOptions;
  element: HTMLElement;
  timer?: NodeJS.Timeout;
}

class NotificationService {
  private notifications: Map<string, Notification> = new Map();
  private container: HTMLElement | null = null;
  private defaultOptions: NotificationOptions = {
    duration: 5000,
    position: 'top-right',
    closable: true,
    persistent: false,
  };

  constructor() {
    this.createContainer();
  }

  // Show success notification
  success(message: string, options: NotificationOptions = {}): string {
    return this.show('success', message, options);
  }

  // Show error notification
  error(message: string, options: NotificationOptions = {}): string {
    return this.show('error', message, { ...options, duration: 0 }); // Errors don't auto-dismiss
  }

  // Show warning notification
  warning(message: string, options: NotificationOptions = {}): string {
    return this.show('warning', message, options);
  }

  // Show info notification
  info(message: string, options: NotificationOptions = {}): string {
    return this.show('info', message, options);
  }

  // Show notification with custom type
  show(type: 'success' | 'error' | 'warning' | 'info', message: string, options: NotificationOptions = {}): string {
    const id = this.generateId();
    const finalOptions = { ...this.defaultOptions, ...options };
    
    const element = this.createElement(id, type, message, finalOptions);
    
    const notification: Notification = {
      id,
      type,
      message,
      options: finalOptions,
      element,
    };

    // Add to container
    if (this.container) {
      this.container.appendChild(element);
    }

    // Store notification
    this.notifications.set(id, notification);

    // Auto-dismiss if duration is set
    if (finalOptions.duration && finalOptions.duration > 0) {
      notification.timer = setTimeout(() => {
        this.dismiss(id);
      }, finalOptions.duration);
    }

    // Animate in
    requestAnimationFrame(() => {
      element.classList.add('show');
    });

    return id;
  }

  // Dismiss a specific notification
  dismiss(id: string): void {
    const notification = this.notifications.get(id);
    if (!notification) return;

    // Clear timer
    if (notification.timer) {
      clearTimeout(notification.timer);
    }

    // Animate out
    notification.element.classList.add('hiding');
    
    setTimeout(() => {
      // Remove from DOM
      if (notification.element.parentNode) {
        notification.element.parentNode.removeChild(notification.element);
      }
      
      // Remove from map
      this.notifications.delete(id);
      
      // Call onClose callback
      if (notification.options.onClose) {
        notification.options.onClose();
      }
    }, 300); // Animation duration
  }

  // Dismiss all notifications
  dismissAll(): void {
    this.notifications.forEach((_, id) => {
      this.dismiss(id);
    });
  }

  // Dismiss all notifications of a specific type
  dismissByType(type: 'success' | 'error' | 'warning' | 'info'): void {
    this.notifications.forEach((notification, id) => {
      if (notification.type === type) {
        this.dismiss(id);
      }
    });
  }

  // Update notification message
  update(id: string, message: string): void {
    const notification = this.notifications.get(id);
    if (!notification) return;

    const messageElement = notification.element.querySelector('.notification-message');
    if (messageElement) {
      messageElement.textContent = message;
      notification.message = message;
    }
  }

  // Create notification container
  private createContainer(): void {
    this.container = document.createElement('div');
    this.container.className = 'tutor-notifications-container';
    this.container.setAttribute('aria-live', 'polite');
    this.container.setAttribute('aria-atomic', 'true');
    
    // Add CSS styles
    this.addStyles();
    
    document.body.appendChild(this.container);
  }

  // Create notification element
  private createElement(id: string, type: string, message: string, options: NotificationOptions): HTMLElement {
    const element = document.createElement('div');
    element.className = `tutor-notification tutor-notification-${type}`;
    element.setAttribute('data-notification-id', id);
    element.setAttribute('role', 'alert');
    
    // Icon based on type
    const icon = this.getIcon(type);
    
    element.innerHTML = `
      <div class="notification-content">
        <div class="notification-icon">${icon}</div>
        <div class="notification-message">${message}</div>
        ${options.closable ? '<button class="notification-close" aria-label="Close">&times;</button>' : ''}
      </div>
    `;

    // Add event listeners
    if (options.closable) {
      const closeBtn = element.querySelector('.notification-close');
      if (closeBtn) {
        closeBtn.addEventListener('click', () => {
          this.dismiss(id);
        });
      }
    }

    if (options.onClick) {
      element.addEventListener('click', (e) => {
        // Don't trigger onClick if close button was clicked
        if ((e.target as HTMLElement).classList.contains('notification-close')) {
          return;
        }
        options.onClick!();
      });
      element.style.cursor = 'pointer';
    }

    return element;
  }

  // Get icon for notification type
  private getIcon(type: string): string {
    const icons = {
      success: '✓',
      error: '✕',
      warning: '⚠',
      info: 'ℹ',
    };
    return icons[type as keyof typeof icons] || 'ℹ';
  }

  // Generate unique ID
  private generateId(): string {
    return `notification_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
  }

  // Add CSS styles
  private addStyles(): void {
    if (document.querySelector('#tutor-notifications-styles')) {
      return; // Styles already added
    }

    const styles = document.createElement('style');
    styles.id = 'tutor-notifications-styles';
    styles.textContent = `
      .tutor-notifications-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10000;
        pointer-events: none;
      }

      .tutor-notification {
        background: white;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        margin-bottom: 12px;
        max-width: 400px;
        opacity: 0;
        transform: translateX(100%);
        transition: all 0.3s ease;
        pointer-events: auto;
        border-left: 4px solid;
      }

      .tutor-notification.show {
        opacity: 1;
        transform: translateX(0);
      }

      .tutor-notification.hiding {
        opacity: 0;
        transform: translateX(100%);
      }

      .tutor-notification-success {
        border-left-color: #10b981;
      }

      .tutor-notification-error {
        border-left-color: #ef4444;
      }

      .tutor-notification-warning {
        border-left-color: #f59e0b;
      }

      .tutor-notification-info {
        border-left-color: #3b82f6;
      }

      .notification-content {
        display: flex;
        align-items: flex-start;
        padding: 16px;
        gap: 12px;
      }

      .notification-icon {
        flex-shrink: 0;
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 14px;
      }

      .tutor-notification-success .notification-icon {
        color: #10b981;
      }

      .tutor-notification-error .notification-icon {
        color: #ef4444;
      }

      .tutor-notification-warning .notification-icon {
        color: #f59e0b;
      }

      .tutor-notification-info .notification-icon {
        color: #3b82f6;
      }

      .notification-message {
        flex: 1;
        font-size: 14px;
        line-height: 1.4;
        color: #374151;
      }

      .notification-close {
        background: none;
        border: none;
        font-size: 18px;
        color: #9ca3af;
        cursor: pointer;
        padding: 0;
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
      }

      .notification-close:hover {
        color: #374151;
      }

      /* Position variants */
      .tutor-notifications-container.position-top-left {
        top: 20px;
        left: 20px;
        right: auto;
      }

      .tutor-notifications-container.position-bottom-right {
        top: auto;
        bottom: 20px;
        right: 20px;
      }

      .tutor-notifications-container.position-bottom-left {
        top: auto;
        bottom: 20px;
        left: 20px;
        right: auto;
      }

      .tutor-notifications-container.position-top-center {
        top: 20px;
        left: 50%;
        right: auto;
        transform: translateX(-50%);
      }

      .tutor-notifications-container.position-bottom-center {
        top: auto;
        bottom: 20px;
        left: 50%;
        right: auto;
        transform: translateX(-50%);
      }

      /* Mobile responsive */
      @media (max-width: 640px) {
        .tutor-notifications-container {
          left: 16px;
          right: 16px;
          top: 16px;
        }

        .tutor-notification {
          max-width: none;
        }
      }
    `;

    document.head.appendChild(styles);
  }

  // Set default options
  setDefaults(options: Partial<NotificationOptions>): void {
    this.defaultOptions = { ...this.defaultOptions, ...options };
  }

  // Get notification count
  getCount(): number {
    return this.notifications.size;
  }

  // Get notifications by type
  getByType(type: 'success' | 'error' | 'warning' | 'info'): Notification[] {
    return Array.from(this.notifications.values()).filter(n => n.type === type);
  }
}

// Create and export singleton instance
export const notificationService = new NotificationService();

// Convenience functions
export const showNotification = (message: string, type: 'success' | 'error' | 'warning' | 'info' = 'info', options?: NotificationOptions): string => {
  return notificationService.show(type, message, options);
};

export const showSuccess = (message: string, options?: NotificationOptions): string => {
  return notificationService.success(message, options);
};

export const showError = (message: string, options?: NotificationOptions): string => {
  return notificationService.error(message, options);
};

export const showWarning = (message: string, options?: NotificationOptions): string => {
  return notificationService.warning(message, options);
};

export const showInfo = (message: string, options?: NotificationOptions): string => {
  return notificationService.info(message, options);
};

// Export types
export type { Notification, NotificationOptions };
