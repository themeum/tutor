import { type Alpine as AlpineType } from 'alpinejs';

import { type TutorComponentRegistry } from '@Core/ts/ComponentRegistry';
import { type TutorCore } from '@Core/ts/types';

declare global {
  interface Window {
    Alpine: AlpineType;
    TutorComponentRegistry: typeof TutorComponentRegistry;
    TutorCore: TutorCore;
  }
}

declare const __TUTOR_TEXT_DOMAIN__: string;
