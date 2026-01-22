import { TUTOR_CUSTOM_EVENTS } from '@Core/ts/constant';
import { type AlpineComponentMeta } from '@Core/ts/types';

export interface ModalConfig {
  id: string;
  isCloseable?: boolean; // if true, the modal can be closed by clicking outside or pressing escape
}

const DEFAULT_CONFIG: ModalConfig = {
  id: 'tutor-modal',
  isCloseable: true,
};

export const modal = (config: ModalConfig = { ...DEFAULT_CONFIG }) => ({
  open: false,
  payload: null,
  isCloseable: config.isCloseable ?? DEFAULT_CONFIG.isCloseable,
  id: config.id,
  cleanup: undefined as (() => void) | undefined,
  $el: undefined as HTMLElement | undefined,

  init(): void {
    const onOpen = (event: CustomEvent) => {
      const targetId = event?.detail?.id as string | undefined;
      if (!targetId || targetId === this.id) {
        this.payload = event?.detail?.data ?? null;

        this.show();
      }
    };

    const onClose = (event: CustomEvent) => {
      const targetId = event?.detail?.id as string | undefined;
      if (!targetId || targetId === this.id) {
        this.close();
      }
    };

    document.addEventListener(TUTOR_CUSTOM_EVENTS.MODAL_OPEN, onOpen as EventListener);
    document.addEventListener(TUTOR_CUSTOM_EVENTS.MODAL_CLOSE, onClose as EventListener);

    this.cleanup = () => {
      document.removeEventListener(TUTOR_CUSTOM_EVENTS.MODAL_OPEN, onOpen as EventListener);
      document.removeEventListener(TUTOR_CUSTOM_EVENTS.MODAL_CLOSE, onClose as EventListener);
    };
  },

  destroy(): void {
    this.cleanup?.();
  },

  show(): void {
    this.open = true;
  },

  close(): void {
    this.open = false;
    this.payload = null;
  },

  getBackdropBindings() {
    const backdrop = this.$el as HTMLElement;
    backdrop.classList.add('tutor-modal-backdrop');

    return {
      'x-show': 'open',
      'x-transition:enter': 'tutor-modal-backdrop-enter',
      'x-transition:enter-start': 'tutor-modal-backdrop-transition',
      'x-transition:enter-end': 'tutor-modal-backdrop-transition-reset',
      'x-transition:leave': 'tutor-modal-backdrop-leave',
      'x-transition:leave-start': 'tutor-modal-backdrop-transition-reset',
      'x-transition:leave-end': 'tutor-modal-backdrop-transition',
    };
  },

  getModalBindings() {
    const modal = this.$el as HTMLElement;
    modal.classList.add('tutor-modal');

    return {
      'x-show': 'open',
      '@keydown.escape.window': this.isCloseable ? 'close()' : '',
      role: 'dialog',
      'aria-modal': 'true',
    };
  },

  getModalContentBindings() {
    const modal = this.$el as HTMLElement;
    modal.classList.add('tutor-modal-content');

    return {
      'x-trap.noscroll.inert.noautofocus': 'open',
      '@click.outside': this.isCloseable ? 'close()' : '',
      'x-show': 'open',
      'x-transition:enter': 'tutor-modal-content-enter',
      'x-transition:enter-start': 'tutor-modal-content-transition',
      'x-transition:enter-end': 'tutor-modal-content-transition-reset',
      'x-transition:leave': 'tutor-modal-content-leave',
      'x-transition:leave-start': 'tutor-modal-content-transition-reset',
      'x-transition:leave-end': 'tutor-modal-content-transition',
    };
  },

  getCloseButtonBindings() {
    const closeButton = this.$el as HTMLElement;
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
