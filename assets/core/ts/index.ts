import Alpine from 'alpinejs';

import { TutorComponentRegistry } from '@Core/ComponentRegistry';

import { buttonMeta } from '@Core/components/button';
import { iconMeta } from '@Core/components/icon';
import { tabsMeta } from '@Core/components/tabs';
import { selectDropdownMeta } from '@Core/components/select-dropdown';

const initializePlugin = () => {
  TutorComponentRegistry.registerAll([buttonMeta, tabsMeta, iconMeta, selectDropdownMeta]);

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
