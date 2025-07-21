type DataType = 'string' | 'number' | 'boolean';
export function getQueryParam(key: string, type: 'string'): string | null;
export function getQueryParam(key: string, type: 'number'): number | null;
export function getQueryParam(key: string, type: 'boolean'): boolean | null;

export function getQueryParam(key: string, type: DataType = 'string') {
  const searchParams = new URLSearchParams(window.location.search);

  if (!searchParams.has(key)) {
    return null;
  }

  const value = searchParams.get(key);

  switch (type) {
    case 'string':
      return String(value);
    case 'number':
      return Number(value);
    case 'boolean':
      return Boolean(value);
    default:
      return value;
  }
}

export const removeAllQueryParams = ({ exclude }: { exclude: string[] }) => {
  const searchParams = new URLSearchParams(window.location.search);
  const keysToRemove: string[] = [];

  for (const key of searchParams.keys()) {
    if (!exclude.includes(key)) {
      keysToRemove.push(key);
    }
  }

  keysToRemove.forEach((key) => {
    searchParams.delete(key);
  });

  const newUrl = searchParams.toString()
    ? `${window.location.pathname}?${searchParams.toString()}`
    : window.location.pathname;
  window.history.replaceState({}, '', newUrl);
};

// eslint-disable-next-line @typescript-eslint/no-explicit-any
export const encodeParams = (params: Record<string, any>): string => {
  const jsonString = JSON.stringify(params);
  return btoa(encodeURIComponent(jsonString));
};

// eslint-disable-next-line @typescript-eslint/no-explicit-any
export const decodeParams = <T = Record<string, any>>(encodedString: string, defaultValue?: T): T => {
  try {
    const jsonString = decodeURIComponent(atob(encodedString));
    return JSON.parse(jsonString) as T;
  } catch (error) {
    // eslint-disable-next-line no-console
    console.error('Failed to decode parameters:', error);
    if (defaultValue !== undefined) {
      return defaultValue;
    }
    throw error;
  }
};
