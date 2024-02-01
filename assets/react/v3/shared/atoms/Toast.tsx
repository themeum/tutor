import { borderRadius, colorPalate, spacing, zIndex } from '@Config/styles';
import { typography } from '@Config/typography';
import { css } from '@emotion/react';
import { AnimatedDiv } from '@Hooks/useAnimation';
import { useTransition } from '@react-spring/web';
import { isBoolean } from '@Utils/types';
import { nanoid } from '@Utils/util';
import React, { ReactNode, useState, useCallback, useContext } from 'react';

import Button from './Button';
import SVGIcon from './SVGIcon';

type Position = 'top-left' | 'top-right' | 'top-center' | 'bottom-left' | 'bottom-right' | 'bottom-center';

interface ToastOption {
  type: 'success' | 'dark' | 'danger' | 'warning';
  message: string;
  id?: string;
  autoCloseDelay?: boolean | number;
  title?: string;
  position?: Position;
}

const defaultToastOption: ToastOption = {
  type: 'dark',
  message: '',
  autoCloseDelay: 3000,
  position: 'bottom-right',
};

interface ToastContextProps {
  showToast: (option: ToastOption) => void;
}

const ToastContext = React.createContext<ToastContextProps>({
  showToast: () => {},
});

export const useToast = () => useContext(ToastContext);

const ToastProvider = ({ children, position = 'bottom-right' }: { children: ReactNode; position?: Position }) => {
  const [toastList, setToastList] = useState<ToastOption[]>([]);

  const transitions = useTransition(toastList, {
    from: {
      opacity: 0,
      y: -40,
    },
    enter: {
      opacity: 1,
      y: 0,
    },
    leave: {
      opacity: 0.5,
      y: 100,
    },
    config: {
      duration: 300,
    },
  });

  const showToast = useCallback<ToastContextProps['showToast']>((option) => {
    const toastOption = { ...defaultToastOption, ...option, id: nanoid() };
    setToastList((prev) => [toastOption, ...prev]);
    let timeout = 0;

    if (!isBoolean(toastOption.autoCloseDelay) && toastOption.autoCloseDelay) {
      timeout = setTimeout(() => {
        setToastList((prev) => prev.slice(0, -1));
      }, toastOption.autoCloseDelay);
    }

    return () => {
      clearTimeout(timeout);
    };
  }, []);

  return (
    <ToastContext.Provider value={{ showToast }}>
      {children}
      <div css={styles.toastWrapper(position)}>
        {transitions((style, toast) => {
          return (
            <AnimatedDiv style={style} key={toast.id} css={styles.toastItem(toast.type)}>
              <h5 css={styles.message}>{toast.message}</h5>

              <Button
                variant="text"
                onClick={() => {
                  setToastList((prev) => prev.filter((item) => item.id !== toast.id));
                }}
              >
                <SVGIcon name="timesAlt" width={16} height={16} />
              </Button>
            </AnimatedDiv>
          );
        })}
      </div>
    </ToastContext.Provider>
  );
};

export default ToastProvider;

const styles = {
  toastWrapper: (position: Position) => css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[16]};
    max-width: 400px;
    position: fixed;
    z-index: ${zIndex.highest};

    ${position === 'top-left' &&
    css`
      left: ${spacing[20]};
      top: calc(${spacing[20]} + 60px);
    `}
    ${position === 'top-right' &&
    css`
      right: ${spacing[20]};
      top: calc(${spacing[20]} + 60px);
    `}
    ${position === 'top-center' &&
    css`
      left: 50%;
      top: calc(${spacing[20]} + 60px);
      transform: translateX(-50%);
    `}
    ${position === 'bottom-left' &&
    css`
      left: ${spacing[20]};
      bottom: ${spacing[20]};
    `}
    ${position === 'bottom-right' &&
    css`
      right: ${spacing[20]};
      bottom: ${spacing[20]};
    `}
    ${position === 'bottom-center' &&
    css`
      left: 50%;
      bottom: ${spacing[20]};
      transform: translateX(-50%);
    `}
  `,
  toastItem: (type: ToastOption['type']) => css`
    width: 100%;
    min-height: 60px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: ${spacing[16]};
    border-radius: ${borderRadius[6]};
    padding: ${spacing[16]};

    svg > path {
      color: ${colorPalate.basic.white};
    }

    ${type === 'dark' &&
    css`
      background: ${colorPalate.basic.onSurface};
    `}
    ${type === 'danger' &&
    css`
      background: ${colorPalate.basic.critical};
    `}
    ${type === 'success' &&
    css`
      background: ${colorPalate.basic.success.default};
    `}
    ${type === 'warning' &&
    css`
      background: ${colorPalate.basic.warning.default};

      h5 {
        color: ${colorPalate.text.default};
      }

      svg > path {
        color: ${colorPalate.text.default};
      }
    `}
  `,
  message: css`
    ${typography.heading5()};
    color: ${colorPalate.basic.white};
  `,
  timesIcon: css`
    path {
      color: ${colorPalate.basic.white};
    }
  `,
};
