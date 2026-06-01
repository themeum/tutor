import collapse from '@alpinejs/collapse';
import focus from '@alpinejs/focus';
import Alpine from 'alpinejs';

import { TutorComponentRegistry } from '@Core/ts/ComponentRegistry';

import { accordionMeta } from '@Core/ts/components/accordion';
import { buttonMeta } from '@Core/ts/components/button';
import { copyToClipboardMeta } from '@Core/ts/components/copy-to-clipboard';
import { iconMeta } from '@Core/ts/components/icon';
import { modalMeta } from '@Core/ts/components/modal';
import { passwordInputMeta } from '@Core/ts/components/password-input';
import { playerMeta } from '@Core/ts/components/player';
import { popoverMeta } from '@Core/ts/components/popover';
import { previewTriggerMeta } from '@Core/ts/components/preview-trigger';
import { readMoreMeta } from '@Core/ts/components/read-more';
import { starRatingMeta } from '@Core/ts/components/star-rating';
import { staticsMeta } from '@Core/ts/components/statics';
import { statusSelectMeta } from '@Core/ts/components/status-select';
import { tabsMeta } from '@Core/ts/components/tabs';
import { toastMeta } from '@Core/ts/components/toast';
import { tooltipMeta } from '@Core/ts/components/tooltip';
import { wpEditorMeta } from '@Core/ts/components/wp-editor';

import { formServiceMeta } from '@Core/ts/services/Form';
import { locationServiceMeta } from '@Core/ts/services/Location';
import { modalServiceMeta } from '@Core/ts/services/Modal';
import { preferenceServiceMeta } from '@Core/ts/services/Preference';
import { queryServiceMeta } from '@Core/ts/services/Query';
import { toastServiceMeta } from '@Core/ts/services/Toast';
import { wpMediaServiceMeta } from '@Core/ts/services/WPMedia';

import { registerLegacyFunctions } from '@Core/ts/legacy';
import { getRequiredComponents } from '@Core/ts/utils/component-discovery';
import { getNonceData } from '@Core/ts/utils/nonce';
import { escapeAttr, escapeHtml } from '@Core/ts/utils/security';

Alpine.plugin(focus);
Alpine.plugin(collapse);

const initializePlugin = async () => {
  TutorComponentRegistry.registerAll({
    components: [
      buttonMeta,
      tabsMeta,
      iconMeta,
      modalMeta,
      popoverMeta,
      staticsMeta,
      accordionMeta,
      tooltipMeta,
      previewTriggerMeta,
      readMoreMeta,
      starRatingMeta,
      toastMeta,
      playerMeta,
      passwordInputMeta,
      copyToClipboardMeta,
      wpEditorMeta,
      statusSelectMeta,
    ],
    services: [
      formServiceMeta,
      locationServiceMeta,
      modalServiceMeta,
      queryServiceMeta,
      toastServiceMeta,
      wpMediaServiceMeta,
      preferenceServiceMeta,
    ],
  });

  TutorComponentRegistry.registerLazy({
    calendar: () =>
      import(
        /* webpackChunkName: "tutor-calendar" */
        '@Core/ts/components/calendar'
      ).then(({ calendarMeta }) => calendarMeta),

    form: () =>
      import(
        /* webpackChunkName: "tutor-form" */
        '@Core/ts/components/form'
      ).then(({ formMeta }) => formMeta),

    fileUploader: () =>
      import(
        /* webpackChunkName: "tutor-file-uploader" */
        '@Core/ts/components/file-uploader'
      ).then(({ fileUploaderMeta }) => fileUploaderMeta),

    select: () =>
      import(
        /* webpackChunkName: "tutor-select" */
        '@Core/ts/components/select'
      ).then(({ selectMeta }) => selectMeta),

    timeInput: () =>
      import(
        /* webpackChunkName: "tutor-time-input" */
        '@Core/ts/components/time-input'
      ).then(({ timeInputMeta }) => timeInputMeta),
  });

  await TutorComponentRegistry.loadComponents(getRequiredComponents());

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
  document.addEventListener('DOMContentLoaded', () => {
    initializePlugin();
  });
} else {
  initializePlugin();
}

export { Alpine, TutorComponentRegistry };
