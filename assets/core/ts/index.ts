import Alpine from 'alpinejs';

import { TutorComponentRegistry } from '@Core/ComponentRegistry';

import { buttonMeta } from '@Core/components/button';
import { fileUploaderMeta } from '@Core/components/file-uploader';
import { iconMeta } from '@Core/components/icon';
import { tabsMeta } from '@Core/components/tabs';
import { accordionMeta } from '@Core/components/accordion';
import { selectDropdownMeta } from './components/select-dropdown';

const initializePlugin = () => {
  TutorComponentRegistry.registerAll([
    buttonMeta,
    fileUploaderMeta,
    tabsMeta,
    iconMeta,
    accordionMeta,
    selectDropdownMeta,
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
