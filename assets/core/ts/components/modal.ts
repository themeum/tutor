import { TUTOR_CUSTOM_EVENTS } from '@Core/constant';
import { type AlpineComponentMeta } from '@Core/types';

export interface ModalConfig {
  id: string;
  isCloseable?: boolean; // if true, the modal can be closed by clicking outside or pressing escape
}

const DEFAULT_CONFIG: ModalConfig = {
  id: 'tutor-modal',
  isCloseable: true,
};

export const modal = (config: ModalConfig = DEFAULT_CONFIG) => ({
  open: false,
  isCloseable: config.isCloseable ?? DEFAULT_CONFIG.isCloseable,
  __id: undefined as string | undefined,
  __cleanup: undefined as (() => void) | undefined,

  init(): void {
    const cfgId = config.id;
    this.__id = cfgId;

    const onOpen = (event: CustomEvent) => {
      const targetId = event?.detail?.id as string | undefined;
      if (!this.__id || !targetId || targetId === this.__id) {
        this.show();
      }
    };

    const onClose = (event: CustomEvent) => {
      const targetId = event?.detail?.id as string | undefined;
      if (!this.__id || !targetId || targetId === this.__id) {
        this.close();
      }
    };

    document.addEventListener(TUTOR_CUSTOM_EVENTS.MODAL_OPEN, onOpen as EventListener);
    document.addEventListener(TUTOR_CUSTOM_EVENTS.MODAL_CLOSE, onClose as EventListener);

    this.__cleanup = () => {
      document.removeEventListener(TUTOR_CUSTOM_EVENTS.MODAL_OPEN, onOpen as EventListener);
      document.removeEventListener(TUTOR_CUSTOM_EVENTS.MODAL_CLOSE, onClose as EventListener);
    };
  },

  show(): void {
    this.open = true;
  },

  close(): void {
    this.open = false;
  },

  setBackdropAttributes() {
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    const backdrop = (this as any).$el;
    backdrop.classList.add('tutor-modal-backdrop');

    return {
      'x-show': 'open',
      'x-transition:enter': 'tutor-fade-enter',
      'x-transition:enter-start': 'tutor-fade-enter-start',
      'x-transition:enter-end': 'tutor-fade-enter-end',
      'x-transition:leave': 'tutor-fade-leave',
      'x-transition:leave-start': 'tutor-fade-leave-start',
      'x-transition:leave-end': 'tutor-fade-leave-end',
    };
  },

  setModalAttributes() {
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    const modal = (this as any).$el;
    modal.classList.add('tutor-modal');

    return {
      'x-show': 'open',
      '@keydown.escape.window': this.isCloseable ? 'close()' : '',
    };
  },

  setModalContentAttributes() {
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    const modal = (this as any).$el;
    modal.classList.add('tutor-modal-content');

    return {
      'x-trap.noscroll.inert.noautofocus': 'open',
      '@click.outside': this.isCloseable ? 'close()' : '',
      'x-show': 'open',
      'x-transition:enter': 'tutor-enter',
      'x-transition:enter-start': 'tutor-enter-start',
      'x-transition:enter-end': 'tutor-enter-end',
      'x-transition:leave': 'tutor-leave',
      'x-transition:leave-start': 'tutor-leave-start',
      'x-transition:leave-end': 'tutor-leave-end',
    };
  },

  async setCloseButtonAttributes() {
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    const closeButton = (this as any).$el;
    closeButton.classList.add('tutor-modal-close');

    return {
      'x-show': 'open',
      '@click': 'close()',
    };
  },
});

export const modalMeta: AlpineComponentMeta<ModalConfig> = {
  name: 'modal',
  component: modal,
};
