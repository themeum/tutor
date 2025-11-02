import { type Alpine as AlpineType } from 'alpinejs';

import { type TutorComponentRegistry } from '@Core/ComponentRegistry';
import { type TutorCore } from '@Core/types';

declare global {
  interface Window {
    Alpine: AlpineType;
    TutorComponentRegistry: typeof TutorComponentRegistry;
    TutorCore: TutorCore;
  }
}
