import Alpine from 'alpinejs';

import { TutorComponentRegistry } from '@Core/ComponentRegistry';

import { accordionMeta } from '@Core/components/accordion';
import { buttonMeta } from '@Core/components/button';
import { fileUploaderMeta } from '@Core/components/file-uploader';
import { iconMeta } from '@Core/components/icon';
import { popoverMeta } from '@Core/components/popover';
import { staticsMeta } from '@Core/components/statics';
import { tabsMeta } from '@Core/components/tabs';

const initializePlugin = () => {
  TutorComponentRegistry.registerAll([
    buttonMeta,
    fileUploaderMeta,
    tabsMeta,
    iconMeta,
    popoverMeta,
    staticsMeta,
    accordionMeta,
  ]);

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
