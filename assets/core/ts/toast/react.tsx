import React, { useContext, useMemo, type PropsWithChildren } from 'react';

import {
  type TutorToastConfig,
  type TutorToastOptions,
  type TutorToastPosition,
  type TutorToastTheme,
} from '../types/toast';
import { toast, tutorToastDefaults, tutorToastManager, type TutorToastApi } from './runtime';

type ReactToastType = 'success' | 'dark' | 'danger' | 'warning' | 'info';

export interface ToastOption {
  type: ReactToastType;
  message: string;
  id?: string;
  autoCloseDelay?: boolean | number;
  title?: string;
  position?: TutorToastPosition;
}

interface ToastContextProps {
  toast: TutorToastApi;
  showToast: (option: ToastOption) => string;
}

const providerDefaults: TutorToastConfig = {
  ...tutorToastDefaults,
  position: 'bottom-right',
};

const ToastContext = React.createContext<ToastContextProps>({
  toast,
  showToast: (option) => toast(option.message),
});

export const useToast = () => useContext(ToastContext);

const normalizeType = (type: ReactToastType): TutorToastOptions['type'] => {
  if (type === 'danger') {
    return 'error';
  }

  if (type === 'dark') {
    return 'default';
  }

  return type;
};

const normalizeDuration = (autoCloseDelay?: boolean | number): number | undefined => {
  if (autoCloseDelay === false) {
    return 0;
  }

  if (typeof autoCloseDelay === 'number') {
    return autoCloseDelay;
  }

  return undefined;
};

const ToastProvider = ({
  children,
  position = 'bottom-right',
  theme = 'light',
}: PropsWithChildren<{ position?: TutorToastPosition; theme?: TutorToastTheme }>) => {
  const config = useMemo<TutorToastConfig>(
    () => ({
      ...providerDefaults,
      position,
      theme,
    }),
    [position, theme],
  );

  const contextToast = useMemo(() => tutorToastManager.createContextBound(config), [config]);

  const value = useMemo<ToastContextProps>(
    () => ({
      toast: contextToast,
      showToast: (option) => {
        const type = normalizeType(option.type);
        const duration = normalizeDuration(option.autoCloseDelay);

        return contextToast(option.message, {
          type,
          title: option.title,
          description: option.title ? option.message : undefined,
          ...(duration !== undefined ? { duration } : {}),
          ...(option.position ? { position: option.position } : {}),
        });
      },
    }),
    [contextToast],
  );

  return <ToastContext.Provider value={value}>{children}</ToastContext.Provider>;
};

export default ToastProvider;
