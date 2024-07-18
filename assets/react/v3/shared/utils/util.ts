import collection from '@Config/icon-list';
import type { Category, CategoryWithChildren } from '@Services/category';
import {
  differenceInDays,
  endOfMonth,
  endOfYear,
  isSameDay,
  isToday,
  isYesterday,
  startOfMonth,
  startOfYear,
  subMonths,
  subYears,
} from 'date-fns';
import type { DateRange } from 'react-day-picker';
import { v4 as uuidv4 } from 'uuid';

import type { IconCollection, PaginatedParams } from './types';

export function assertIsDefined<T>(val: T, errorMsg: string): asserts val is NonNullable<T> {
  if (val === undefined || val === null) {
    throw new Error(errorMsg);
  }
}

export const noop = () => {};

export const range = (count: number) => Array.from(Array(count).keys());

export const arrayRange = (start: number, end: number) => Array.from({ length: end - start }, (v, k) => k + start);

export const isFileOrBlob = (value: unknown): value is Blob | File => {
  return value instanceof Blob || value instanceof File;
};

export const getValueInArray = <T>(value: T | T[] | undefined): T[] => {
  return Array.isArray(value) ? value : value ? [value] : [];
};

export const getIcon = (name: IconCollection) => {
  return collection[name];
};

// Generate unique id
export const nanoid = (): string => uuidv4();

// Generate coupon code
export const generateCouponCode = (size = 8) => {
  let localSize = size;
  const urlAlphabet = 'MSOP0123456789ABCDEFGHNRVUKYTJLZXIW';
  let code = '';

  while (localSize--) {
    code += urlAlphabet[(Math.random() * 35) | 0];
  }

  return code;
};

// Useful for mock api call
export const wait = (ms = 0) => new Promise((resolve) => setTimeout(resolve, ms));

/**
 * Move one array item from one index to another index
 * (don't change the original array) instead return a new one.
 *
 * @param arr Array
 * @param fromIndex Number
 * @param toIndex Number
 * @returns new Array
 */
export const moveTo = <T>(arr: T[], fromIndex: number, toIndex: number) => {
  const newArr = [...arr];
  let mutatingFromIndex = fromIndex;
  let mutatingToIndex = toIndex;

  if (fromIndex < 0) {
    mutatingFromIndex = arr.length + fromIndex;
  }

  if (fromIndex >= 0 && fromIndex < arr.length) {
    if (toIndex < 0) {
      mutatingToIndex = arr.length + toIndex;
    }

    const [item] = newArr.splice(mutatingFromIndex, 1);
    item && newArr.splice(mutatingToIndex, 0, item);
  }

  return newArr;
};

export const getFileExtensionFromName = (filename: string) => {
  const chunk = filename.split('.');
  const extension = chunk.pop();

  return extension ? `.${extension}` : '';
};

export const hasDuplicateEntries = <T>(items: T[], callback: (item: T) => string | number, caseSensitive = true) => {
  const counterHash: Record<string | number, number> = {};

  for (const item of items) {
    let key = callback(item);
    key = caseSensitive ? key : key.toString().toLowerCase();

    counterHash[key] ||= 0;
    counterHash[key]++;

    const hash = counterHash[key];
    if (hash && hash > 1) {
      return true;
    }
  }

  return false;
};

export const generateTree = (data: Category[], parent = 0): CategoryWithChildren[] => {
  return data
    .filter((node) => node.parent === parent)
    .reduce<CategoryWithChildren[]>((tree, node) => [...tree, { ...node, children: generateTree(data, node.id) }], []);
};

export const getCategoryLeftBarHeight = (isLastChild: boolean, totalChildren: number) => {
  let height = '0';
  if (!isLastChild) {
    height = '100%';
  } else if (isLastChild && totalChildren > 0) {
    if (totalChildren > 1) {
      height = `${24 + 32 * (totalChildren - 1)}px`;
    } else {
      height = '24px';
    }
  }
  return height;
};

