import Alpine from 'alpinejs';

import { TutorComponentRegistry } from '@Core/ComponentRegistry';

import { buttonMeta } from '@Core/components/button';
import { fileUploaderMeta } from '@Core/components/file-uploader';

const initializePlugin = () => {
  TutorComponentRegistry.registerAll([buttonMeta, fileUploaderMeta]);

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
