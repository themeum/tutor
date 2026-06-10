import { tutorToastManager } from '@Core/ts/services/toast/runtime';
import { type ServiceMeta } from '@Core/ts/types';
import {
  type ToastConfig,
  type TutorToastConfig,
  type TutorToastOptions,
  type TutorToastPromiseMessages,
  type TutorToastUpdateOptions,
} from '@Core/ts/types/toast';

export class ToastService {
  show(message: string, config: ToastConfig = {}): string {
    return tutorToastManager.show(message, config);
  }

  success(message: string, duration?: number): string {
    return tutorToastManager.success(message, duration);
  }

  error(message: string, duration?: number): string {
    return tutorToastManager.error(message, duration);
  }

  warning(message: string, duration?: number): string {
    return tutorToastManager.warning(message, duration);
  }

  info(message: string, duration?: number): string {
    return tutorToastManager.info(message, duration);
  }

  loading(message: string, options?: TutorToastOptions): string {
    return tutorToastManager.loading(message, options);
  }

  update(id: string, options: TutorToastUpdateOptions): void {
    tutorToastManager.update(id, options);
  }

  dismiss(id?: string): void {
    tutorToastManager.dismiss(id);
  }

  clear(): void {
    tutorToastManager.clear();
  }

  promise<T>(promise: Promise<T>, messages: TutorToastPromiseMessages<T>, options?: TutorToastOptions): string {
    return tutorToastManager.promise(promise, messages, options);
  }

  configure(options: TutorToastConfig): void {
    tutorToastManager.configure(options);
  }
}

export const toastServiceMeta: ServiceMeta<ToastService> = {
  name: 'toast',
  instance: new ToastService(),
};
