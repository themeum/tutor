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

export const calculateDiscountedPrice = ({
  discount_type,
  discount_amount,
  total,
}: { discount_type: 'percentage' | 'flat'; discount_amount: number; total: number }) => {
  const discountValue = calculateDiscountValue({ discount_amount, discount_type, total });
  return total - discountValue;
};

export const calculateDiscountValue = ({
  discount_type,
  discount_amount,
  total,
}: { discount_type: 'percentage' | 'flat'; discount_amount: number; total: number }) => {
  if (discount_type === 'flat') {
    return discount_amount;
  }
  return total * (discount_amount / 100);
};
