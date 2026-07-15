/** Coordinates question field: instructor places correct answer points on the graph grid. */

import { useCallback, useEffect, useId, useRef, useState } from 'react';
import { useFormContext } from 'react-hook-form';
import { css } from '@emotion/react';
import { __, sprintf } from '@wordpress/i18n';

import Button from '@TutorShared/atoms/Button';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import TextInput from '@TutorShared/atoms/TextInput';
import Tooltip from '@TutorShared/atoms/Tooltip';

import FormSelectInput from '@TutorShared/components/fields/FormSelectInput';

import { tutorConfig } from '@TutorShared/config/config';
import { borderRadius, Breakpoint, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import type { FormControllerProps } from '@TutorShared/utils/form';
import { calculateQuizDataStatus } from '@TutorShared/utils/quiz';
import {
  announceQuizBuilderPolite,
  bindQuizBuilderDescribedBy,
  isQuizBuilderGridMoveKey,
  moveQuizBuilderGridCursor,
  normalizeQuizBuilderKey,
  quizBuilderInteractionFocusCss,
  quizBuilderSrOnlyCss,
} from '@TutorShared/utils/quizBuilderA11y';
import { styleUtils } from '@TutorShared/utils/style-utils';
import {
  type ID,
  QuizDataStatus,
  type QuizQuestionOption,
  type QuizValidationErrorType,
} from '@TutorShared/utils/types';

const PADDING = 16;
const SNAP_THRESHOLD = 0.3;
const CANVAS_SIZE = 420;
const MARKER_DISPLAY_SIZE = 27;
const HOVER_ALPHA_LERP = 0.28;
const HOVER_SCALE_MIN = 0.85;
const MAX_COORDINATES = 5;
const AXIS_LABEL_STEP = 2;
const AXIS_RANGE_OPTIONS = [10, 20].map((value) => ({
  label: `${value} ${__('Unit', __TUTOR_TEXT_DOMAIN__)}`,
  icon: value === 10 ? 'unit10' : 'unit20',
  value,
}));

/**
 * Whether to draw a numeric label at this axis tick (every `AXIS_LABEL_STEP` units plus zero handling).
 */
const shouldRenderAxisLabel = (value: number): boolean => value === 0 || Math.abs(value % AXIS_LABEL_STEP) === 0;

interface FormCoordinatesProps extends FormControllerProps<QuizQuestionOption> {
  questionId: ID;
  activeQuestionIndex?: number;
  axisRangeControllerProps?: FormControllerProps<number | null>;
  validationError?: {
    message: string;
    type: QuizValidationErrorType;
  } | null;
  setValidationError?: React.Dispatch<
    React.SetStateAction<{
      message: string;
      type: QuizValidationErrorType;
    } | null>
  >;
}

type CoordinatePoint = { x: number; y: number };

/**
 * Normalizes stored or UI axis range to supported values (10 or 20 units).
 */
const resolveAxisRange = (value?: number | null): 10 | 20 => (Number(value) === 20 ? 20 : 10);

/**
 * Rounds to the nearest integer and clamps to `[-axisRange, axisRange]`.
 */
const clampIntToRange = (n: number, axisRange: number): number => {
  const min = -axisRange;
  const max = axisRange;
  const rounded = Math.round(n);
  return Math.max(min, Math.min(max, rounded));
};

/**
 * Clamps a point so both coordinates lie within the current axis range.
 */
const sanitizePoint = (p: CoordinatePoint, axisRange: number): CoordinatePoint => ({
  x: clampIntToRange(p.x, axisRange),
  y: clampIntToRange(p.y, axisRange),
});

/**
 * Parses `answer_two_gap_match` JSON into up to five points (`MAX_COORDINATES`).
 * Accepts either an array of `{x,y}` or a legacy single object; invalid input yields `[]`.
 *
 * @param axisRange - When set, clamps each parsed point to the grid extent.
 */
const parseStoredCoordinates = (value: string, axisRange?: number): CoordinatePoint[] => {
  if (!value || typeof value !== 'string') return [];
  try {
    const parsed = JSON.parse(value) as unknown;
    if (Array.isArray(parsed)) {
      const pts = parsed
        .map((p) => {
          if (!p || typeof p !== 'object') return null;
          const maybe = p as { x?: unknown; y?: unknown };
          if (typeof maybe.x !== 'number' || typeof maybe.y !== 'number') return null;
          const point = { x: Math.round(maybe.x), y: Math.round(maybe.y) };
          return typeof axisRange === 'number' ? sanitizePoint(point, axisRange) : point;
        })
        .filter(Boolean) as CoordinatePoint[];
      return pts.slice(0, MAX_COORDINATES);
    }
    if (parsed && typeof parsed === 'object') {
      const o = parsed as { x?: unknown; y?: unknown };
      if (typeof o.x === 'number' && typeof o.y === 'number') {
        const point = { x: Math.round(o.x), y: Math.round(o.y) };
        return [typeof axisRange === 'number' ? sanitizePoint(point, axisRange) : point];
      }
    }
  } catch {
    return [];
  }
  return [];
};

/**
 * Parses manual "x,y" text into a grid point; requires integers within `[-axisRange, axisRange]`.
 */
const parseCoordinateText = (value: string, axisRange: number): CoordinatePoint | null => {
  const min = -axisRange;
  const max = axisRange;
  const raw = (value ?? '').trim();
  if (!raw) return null;
  const parts = raw.split(',').map((p) => p.trim());
  if (parts.length !== 2) return null;
  const x = Number(parts[0]);
  const y = Number(parts[1]);
  if (!Number.isFinite(x) || !Number.isFinite(y)) return null;
  if (!Number.isInteger(x) || !Number.isInteger(y)) return null;
  if (x < min || x > max || y < min || y > max) return null;
  return { x, y };
};

/** Serializes a point for display and draft state (`"x,y"`). */
const formatCoordinateText = (pt: CoordinatePoint): string => `${pt.x},${pt.y}`;

/** i18n message shown when a stored point lies outside the current axis range. */
const getCoordinatesRangeErrorMessage = (axisRange: number): string =>
  sprintf(
    // translators: %1$d is the minimum axis range, %2$d is the maximum axis range
    __('Range is from -%1$d to %2$d', __TUTOR_TEXT_DOMAIN__),
    axisRange,
    axisRange,
  );

/** Absolute URL for overlay marker SVGs shipped with Tutor assets. */
const graphMarkerAssetUrl = (filename: 'graph-marker-hover' | 'graph-marker-selected'): string =>
  `${tutorConfig.tutor_url}assets/icons/${filename}.svg`;

/**
 * Maps graph coordinates to CSS pixel position within a square logical canvas (padding inset, y-up).
 */
const graphToPixelLayout = (
  logicalSize: number,
  minCoord: number,
  maxCoord: number,
  gx: number,
  gy: number,
): { x: number; y: number } => {
  const drawableWidth = logicalSize - 2 * PADDING;
  const drawableHeight = logicalSize - 2 * PADDING;
  const centerX = PADDING + drawableWidth / 2;
  const centerY = PADDING + drawableHeight / 2;
  const pixelsPerUnit = Math.min(drawableWidth, drawableHeight) / (maxCoord - minCoord);
  return {
    x: centerX + gx * pixelsPerUnit,
    y: centerY - gy * pixelsPerUnit,
  };
};

/**
 * Converts pointer position in CSS pixels to the nearest grid intersection if within snap distance.
 * Returns `null` when not close enough to a lattice point or outside the axis bounds (student quiz parity).
 */
const pixelToSnappedGrid = (
  pixelX: number,
  pixelY: number,
  logicalSize: number,
  minCoord: number,
  maxCoord: number,
): CoordinatePoint | null => {
  const drawableWidth = logicalSize - 2 * PADDING;
  const drawableHeight = logicalSize - 2 * PADDING;
  const centerX = PADDING + drawableWidth / 2;
  const centerY = PADDING + drawableHeight / 2;
  const pixelsPerUnit = Math.min(drawableWidth, drawableHeight) / (maxCoord - minCoord);
  const graphX = (pixelX - centerX) / pixelsPerUnit;
  const graphY = (centerY - pixelY) / pixelsPerUnit;
  const snappedX = Math.round(graphX);
  const snappedY = Math.round(graphY);
  const distX = Math.abs(graphX - snappedX);
  const distY = Math.abs(graphY - snappedY);
  if (
    distX <= SNAP_THRESHOLD &&
    distY <= SNAP_THRESHOLD &&
    snappedX >= minCoord &&
    snappedX <= maxCoord &&
    snappedY >= minCoord &&
    snappedY <= maxCoord
  ) {
    return { x: snappedX, y: snappedY };
  }
  return null;
};

/** True if either coordinate falls outside `[-axisRange, axisRange]`. */
const isPointOutOfRange = (point: CoordinatePoint, axisRange: number): boolean => {
  const min = -axisRange;
  const max = axisRange;
  return point.x < min || point.x > max || point.y < min || point.y > max;
};

/**
 * Renders the coordinates answer editor: list of points, canvas grid, axis range selector, and marker overlays.
 */
const FormCoordinates = ({ field, activeQuestionIndex = 0, axisRangeControllerProps }: FormCoordinatesProps) => {
  const form = useFormContext();
  const option = field.value;
  const resolvedQuestionDataStatusPath = Array.isArray(form?.getValues?.('questions'))
    ? (`questions.${activeQuestionIndex}._data_status` as const)
    : ('_data_status' as const);
  const activeQuestionDataStatus = form
    ? ((form.watch(resolvedQuestionDataStatusPath) as QuizDataStatus | undefined) ?? QuizDataStatus.NO_CHANGE)
    : QuizDataStatus.NO_CHANGE;
  const axisRange = resolveAxisRange(axisRangeControllerProps?.field.value);
  const minCoord = -axisRange;
  const maxCoord = axisRange;
  const a11yBaseId = useId();
  const instructionId = `${a11yBaseId}-instruction`;
  const liveRegionId = `${a11yBaseId}-live-region`;
  const canvasRef = useRef<HTMLCanvasElement | null>(null);
  const liveRegionRef = useRef<HTMLDivElement | null>(null);
  const gridWrapRef = useRef<HTMLDivElement | null>(null);
  const focusFromPointerRef = useRef(false);
  const [gridLogicalSize, setGridLogicalSize] = useState(CANVAS_SIZE);
  const [keyboardActive, setKeyboardActive] = useState(false);
  const [keyboardCursor, setKeyboardCursor] = useState<CoordinatePoint | null>(null);
  const [coordinates, setCoordinates] = useState<CoordinatePoint[]>(() => {
    const parsed = parseStoredCoordinates(option?.answer_two_gap_match ?? '');
    return parsed.length ? parsed : [{ x: 0, y: 0 }];
  });
  const [drafts, setDrafts] = useState<string[]>(() => coordinates.map(formatCoordinateText));
  const [activeIndex, setActiveIndex] = useState(0);

  // Keep local editable state in sync when the persisted answer payload changes externally
  // (e.g. question switch/reset), while preserving a valid active index.
  useEffect(() => {
    const parsed = parseStoredCoordinates(option?.answer_two_gap_match ?? '');
    const next = parsed.length ? parsed : [{ x: 0, y: 0 }];
    setCoordinates(next);
    setDrafts(next.map(formatCoordinateText));
    setActiveIndex((idx) => Math.max(0, Math.min(next.length - 1, idx)));
  }, [option?.answer_two_gap_match]);

  const updateOption = useCallback(
    (updated: QuizQuestionOption) => {
      field.onChange(updated);
    },
    [field],
  );

  /** Normalizes coordinates, syncs drafts/active index, and persists `answer_two_gap_match` on the option. */
  const commitCoordinates = useCallback(
    (nextCoords: CoordinatePoint[]) => {
      if (!option) return;
      const normalized = (nextCoords.length ? nextCoords : [{ x: 0, y: 0 }])
        .slice(0, MAX_COORDINATES)
        .map((point) => sanitizePoint(point, axisRange));
      setCoordinates(normalized);
      setDrafts(normalized.map(formatCoordinateText));
      setActiveIndex((idx) => Math.max(0, Math.min(normalized.length - 1, idx)));
      updateOption({
        ...option,
        ...(calculateQuizDataStatus(option._data_status, QuizDataStatus.UPDATE) && {
          _data_status: calculateQuizDataStatus(option._data_status, QuizDataStatus.UPDATE) as QuizDataStatus,
        }),
        answer_two_gap_match: JSON.stringify(normalized),
        is_saved: true,
      });
    },
    [axisRange, option, updateOption],
  );

  /** Draws axes, grid lines, and numeric tick labels on the backing canvas. */
  const drawGrid = useCallback(
    (ctx: CanvasRenderingContext2D, width: number, height: number) => {
      const drawableWidth = width - 2 * PADDING;
      const drawableHeight = height - 2 * PADDING;
      const centerX = PADDING + drawableWidth / 2;
      const centerY = PADDING + drawableHeight / 2;
      const pixelsPerUnit = Math.min(drawableWidth, drawableHeight) / (maxCoord - minCoord);

      const graphToPixel = (gx: number, gy: number) => ({
        x: centerX + gx * pixelsPerUnit,
        y: centerY - gy * pixelsPerUnit,
      });

      ctx.clearRect(0, 0, width, height);

      const leftEdge = graphToPixel(minCoord, 0).x;
      const rightEdge = graphToPixel(maxCoord, 0).x;
      const topEdge = graphToPixel(0, maxCoord).y;
      const bottomEdge = graphToPixel(0, minCoord).y;

      ctx.strokeStyle = '#cecfd2';
      ctx.lineWidth = 0.5;
      for (let i = minCoord; i <= maxCoord; i++) {
        if (i === 0) continue;
        const p = graphToPixel(i, 0);
        ctx.beginPath();
        ctx.moveTo(p.x, topEdge);
        ctx.lineTo(p.x, bottomEdge);
        ctx.stroke();
        const py = graphToPixel(0, i);
        ctx.beginPath();
        ctx.moveTo(leftEdge, py.y);
        ctx.lineTo(rightEdge, py.y);
        ctx.stroke();
      }

      ctx.strokeStyle = '#0c111d';
      ctx.lineWidth = 1.5;
      ctx.beginPath();
      ctx.moveTo(leftEdge, centerY);
      ctx.lineTo(rightEdge, centerY);
      ctx.stroke();
      ctx.beginPath();
      ctx.moveTo(centerX, topEdge);
      ctx.lineTo(centerX, bottomEdge);
      ctx.stroke();

      ctx.fillStyle = colorTokens.text.subdued;
      ctx.font = '11px Arial';
      ctx.textAlign = 'center';
      ctx.textBaseline = 'top';
      for (let i = minCoord; i <= maxCoord; i++) {
        if (i === 0 || !shouldRenderAxisLabel(i)) continue;
        const p = graphToPixel(i, 0);
        ctx.fillText(String(i), p.x, centerY + 5);
      }
      ctx.textAlign = 'right';
      ctx.textBaseline = 'middle';
      for (let i = minCoord; i <= maxCoord; i++) {
        if (i === 0 || !shouldRenderAxisLabel(i)) continue;
        const p = graphToPixel(0, i);
        ctx.fillText(String(i), centerX - 5, p.y);
      }
      ctx.textAlign = 'right';
      ctx.textBaseline = 'top';
      ctx.fillText('0', centerX - 5, centerY + 5);
    },
    [maxCoord, minCoord],
  );

  // Measure the responsive square canvas container and subscribe to size changes so marker/layout math
  // always uses the current rendered width; cleanup detaches observers/listeners and pending RAF.
  useEffect(() => {
    const wrap = gridWrapRef.current;
    if (!wrap) {
      return;
    }
    const measure = () => {
      setGridLogicalSize(Math.max(1, wrap.getBoundingClientRect().width || CANVAS_SIZE));
    };
    measure();
    if (typeof ResizeObserver === 'undefined') {
      window.addEventListener('resize', measure);
      return () => {
        window.removeEventListener('resize', measure);
        if (hoverRafRef.current !== null) {
          cancelAnimationFrame(hoverRafRef.current);
        }
      };
    }
    const ro = new ResizeObserver(measure);
    ro.observe(wrap);
    return () => {
      ro.disconnect();
      if (hoverRafRef.current !== null) {
        cancelAnimationFrame(hoverRafRef.current);
      }
    };
  }, []);

  // Redraw the canvas grid when either the logical canvas size or axis range math changes.
  // Canvas pixels are imperative state, so this rendering side effect must run after React commits.
  useEffect(() => {
    const canvas = canvasRef.current;
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    if (!ctx) return;
    const logicalSize = gridLogicalSize;
    const dpr = window.devicePixelRatio || 1;
    const nextWidth = Math.max(1, Math.round(logicalSize * dpr));
    const nextHeight = Math.max(1, Math.round(logicalSize * dpr));

    if (canvas.width !== nextWidth || canvas.height !== nextHeight) {
      canvas.width = nextWidth;
      canvas.height = nextHeight;
    }

    canvas.style.width = `${logicalSize}px`;
    canvas.style.height = `${logicalSize}px`;

    const scaleX = canvas.width / logicalSize;
    const scaleY = canvas.height / logicalSize;
    ctx.setTransform(scaleX, 0, 0, scaleY, 0, 0);

    drawGrid(ctx, logicalSize, logicalSize);
  }, [drawGrid, gridLogicalSize]);

  const hoverTargetRef = useRef<CoordinatePoint | null>(null);
  const hoverAlphaRef = useRef(0);
  const hoverScaleRef = useRef(HOVER_SCALE_MIN);
  const hoverRafRef = useRef<number | null>(null);
  const [, setHoverRenderTick] = useState(0);

  /**
   * Eases hover marker opacity and scale toward the pointer target each frame (`HOVER_ALPHA_LERP`).
   */
  const runHoverFrame = useCallback(() => {
    hoverRafRef.current = null;
    const targetAlpha = hoverTargetRef.current ? 1 : 0;
    const targetScale = hoverTargetRef.current ? 1 : HOVER_SCALE_MIN;
    let a = hoverAlphaRef.current;
    let s = hoverScaleRef.current;
    a += (targetAlpha - a) * HOVER_ALPHA_LERP;
    s += (targetScale - s) * HOVER_ALPHA_LERP;
    if (Math.abs(targetAlpha - a) < 0.01) {
      a = targetAlpha;
    }
    if (Math.abs(targetScale - s) < 0.01) {
      s = targetScale;
    }
    hoverAlphaRef.current = a;
    hoverScaleRef.current = s;
    setHoverRenderTick((t) => t + 1);
    if (a !== targetAlpha || s !== targetScale) {
      hoverRafRef.current = requestAnimationFrame(runHoverFrame);
    }
  }, []);

  /** Schedules one animation frame for hover easing if not already queued. */
  const queueHoverFrame = useCallback(() => {
    if (hoverRafRef.current === null) {
      hoverRafRef.current = requestAnimationFrame(runHoverFrame);
    }
  }, [runHoverFrame]);

  /** Clears eased hover preview state and cancels any pending hover animation frame. */
  const clearHoverPreview = useCallback(() => {
    hoverTargetRef.current = null;
    hoverAlphaRef.current = 0;
    hoverScaleRef.current = HOVER_SCALE_MIN;
    if (hoverRafRef.current !== null) {
      cancelAnimationFrame(hoverRafRef.current);
      hoverRafRef.current = null;
    }
    setHoverRenderTick((t) => t + 1);
  }, []);

  /** Updates eased hover preview to the grid cell under the cursor, or clears it when not snapped. */
  const handleCanvasMouseMove = useCallback(
    (e: React.MouseEvent<HTMLCanvasElement>) => {
      const canvas = canvasRef.current;
      if (!canvas) {
        return;
      }
      const rect = canvas.getBoundingClientRect();
      const logicalSize = Math.max(1, rect.width || CANVAS_SIZE);
      const pixelX = e.clientX - rect.left;
      const pixelY = e.clientY - rect.top;
      const snapped = pixelToSnappedGrid(pixelX, pixelY, logicalSize, minCoord, maxCoord);
      if (!snapped) {
        if (hoverTargetRef.current !== null || hoverAlphaRef.current > 0.01) {
          clearHoverPreview();
        }
        return;
      }
      const prev = hoverTargetRef.current;
      if (prev?.x === snapped.x && prev?.y === snapped.y) {
        return;
      }
      hoverTargetRef.current = snapped;
      if (!prev) {
        hoverAlphaRef.current = 0;
        hoverScaleRef.current = HOVER_SCALE_MIN;
      }
      queueHoverFrame();
    },
    [clearHoverPreview, maxCoord, minCoord, queueHoverFrame],
  );

  /** Hides hover preview when the pointer exits the canvas. */
  const handleCanvasMouseLeave = useCallback(() => {
    if (hoverTargetRef.current !== null || hoverAlphaRef.current > 0.01) {
      clearHoverPreview();
    }
  }, [clearHoverPreview]);

  const announceStatus = useCallback((message: string) => {
    announceQuizBuilderPolite(liveRegionRef.current, message);
  }, []);

  /** Apply a grid point to the active coordinate (click or keyboard Enter). */
  const applyGridPoint = useCallback(
    (snapped: CoordinatePoint) => {
      if (!option) {
        return;
      }

      const existingIdx = coordinates.findIndex((p) => p.x === snapped.x && p.y === snapped.y);
      if (existingIdx !== -1) {
        setActiveIndex(existingIdx);
        announceStatus(
          sprintf(
            // translators: %1$d is the x coordinate, %2$d is the y coordinate
            __('Coordinate (%1$d, %2$d) selected.', __TUTOR_TEXT_DOMAIN__),
            snapped.x,
            snapped.y,
          ),
        );
        return;
      }

      const next = coordinates.slice();
      next[activeIndex] = { x: snapped.x, y: snapped.y };
      commitCoordinates(next);
      announceStatus(
        sprintf(
          // translators: %1$d is the x coordinate, %2$d is the y coordinate
          __('Coordinate set to (%1$d, %2$d).', __TUTOR_TEXT_DOMAIN__),
          snapped.x,
          snapped.y,
        ),
      );
    },
    [activeIndex, announceStatus, commitCoordinates, coordinates, option],
  );

  /** Sets the active coordinate slot to the clicked grid intersection (or focuses an existing match). */
  const handleCanvasClick = useCallback(
    (e: React.MouseEvent<HTMLCanvasElement>) => {
      const canvas = canvasRef.current;
      if (!canvas || !option) return;

      const rect = canvas.getBoundingClientRect();
      const size = Math.max(1, rect.width || CANVAS_SIZE);
      const pixelX = e.clientX - rect.left;
      const pixelY = e.clientY - rect.top;

      const snapped = pixelToSnappedGrid(pixelX, pixelY, size, minCoord, maxCoord);
      if (!snapped) {
        return;
      }

      applyGridPoint(snapped);
    },
    [applyGridPoint, maxCoord, minCoord, option],
  );

  const handleCanvasPointerDown = useCallback(() => {
    focusFromPointerRef.current = true;
  }, []);

  const handleCanvasFocus = useCallback(() => {
    bindQuizBuilderDescribedBy(canvasRef.current, [instructionId]);
    if (focusFromPointerRef.current) {
      focusFromPointerRef.current = false;
      return;
    }
    clearHoverPreview();
    setKeyboardActive(true);
    setKeyboardCursor((prev) => prev ?? coordinates[activeIndex] ?? { x: 0, y: 0 });
  }, [activeIndex, clearHoverPreview, coordinates, instructionId]);

  const handleCanvasBlur = useCallback(() => {
    setKeyboardActive(false);
    setKeyboardCursor(null);
  }, []);

  const handleCanvasKeyDown = useCallback(
    (event: React.KeyboardEvent<HTMLCanvasElement>) => {
      const key = normalizeQuizBuilderKey(event);

      if (key === 'Enter') {
        event.preventDefault();
        const cursor = keyboardCursor ?? coordinates[activeIndex] ?? { x: 0, y: 0 };
        if (!keyboardActive) {
          setKeyboardActive(true);
          setKeyboardCursor(cursor);
        }
        applyGridPoint(cursor);
        return;
      }

      if (key === 'Backspace' || key === 'Delete') {
        event.preventDefault();
        if (coordinates.length <= 1) {
          announceStatus(__('At least one coordinate is required.', __TUTOR_TEXT_DOMAIN__));
          return;
        }
        const next = coordinates.slice();
        next.splice(activeIndex, 1);
        commitCoordinates(next);
        announceStatus(__('Coordinate removed.', __TUTOR_TEXT_DOMAIN__));
        return;
      }

      if (!isQuizBuilderGridMoveKey(key)) {
        return;
      }

      event.preventDefault();
      const current = keyboardCursor ?? coordinates[activeIndex] ?? { x: 0, y: 0 };
      if (!keyboardActive) {
        setKeyboardActive(true);
      }
      const nextCursor = moveQuizBuilderGridCursor(current, key, { min: minCoord, max: maxCoord });
      if (!nextCursor) {
        return;
      }
      setKeyboardCursor(nextCursor);
      announceStatus(
        sprintf(
          // translators: %1$d is the x coordinate, %2$d is the y coordinate
          __('Grid position (%1$d, %2$d). Press Enter to set the active coordinate.', __TUTOR_TEXT_DOMAIN__),
          nextCursor.x,
          nextCursor.y,
        ),
      );
    },
    [
      activeIndex,
      announceStatus,
      applyGridPoint,
      commitCoordinates,
      coordinates,
      keyboardActive,
      keyboardCursor,
      maxCoord,
      minCoord,
    ],
  );

  /**
   * Applies a new axis range via react-hook-form so the question stays dirty until explicitly saved
   * (including toggling 10 ↔ 20) and quiz data status reflects an update when a form context exists.
   */
  const handleAxisRangeChange = useCallback(
    (selectedOption: { value: number | string }) => {
      const nextAxisRange = resolveAxisRange(Number(selectedOption.value));

      if (form && axisRangeControllerProps?.field.name) {
        form.setValue(axisRangeControllerProps.field.name as never, nextAxisRange as never, {
          shouldDirty: true,
        });
      } else {
        axisRangeControllerProps?.field.onChange(nextAxisRange);
      }

      if (!form) {
        return;
      }

      const nextQuestionDataStatus = calculateQuizDataStatus(activeQuestionDataStatus, QuizDataStatus.UPDATE);
      if (nextQuestionDataStatus) {
        form.setValue(resolvedQuestionDataStatusPath, nextQuestionDataStatus as QuizDataStatus, {
          shouldDirty: true,
        });
      }
    },
    [activeQuestionDataStatus, axisRangeControllerProps, form, resolvedQuestionDataStatusPath],
  );

  if (!option) {
    return null;
  }

  return (
    <div css={styles.wrapper}>
      <div css={styles.card}>
        <div css={styles.answerHeader}>
          <span css={styles.answerHeaderTitle}>{__('Coordinates', __TUTOR_TEXT_DOMAIN__)}</span>
        </div>
        <div css={styles.list}>
          {coordinates.map((pt, idx) => {
            const isActive = idx === activeIndex;
            const isAtLimit = coordinates.length >= MAX_COORDINATES;
            const isOnlyOne = coordinates.length <= 1;
            const hasRangeError = isPointOutOfRange(pt, axisRange);

            return (
              <div
                key={`coord-${idx}`}
                css={styles.listRow({ isActive })}
                onClick={() => setActiveIndex(idx)}
                onKeyDown={(event) => {
                  if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    setActiveIndex(idx);
                    setKeyboardCursor(coordinates[idx] ?? { x: 0, y: 0 });
                  }
                }}
                role="button"
                tabIndex={0}
              >
                <div css={styles.rowTop}>
                  <div css={styles.rowIndex}>{idx + 1}</div>
                  <div css={styles.rowActions}>
                    <Tooltip content={__('Duplicate', __TUTOR_TEXT_DOMAIN__)} delay={200}>
                      <button
                        type="button"
                        css={styleUtils.actionButton}
                        onClick={(e) => {
                          e.stopPropagation();
                          if (isAtLimit) return;
                          const next = coordinates.slice();
                          next.splice(idx + 1, 0, { ...coordinates[idx] });
                          commitCoordinates(next);
                          setActiveIndex(idx + 1);
                        }}
                        disabled={isAtLimit}
                        aria-label={__('Copy coordinate', __TUTOR_TEXT_DOMAIN__)}
                      >
                        <SVGIcon name="copyPaste" width={24} height={24} />
                      </button>
                    </Tooltip>
                    <Tooltip content={__('Delete', __TUTOR_TEXT_DOMAIN__)} delay={200}>
                      <button
                        type="button"
                        css={styleUtils.actionButton}
                        onClick={(e) => {
                          e.stopPropagation();
                          if (isOnlyOne) return;
                          const next = coordinates.slice();
                          next.splice(idx, 1);
                          commitCoordinates(next);
                          setActiveIndex((current) => {
                            if (current > idx) return current - 1;
                            if (current === idx) return Math.max(0, idx - 1);
                            return current;
                          });
                        }}
                        disabled={isOnlyOne}
                        aria-label={__('Delete coordinate', __TUTOR_TEXT_DOMAIN__)}
                      >
                        <SVGIcon name="delete" width={24} height={24} />
                      </button>
                    </Tooltip>
                  </div>
                </div>
                <TextInput
                  value={drafts[idx] ?? formatCoordinateText(pt)}
                  size="small"
                  inputCss={styles.rowInput}
                  onChange={(value) => {
                    const next = drafts.slice();
                    next[idx] = value;
                    setDrafts(next);
                  }}
                  onBlur={(value) => {
                    const parsed = parseCoordinateText(value ?? '', axisRange);
                    if (!parsed) {
                      setDrafts((prev) => {
                        const next = prev.slice();
                        next[idx] = formatCoordinateText(coordinates[idx] ?? { x: 0, y: 0 });
                        return next;
                      });
                      return;
                    }
                    const nextCoords = coordinates.slice();
                    nextCoords[idx] = parsed;
                    commitCoordinates(nextCoords);
                  }}
                  onKeyDown={(keyName, event) => {
                    if (keyName !== 'Enter') return;
                    event.currentTarget.blur();
                  }}
                  placeholder={__('x,y', __TUTOR_TEXT_DOMAIN__)}
                />
                {hasRangeError && <p css={styles.rowError}>{getCoordinatesRangeErrorMessage(axisRange)}</p>}
              </div>
            );
          })}
        </div>
        <Button
          variant="text"
          size="small"
          icon={<SVGIcon name="plus" width={18} height={18} />}
          buttonCss={styles.addButton}
          onClick={() => {
            if (coordinates.length >= MAX_COORDINATES) return;
            const next = coordinates.concat([{ x: 0, y: 0 }]);
            commitCoordinates(next);
            setActiveIndex(next.length - 1);
          }}
          disabled={coordinates.length >= MAX_COORDINATES}
        >
          {__('Add Coordinates', __TUTOR_TEXT_DOMAIN__)}
        </Button>
        <p id={instructionId} css={quizBuilderSrOnlyCss}>
          {__(
            'Use arrow keys to move the active grid point, Enter to set it, and Backspace or Delete to remove the active coordinate.',
            __TUTOR_TEXT_DOMAIN__,
          )}
        </p>
        <div
          id={liveRegionId}
          ref={liveRegionRef}
          css={quizBuilderSrOnlyCss}
          aria-live="polite"
          aria-atomic="true"
          role="status"
        />
        <div css={styles.canvasWrap} ref={gridWrapRef}>
          <canvas
            ref={canvasRef}
            width={CANVAS_SIZE}
            height={CANVAS_SIZE}
            css={[styles.canvas, quizBuilderInteractionFocusCss]}
            tabIndex={0}
            role="application"
            aria-describedby={instructionId}
            onPointerDown={handleCanvasPointerDown}
            onClick={handleCanvasClick}
            onFocus={handleCanvasFocus}
            onBlur={handleCanvasBlur}
            onKeyDown={handleCanvasKeyDown}
            onMouseMove={handleCanvasMouseMove}
            onMouseLeave={handleCanvasMouseLeave}
            aria-label={__(
              'Coordinate grid: click or use arrow keys and Enter to set the correct answer point.',
              __TUTOR_TEXT_DOMAIN__,
            )}
          />
          <div css={styles.markerLayer} aria-hidden>
            {coordinates.map((coord, idx) => {
              const pt = graphToPixelLayout(gridLogicalSize, minCoord, maxCoord, coord.x, coord.y);
              const isActive = idx === activeIndex;
              const markerPct = (MARKER_DISPLAY_SIZE / gridLogicalSize) * 100;
              const markerSrc = graphMarkerAssetUrl('graph-marker-selected');
              return (
                <img
                  key={`coord-marker-${idx}-${coord.x}-${coord.y}`}
                  src={markerSrc}
                  alt=""
                  css={styles.markerImg}
                  style={{
                    left: `${(pt.x / gridLogicalSize) * 100}%`,
                    top: `${(pt.y / gridLogicalSize) * 100}%`,
                    width: `${markerPct}%`,
                    height: `${markerPct}%`,
                    objectFit: 'contain',
                    transform: `translate(-50%, -50%) scale(${isActive ? 1.05 : 1})`,
                  }}
                />
              );
            })}
            {keyboardActive && keyboardCursor
              ? (() => {
                  const keyboardPt = graphToPixelLayout(
                    gridLogicalSize,
                    minCoord,
                    maxCoord,
                    keyboardCursor.x,
                    keyboardCursor.y,
                  );
                  const markerPct = (MARKER_DISPLAY_SIZE / gridLogicalSize) * 100;
                  return (
                    <img
                      key="coord-grid-keyboard-preview"
                      src={graphMarkerAssetUrl('graph-marker-hover')}
                      alt=""
                      css={styles.markerImg}
                      style={{
                        left: `${(keyboardPt.x / gridLogicalSize) * 100}%`,
                        top: `${(keyboardPt.y / gridLogicalSize) * 100}%`,
                        width: `${markerPct}%`,
                        height: `${markerPct}%`,
                        objectFit: 'contain',
                        transform: 'translate(-50%, -50%)',
                      }}
                    />
                  );
                })()
              : null}
            {!keyboardActive && hoverTargetRef.current !== null && hoverAlphaRef.current > 0.01
              ? (() => {
                  const hoverCell = hoverTargetRef.current;
                  if (!hoverCell) {
                    return null;
                  }
                  const hoverPt = graphToPixelLayout(gridLogicalSize, minCoord, maxCoord, hoverCell.x, hoverCell.y);
                  const markerPct = (MARKER_DISPLAY_SIZE / gridLogicalSize) * 100;
                  return (
                    <img
                      key="coord-grid-hover-preview"
                      src={graphMarkerAssetUrl('graph-marker-hover')}
                      alt=""
                      css={styles.markerImg}
                      style={{
                        left: `${(hoverPt.x / gridLogicalSize) * 100}%`,
                        top: `${(hoverPt.y / gridLogicalSize) * 100}%`,
                        width: `${markerPct}%`,
                        height: `${markerPct}%`,
                        objectFit: 'contain',
                        opacity: hoverAlphaRef.current,
                        transform: `translate(-50%, -50%) scale(${Math.max(0.8, hoverScaleRef.current)})`,
                      }}
                    />
                  );
                })()
              : null}
          </div>
        </div>
        {axisRangeControllerProps && (
          <FormSelectInput
            {...axisRangeControllerProps}
            leftIconPadding={36}
            size="small"
            options={AXIS_RANGE_OPTIONS}
            onChange={handleAxisRangeChange}
          />
        )}
      </div>
    </div>
  );
};

