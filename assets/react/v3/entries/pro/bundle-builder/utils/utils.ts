export const getBundleId = () => {
  const params = new URLSearchParams(window.location.search);
  const bundleId = params.get('id');
  return Number(bundleId);
};
