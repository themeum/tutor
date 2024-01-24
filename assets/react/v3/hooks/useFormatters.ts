import { useAppConfigQuery } from '@Services/app';
import { isDefined } from '@Utils/types';
import { useCallback } from 'react';

interface PriceFormatterOption {
  fractionDigits?: number;
  numberOnly?: boolean;
}

export const useFormatters = () => {
  const appConfigQuery = useAppConfigQuery();

  const priceFormat = useCallback(
    (price: number | null, options?: PriceFormatterOption) => {
      if (!isDefined(price)) {
        return '';
      }
      const currency = appConfigQuery.data?.settings.general?.currency || 'USD:$';
      const [name] = currency.split(':');

      if (options?.numberOnly) {
        return new Intl.NumberFormat(appConfigQuery.data?.settings.general?.locale || 'en-us', {
          maximumSignificantDigits: 3,
        }).format(price);
      }

      return new Intl.NumberFormat(appConfigQuery.data?.settings.general?.locale || 'en-us', {
        style: 'currency',
        currency: name,
        maximumFractionDigits: isDefined(options?.fractionDigits) ? options?.fractionDigits : 2,
      }).format(price);
    },
    [appConfigQuery.data],
  );

  const getCurrency = useCallback(() => {
    const currency = appConfigQuery.data?.settings.general?.currency || 'USD:$';
    const [currency_name, currency_symbol] = currency.split(':');

    return { currency_name, currency_symbol };
  }, [appConfigQuery.data]);

  const getUnit = useCallback(() => {
    return appConfigQuery.data?.settings.general?.unit || 'km';
  }, [appConfigQuery.data]);

  const arrayToReadableString = (array: string[], options?: Intl.ListFormatOptions) => {
    const defaultOptions: Intl.ListFormatOptions = { style: 'long', type: 'conjunction' };
    const formatter = new Intl.ListFormat(appConfigQuery.data?.settings.general?.locale || 'en-us', {
      ...defaultOptions,
      ...options,
    });

    return formatter.format(array);
  };

  return {
    priceFormat,
    getCurrency,
    getUnit,
    arrayToReadableString,
  };
};
