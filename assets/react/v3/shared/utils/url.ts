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
