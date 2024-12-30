export const getBundleId = () => {
  const params = new URLSearchParams(window.location.search);
  const bundleId = params.get('id');
  return Number(bundleId);
};

export const priceWithOutCurrencySymbol = (price: string) => {
  return Number(price.replace(/[^0-9.]/g, '')) || 0;
};
