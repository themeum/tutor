import routes from '@App/routes';
import { isDefined } from '@Utils/types';
import { useLocation, matchRoutes } from 'react-router-dom';

export const useCurrentPath = () => {
  const location = useLocation();
  const routeMatches = matchRoutes(routes, location);

  if (!isDefined(routeMatches)) {
    return location.pathname;
  }

  const route = routeMatches.find((item) => item.pathname === location.pathname);
  return route?.route.path || '';
};
