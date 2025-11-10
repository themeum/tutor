import collapse from '@alpinejs/collapse';
import focus from '@alpinejs/focus';
import Alpine from 'alpinejs';

import { TutorComponentRegistry } from '@Core/ts/ComponentRegistry';

import { accordionMeta } from '@Core/ts/components/accordion';
import { buttonMeta } from '@Core/ts/components/button';
import { fileUploaderMeta } from '@Core/ts/components/file-uploader';
import { iconMeta } from '@Core/ts/components/icon';
import { modalMeta } from '@Core/ts/components/modal';
import { popoverMeta } from '@Core/ts/components/popover';
import { staticsMeta } from '@Core/ts/components/statics';
import { tabsMeta } from '@Core/ts/components/tabs';
import { modalServiceMeta } from '@Core/ts/services/Modal';
import { previewTriggerMeta } from './components/preview-trigger';
import { selectDropdownMeta } from './components/select-dropdown';
import { stepperDropdownMeta } from './components/stepper-dropdown';

Alpine.plugin(focus);
Alpine.plugin(collapse);

const initializePlugin = () => {
  TutorComponentRegistry.registerAll({
    components: [
      buttonMeta,
      fileUploaderMeta,
      tabsMeta,
      iconMeta,
      modalMeta,
      popoverMeta,
      staticsMeta,
      accordionMeta,
      selectDropdownMeta,
      stepperDropdownMeta,
      previewTriggerMeta,
    ],
    services: [modalServiceMeta],
  });

  TutorComponentRegistry.initWithAlpine(Alpine);

  window.TutorComponentRegistry = TutorComponentRegistry;
  window.Alpine = Alpine;

  Alpine.start();
};

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initializePlugin);
} else {
  initializePlugin();
}

export { Alpine, TutorComponentRegistry };
