import { type AlpineComponentMeta } from '@Core/ts/types';
import { type AlpineToastData, type ToastConfig } from '@Core/ts/types/toast';
import { toastServiceMeta } from '@Core/ts/services/Toast';

export function createToast(): AlpineToastData {
  return {
    init(): void {},

    show(message: string, config: ToastConfig = {}): string {
      return toastServiceMeta.instance.show(message, config);
    },

    remove(id: string): void {
      toastServiceMeta.instance.dismiss(id);
    },

    clear(): void {
      toastServiceMeta.instance.clear();
    },

    dismiss(id?: string): void {
      toastServiceMeta.instance.dismiss(id);
    },

    success(message: string, duration?: number): string {
      return toastServiceMeta.instance.success(message, duration);
    },

    error(message: string, duration?: number): string {
      return toastServiceMeta.instance.error(message, duration);
    },

    warning(message: string, duration?: number): string {
      return toastServiceMeta.instance.warning(message, duration);
    },

    info(message: string, duration?: number): string {
      return toastServiceMeta.instance.info(message, duration);
    },
  };
}

export const toastMeta: AlpineComponentMeta = {
  name: 'toast',
  component: createToast,
};
