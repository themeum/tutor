// Toast Component
// Alpine.js toast notifications with stacking and auto-dismiss

import { type AlpineToastData, type ToastConfig, type ToastItem } from '../types/components';

export function createToast(): AlpineToastData {
  return {
    toasts: [] as ToastItem[],
    $el: undefined as HTMLElement | undefined,

    show(message: string, config: ToastConfig = {}): void {
      const toast: ToastItem = {
        id: Date.now() + Math.random(),
        message,
        type: config.type || 'info',
        duration: config.duration || 5000,
      };

      this.toasts.push(toast);

      if (toast.duration > 0) {
        setTimeout(() => this.remove(toast.id), toast.duration);
      }
    },

    remove(id: number): void {
      this.toasts = this.toasts.filter((toast: ToastItem) => toast.id !== id);
    },

    clear(): void {
      this.toasts = [];
    },

    success(message: string, duration?: number): void {
      this.show(message, { type: 'success', ...(duration !== undefined && { duration }) });
    },

    error(message: string, duration?: number): void {
      this.show(message, { type: 'error', ...(duration !== undefined && { duration }) });
    },

    warning(message: string, duration?: number): void {
      this.show(message, { type: 'warning', ...(duration !== undefined && { duration }) });
    },

    info(message: string, duration?: number): void {
      this.show(message, { type: 'info', ...(duration !== undefined && { duration }) });
    },
  };
}
