import type { LocalStorageKeys } from '@TutorShared/config/constants';

export const getStorageItem = (key: LocalStorageKeys) => {
  if (typeof window !== 'undefined') {
    return window.localStorage.getItem(key) || null;
  }

  return null;
};

export const setStorageItem = (key: LocalStorageKeys, value: string) => {
  if (typeof window !== 'undefined') {
    window.localStorage.setItem(key, value);
  }
};

export const removeStorageItem = (key: LocalStorageKeys) => {
  if (typeof window !== 'undefined') {
    window.localStorage.removeItem(key);
  }
};

export const clearStorage = () => {
  if (typeof window !== 'undefined') {
    window.localStorage.clear();
  }
};
