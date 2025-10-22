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
  labelContent?: ReactNode | string;
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

export type DurationUnit = 'hour' | 'day' | 'week' | 'month' | 'year';

export interface MembershipPlan {
  id: number;
  payment_type: string;
  plan_type: string;
  restriction_mode: string | null;
  plan_name: string;
  description: string | null;
  is_enabled: '1' | '0';
  is_featured: string;
  featured_text: string | null;
  recurring_value: string;
  recurring_interval: Exclude<DurationUnit, 'hour'>;
  recurring_limit: string;
  plan_duration: string;
  regular_price: string;
  sale_price: string;
  sale_price_from: string | null;
  sale_price_to: string | null;
  provide_certificate: string;
  enrollment_fee: string;
  trial_fee: string;
  trial_value: string;
  trial_interval: Extract<DurationUnit, 'hour' | 'day'>;
  plan_order: string;
  plan_id: string;
  object_name: string;
  object_id: string;
  categories: {
    id: number;
    title: string;
    image: string;
    total_courses: number;
  }[];
}

interface VisibilityProps {
  visibilityKey?: string;
}
export type WithVisibilityProps<T> = T & VisibilityProps;

export type TopicContentType =
  | 'tutor-google-meet'
  | 'tutor_zoom_meeting'
  | 'lesson'
  | 'cb-lesson'
  | 'tutor_quiz'
  | 'tutor_assignments'
  | 'tutor_h5p_quiz';

export interface H5PContent {
  id: ID;
  title: string;
  content_type: string;
  user_id: ID;
  user_name: string;
  updated_at: string;
}

export interface H5PContent {
  id: ID;
  title: string;
  content_type: string;
  user_id: ID;
  user_name: string;
  updated_at: string;
}

export interface H5PContentResponse {
  output: H5PContent[];
}

export const QuizDataStatus = {
  NEW: 'new',
  UPDATE: 'update',
  NO_CHANGE: 'no_change',
} as const;

export type QuizDataStatus = (typeof QuizDataStatus)[keyof typeof QuizDataStatus];

export type QuizQuestionType =
  | 'true_false'
  | 'single_choice'
  | 'multiple_choice'
  | 'open_ended'
  | 'fill_in_the_blank'
  | 'short_answer'
  | 'matching'
  | 'image_matching'
  | 'image_answering'
  | 'ordering'
  | 'h5p';

export interface QuizQuestionOption {
  _data_status: QuizDataStatus;
  is_saved: boolean;
  answer_id: ID;
  belongs_question_id: ID;
  belongs_question_type: QuizQuestionType;
  answer_title: string;
  is_correct: '0' | '1';
  image_id?: ID;
  image_url?: string;
  answer_two_gap_match: string;
  answer_view_format: string;
  answer_order: number;
}

export interface QuizQuestion {
  _data_status: QuizDataStatus;
  is_cb_question?: boolean;
  question_id: ID;
  question_title: string;
  question_description: string;
  question_mark: number;
  answer_explanation: string;
  question_order: number;
  question_type: QuizQuestionType;
  question_settings: {
    question_type: QuizQuestionType;
    answer_required: boolean;
    randomize_question: boolean;
    question_mark: number;
    show_question_mark: boolean;
    has_multiple_correct_answer: boolean;
    is_image_matching: boolean;
  };
  question_answers: QuizQuestionOption[];
}

export interface QuizQuestionsForPayload extends Omit<QuizQuestion, 'question_settings' | 'answer_explanation'> {
  answer_explanation?: string;
  question_settings: {
    question_type: QuizQuestionType;
    answer_required: '0' | '1';
    randomize_question: '0' | '1';
    question_mark: number;
    show_question_mark: '0' | '1';
    has_multiple_correct_answer?: '0' | '1';
    is_image_matching?: '0' | '1';
  };
}

export type QuizValidationErrorType = 'question' | 'quiz' | 'correct_option' | 'add_option' | 'save_option';

export type CollectionContentType = 'cb-question' | 'cb-lesson' | 'cb-assignment';

export interface Collection {
  ID: number;
  post_title: string;
  count_stats: {
    lesson: number;
    assignment: number;
    question: number;
    total: number;
  };
}

export interface CollectionResponse {
  total_record: number;
  per_page: number;
  current_page: number;
  total_page: number;
  data: Collection[];
}

export interface ContentBankContent {
  ID: number;
  post_title: string;
  post_content: string;
  post_name: string | null;
  post_type: CollectionContentType;
  question_type?: QuizQuestionType;
  post_author: string;
  post_parent: string;
  post_date: string;
  linked_courses: {
    total: number;
    courses: {
      ID: number;
      post_title: string;
    }[];
    more_text: string;
  };
}

export interface ContentBankContents {
  total_record: number;
  per_page: number;
  current_page: number;
  total_page: number;
  data: ContentBankContent[];
  collection: Collection;
}

export interface Certificate {
  name: string;
  orientation: 'landscape' | 'portrait';
  edit_url?: string;
  url: string;
  preview_src: string;
  background_src: string;
  key: string;
  is_default?: boolean;
}
