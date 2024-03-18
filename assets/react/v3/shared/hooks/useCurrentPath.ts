import { isDefined } from '@Utils/types';
import { type RouteObject, matchRoutes, useLocation } from 'react-router-dom';

export const useCurrentPath = (routes: RouteObject[]) => {
	const location = useLocation();
	const routeMatches = matchRoutes(routes, location);

	if (!isDefined(routeMatches)) {
		return location.pathname;
	}

	const route = routeMatches.find((item) => item.pathname === location.pathname);
	return route?.route.path || '';
};
