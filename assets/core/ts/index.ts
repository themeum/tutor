import collapse from '@alpinejs/collapse';
import focus from '@alpinejs/focus';
import Alpine from 'alpinejs';

import { TutorComponentRegistry } from '@Core/ts/ComponentRegistry';

import { accordionMeta } from '@Core/ts/components/accordion';
import { buttonMeta } from '@Core/ts/components/button';
import { calendarMeta } from '@Core/ts/components/calendar';
import { commentMeta } from '@Core/ts/components/comments';
import { copyToClipboardMeta } from '@Core/ts/components/copy-to-clipboard';
import { fileUploaderMeta } from '@Core/ts/components/file-uploader';
import { formMeta } from '@Core/ts/components/form';
import { iconMeta } from '@Core/ts/components/icon';
import { modalMeta } from '@Core/ts/components/modal';
import { passwordInputMeta } from '@Core/ts/components/password-input';
import { popoverMeta } from '@Core/ts/components/popover';
import { previewTriggerMeta } from '@Core/ts/components/preview-trigger';
import { selectMeta } from '@Core/ts/components/select';
import { selectDropdownMeta } from '@Core/ts/components/select-dropdown';
import { starRatingMeta } from '@Core/ts/components/star-rating';
import { staticsMeta } from '@Core/ts/components/statics';
import { stepperDropdownMeta } from '@Core/ts/components/stepper-dropdown';
import { tabsMeta } from '@Core/ts/components/tabs';
import { toastMeta } from '@Core/ts/components/toast';

import { formServiceMeta } from '@Core/ts/services/Form';
import { modalServiceMeta } from '@Core/ts/services/Modal';
import { queryServiceMeta } from '@Core/ts/services/Query';
import { toastServiceMeta } from '@Core/ts/services/Toast';
import { wpMediaServiceMeta } from '@Core/ts/services/WPMedia';

import { registerLegacyFunctions } from '@Core/ts/legacy';
import { getNonceData } from '@Core/ts/utils/nonce';
import { escapeAttr, escapeHtml } from '@Core/ts/utils/security';

Alpine.plugin(focus);
Alpine.plugin(collapse);

const initializePlugin = () => {
  TutorComponentRegistry.registerAll({
    components: [
      buttonMeta,
      calendarMeta,
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
      starRatingMeta,
      toastMeta,
      passwordInputMeta,
      copyToClipboardMeta,
    ],
    services: [formServiceMeta, modalServiceMeta, queryServiceMeta, toastServiceMeta, wpMediaServiceMeta],
  });

  TutorComponentRegistry.initWithAlpine(Alpine);

  window.TutorComponentRegistry = TutorComponentRegistry;
  window.Alpine = Alpine;

  // Expose TutorCore with services and utilities
  // Use Object.assign to extend existing TutorCore instead of overwriting
  window.TutorCore = Object.assign(window.TutorCore || {}, {
    toast: toastServiceMeta.instance,
    security: {
      escapeHtml,
      escapeAttr,
    },
    nonce: {
      getNonceData,
    },
  });

  // Register legacy functions for backward compatibility
  // This should be called AFTER TutorCore is set up
  registerLegacyFunctions();

  Alpine.start();
};

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initializePlugin);
} else {
  initializePlugin();
}

export { Alpine, TutorComponentRegistry };
