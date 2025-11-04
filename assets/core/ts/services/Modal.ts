import { TUTOR_CUSTOM_EVENTS } from '@Core/constant';

/**
 * ModalService: single programmatic API for opening/closing/updating modals.
 * Emits DOM CustomEvents consumed by Alpine modal instances.
 */
export class ModalService {
  showModal(id?: string | null): void {
    document.dispatchEvent(new CustomEvent(TUTOR_CUSTOM_EVENTS.MODAL_OPEN, { detail: { id } }));
  }

  /** Close a modal by optional `id`. If omitted, the active modal closes. */
  closeModal(id?: string | null): void {
    document.dispatchEvent(new CustomEvent(TUTOR_CUSTOM_EVENTS.MODAL_CLOSE, { detail: { id } }));
  }
}
