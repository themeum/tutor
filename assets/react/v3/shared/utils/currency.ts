import { tutorConfig } from '@TutorShared/config/config';

export const createPriceFormatter = ({
  locale,
  currency,
  position = 'left',
  thousandSeparator = ',',
  decimalSeparator = '.',
  fraction_digits = 2,
}: {
  locale: string;
  currency: string;
  position: string; // 'left' or 'right'
  thousandSeparator: string;
  decimalSeparator: string;
  fraction_digits: number;
}) => {
  return (price: number) => {
    const formatNumberWithSeparators = (num: number): string => {
      const fixed = num.toFixed(fraction_digits);
      const [intPart, decimalPart] = fixed.split('.');

      const formattedIntPart = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, thousandSeparator);

      return decimalPart ? `${formattedIntPart}${decimalSeparator}${decimalPart}` : formattedIntPart;
    };

    const formatter = new Intl.NumberFormat(locale, {
      style: 'currency',
      currency,
      maximumFractionDigits: fraction_digits,
    });

    const parts = formatter.formatToParts(price);
    const currencySymbol = parts.find((part) => part.type === 'currency')?.value || currency;

    const formattedNumber = formatNumberWithSeparators(price);

    if (position === 'left') {
      return `${currencySymbol}${formattedNumber}`;
    }

    if (position === 'right') {
      return `${formattedNumber}${currencySymbol}`;
    }

    return `${currencySymbol}${formattedNumber}`;
  };
};

export const formatPrice = createPriceFormatter({
  locale: tutorConfig.local?.replace('_', '-') ?? 'en-US',
  currency: tutorConfig.tutor_currency?.currency ?? 'USD',
  position: tutorConfig.tutor_currency?.position ?? ('left' as 'left' | 'right'),
  thousandSeparator: tutorConfig.tutor_currency?.thousand_separator ?? ',',
  decimalSeparator: tutorConfig.tutor_currency?.decimal_separator ?? '.',
  fraction_digits: Number(tutorConfig.tutor_currency?.no_of_decimal ?? 2),
});

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
