/**
 * Check if the current device is a mobile device
 *
 * @returns {boolean} True if the device is a mobile device, false otherwise
 *
 * @since 4.0.0
 */
export function isMobileDevice() {
  return /Mobi|Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
}
