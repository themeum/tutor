/**
 * Legacy security function wrappers for backward compatibility
 *
 * @since 4.0.0
 */

import { escapeAttr, escapeHtml } from '@Core/ts/utils/security';

/**
 * Legacy tutor_esc_html wrapper
 *
 * @deprecated Use escapeHtml from @Core/ts/utils/security instead
 *
 * @param unsafeText - HTML string to escape
 * @returns Escaped HTML string
 *
 * @since 4.0.0
 */
export function tutor_esc_html(unsafeText: string): string {
  return escapeHtml(unsafeText);
}

/**
 * Legacy tutor_esc_attr wrapper
 *
 * @deprecated Use escapeAttr from @Core/ts/utils/security instead
 *
 * @param str - String to escape
 * @returns Escaped attribute string
 *
 * @since 4.0.0
 */
export function tutor_esc_attr(str: string): string {
  return escapeAttr(str);
}
