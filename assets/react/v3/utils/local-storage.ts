import { LocalStorage } from '@Config/constants';

export const getStorageItem = (key: LocalStorage) => {
  if (typeof window !== 'undefined') {
    return window.localStorage.getItem(key) || null;
  }

  return null;
};

export const setStorageItem = (key: LocalStorage, value: string) => {
  if (typeof window !== 'undefined') {
    window.localStorage.setItem(key, value);
  }
};

export const removeStorageItem = (key: LocalStorage) => {
  if (typeof window !== 'undefined') {
    window.localStorage.removeItem(key);
  }
};

export const clearStorage = () => {
  if (typeof window !== 'undefined') {
    window.localStorage.clear();
  }
};
