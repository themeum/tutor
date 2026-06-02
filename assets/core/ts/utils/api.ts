import config, { tutorConfig } from '@TutorShared/config/config';

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

  if (!response.ok) {
    throw new Error(`HTTP ${response.status}`);
  }

  const text = await response.text();

  try {
    return JSON.parse(text) as TResponse;
  } catch {
    return text as TResponse;
  }
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
