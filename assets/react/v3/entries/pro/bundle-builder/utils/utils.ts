export const getBundleId = () => {
  const params = new URLSearchParams(window.location.search);
  const bundleId = params.get('bundle-id');
  return Number(bundleId);
};
