/**
 * Security utilities for escaping HTML and attributes
 *
 * @since 4.0.0
 */

/**
 * Escape a string for safe use as HTML text content.
 *
 * Only `&`, `<` and `>` are encoded (quotes are NOT), so the result is safe
 * between tags but must never be placed inside an HTML attribute value.
 * Use escapeAttr() for attributes, and sanitizeUrl() before using a URL in
 * href/src.
 *
 * @param unsafeText - Plain text to escape
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

/**
 * Protocols allowed by sanitizeUrl.
 */
const SAFE_URL_PROTOCOLS = new Set(['http:', 'https:']);

/**
 * Validate a URL for use in href/src attributes.
 *
 * Relative URLs are resolved against the current origin. Only http(s) URLs
 * are returned, so javascript:, data:, vbscript: and similar schemes become
 * an empty string. The result is NOT escaped: pass it through escapeAttr()
 * before placing it inside an HTML attribute.
 *
 * @param value - URL to validate
 * @returns Normalized absolute URL, or an empty string if unsafe or invalid
 *
 * @since 4.2.2
 */
export function sanitizeUrl(value: unknown): string {
  if (typeof value !== 'string' || value.trim() === '') {
    return '';
  }

  try {
    const parsed = new URL(value.trim(), window.location.origin);
    return SAFE_URL_PROTOCOLS.has(parsed.protocol) ? parsed.href : '';
  } catch {
    return '';
  }
}

/**
 * Tags allowed by setSafeInlineHtml. No attributes are ever copied.
 */
const SAFE_INLINE_TAGS = new Set(['STRONG', 'B', 'EM', 'BR']);

/**
 * Elements removed together with their content. Their text is code or
 * non-display data, so it must never be copied out as visible text.
 */
const SAFE_INLINE_DROP_TAGS = new Set([
  'SCRIPT',
  'STYLE',
  'TEMPLATE',
  'NOSCRIPT',
  'IFRAME',
  'OBJECT',
  'EMBED',
  'TEXTAREA',
  'TITLE',
  'NOEMBED',
  'NOFRAMES',
  'XMP',
]);

/**
 * Recursively copy text and allow-listed inline elements from an untrusted
 * DOM tree into a target node.
 *
 * - Allow-listed elements are rebuilt without attributes.
 * - Elements in SAFE_INLINE_DROP_TAGS (script, style, etc.) are dropped
 *   together with their content.
 * - Any other unknown element is dropped but its text is kept.
 */
function copySafeInline(target: Node, source: Node): void {
  source.childNodes.forEach((node) => {
    if (node.nodeType === Node.TEXT_NODE) {
      target.appendChild(document.createTextNode(node.textContent ?? ''));
      return;
    }

    if (node.nodeType !== Node.ELEMENT_NODE) {
      return;
    }

    const el = node as Element;
    // tagName is lowercase for foreign content (e.g. inside <svg>), so normalize it.
    const tag = el.tagName.toUpperCase();

    if (SAFE_INLINE_DROP_TAGS.has(tag)) {
      return;
    }

    if (SAFE_INLINE_TAGS.has(tag)) {
      const clean = document.createElement(tag.toLowerCase());
      copySafeInline(clean, el);
      target.appendChild(clean);
      return;
    }

    copySafeInline(target, el);
  });
}

/**
 * Render a string that may contain a few inline tags (<strong>, <b>, <em>, <br>)
 * into an element without using innerHTML on untrusted input.
 *
 * The string is parsed in an inert document, then only allow-listed tags are
 * rebuilt without attributes. Everything else is reduced to plain text.
 * Dynamic values inside the string should still be passed through escapeHtml().
 *
 * @param target - Element to render into (existing content is replaced)
 * @param html - String that may contain allow-listed inline tags
 *
 * @since 4.2.2
 */
export function setSafeInlineHtml(target: HTMLElement, html: string): void {
  const doc = new DOMParser().parseFromString(html, 'text/html');
  target.textContent = '';
  copySafeInline(target, doc.body);
}
