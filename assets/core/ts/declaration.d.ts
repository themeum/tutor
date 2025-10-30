import { type Alpine as AlpineType } from 'alpinejs';

import { type TutorCore } from '@Core/@types';
import { type TutorComponentRegistry } from '@Core/ComponentRegistry';

declare global {
  interface Window {
    Alpine: AlpineType;
    TutorComponentRegistry: typeof TutorComponentRegistry;
    TutorCore: TutorCore;
  }
}
