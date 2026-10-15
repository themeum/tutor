/**
 * Shared accessibility helpers for quiz interaction fields in the course builder.
 *
 * @package Tutor
 * @since 4.0.0
 */

import { css } from '@emotion/react';

import { colorTokens } from '@TutorShared/config/styles';

/**
 * Screen-reader-only text (matches tutor-pro quiz interaction a11y styles).
 */
export const quizBuilderSrOnlyCss = css`
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border: 0;
`;

/**
 * Focus ring for keyboard-operable quiz interaction surfaces (canvas, grid, etc.).
 */
export const quizBuilderInteractionFocusCss = css`
  &:focus {
    outline: none;
  }

  &:focus-visible {
    outline: 2px solid ${colorTokens.stroke.brand};
    outline-offset: 2px;
  }
`;

/**
 * Announce text through a polite live region.
 */
export const announceQuizBuilderPolite = (region: HTMLElement | null, message: string): void => {
  if (!region || typeof message !== 'string') {
    return;
  }

  const text = message.trim();
  if (!text) {
    return;
  }

  region.textContent = '';
  if (typeof window.requestAnimationFrame === 'function') {
    window.requestAnimationFrame(() => {
      region.textContent = text;
    });
    return;
  }

  region.textContent = text;
};

/**
 * Merge unique id references into aria-describedby.
 */
export const bindQuizBuilderDescribedBy = (controlEl: HTMLElement | null, ids: string[]): void => {
  if (!controlEl || !ids.length) {
    return;
  }

  const validIds = ids.filter((id) => typeof id === 'string' && id.length > 0 && document.getElementById(id));
  if (!validIds.length) {
    return;
  }

  const existing = (controlEl.getAttribute('aria-describedby') || '').split(/\s+/).filter(Boolean);
  const merged: string[] = [];
  const seen: Record<string, boolean> = {};

  for (const id of existing) {
    if (!seen[id]) {
      seen[id] = true;
      merged.push(id);
    }
  }

  for (const id of validIds) {
    if (!seen[id]) {
      seen[id] = true;
      merged.push(id);
    }
  }

  controlEl.setAttribute('aria-describedby', merged.join(' '));
};

/**
 * Normalize a keyboard event key (with legacy keyCode fallback).
 */
export const normalizeQuizBuilderKey = (event: KeyboardEvent | React.KeyboardEvent): string => {
  if (event.key && event.key !== 'Unidentified') {
    return event.key;
  }

  const codeMap: Record<number, string> = {
    37: 'ArrowLeft',
    38: 'ArrowUp',
    39: 'ArrowRight',
    40: 'ArrowDown',
  };

  return codeMap[event.keyCode] || '';
};

export const isQuizBuilderGridMoveKey = (key: string): boolean =>
  key === 'ArrowLeft' || key === 'ArrowRight' || key === 'ArrowUp' || key === 'ArrowDown';

/**
 * Move an integer grid cursor by one unit using arrow keys.
 */
export const moveQuizBuilderGridCursor = (
  cursor: { x: number; y: number },
  key: string,
  bounds: { min: number; max: number },
): { x: number; y: number } | null => {
  const { min, max } = bounds;
  let nextX = cursor.x;
  let nextY = cursor.y;

  if (key === 'ArrowLeft') {
    nextX -= 1;
  } else if (key === 'ArrowRight') {
    nextX += 1;
  } else if (key === 'ArrowUp') {
    nextY += 1;
  } else if (key === 'ArrowDown') {
    nextY -= 1;
  } else {
    return null;
  }

  nextX = Math.max(min, Math.min(max, nextX));
  nextY = Math.max(min, Math.min(max, nextY));

  return { x: nextX, y: nextY };
};

/**
 * Move a pixel cursor within canvas bounds using arrow keys.
 */
export const moveQuizBuilderPixelCursor = (
  cursor: { x: number; y: number },
  key: string,
  bounds: { width: number; height: number },
  options?: { step?: number; largeStep?: number; shiftKey?: boolean },
): { x: number; y: number } | null => {
  if (!isQuizBuilderGridMoveKey(key)) {
    return null;
  }

  const step = typeof options?.step === 'number' && options.step > 0 ? options.step : 8;
  const largeStep = typeof options?.largeStep === 'number' && options.largeStep > 0 ? options.largeStep : 24;
  const delta = options?.shiftKey ? largeStep : step;
  const maxX = Math.max(0, bounds.width);
  const maxY = Math.max(0, bounds.height);
  let nextX = cursor.x;
  let nextY = cursor.y;

  if (key === 'ArrowLeft') {
    nextX -= delta;
  } else if (key === 'ArrowRight') {
    nextX += delta;
  } else if (key === 'ArrowUp') {
    nextY -= delta;
  } else if (key === 'ArrowDown') {
    nextY += delta;
  }

  nextX = Math.max(0, Math.min(maxX, nextX));
  nextY = Math.max(0, Math.min(maxY, nextY));

  return { x: nextX, y: nextY };
};
