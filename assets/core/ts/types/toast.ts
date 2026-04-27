export type TutorToastType = 'success' | 'error' | 'warning' | 'info' | 'loading' | 'default';

export type TutorToastPosition =
  | 'top-left'
  | 'top-center'
  | 'top-right'
  | 'bottom-left'
  | 'bottom-center'
  | 'bottom-right';

export type TutorToastExpandMode = 'hover' | 'always' | 'never';

export interface TutorToastOffset {
  x?: number;
  y?: number;
  mobile?: {
    x?: number;
    y?: number;
  };
  lg?: {
    x?: number;
    y?: number;
  };
}

export interface TutorToastAction {
  label: string;
  onClick: () => void;
  dismissOnClick?: boolean;
}

export interface TutorToastOptions {
  type?: TutorToastType;
  title?: string;
  description?: string;
  icon?: string | null;
  action?: TutorToastAction;
  duration?: number;
  progressBar?: boolean;
  closeButton?: boolean;
  dir?: 'ltr' | 'rtl';
  richColors?: boolean;
  position?: TutorToastPosition;
}

export interface TutorToastConfig {
  position?: TutorToastPosition;
  duration?: number;
  closeButton?: boolean;
  progressBar?: boolean;
  maxVisible?: number;
  dir?: 'ltr' | 'rtl' | 'auto';
  offset?: TutorToastOffset;
  expandMode?: TutorToastExpandMode;
  richColors?: boolean;
}

export interface TutorToastUpdateOptions {
  type?: TutorToastType;
  title?: string;
  description?: string;
  icon?: string | null;
  duration?: number;
  progressBar?: boolean;
}

export interface TutorToastPromiseMessages<T = unknown> {
  loading: string | (() => string);
  success: string | ((result: T) => string);
  error: string | ((error: unknown) => string);
}

export interface TutorToastItem {
  id: string;
  title: string;
  description?: string;
  type: TutorToastType;
  duration: number;
}

export interface AlpineToastData {
  init(): void;
  show(message: string, config?: ToastConfig): string;
  remove(id: string): void;
  clear(): void;
  dismiss(id?: string): void;
  success(message: string, duration?: number): string;
  error(message: string, duration?: number): string;
  warning(message: string, duration?: number): string;
  info(message: string, duration?: number): string;
}

export type ToastType = Extract<TutorToastType, 'info' | 'success' | 'warning' | 'error'>;
export type ToastConfig = Pick<TutorToastOptions, 'type' | 'duration' | 'title' | 'description'> &
  Pick<TutorToastConfig, 'position'>;
export type ToastItem = TutorToastItem;
