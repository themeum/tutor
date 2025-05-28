import { tutorConfig } from '@TutorShared/config/config';

export const createPriceFormatter = ({
  locale,
  currency,
  fraction_digits = 2,
}: {
  locale: string;
  currency: string;
  fraction_digits?: number;
}) => {
  const position = tutorConfig.tutor_currency?.position ?? 'left';

  return (price: number) => {
    const formatter = new Intl.NumberFormat(locale, {
      style: 'currency',
      currency,
      maximumFractionDigits: fraction_digits,
    });

    const formattedPrice = formatter.format(price);

    if (position === 'left') {
      return formattedPrice;
    }

    if (position === 'right') {
      const parts = formatter.formatToParts(price);
      const currencyPart = parts.find((part) => part.type === 'currency');
      const numberParts = parts.filter((part) => part.type !== 'currency');

      if (currencyPart) {
        const numberString = numberParts.map((part) => part.value).join('');
        return `${numberString}${currencyPart.value}`;
      }
    }

    return formattedPrice;
  };
};

export const formatPrice = createPriceFormatter({
  locale: tutorConfig.local?.replace('_', '-') ?? 'en-US',
  currency: tutorConfig.tutor_currency.currency ?? 'USD',
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
