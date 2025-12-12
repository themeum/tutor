import { TUTOR_CUSTOM_EVENTS } from '@Core/ts/constant';
import { type AlpineComponentMeta } from '@Core/ts/types';
import { type AlpineToastData, type ToastConfig, type ToastItem, type ToastType } from '@Core/ts/types/toast';

export function createToast(): AlpineToastData {
  return {
    toasts: [] as ToastItem[],
    $el: undefined as HTMLElement | undefined,

    init(): void {
      document.addEventListener(TUTOR_CUSTOM_EVENTS.TOAST_SHOW, ((event: CustomEvent) => {
        const { message, config } = event.detail;
        this.show(message, config);
      }) as EventListener);

      document.addEventListener(TUTOR_CUSTOM_EVENTS.TOAST_CLEAR, () => {
        this.clear();
      });
    },

    show(message: string, config: ToastConfig = {}): void {
      const type = config.type || 'info';
      const defaultTitles: Record<ToastType, string> = {
        success: 'Success',
        error: 'Error',
        warning: 'Warning',
        info: 'Info',
      };

      const toast: ToastItem = {
        id: Date.now() + Math.random(),
        message,
        type,
        duration: config.duration || 5000,
        title: config.title || defaultTitles[type],
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

    // Fallback methods for type compatibility if needed, but not used for global triggering anymore
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

export const toastMeta: AlpineComponentMeta = {
  name: 'toast',
  component: createToast,
};
