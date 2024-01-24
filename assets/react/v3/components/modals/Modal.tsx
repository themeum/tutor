/* eslint-disable jsx-a11y/no-static-element-interactions */
/* eslint-disable jsx-a11y/click-events-have-key-events */
import { colorPalate, zIndex } from '@Config/styles';
import { css } from '@emotion/react';
import { AnimatedDiv, AnimationType, useAnimation } from '@Hooks/useAnimation';
import { nanoid, noop } from '@Utils/util';
import React, { useCallback, useContext, useMemo, useState } from 'react';

const styles = {
  backdrop: css`
    position: fixed;
    background-color: ${colorPalate.basic.onSurface};
    opacity: 0.4;
    inset: 0;
    z-index: ${zIndex.negative};
  `,
  container: css`
    z-index: ${zIndex.modal};
    position: fixed;
    display: flex;
    justify-content: center;
    align-items: center;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
  `,
};

export type ModalProps = {
  closeModal: (param?: PromiseResolvePayload<'CLOSE'>) => void;
  title?: string;
};

type PromiseResolvePayload<A extends string = string> = { action: A; [key: string]: unknown };

type ModalContextType = {
  showModal<P extends ModalProps>(options: {
    component: React.FunctionComponent<P>;
    props?: Omit<P, 'closeModal'>;
    closeOnOutsideClick?: boolean;
  }): Promise<NonNullable<Parameters<P['closeModal']>[0]> | PromiseResolvePayload<'CLOSE'>>;
  closeModal(data?: PromiseResolvePayload): void;
  hasModalOnStack?: boolean;
};

const ModalContext = React.createContext<ModalContextType>({
  showModal: () => Promise.resolve({ action: 'CLOSE' }),
  closeModal: noop,
  hasModalOnStack: false,
});

export const useModal = () => useContext(ModalContext);

export const ModalProvider: React.FunctionComponent = ({ children }) => {
  const [state, setState] = useState<{
    modals: {
      id: string;
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      component: React.FunctionComponent<any>;
      props?: { [key: string]: unknown };
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      resolve: (data: PromiseResolvePayload<any>) => void;
      closeOnOutsideClick: boolean;
    }[];
  }>({
    modals: [],
  });

  const showModal = useCallback<ModalContextType['showModal']>(({ component, props, closeOnOutsideClick = false }) => {
    return new Promise((resolve) => {
      setState((prevState) => ({
        ...prevState,
        modals: [...prevState.modals, { component, props, resolve, closeOnOutsideClick, id: nanoid() }],
      }));
    });
  }, []);

  const closeModal = useCallback<ModalContextType['closeModal']>((data = { action: 'CLOSE' }) => {
    setState((prevState) => {
      const lastModal = prevState.modals[prevState.modals.length - 1];
      lastModal.resolve(data);
      return {
        ...prevState,
        modals: prevState.modals.slice(0, prevState.modals.length - 1),
      };
    });
  }, []);

  const { transitions } = useAnimation({
    data: state.modals,
    animationType: AnimationType.zoomIn,
    animationDuration: 250,
  });

  const hasModalOnStack = useMemo(() => {
    return state.modals.length > 0;
  }, [state.modals]);

  return (
    <ModalContext.Provider value={{ showModal, closeModal, hasModalOnStack }}>
      {children}
      {transitions((style, modal) => {
        return (
          <div css={styles.container}>
            <AnimatedDiv style={style} hideOnOverflow={false}>
              {React.createElement(modal.component, { ...modal.props, closeModal })}
            </AnimatedDiv>
            <div
              css={styles.backdrop}
              // This is not ideal to attach a click event on a non-interactive element like div,
              // but in this case we have to do it.
              onClick={() => {
                if (modal.closeOnOutsideClick) {
                  closeModal({ action: 'CLOSE' });
                }
              }}
            />
          </div>
        );
      })}
    </ModalContext.Provider>
  );
};
