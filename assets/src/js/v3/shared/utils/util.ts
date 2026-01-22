import type { Category, CategoryWithChildren } from '@TutorShared/services/category';
import { __, sprintf } from '@wordpress/i18n';
import { addMinutes, format } from 'date-fns';
import { v4 as uuidv4 } from 'uuid';

import { tutorConfig } from '@TutorShared/config/config';
import { type Addons, DateFormats } from '@TutorShared/config/constants';
import type { ErrorResponse } from '@TutorShared/utils/form';
import {
  type DurationUnit,
  type InjectedField,
  type PaginatedParams,
  type WPPostStatus,
  isDefined,
  isObject,
} from '@TutorShared/utils/types';

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
    if (item) {
      newArr.splice(mutatingToIndex, 0, item);
    }
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

export const generateTree = (
  data: Category[],
  parent = 0,
  processedIds: Set<number> = new Set(),
): CategoryWithChildren[] => {
  const categoryIds = new Set(data.map((category) => category.id));

  const levelNodes = data.filter((node) => {
    if (processedIds.has(node.id)) {
      return false;
    }

    if (parent === 0) {
      return node.parent === 0 || !categoryIds.has(node.parent);
    }

    return node.parent === parent;
  });

  return levelNodes.reduce<CategoryWithChildren[]>((tree, node) => {
    processedIds.add(node.id);

    const children = generateTree(data, node.id, processedIds);

    return [...tree, { ...node, children }];
  }, []);
};

