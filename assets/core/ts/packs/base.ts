import { type TutorComponentRegistry } from '@Core/ts/ComponentRegistry';
import { accordionMeta } from '@Core/ts/components/accordion';
import { buttonMeta } from '@Core/ts/components/button';
import { copyToClipboardMeta } from '@Core/ts/components/copy-to-clipboard';
import { formMeta } from '@Core/ts/components/form';
import { iconMeta } from '@Core/ts/components/icon';
import { modalMeta } from '@Core/ts/components/modal';
import { passwordInputMeta } from '@Core/ts/components/password-input';
import { popoverMeta } from '@Core/ts/components/popover';
import { previewTriggerMeta } from '@Core/ts/components/preview-trigger';
import { readMoreMeta } from '@Core/ts/components/read-more';
import { staticsMeta } from '@Core/ts/components/statics';
import { tabsMeta } from '@Core/ts/components/tabs';
import { toastMeta } from '@Core/ts/components/toast';
import { tooltipMeta } from '@Core/ts/components/tooltip';
import { formServiceMeta } from '@Core/ts/services/Form';
import { modalServiceMeta } from '@Core/ts/services/Modal';
import { preferenceServiceMeta } from '@Core/ts/services/Preference';
import { queryServiceMeta } from '@Core/ts/services/Query';
import { toastServiceMeta } from '@Core/ts/services/Toast';

export const registerCoreBasePack = (registry: typeof TutorComponentRegistry): void => {
  registry.registerAll({
    components: [
      buttonMeta,
      tabsMeta,
      iconMeta,
      modalMeta,
      popoverMeta,
      staticsMeta,
      accordionMeta,
      formMeta,
      tooltipMeta,
      toastMeta,
      readMoreMeta,
      passwordInputMeta,
      previewTriggerMeta,
      copyToClipboardMeta,
    ],
    services: [formServiceMeta, modalServiceMeta, queryServiceMeta, toastServiceMeta, preferenceServiceMeta],
  });
};
