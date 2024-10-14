import countriesData from '@Assets/json/countries.json';

import { Option } from './types';

export interface State {
  name: string;
  id: number;
}

export const euCountryCode = '000';

export const isEuropeanUnion = (countryCode: string) => countryCode == euCountryCode;

export const europeanUnionData = {
  name: 'European Union',
  numeric_code: euCountryCode,
  alpha_2: 'EU',
  alpha_3: 'EUR',
  currency: 'EUR',
  currency_name: 'Euro',
  currency_symbol: '\u20ac',
  emoji: '\ud83c\uddea\ud83c\uddfa',
  states: [
    {
      name: 'Austria',
      numeric_code: '040',
      emoji: '\ud83c\udde6\ud83c\uddf9',
    },
    {
      name: 'Belgium',
      numeric_code: '056',
      emoji: '\ud83c\udde7\ud83c\uddea',
    },
    {
      name: 'Bulgaria',
      numeric_code: '100',
      emoji: '\ud83c\udde7\ud83c\uddec',
    },
    {
      name: 'Croatia',
      numeric_code: '191',
      emoji: '\ud83c\udded\ud83c\uddf7',
    },
    {
      name: 'Cyprus',
      numeric_code: '196',
      emoji: '\ud83c\uddfe\ud83c\uddfe',
    },
    {
      name: 'Czech Republic',
      numeric_code: '203',
      emoji: '\ud83c\udded\ud83c\uddf7',
    },
    {
      name: 'Denmark',
      numeric_code: '208',
      emoji: '\ud83c\udde9\ud83c\uddf0',
    },
    {
      name: 'Estonia',
      numeric_code: '233',
      emoji: '\ud83c\uddea\ud83c\uddea',
    },
    {
      name: 'Finland',
      numeric_code: '246',
      emoji: '\ud83c\uddeb\ud83c\uddee',
    },
    {
      name: 'France',
      numeric_code: '250',
      emoji: '\ud83c\uddeb\ud83c\uddf7',
    },
    {
      name: 'Germany',
      numeric_code: '276',
      emoji: '\ud83c\udde9\ud83c\uddea',
    },
    {
      name: 'Greece',
      numeric_code: '300',
      emoji: '\ud83c\uddec\ud83c\uddf7',
    },
    {
      name: 'Hungary',
      numeric_code: '348',
      emoji: '\ud83c\udded\ud83c\uddfa',
    },
    {
      name: 'Ireland',
      numeric_code: '372',
      emoji: '\ud83c\uddee\ud83c\uddea',
    },
    {
      name: 'Italy',
      numeric_code: '380',
      emoji: '\ud83c\uddee\ud83c\uddf9',
    },
    {
      name: 'Latvia',
      numeric_code: '428',
      emoji: '\ud83c\uddf1\ud83c\uddff',
    },
    {
      name: 'Lithuania',
      numeric_code: '440',
      emoji: '\ud83c\uddf1\ud83c\uddf9',
    },
    {
      name: 'Luxembourg',
      numeric_code: '442',
      emoji: '\ud83c\uddf1\ud83c\uddfa',
    },
    {
      name: 'Malta',
      numeric_code: '470',
      emoji: '\ud83c\uddf2\ud83c\uddfe',
    },
    {
      name: 'Netherlands',
      numeric_code: '528',
      emoji: '\ud83c\uddf3\ud83c\uddf1',
    },
    {
      name: 'Poland',
      numeric_code: '616',
      emoji: '\ud83c\uddf5\ud83c\uddf1',
    },
    {
      name: 'Portugal',
      numeric_code: '620',
      emoji: '\ud83c\uddf5\ud83c\uddf9',
    },
    {
      name: 'Romania',
      numeric_code: '642',
      emoji: '\ud83c\uddf7\ud83c\uddf4',
    },
    {
      name: 'Slovakia',
      numeric_code: '703',
      emoji: '\ud83c\uddf8\ud83c\uddf0',
    },
    {
      name: 'Slovenia',
      numeric_code: '705',
      emoji: '\ud83c\uddf8\ud83c\uddee',
    },
    {
      name: 'Spain',
      numeric_code: '724',
      emoji: '\ud83c\uddea\ud83c\uddf8',
    },
    {
      name: 'Sweden',
      numeric_code: '752',
      emoji: '\ud83c\uddf8\ud83c\uddea',
    },
  ],
};

export const getCountriesAsOptions = () => {
  return countriesData.map((country) => ({ label: country.name, value: country.numeric_code, icon: country.emoji }));
};

export const getStatesByCountryAsOptions = (countryCode: string) => {
  const country = countriesData.find((country) => country.numeric_code === countryCode);
  const states: State[] = country?.states || [];
  return states.map((state) => ({ label: state.name, value: String(state.id) }));
};


export const getCountryByCode = (countryCode: string) => {
  return countriesData.find((country) => country.numeric_code === countryCode);
};

export const getStateByCode = (countryCode: string, stateCode: number) => {
  const country = getCountryByCode(countryCode);

  if (!country) {
    return;
  }

  return (country.states as State[]).find((state) => state.id === stateCode);
};

export const getCountryListAsOptions = (codes: string[], exclude = false) => {
  if (codes.length === 0) {
    return getCountriesAsOptions();
  }

  if (!exclude) {
    return codes
      .map((countryCode) => {
        const country = getCountryByCode(countryCode);

        if (!country) {
          return null as unknown as Option<string>;
        }

        return {
          label: country.name,
          value: country.numeric_code,
        } as Option<string>;
      })
      .filter((item) => !!item);
  }

  return countriesData
    .filter((country) => {
      return !codes.includes(country.numeric_code);
    })
    .map((country) => {
      return {
        label: country.name,
        value: country.numeric_code,
      } as Option<string>;
    });
};
