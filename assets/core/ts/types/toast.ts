export type TutorToastType = 'success' | 'error' | 'warning' | 'info' | 'default';

export type TutorToastPosition =
  | 'top-left'
  | 'top-center'
  | 'top-right'
  | 'bottom-left'
  | 'bottom-center'
  | 'bottom-right';

export type TutorToastExpandMode = 'hover' | 'always' | 'never';

export type TutorToastTheme = 'light' | 'dark' | 'auto';

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
  closeButton?: boolean;
  dir?: 'ltr' | 'rtl' | 'auto';
  richColors?: boolean;
  position?: TutorToastPosition;
  theme?: TutorToastTheme;
}

export interface TutorToastConfig {
  position?: TutorToastPosition;
  duration?: number;
  closeButton?: boolean;
  maxVisible?: number;
  dir?: 'ltr' | 'rtl' | 'auto';
  offset?: TutorToastOffset;
  expandMode?: TutorToastExpandMode;
  richColors?: boolean;
  theme?: TutorToastTheme;
}

export interface TutorToastItem {
  id: string;
  title: string;
  description?: string;
  type: TutorToastType;
  duration: number;
}

export type ToastType = Extract<TutorToastType, 'info' | 'success' | 'warning' | 'error'>;
export type ToastConfig = Pick<TutorToastOptions, 'type' | 'duration' | 'title' | 'description'> &
  Pick<TutorToastConfig, 'position'>;
export type ToastItem = TutorToastItem;
