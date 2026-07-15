import { __ } from '@wordpress/i18n';

import config, { tutorConfig } from '@TutorShared/config/config';
import { convertToFormData } from '@TutorShared/utils/form';

type HttpMethod = 'GET' | 'POST';

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
    convertToFormData(
      {
        ...data,
        action,
        [tutorConfig.nonce_key]: tutorConfig._tutor_nonce,
      },
      'POST',
    ),
  );
}

/**
 * Generic form POST helper (page submits / quiz / redirects)
 */
export function wpPostForm<TResponse>(url: string, data: object = {}): Promise<TResponse> {
  return wpFetch<TResponse>(
    url,
    'POST',
    convertToFormData(
      {
        ...data,
        _tutor_nonce: tutorConfig._tutor_nonce,
      },
      'POST',
    ),
  );
}
