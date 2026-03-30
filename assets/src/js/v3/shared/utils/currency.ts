import { tutorConfig } from '@TutorShared/config/config';

type PriceFormatterOptions = {
  symbol?: string;
  position?: string;
  thousandSeparator?: string;
  decimalSeparator?: string;
  fraction_digits?: number;
};

export const createPriceFormatter = ({
  symbol = '$',
  position = 'left',
  thousandSeparator = ',',
  decimalSeparator = '.',
  fraction_digits = 2,
}: PriceFormatterOptions) => {
  return (price: number): string => {
    const formatNumberWithSeparators = (num: number): string => {
      const fixed = num.toFixed(fraction_digits);
      const [intPart, decimalPart] = fixed.split('.');

      const formattedIntPart = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, thousandSeparator);

      return decimalPart ? `${formattedIntPart}${decimalSeparator}${decimalPart}` : formattedIntPart;
    };

    const formattedNumber = formatNumberWithSeparators(Number(price));

    if (position === 'left') {
      return `${symbol}${formattedNumber}`;
    }

    return `${formattedNumber}${symbol}`;
  };
};

export const formatPrice = createPriceFormatter({
  symbol: tutorConfig.tutor_currency?.symbol ?? '$',
  position: tutorConfig.tutor_currency?.position ?? 'left',
  thousandSeparator: tutorConfig.tutor_currency?.thousand_separator ?? ',',
  decimalSeparator: tutorConfig.tutor_currency?.decimal_separator ?? '.',
  fraction_digits: Number(tutorConfig.tutor_currency?.no_of_decimal ?? 2),
});

export const formatPriceIntl = (price: number): string => {
  const currency = tutorConfig.tutor_currency?.currency ?? 'USD';
  const locale = tutorConfig.local?.replace('_', '-') ?? 'en-US';
  const fractionDigits = Number(tutorConfig.tutor_currency?.no_of_decimal ?? 2);

  const formatter = new Intl.NumberFormat(locale, {
    style: 'currency',
    currency,
    minimumFractionDigits: fractionDigits,
  });

  return formatter.format(price);
};

export const calculateDiscountedPrice = ({
  discount_type,
  discount_amount,
  total,
}: {
  discount_type: 'percentage' | 'flat';
  discount_amount: number;
  total: number;
}) => {
  const discountValue = calculateDiscountValue({ discount_amount, discount_type, total });
  return total - discountValue;
};

export const calculateDiscountValue = ({
  discount_type,
  discount_amount,
  total,
}: {
  discount_type: 'percentage' | 'flat';
  discount_amount: number;
  total: number;
}) => {
  if (discount_type === 'flat') {
    return discount_amount;
  }
  return total * (discount_amount / 100);
};
