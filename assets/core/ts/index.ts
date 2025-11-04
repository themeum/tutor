import focus from '@alpinejs/focus';
import Alpine from 'alpinejs';

import { TutorComponentRegistry } from '@Core/ComponentRegistry';

import { buttonMeta } from '@Core/components/button';
import { fileUploaderMeta } from '@Core/components/file-uploader';
import { iconMeta } from '@Core/components/icon';
import { modalMeta } from '@Core/components/modal';
import { tabsMeta } from '@Core/components/tabs';
import { ModalService } from '@Core/services/Modal';

Alpine.plugin(focus);
import { accordionMeta } from '@Core/components/accordion';

const initializePlugin = () => {
  TutorComponentRegistry.registerAll([buttonMeta, fileUploaderMeta, tabsMeta, iconMeta, modalMeta, accordionMeta]);

  TutorComponentRegistry.initWithAlpine(Alpine);

  window.TutorComponentRegistry = TutorComponentRegistry;
  window.Alpine = Alpine;

  // Expose ModalService to TutorCore using the registry lifecycle
  const modalService = new ModalService();
  // Attach to global TutorCore without polluting component registration
  const TutorCore = (window as unknown as { TutorCore: Record<string, unknown> }).TutorCore || {};
  TutorCore.modal = modalService;
  (window as unknown as { TutorCore: Record<string, unknown> }).TutorCore = TutorCore;

  Alpine.start();
};

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initializePlugin);
} else {
  initializePlugin();
}

export { Alpine, TutorComponentRegistry };
