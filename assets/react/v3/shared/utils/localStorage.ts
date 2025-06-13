import type { LocalStorageKeys } from '@TutorShared/config/constants';
import { EventEmitter } from 'events';

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
