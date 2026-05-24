import { type QueryService, type QueryState, queryServiceMeta } from '@Core/ts/services/Query';
import Alpine from 'alpinejs';

import { type ServiceMeta } from '@Core/ts/types';
import { wpGet } from '@Core/ts/utils/api';
import { tutorConfig } from '@TutorShared/config/config';
import endpoints from '@TutorShared/utils/endpoints';

interface Country {
  name: string;
  states: { id: number; name: string }[];
}

class LocationService {
  /**
   * 24 hours in milliseconds
   */
  private readonly STALE_TIME = 1000 * 60 * 60 * 24;

  constructor() {
    this.initStore();
  }

  private initStore() {
    const query = queryServiceMeta.instance as QueryService;

    Alpine.store('tutorLocation', {
      fetchCountriesQuery: query.useQuery(
        'fetch-countries',
        async () => wpGet<Country[]>(`${tutorConfig.tutor_url}${endpoints.FETCH_COUNTRIES}`),
        {
          staleTime: this.STALE_TIME,
          cacheTime: this.STALE_TIME,
        },
      ) as QueryState<Country[]>,
    });
  }
}

export const locationServiceMeta: ServiceMeta = {
  name: 'location',
  instance: new LocationService(),
};
