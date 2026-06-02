import config, { tutorConfig } from '@TutorShared/config/config';
import { __ } from '@wordpress/i18n';

type HttpMethod = 'GET' | 'POST';

function toFormDataValue(value: unknown): string | Blob {
  if (value instanceof File || value instanceof Blob) return value;
  if (typeof value === 'string') return value;
  if (typeof value === 'boolean' || typeof value === 'number') return String(value);
  if (typeof value === 'object') return JSON.stringify(value);

  return String(value);
}

function toFormData(data: Record<string, unknown>): FormData {
  const formData = new FormData();

  for (const [key, value] of Object.entries(data)) {
    if (value === undefined || value === null) continue;

    if (Array.isArray(value)) {
      for (const item of value) {
        if (item === undefined || item === null) continue;

        formData.append(`${key}[]`, toFormDataValue(item));
      }
      continue;
    }

    formData.append(key, toFormDataValue(value));
  }

  return formData;
}

async function wpFetch<TResponse>(url: string, method: HttpMethod, body?: FormData): Promise<TResponse> {
  const response = await fetch(url, {
    method,
    body,
    credentials: 'same-origin',
  });

  const text = await response.text();

  let parsed: unknown;
  try {
    parsed = JSON.parse(text);
  } catch {
    if (!response.ok) {
      throw new Error(`HTTP ${response.status}`);
    }
    return text as TResponse;
  }

  const json = parsed as Record<string, unknown>;

  // JsonResponse trait shape: { status_code, message, data }
  // Non-2xx HTTP status means error.
  if (!response.ok) {
    const message = typeof json?.message === 'string' ? json.message : `HTTP ${response.status}`;
    throw new Error(message);
  }

  // wp_send_json_error shape: { success: false, data }
  // Always returns HTTP 200, so we have to inspect the payload.
  if (json?.success === false) {
    const message =
      typeof json?.data === 'string'
        ? json.data
        : typeof (json?.data as Record<string, unknown>)?.message === 'string'
          ? ((json.data as Record<string, unknown>).message as string)
          : __('Something went wrong', 'tutor');
    throw new Error(message);
  }

  return parsed as TResponse;
}

export function wpGet<TResponse>(url: string): Promise<TResponse> {
  return wpFetch<TResponse>(url, 'GET');
}

/**
 * WordPress admin-ajax.php POST helper
 */
export function wpPost<TResponse>(action: string, data: object = {}): Promise<TResponse> {
  return wpFetch<TResponse>(
    config.WP_AJAX_BASE_URL,
    'POST',
    toFormData({
      ...data,
      action,
      [tutorConfig.nonce_key]: tutorConfig._tutor_nonce,
    }),
  );
}

/**
 * Generic form POST helper (page submits / quiz / redirects)
 */
export function wpPostForm<TResponse>(url: string, data: object = {}): Promise<TResponse> {
  return wpFetch<TResponse>(
    url,
    'POST',
    toFormData({
      ...data,
      _tutor_nonce: tutorConfig._tutor_nonce,
    }),
  );
}
