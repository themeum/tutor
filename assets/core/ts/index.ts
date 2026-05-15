import collapse from '@alpinejs/collapse';
import focus from '@alpinejs/focus';
import Alpine from 'alpinejs';

import { TutorComponentRegistry } from '@Core/ts/ComponentRegistry';
import { registerCoreBasePack } from '@Core/ts/packs/base';
import { type OptionalTutorCorePackName, type TutorCorePackName } from '@Core/ts/packs/types';

import { registerLegacyFunctions } from '@Core/ts/legacy';
import { getNonceData } from '@Core/ts/utils/nonce';
import { escapeAttr, escapeHtml } from '@Core/ts/utils/security';

Alpine.plugin(focus);
Alpine.plugin(collapse);

window.TutorComponentRegistry = TutorComponentRegistry;
window.Alpine = Alpine;

type CorePackModule = {
  register: (registry: typeof TutorComponentRegistry) => void;
};

const optionalCorePackLoaders: Record<OptionalTutorCorePackName, () => Promise<CorePackModule>> = {
  'core-form-controls': async () => {
    const module = await import(/* webpackChunkName: "core-form-controls" */ '@Core/ts/packs/form-controls');
    return {
      register: module.registerCoreFormControlsPack,
    };
  },
  'core-media-editor': async () => {
    const module = await import(/* webpackChunkName: "core-media-editor" */ '@Core/ts/packs/media-editor');
    return {
      register: module.registerCoreMediaEditorPack,
    };
  },
  'core-learning': async () => {
    const module = await import(/* webpackChunkName: "core-learning" */ '@Core/ts/packs/learning');
    return {
      register: module.registerCoreLearningPack,
    };
  },
};

const corePackModulePromises = new Map<OptionalTutorCorePackName, Promise<CorePackModule>>();

const normalizeOptionalCorePacks = (packs: TutorCorePackName[]): OptionalTutorCorePackName[] => {
  const normalizedPacks = new Set<OptionalTutorCorePackName>();
  for (const pack of packs) {
    if (pack !== 'core-base') {
      normalizedPacks.add(pack);
    }
  }

  return Array.from(normalizedPacks);
};

const preloadOptionalCorePacks = (packs: TutorCorePackName[]): Promise<void> => {
  const normalizedPacks = normalizeOptionalCorePacks(packs);
  for (const pack of normalizedPacks) {
    if (!corePackModulePromises.has(pack)) {
      corePackModulePromises.set(pack, optionalCorePackLoaders[pack]());
    }
  }

  return Promise.all(normalizedPacks.map((pack) => corePackModulePromises.get(pack)!)).then(() => undefined);
};

const getRequestedCorePacks = (): OptionalTutorCorePackName[] => {
  return normalizeOptionalCorePacks(window.TutorRequestedCorePacks || []);
};

const registerOptionalCorePacks = async (): Promise<void> => {
  const requestedPacks = getRequestedCorePacks();
  const modules = await Promise.all(
    requestedPacks.map((pack) => corePackModulePromises.get(pack) || optionalCorePackLoaders[pack]()),
  );

  for (const module of modules) {
    module.register(TutorComponentRegistry);
  }
};

window.TutorPreloadCorePacks = preloadOptionalCorePacks;

const initializePlugin = async () => {
  const preloadPromise = window.TutorRoutePreload;
  if (preloadPromise) {
    await preloadPromise;
  }

  registerCoreBasePack(TutorComponentRegistry);
  await registerOptionalCorePacks();

  TutorComponentRegistry.initWithAlpine(Alpine);

  // Expose TutorCore with services and utilities
  // Use Object.assign to extend existing TutorCore instead of overwriting
  window.TutorCore = Object.assign(window.TutorCore || {}, {
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
    void initializePlugin();
  });
} else {
  void initializePlugin();
}

export { Alpine, TutorComponentRegistry };
