/**
 * Security utilities for escaping HTML and attributes
 *
 * @since 4.0.0
 */

/**
 * Escape HTML and return safe HTML
 *
 * @param unsafeText - HTML string to escape
 * @returns Escaped HTML string
 *
 * @since 4.0.0
 */
export function escapeHtml(unsafeText: string): string {
  const div = document.createElement('div');
  /**
   * When set an HTML string to an element's innerText
   * the browser automatically escapes any HTML tags and
   * treats the content as plain text.
   */
  div.innerText = unsafeText;
  const safeHTML = div.innerHTML;
  div.remove();

  return safeHTML;
}

/**
 * Escape attribute value for safe usage in HTML attributes
 *
 * @param str - String to escape
 * @returns Escaped attribute string
 *
 * @since 4.0.0
 */
export function escapeAttr(str: string): string {
  return str
    .replace(/&/g, '&amp;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;');
}
