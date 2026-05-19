import { type OptionalTutorCorePackName, type TutorCorePackName } from '@Core/ts/packs/types';
import { chainRoutePreload, requestCorePacks } from './core-packs';

export type TutorRouteConfig<TModule> = {
  packs: TutorCorePackName[];
  load: () => Promise<TModule>;
};

type RegisterRoutePreloadArgs<TModule> = {
  routeConfig?: TutorRouteConfig<TModule>;
  beforeLoad?: () => void | Promise<void>;
  initializeRoute: (routeModule: TModule) => void | Promise<void>;
  defaultPacks?: TutorCorePackName[];
};

export const withBasePack = (...packs: OptionalTutorCorePackName[]): TutorCorePackName[] => {
  return Array.from(new Set<TutorCorePackName>(['core-base', ...packs]));
};

export const createRouteConfig = <TModule>(
  packs: TutorCorePackName[],
  load: () => Promise<TModule>,
): TutorRouteConfig<TModule> => {
  return {
    packs,
    load,
  };
};

export const registerRoutePreload = <TModule>({
  routeConfig,
  beforeLoad,
  initializeRoute,
  defaultPacks = ['core-base'],
}: RegisterRoutePreloadArgs<TModule>): Promise<void> => {
  const preloadedRouteModule = routeConfig ? routeConfig.load() : null;

  const preloadRoute = async () => {
    await beforeLoad?.();

    if (!preloadedRouteModule) {
      return;
    }

    const routeModule = await preloadedRouteModule;
    await initializeRoute(routeModule);
  };

  const corePackPreload = requestCorePacks(routeConfig?.packs || defaultPacks);

  return chainRoutePreload(corePackPreload, preloadRoute()).catch(() => undefined);
};
