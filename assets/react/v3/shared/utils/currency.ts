export const createPriceFormatter = ({
  locale,
  currency,
  fraction_digits = 2,
}: { locale: string; currency: string; fraction_digits?: number }) => {
  return (price: number) => {
    const formatter = new Intl.NumberFormat(locale, {
      style: 'currency',
      currency,
      maximumFractionDigits: fraction_digits,
    });

    return formatter.format(price);
  };
};
