import { TUTOR_CUSTOM_EVENTS } from '@Core/ts/constant';
import { type ServiceMeta } from '@Core/ts/types';

/**
 * ModalService: programmatic API for opening/closing/updating modals.
 * Emits DOM CustomEvents consumed by Alpine modal instances.
 */
export class ModalService {
  showModal(id?: string | null, data?: unknown): void {
    document.dispatchEvent(
      new CustomEvent(TUTOR_CUSTOM_EVENTS.MODAL_OPEN, {
        detail: { id, data },
      }),
    );
  }

  updateModal(id: string, data: unknown): void {
    document.dispatchEvent(
      new CustomEvent(TUTOR_CUSTOM_EVENTS.MODAL_UPDATE, {
        detail: { id, data },
      }),
    );
  }

  /** Close a modal by optional `id`. If omitted, the active modal closes. */
  closeModal(id?: string | null): void {
    document.dispatchEvent(
      new CustomEvent(TUTOR_CUSTOM_EVENTS.MODAL_CLOSE, {
        detail: { id },
      }),
    );
  }
}

export const modalServiceMeta: ServiceMeta = {
  name: 'modal',
  instance: new ModalService(),
};
