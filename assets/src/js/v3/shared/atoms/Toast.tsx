import React, { type PropsWithChildren, useContext, useMemo } from 'react';

import {
  createTutorToastApi,
  tutorToastDefaults,
  tutorToastManager,
  type TutorToastApi,
} from '@Core/ts/services/Toast';
import { type TutorToastConfig, type TutorToastOptions, type TutorToastPosition } from '@Core/ts/types/toast';

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

const toastContextFallback = createTutorToastApi(
  (message: string, options?: TutorToastOptions) => tutorToastManager.show(message, options),
  tutorToastManager,
);

const ToastContext = React.createContext<ToastContextProps>({
  toast: toastContextFallback,
  showToast: (option) => toastContextFallback(option.message),
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
}: PropsWithChildren<{ position?: TutorToastPosition }>) => {
  const config = useMemo<TutorToastConfig>(
    () => ({
      ...providerDefaults,
      position,
    }),
    [position],
  );

  const toast = useMemo(() => tutorToastManager.createContextBound(config), [config]);

  const value = useMemo<ToastContextProps>(
    () => ({
      toast,
      showToast: (option) => {
        const type = normalizeType(option.type);
        const duration = normalizeDuration(option.autoCloseDelay);

        return toast(option.message, {
          type,
          title: option.title ?? option.message,
          description: option.title ? option.message : undefined,
          ...(duration !== undefined ? { duration } : {}),
          ...(option.position ? { position: option.position } : {}),
        });
      },
    }),
    [toast],
  );

  return <ToastContext.Provider value={value}>{children}</ToastContext.Provider>;
};

export default ToastProvider;