export default FormCoordinates;

const styles = {
  wrapper: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[24]};

    ${Breakpoint.smallMobile} {
      padding-left: ${spacing[8]};
    }
  `,
  card: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[16]};
    padding: ${spacing[20]};
  `,
  answerHeader: css`
    ${styleUtils.display.flex('row')};
    align-items: center;
    justify-content: space-between;
    gap: ${spacing[12]};
  `,
  answerHeaderTitle: css`
    ${typography.body('medium')};
    color: ${colorTokens.text.primary};
  `,
  hint: css`
    ${typography.caption()};
    color: ${colorTokens.text.subdued};
    margin: 0;
  `,
  list: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[12]};
  `,
  listRow: ({ isActive }: { isActive: boolean }) => css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[12]};
    padding: ${spacing[12]};
    border-radius: ${borderRadius.card};
    border: ${isActive ? `1px solid ${colorTokens.stroke.brand}` : 'none'};
    background: ${colorTokens.background.white};
    cursor: pointer;

    &:focus-visible {
      outline: 2px solid ${colorTokens.stroke.brand};
      outline-offset: 0;
    }
  `,
  rowTop: css`
    ${styleUtils.display.flex('row')};
    align-items: center;
    justify-content: space-between;
    gap: ${spacing[8]};
    width: 100%;
  `,
  rowIndex: css`
    width: 26px;
    height: 26px;
    border-radius: 6px;
    ${styleUtils.flexCenter()};
    ${typography.caption('medium')};
    color: ${colorTokens.text.primary};
    background: ${colorTokens.background.hover};
    flex-shrink: 0;
  `,
  rowInput: css`
    ${typography.small()};
    width: 100%;
    padding: ${spacing[10]} ${spacing[12]};
    border-radius: 8px;
    color: ${colorTokens.text.primary};
    background: ${colorTokens.background.white};

    &:focus {
      outline: 2px solid ${colorTokens.stroke.brand};
      outline-offset: 1px;
    }
  `,
  rowActions: css`
    ${styleUtils.display.flex('row')};
    align-items: center;
    flex-shrink: 0;
    gap: ${spacing[8]};
  `,
  rowError: css`
    ${typography.caption()};
    color: ${colorTokens.text.error};
    margin: 0;
  `,
  addButton: css`
    ${typography.small('medium')};
    width: fit-content;
    border: none;
    color: ${colorTokens.text.brand};

    &:focus {
      border: none;
      box-shadow: none;
    }

    &:focus-visible {
      border: none;
      outline: 2px solid ${colorTokens.stroke.brand};
      outline-offset: 2px;
      box-shadow: none;
    }

    &:disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }
  `,
  canvasWrap: css`
    display: block;
    position: relative;
    width: 100%;
    aspect-ratio: 1 / 1;
    overflow: hidden;
    background-color: ${colorTokens.background.white};
    border-radius: ${borderRadius.card};
  `,
  markerLayer: css`
    position: absolute;
    inset: 0;
    pointer-events: none;
  `,
  markerImg: css`
    position: absolute;
    display: block;
    object-fit: contain;
    object-position: center;
    transform-origin: center center;
  `,
  canvas: css`
    display: block;
    width: 100%;
    height: 100%;
    cursor: crosshair;
    border: 1px solid #ececed;
    border-radius: ${borderRadius.card};
  `,
};
