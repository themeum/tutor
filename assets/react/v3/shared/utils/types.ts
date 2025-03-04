import type { AxiosError, AxiosResponse } from 'axios';
import type { ReactNode } from 'react';
import type { RegisterOptions } from 'react-hook-form';

export type CourseProgressSteps = 'basic' | 'curriculum' | 'additional' | 'certificate';

export const localHasOwnProperty = <T extends object>(obj: T, key: PropertyKey): key is keyof T => {
  return key in obj;
};

export const isAxiosError = <T>(error: unknown): error is AxiosError<T> => {
  return (error as AxiosError).isAxiosError;
};

export const isDefined = <T>(value: T | null | undefined): value is T => {
  return value !== undefined && value !== null;
};

export function isString(value: unknown): value is string {
  return typeof value === 'string' || value instanceof String;
}

export function isPrimitivesArray(value: unknown | null): value is unknown[] {
  return !!value && Array.isArray(value) && (!value.length || typeof value[0] !== 'object');
}

export function isStringArray(value: unknown): value is string[] {
  return isPrimitivesArray(value) && (!value.length || typeof value[0] === 'string' || value[0] instanceof String);
}

export function isNumber(value: unknown): value is number {
  return typeof value === 'number' || value instanceof Number;
}

export function isBoolean(value: unknown): value is boolean {
  return typeof value === 'boolean' || value instanceof Boolean;
}

export function isObject<T>(value: T): value is T {
  return typeof value === 'object' && value !== null && !Array.isArray(value);
}

export interface Option<T> {
  label: string;
  value: T;
  icon?: ReactNode | string;
  disabled?: boolean;
  description?: string;
}
export type CouponStatus = 'active' | 'inactive';
export type ProductStatus = 'draft' | 'published' | 'trashed';
export type ProductDiscount = {
  type: 'percent' | 'amount';
  amount: number | null;
};
export type ProductOptionType = 'color' | 'list';
export interface ProductOptionValue {
  id?: number;
  name: string;
  color?: string;
}

export interface OptionWithIcon<T> {
  label: string;
  value: T;
  icon: ReactNode;
  disabled?: boolean;
}
export interface OptionWithImage<T> {
  label: string;
  value: T;
  image: string;
  disabled?: boolean;
}

export interface PaginatedResult<T> {
  total_items: number;
  results: T[];
}

export interface PaginatedParams {
  limit?: number;
  offset?: number;
  sort?: {
    property?: string;
    direction?: 'asc' | 'desc';
  };
  filter?: Record<string, string>;
}

export interface PostResponse<T> {
  data: {
    status: boolean;
    message: string;
    id: T;
  };
}

export interface TableSelectedItems {
  ids: number[];
  indexes: number[];
}

export interface MoreOptionsProps<T> {
  item: T;
  updateSelectedItems?: () => void;
}

export type Prettify<T> = {
  [K in keyof T]: T[K];
} & {};

export type ID = number | string;

export interface TutorMutationResponse<T> {
  data: T;
  message: string;
  status_code: AxiosResponse['status'];
}

export interface WPUser {
  user_id: number;
  display_name: string;
  user_email: string;
  avatar_url: string;
}

export type WPPostStatus = 'publish' | 'private' | 'draft' | 'future' | 'pending' | 'trash';
export type TutorSellingOption = 'subscription' | 'one_time' | 'both';

export interface TutorCategory {
  term_id: number;
  name: string;
  slug: string;
  term_group: number;
  term_taxonomy_id: number;
  taxonomy: string;
  description: string;
  parent: number;
  count: number;
  filter: string;
}

export type InjectionSlots = {
  Basic: 'after_description' | 'after_settings';
  Curriculum: {
    Lesson: 'after_description' | 'bottom_of_sidebar';
    Quiz: 'after_question_description' | 'bottom_of_question_sidebar' | 'bottom_of_settings';
    Assignment: 'after_description' | 'bottom_of_sidebar';
  };
  Additional: 'after_certificates' | 'bottom_of_sidebar';
};

export type SectionStructure = {
  [K in keyof InjectionSlots]: K extends 'Curriculum'
    ? { [C in keyof InjectionSlots[K]]: `${C & string}.${InjectionSlots[K][C] & string}` }
    : `${K}.${InjectionSlots[K] & string}`;
};

type Path<T> = T extends object
  ? {
      [K in keyof T]: T[K] extends object ? `${string & K}.${Path<T[K]> & string}` : T[K];
    }[keyof T]
  : never;

export type SectionPath = Path<SectionStructure>;

export type FieldType =
  | 'text'
  | 'number'
  | 'password'
  | 'textarea'
  | 'select'
  | 'radio'
  | 'checkbox'
  | 'switch'
  | 'date'
  | 'time'
  | 'image'
  | 'video'
  | 'uploader'
  | 'WPEditor';

export interface InjectedField {
  name: string;
  type: FieldType;
  options?: Array<{ label: string; value: string }>;
  label?: string;
  placeholder?: string;
  rules?: Exclude<RegisterOptions, 'valueAsNumber' | 'valueAsDate' | 'setValueAs'>;
  priority?: number;
}

export interface InjectedContent {
  component: ReactNode;
  priority?: number;
}

export interface Editor {
  label: string;
  link: string;
  name: string;
}
