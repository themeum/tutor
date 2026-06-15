import { matchRoutes, type RouteObject, useLocation } from 'react-router-dom';

import { isDefined } from '@TutorShared/utils/types';

export const useCurrentPath = (routes: RouteObject[]) => {
  const location = useLocation();
  const routeMatches = matchRoutes(routes, location);

  if (!isDefined(routeMatches)) {
    return location.pathname;
  }

  const route = routeMatches.find((item) => item.pathname === location.pathname);
  return route?.route.path || '';
};
