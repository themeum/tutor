import collapse from '@alpinejs/collapse';
import focus from '@alpinejs/focus';
import Alpine from 'alpinejs';

import { TutorComponentRegistry } from '@Core/ts/ComponentRegistry';

import { accordionMeta } from '@Core/ts/components/accordion';
import { buttonMeta } from '@Core/ts/components/button';
import { commentMeta } from '@Core/ts/components/comments';
import { fileUploaderMeta } from '@Core/ts/components/file-uploader';
import { formMeta } from '@Core/ts/components/form';
import { iconMeta } from '@Core/ts/components/icon';
import { modalMeta } from '@Core/ts/components/modal';
import { popoverMeta } from '@Core/ts/components/popover';
import { previewTriggerMeta } from '@Core/ts/components/preview-trigger';
import { selectMeta } from '@Core/ts/components/select';
import { selectDropdownMeta } from '@Core/ts/components/select-dropdown';
import { staticsMeta } from '@Core/ts/components/statics';
import { stepperDropdownMeta } from '@Core/ts/components/stepper-dropdown';
import { tabsMeta } from '@Core/ts/components/tabs';

import { formServiceMeta } from '@Core/ts/services/Form';
import { modalServiceMeta } from '@Core/ts/services/Modal';
import { queryServiceMeta } from '@Core/ts/services/Query';

Alpine.plugin(focus);
Alpine.plugin(collapse);

const initializePlugin = () => {
  TutorComponentRegistry.registerAll({
    components: [
      buttonMeta,
      commentMeta,
      fileUploaderMeta,
      tabsMeta,
      iconMeta,
      modalMeta,
      popoverMeta,
      staticsMeta,
      accordionMeta,
      formMeta,
      selectDropdownMeta,
      stepperDropdownMeta,
      selectMeta,
      previewTriggerMeta,
    ],
    services: [formServiceMeta, modalServiceMeta, queryServiceMeta],
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