export const transformParams = (params: PaginatedParams) => {
  const sortDirection = params.sort?.direction === 'desc' ? '-' : '';

  return {
    limit: params.limit,
    offset: params.offset,
    sort: params.sort?.property && `${sortDirection}${params.sort.property}`,
    ...params.filter,
  };
};

export const getRandom = (min: number, max: number) => Math.floor(Math.random() * (max - min)) + min;

export const mapInBetween = (
  value: number,
  originalMin: number,
  originalMax: number,
  expectedMin: number,
  expectedMax: number,
) => {
  return ((value - originalMin) * (expectedMax - expectedMin)) / (originalMax - originalMin) + expectedMin;
};

export const getActiveDateRange = (range: DateRange | undefined) => {
  if (!range || !range.from) {
    return;
  }

  if (isToday(range.from) && !range.to) {
    return 'today';
  }

  if (isYesterday(range.from) && !range.to) {
    return 'yesterday';
  }

  if (range.to) {
    if (isToday(range.to) && differenceInDays(range.to, range.from) === 6) {
      return 'last_seven_days';
    }

    if (isToday(range.to) && differenceInDays(range.to, range.from) === 29) {
      return 'last_thirty_days';
    }

    if (isToday(range.to) && differenceInDays(range.to, range.from) === 89) {
      return 'last_ninety_days';
    }

    if (
      isSameDay(startOfMonth(subMonths(new Date(), 1)), range.from) &&
      isSameDay(endOfMonth(subMonths(new Date(), 1)), range.to)
    ) {
      return 'last_month';
    }

    if (
      isSameDay(startOfYear(subYears(new Date(), 1)), range.from) &&
      isSameDay(endOfYear(subYears(new Date(), 1)), range.to)
    ) {
      return 'last_year';
    }

    return;
  }
};

export const extractIdOnly = <T extends { id: number }>(data: T[]) => {
  return data.map((item) => item.id);
};

export const arrayIntersect = <T>(first: T[], second: T[]) => {
  const firstSet = new Set(first);
  const secondSet = new Set(second);
  const intersect = [];

  for (const element of firstSet) {
    if (secondSet.has(element)) {
      intersect.push(element);
    }
  }

  return intersect;
};

export const makeFirstCharacterUpperCase = (word: string) => {
  const firstCharacterUpperCase = word.charAt(0).toUpperCase();
  const wordWithoutFirstCharacter = word.slice(1);

  return `${firstCharacterUpperCase}${wordWithoutFirstCharacter}`;
};

export const formatBytes = (bytes: number, decimals = 2) => {
  if (!bytes || bytes <= 1) {
    return '0 Bytes';
  }

  const kilobit = 1024;
  const decimal = Math.max(0, decimals);
  const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];

  const index = Math.floor(Math.log(bytes) / Math.log(kilobit));

  return `${Number.parseFloat((bytes / kilobit ** index).toFixed(decimal))} ${sizes[index]}`;
};

export const parseNumberOnly = (value: string) => {
  return value.replace(/[^-0-9.]/g, '');
};

export const throttle = <T extends (args: MouseEvent) => void>(func: T, limit: number) => {
  let inThrottle = false;

  return function (this: ThisParameterType<T>, ...args: Parameters<T>) {
    if (!inThrottle) {
      func.apply(this, args);
      inThrottle = true;

      setTimeout(() => {
        inThrottle = false;
      }, limit);
    }
  };
};

export const jsonParse = <T>(data: string): T => {
  return JSON.parse(data) as T;
};

export const formatSeconds = (seconds: number) => {
  const hours = Math.floor(seconds / 3600)
    .toString()
    .padStart(2, '0');
  const minutes = Math.floor((seconds % 3600) / 60)
    .toString()
    .padStart(2, '0');
  const remainingSeconds = Math.floor(seconds % 60);

  if (hours === '00') {
    return `${minutes}:${remainingSeconds} mins`;
  }

  return `${hours}:${minutes}:${remainingSeconds} hrs`;
};
