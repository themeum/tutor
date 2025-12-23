// Toast Component Types

export type ToastType = 'info' | 'success' | 'warning' | 'error';

export interface ToastConfig {
  type?: ToastType;
  duration?: number;
  title?: string;
}

export interface ToastItem {
  id: number;
  message: string;
  type: ToastType;
  duration: number;
  title: string;
}

export interface AlpineToastData {
  toasts: ToastItem[];
  $el?: HTMLElement;
  init(): void;
  show(message: string, config?: ToastConfig): void;
  remove(id: number): void;
  clear(): void;
  success(message: string, duration?: number): void;
  error(message: string, duration?: number): void;
  warning(message: string, duration?: number): void;
  info(message: string, duration?: number): void;
}
