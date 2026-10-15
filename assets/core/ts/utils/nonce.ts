/**
 * WordPress nonce utilities for AJAX requests
 *
 * @since 4.0.0
 */

interface NonceData {
  [key: string]: string;
}

interface NonceKeyValue {
  key: string;
  value: string;
}

/**
 * Get WordPress nonce data for AJAX requests
 *
 * @param sendKeyValue - If true, returns {key, value}, otherwise returns {[key]: value}
 * @returns Nonce data object
 *
 * @since 4.0.0
 */
export function getNonceData(sendKeyValue?: boolean): NonceData | NonceKeyValue {
  const nonceData = (window._tutorobject || {}) as Record<string, unknown>;
  const nonceKey = (nonceData.nonce_key as string) || '';
  const nonceValue = (nonceData[nonceKey] as string) || '';

  if (sendKeyValue) {
    return { key: nonceKey, value: nonceValue };
  }

  return { [nonceKey]: nonceValue };
}
