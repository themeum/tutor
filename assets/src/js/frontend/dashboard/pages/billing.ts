import { type QueryState } from '@Core/ts/services/Query';
import { tutorConfig } from '@TutorShared/config/config';
import endpoints from '@TutorShared/utils/endpoints';
import { type TutorMutationResponse } from '@TutorShared/utils/types';
import axios from 'axios';

const billing = () => {
  const query = window.TutorCore.query;

  return {
    $el: null as HTMLElement | null,
    query,
    fetchCountriesQuery: null as QueryState<TutorMutationResponse<string>> | null,

    async fetchCountries() {
      return await axios.get(`${tutorConfig.tutor_url}${endpoints.FETCH_COUNTRIES}`).then((res) => res.data);
    },

    init() {
      if (!this.$el) {
        return;
      }

      this.fetchCountriesQuery = query.useQuery('fetch-countries', () => this.fetchCountries());
    },
  };
};

export const initializeBilling = () => {
  window.TutorComponentRegistry.register({
    type: 'component',
    meta: {
      name: 'billing',
      component: billing,
    },
  });
  window.TutorComponentRegistry.initWithAlpine(window.Alpine);
};