export const getCategoryLeftBarHeight = (isLastChild: boolean, totalChildren: number) => {
  let height = '0';
  if (!isLastChild) {
    height = '100%';
  } else if (isLastChild && totalChildren > 0) {
    if (totalChildren > 1) {
      height = `${23 + 32 * (totalChildren - 1)}px`;
    } else {
      height = '23px';
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
  if (!word) return word;

  const firstCharacterUpperCase = word.charAt(0).toUpperCase();
  const wordWithoutFirstCharacter = word.slice(1);

  return `${firstCharacterUpperCase}${wordWithoutFirstCharacter}`;
};

export const formatBytes = (bytes: number, decimals = 2) => {
  if (!bytes || bytes <= 1) {
    return __('0 Bytes', __TUTOR_TEXT_DOMAIN__);
  }

  const kilobit = 1024;
  const decimal = Math.max(0, decimals);
  const sizes = [
    __('Bytes', __TUTOR_TEXT_DOMAIN__),
    __('KB', __TUTOR_TEXT_DOMAIN__),
    __('MB', __TUTOR_TEXT_DOMAIN__),
    __('GB', __TUTOR_TEXT_DOMAIN__),
    __('TB', __TUTOR_TEXT_DOMAIN__),
    __('PB', __TUTOR_TEXT_DOMAIN__),
    __('EB', __TUTOR_TEXT_DOMAIN__),
    __('ZB', __TUTOR_TEXT_DOMAIN__),
    __('YB', __TUTOR_TEXT_DOMAIN__),
  ];

  const index = Math.floor(Math.log(bytes) / Math.log(kilobit));

  return `${Number.parseFloat((bytes / kilobit ** index).toFixed(decimal))} ${sizes[index]}`;
};

export const formatReadAbleBytesToBytes = (readableBytes: string): number => {
  if (!readableBytes || typeof readableBytes !== 'string') {
    return 0;
  }

  const [value, unit] = readableBytes.split(' ');
  const byteValue = parseFloat(value);
  const units = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];

  const index = units.indexOf(unit);
  if (index === -1) {
    return 0;
  }

  return byteValue * 1024 ** index;
};

export const parseNumberOnly = (v: string, allowNegative = false, whole = false) =>
  v
    .replace(whole ? (allowNegative ? /[^0-9-]/g : /[^0-9]/g) : allowNegative ? /[^0-9.-]/g : /[^0-9.]/g, '')
    .replace(/(?!^)-/g, '')
    .replace(whole ? /\./g : /(\..*)\./g, '$1');

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

export const getObjectKeys = <T extends object>(object: T) => {
  if (!isDefined(object) || !isObject(object)) {
    return [] as (keyof T)[];
  }
  return Object.keys(object) as (keyof T)[];
};

export const getObjectValues = <T extends object, K extends keyof T = keyof T>(object: T): T[K][] => {
  return Object.values(object);
};

export const getObjectEntries = <T extends object, K extends keyof T = keyof T>(object: T): [K, T[K]][] => {
  return Object.entries(object) as [K, T[K]][];
};

export function objectToQueryParams(obj: Record<string, string>) {
  const params = new URLSearchParams();

  for (const key in obj) {
    if (key in obj) {
      params.append(key, obj[key]);
    }
  }

  return params.toString();
}

export const convertToGMT = (date: Date, dateFormat = DateFormats.yearMonthDayHourMinuteSecond24H) => {
  const offsetInMinutes = date.getTimezoneOffset();
  const gmtDate = addMinutes(date, offsetInMinutes);
  return format(gmtDate, dateFormat);
};

export const convertGMTtoLocalDate = (date: string) => {
  const localDate = new Date(date);
  const offset = localDate.getTimezoneOffset();
  return addMinutes(localDate, -offset);
};

export const normalizeLineEndings = (text: string) => {
  return (text || '').replace(/\r\n/g, '\n');
};

export const copyToClipboard = (text: string) => {
  return new Promise<void>((resolve, reject) => {
    if (navigator.clipboard && window.isSecureContext) {
      navigator.clipboard
        .writeText(text)
        .then(() => resolve())
        .catch((error) => reject(error));
    } else {
      const textarea = document.createElement('textarea');
      textarea.value = text;
      document.body.appendChild(textarea);
      textarea.select();

      try {
        // if navigator.clipboard is not available, use document.execCommand('copy')
        document.execCommand('copy');
        resolve();
      } catch (error) {
        reject(error);
      } finally {
        document.body.removeChild(textarea); // Clean up
      }
    }
  });
};

export const convertToErrorMessage = (error: ErrorResponse) => {
  if (!error || !error.response || !error.response.data) {
    return __('Something went wrong', __TUTOR_TEXT_DOMAIN__);
  }

  let errorMessage = error.response.data.message;
  if (error.response.data.status_code === 422 && error.response.data.data) {
    errorMessage = error.response.data.data[Object.keys(error.response.data.data)[0]];
  }
  return errorMessage || __('Something went wrong', __TUTOR_TEXT_DOMAIN__);
};

export const fetchImageUrlAsBase64 = async (url: string): Promise<string> => {
  try {
    const response = await fetch(url);
    const blob = await response.blob();
    const reader = new FileReader();

    return new Promise((resolve, reject) => {
      reader.readAsDataURL(blob);
      reader.onload = () => resolve(reader.result as string);
      reader.onerror = (error) => reject(error);
    });
  } catch (error) {
    throw new Error(`Failed to fetch and convert image: ${error}`);
  }
};

export const determinePostStatus = (postStatus: WPPostStatus, postVisibility: 'private' | 'password_protected') => {
  if (postStatus === 'trash') {
    return 'trash';
  }

  if (postVisibility === 'private') {
    return 'private';
  }

  if (postStatus === 'future') {
    return 'future';
  }

  if (postVisibility === 'password_protected' && postStatus !== 'draft') {
    return 'publish';
  }

  return postStatus;
};

export type Addon = `${Addons}`;

export const isAddonEnabled = (addon: Addon) => {
  return !!tutorConfig.addons_data.find((item) => item.base_name === addon)?.is_enabled;
};

export const convertToSlug = (value: string): string => {
  if (!value || typeof value !== 'string') {
    return '';
  }

  return (
    value
      .normalize('NFKD') // Normalize accented characters into base forms + diacritics
      .replace(/[\u0300-\u036f]/g, '') // Remove combining diacritical marks
      .toLowerCase()
      // Remove special characters !~@#$%^&*(){}[]|\;:"',./?
      // Remove characters that are NOT:
      // - Basic Latin letters and numbers (a-z, 0-9)
      // - Spaces and hyphens
      // - Latin Extended (À-ž, etc.)
      // - Greek and Coptic (Α-ω)
      // - Cyrillic (А-я)
      // - Hebrew (א-ת)
      // - Arabic (ا-ي)
      // - Devanagari (Hindi)
      // - Thai
      // - Tamil
      // - Georgian
      // - Hangul Jamo (Korean building blocks)
      // - Hiragana (Japanese)
      // - Katakana (Japanese)
      // - CJK Unified Ideographs (Chinese/Japanese/Korean characters)
      // - Hangul Syllables (Korean)
      // - Hangul Compatibility Jamo
      // - Hangul Jamo Extended-A
      // - Hangul Jamo Extended-B
      .replace(
        // eslint-disable-next-line no-misleading-character-class
        /[^a-z0-9\s\-\u00C0-\u024F\u0370-\u03FF\u0400-\u04FF\u0590-\u05FF\u0600-\u06FF\u0900-\u097F\u0E00-\u0E7F\u0B80-\u0BFF\u10A0-\u10FF\u1100-\u11FF\u3130-\u318F\u3040-\u309F\u30A0-\u30FF\u4E00-\u9FFF\uA960-\uA97F\uAC00-\uD7AF\uD7B0-\uD7FF]/g,
        '',
      )
      .replace(/\s+/g, '-') // Replace multiple spaces with single dash
      .replace(/-+/g, '-') // Replace multiple dashes with single dash
      .replace(/^-+|-+$/g, '') // Remove leading and trailing dashes
  );
};

export const findSlotFields = (...fieldArgs: { fields: Record<string, InjectedField[]>; slotKey?: string }[]) => {
  const slotFields: string[] = [];
  fieldArgs.forEach((arg) => {
    if (arg.slotKey) {
      arg.fields[arg.slotKey].forEach((i) => {
        slotFields.push(i.name);
      });
    } else {
      Object.keys(arg.fields).forEach((i) => {
        arg.fields[i].forEach((j) => {
          slotFields.push(j.name);
        });
      });
    }
  });

  return slotFields;
};

export const decodeHtmlEntities = (text: string) => {
  const parser = new DOMParser();
  const doc = parser.parseFromString(text, 'text/html');
  return doc.body.textContent || '';
};

export const formatSubscriptionRepeatUnit = ({
  unit = 'hour',
  value,
  useLySuffix = false,
  capitalize = true,
  showSingular = false,
}: {
  unit: DurationUnit | 'until_cancellation';
  value: number;
  useLySuffix?: boolean;
  capitalize?: boolean;
  showSingular?: boolean;
}) => {
  if (unit === 'until_cancellation') {
    const result = __('Until Cancellation', __TUTOR_TEXT_DOMAIN__);
    return capitalize ? capitalizeWords(result) : result;
  }

  const unitFormats = {
    hour: {
      // translators: %d is the number of hours
      plural: __('%d hours', __TUTOR_TEXT_DOMAIN__),
      // translators: %d is the number of hours
      singular: __('%d hour', __TUTOR_TEXT_DOMAIN__),
      suffix: __('hourly', __TUTOR_TEXT_DOMAIN__),
      base: __('hour', __TUTOR_TEXT_DOMAIN__),
    },
    day: {
      // translators: %d is the number of days
      plural: __('%d days', __TUTOR_TEXT_DOMAIN__),
      // translators: %d is the number of days
      singular: __('%d day', __TUTOR_TEXT_DOMAIN__),
      suffix: __('daily', __TUTOR_TEXT_DOMAIN__),
      base: __('day', __TUTOR_TEXT_DOMAIN__),
    },
    week: {
      // translators: %d is the number of weeks
      plural: __('%d weeks', __TUTOR_TEXT_DOMAIN__),
      // translators: %d is the number of weeks
      singular: __('%d week', __TUTOR_TEXT_DOMAIN__),
      suffix: __('weekly', __TUTOR_TEXT_DOMAIN__),
      base: __('week', __TUTOR_TEXT_DOMAIN__),
    },
    month: {
      // translators: %d is the number of months
      plural: __('%d months', __TUTOR_TEXT_DOMAIN__),
      // translators: %d is the number of months
      singular: __('%d month', __TUTOR_TEXT_DOMAIN__),
      suffix: __('monthly', __TUTOR_TEXT_DOMAIN__),
      base: __('month', __TUTOR_TEXT_DOMAIN__),
    },
    year: {
      // translators: %d is the number of years
      plural: __('%d years', __TUTOR_TEXT_DOMAIN__),
      // translators: %d is the number of years
      singular: __('%d year', __TUTOR_TEXT_DOMAIN__),
      suffix: __('yearly', __TUTOR_TEXT_DOMAIN__),
      base: __('year', __TUTOR_TEXT_DOMAIN__),
    },
  };

  if (!unitFormats[unit]) {
    return '';
  }

  let result = '';

  if (value > 1) {
    result = sprintf(unitFormats[unit].plural, value);
  } else if (showSingular) {
    result = sprintf(unitFormats[unit].singular, value);
  } else if (useLySuffix) {
    result = unitFormats[unit].suffix;
  } else {
    result = unitFormats[unit].base;
  }

  return capitalize ? capitalizeWords(result) : result;
};

const capitalizeWords = (text: string): string => {
  return text
    .split(' ')
    .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
    .join(' ');
};

export const covertSecondsToHMS = (seconds: number) => {
  const hours = Math.floor(seconds / 3600);
  const minutes = Math.floor((seconds % 3600) / 60);
  const sec = seconds % 60;
  return { hours, minutes, seconds: sec };
};
