import focus from '@alpinejs/focus';
import Alpine from 'alpinejs';

import { TutorComponentRegistry } from '@Core/ComponentRegistry';

import { accordionMeta } from '@Core/components/accordion';
import { buttonMeta } from '@Core/components/button';
import { fileUploaderMeta } from '@Core/components/file-uploader';
import { iconMeta } from '@Core/components/icon';
import { modalMeta } from '@Core/components/modal';
import { popoverMeta } from '@Core/components/popover';
import { staticsMeta } from '@Core/components/statics';
import { tabsMeta } from '@Core/components/tabs';
import { modalServiceMeta } from '@Core/services/Modal';
import { selectDropdownMeta } from './components/select-dropdown';
import { stepperDropdownMeta } from './components/stepper-dropdown';

Alpine.plugin(focus);

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
