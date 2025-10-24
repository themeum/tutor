// Alpine.js Type Extensions
// TypeScript declarations for Alpine.js integration

import type { TutorCore } from '../index';

declare global {
  interface Window {
    Alpine: AlpineJS;
    TutorCore: typeof TutorCore;
  }
}

export interface AlpineJS {
  data(name: string, callback: (...args: unknown[]) => unknown): void;
  store(name: string, value: unknown): void;
  start(): void;
  plugin(callback: (Alpine: AlpineJS) => void): void;
  directive(name: string, callback: DirectiveCallback): void;
  magic(name: string, callback: MagicCallback): void;
  version: string;
}

export interface AlpineComponent {
  init?(): void;
  destroy?(): void;
  $el?: HTMLElement;
  $refs?: Record<string, HTMLElement>;
  $nextTick?: (callback: () => void) => void;
  $watch?: (property: string, callback: (value: unknown) => void) => void;
  [key: string]: unknown;
}

export interface AlpineStore {
  [key: string]: unknown;
}

export interface DirectiveCallback {
  (el: HTMLElement, directive: DirectiveData, utilities: DirectiveUtilities): void;
}

export interface DirectiveData {
  value: string;
  modifiers: string[];
  expression: string;
  original: string;
}

export interface DirectiveUtilities {
  Alpine: AlpineJS;
  effect: (callback: () => void) => void;
  cleanup: (callback: () => void) => void;
  evaluate: (expression: string) => unknown;
  evaluateLater: (expression: string) => (callback?: (result: unknown) => void) => unknown;
}

export interface MagicCallback {
  (el: HTMLElement, utilities: { Alpine: AlpineJS }): unknown;
}

// RTL Detection utilities
export interface RTLUtils {
  isRTL(): boolean;
  getDirection(): 'ltr' | 'rtl';
  adaptPlacement(placement: string): string;
  getStartEnd(): { start: 'left' | 'right'; end: 'left' | 'right' };
}

export {};
