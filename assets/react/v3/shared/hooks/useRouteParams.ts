import type { RouteConfig } from '@Config/route-configs';
import { useParams } from 'react-router-dom';

export const useRouteParams = <T extends (typeof RouteConfig)[keyof typeof RouteConfig]>(routeConfig: T) => {
	type UrlParams = Parameters<(typeof routeConfig)['buildLink']>[0];
	return useParams() as unknown as UrlParams extends Record<string, string> ? UrlParams : Record<string, never>;
};
