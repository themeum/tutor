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

/**
 * Formula injection prevention prefixes (OWASP / CWE-1236)
 */
const FORMULA_INJECTION_PREFIXES = ['=', '+', '-', '@', '\t', '\r'] as const;

/**
 * Sanitizes a single cell value for CSV export to prevent formula injection (OWASP / CWE-1236)
 * and properly escape commas, double quotes, and newlines.
 *
 * @param rawVal - The raw value to sanitize
 * @returns The sanitized cell string
 *
 * @since 4.2.0
 */
export function sanitizeCsvCell(rawVal: unknown): string {
  if (rawVal === null || rawVal === undefined) {
    return '';
  }

  let str = String(rawVal);

  if (FORMULA_INJECTION_PREFIXES.some((prefix) => str.startsWith(prefix))) {
    str = `'${str}`;
  }

  if (str.includes(',') || str.includes('"') || str.includes('\n') || str.includes('\r')) {
    return `"${str.replace(/"/g, '""')}"`;
  }

  return str;
}
