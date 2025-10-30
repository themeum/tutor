// Modal Component
// Alpine.js modal with focus management and accessibility

import { type AlpineModalData, type ModalConfig } from '../types/components';

export function createModal(config: ModalConfig = {}): AlpineModalData {
  return {
    open: false,
    previousFocus: null as HTMLElement | null,
    $el: undefined as HTMLElement | undefined,
    $nextTick: undefined as ((callback: () => void) => void) | undefined,

    show(): void {
      this.previousFocus = document.activeElement as HTMLElement;
      this.open = true;
      document.body.style.overflow = 'hidden';
      if (this.$nextTick) {
        this.$nextTick(() => {
          this.trapFocus();
        });
      } else {
        setTimeout(() => this.trapFocus(), 0);
      }
    },

    hide(): void {
      this.open = false;
      document.body.style.overflow = '';
      this.releaseFocus();
    },

    handleKeydown(event: KeyboardEvent): void {
      if (event.key === 'Escape' && config.keyboard !== false) {
        this.hide();
      }
    },

    handleBackdropClick(): void {
      if (config.backdrop !== false) {
        this.hide();
      }
    },

    trapFocus(): void {
      if (!this.$el) return;

      const modal = this.$el.querySelector('.tutor-modal__content');
      if (!modal) return;

      const focusableElements = modal.querySelectorAll(
        'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])',
      );

      if (focusableElements.length > 0) {
        (focusableElements[0] as HTMLElement).focus();
      }
    },

    handleTabKey(event: KeyboardEvent): void {
      if (!this.$el) return;

      const modal = this.$el.querySelector('.tutor-modal__content');
      if (!modal) return;

      const focusableElements = modal.querySelectorAll(
        'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])',
      ) as NodeListOf<HTMLElement>;

      if (focusableElements.length === 0) return;

      const firstElement = focusableElements[0];
      const lastElement = focusableElements[focusableElements.length - 1];

      if (event.shiftKey) {
        if (document.activeElement === firstElement) {
          lastElement.focus();
          event.preventDefault();
        }
      } else {
        if (document.activeElement === lastElement) {
          firstElement.focus();
          event.preventDefault();
        }
      }
    },

    releaseFocus(): void {
      if (this.previousFocus) {
        this.previousFocus.focus();
        this.previousFocus = null;
      }
    },
  };
}
