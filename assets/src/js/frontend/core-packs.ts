import { type TutorCorePackName } from '@Core/ts/packs/types';

export const requestCorePacks = (packs: TutorCorePackName[]): Promise<void> => {
  const requestedPacks = new Set(window.TutorRequestedCorePacks || []);
  for (const pack of packs) {
    requestedPacks.add(pack);
  }

  window.TutorRequestedCorePacks = Array.from(requestedPacks);

  return window.TutorPreloadCorePacks ? window.TutorPreloadCorePacks(packs) : Promise.resolve();
};

export const chainRoutePreload = (...promises: Promise<unknown>[]): Promise<void> => {
  window.TutorRoutePreload = Promise.all([window.TutorRoutePreload, ...promises]).then(() => undefined);
  return window.TutorRoutePreload;
};
