// biome-ignore lint/style/useNodejsImportProtocol: <explanation>
import { EventEmitter } from 'events';
import type { LocalStorageKeys } from '@Config/constants';

export const localStorageEventEmitter = new EventEmitter();

export const setToLocalStorage = (key: LocalStorageKeys, value: string) => {
  localStorage.setItem(key, value);
};

export const deleteFromLocalStorage = (key: LocalStorageKeys) => {
  localStorage.removeItem(key);
};

export const getFromLocalStorage = <T extends string>(key: LocalStorageKeys) => {
  if (typeof window !== 'undefined') {
    return (localStorage.getItem(key) as T) || null;
  }

  return null;
};
