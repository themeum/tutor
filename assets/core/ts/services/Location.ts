import Alpine from 'alpinejs';
import axios from 'axios';
import { type QueryService, type QueryState, queryServiceMeta } from '@Core/ts/services/Query';
import { type ServiceMeta } from '@Core/ts/types';
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
  private readonly STEAL_TIME = 1000 * 60 * 60 * 24;

  constructor() {
    this.initStore();
  }

  private initStore() {
    const query = queryServiceMeta.instance as QueryService;

    Alpine.store('tutorLocation', {
      fetchCountriesQuery: query.useQuery(
        'fetch-countries',
        async () => {
          return await axios.get(`${tutorConfig.tutor_url}${endpoints.FETCH_COUNTRIES}`).then((res) => res.data);
        },
        {
          staleTime: this.STEAL_TIME,
          cacheTime: this.STEAL_TIME,
        },
      ) as QueryState<Country[]>,
    });
  }
}

export const locationServiceMeta: ServiceMeta = {
  name: 'location',
  instance: new LocationService(),
};
