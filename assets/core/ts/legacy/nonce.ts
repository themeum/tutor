/**
 * Legacy nonce function wrapper for backward compatibility
 *
 * @since 4.0.0
 */

import { getNonceData } from '@Core/ts/utils/nonce';

/**
 * Legacy tutor_get_nonce_data wrapper
 *
 * @deprecated Use getNonceData from @Core/ts/utils/nonce instead
 *
 * @param sendKeyValue - If true, returns {key, value}, otherwise returns {[key]: value}
 * @returns Nonce data object
 *
 * @since 4.0.0
 */
export function tutor_get_nonce_data(sendKeyValue?: boolean) {
  return getNonceData(sendKeyValue);
}
