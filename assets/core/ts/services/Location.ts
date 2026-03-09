import Alpine from 'alpinejs';
import axios from 'axios';
import { type QueryService, type QueryState, queryServiceMeta } from '@Core/ts/services/Query';
import { type ServiceMeta } from '@Core/ts/types';
import { tutorConfig } from '@TutorShared/config/config';
import endpoints from '@TutorShared/utils/endpoints';

export interface Country {
  name: string;
  states: { id: number; name: string }[];
}

export class LocationService {
  private readonly _24_HOURS = 1000 * 60 * 60 * 24;

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
          staleTime: this._24_HOURS,
          cacheTime: this._24_HOURS,
        },
      ) as QueryState<Country[]>,
    });
  }
}

export const locationServiceMeta: ServiceMeta = {
  name: 'location',
  instance: new LocationService(),
};
