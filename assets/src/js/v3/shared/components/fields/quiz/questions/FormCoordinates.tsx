/**
 * Form field for Coordinates quiz question type (instructor sets target point on grid).
 *
 * @package Tutor
 * @since 4.0.0
 */

import { css } from '@emotion/react';
import { __, sprintf } from '@wordpress/i18n';
import { useCallback, useEffect, useRef, useState } from 'react';
import { useFormContext } from 'react-hook-form';

import Button from '@TutorShared/atoms/Button';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import TextInput from '@TutorShared/atoms/TextInput';
import FormSelectInput from '@TutorShared/components/fields/FormSelectInput';
import Tooltip from '@TutorShared/atoms/Tooltip';
import { borderRadius, Breakpoint, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import type { FormControllerProps } from '@TutorShared/utils/form';
import { calculateQuizDataStatus } from '@TutorShared/utils/quiz';
import { styleUtils } from '@TutorShared/utils/style-utils';
import {
  type ID,
  QuizDataStatus,
  type QuizQuestionOption,
  type QuizValidationErrorType,
} from '@TutorShared/utils/types';

const PADDING = 12;
const SNAP_THRESHOLD = 0.3;
const CANVAS_SIZE = 420;
const MAX_COORDINATES = 5;
const AXIS_LABEL_STEP = 2;
const AXIS_RANGE_OPTIONS = [10, 20].map((value) => ({
  label: `${value} ${__('points', __TUTOR_TEXT_DOMAIN__)}`,
  value,
}));

function shouldRenderAxisLabel(value: number): boolean {
  return value === 0 || Math.abs(value % AXIS_LABEL_STEP) === 0;
}

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

function resolveAxisRange(value?: number | null): 10 | 20 {
  return Number(value) === 20 ? 20 : 10;
}

function clampIntToRange(n: number, axisRange: number): number {
  const min = -axisRange;
  const max = axisRange;
  const rounded = Math.round(n);
  return Math.max(min, Math.min(max, rounded));
}

function sanitizePoint(p: CoordinatePoint, axisRange: number): CoordinatePoint {
  return {
    x: clampIntToRange(p.x, axisRange),
    y: clampIntToRange(p.y, axisRange),
  };
}

function parseStoredCoordinates(value: string, axisRange?: number): CoordinatePoint[] {
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
    // ignore
  }
  return [];
}

function parseCoordinateText(value: string, axisRange: number): CoordinatePoint | null {
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
}

function formatCoordinateText(pt: CoordinatePoint): string {
  return `${pt.x},${pt.y}`;
}

function getCoordinatesRangeErrorMessage(axisRange: number): string {
  return sprintf(__('Range is from -%d to %d', __TUTOR_TEXT_DOMAIN__), axisRange, axisRange);
}

function isPointOutOfRange(point: CoordinatePoint, axisRange: number): boolean {
  const min = -axisRange;
  const max = axisRange;
  return point.x < min || point.x > max || point.y < min || point.y > max;
}

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
  const canvasRef = useRef<HTMLCanvasElement | null>(null);
  const [coordinates, setCoordinates] = useState<CoordinatePoint[]>(() => {
    const parsed = parseStoredCoordinates(option?.answer_two_gap_match ?? '');
    return parsed.length ? parsed : [{ x: 0, y: 0 }];
  });
  const [drafts, setDrafts] = useState<string[]>(() => coordinates.map(formatCoordinateText));
  const [activeIndex, setActiveIndex] = useState(0);

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

      ctx.strokeStyle = colorTokens.stroke.divider;
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

      ctx.strokeStyle = colorTokens.background.black;
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

      const markerOuterLayer = colorTokens.color.black[20];
      const markerMiddleLayer = colorTokens.background.white;
      const markerFillActive = colorTokens.stroke.brand;
      const markerFillIdle = colorTokens.text.disable;

      const drawPointMarker = (pt: { x: number; y: number }, fillColor: string, scale = 1) => {
        const s = Number.isFinite(scale) ? scale : 1;
        ctx.beginPath();
        ctx.arc(pt.x, pt.y, 14 * s, 0, 2 * Math.PI);
        ctx.fillStyle = markerOuterLayer;
        ctx.fill();

        ctx.beginPath();
        ctx.arc(pt.x, pt.y, 10 * s, 0, 2 * Math.PI);
        ctx.fillStyle = markerMiddleLayer;
        ctx.fill();

        ctx.beginPath();
        ctx.arc(pt.x, pt.y, 6 * s, 0, 2 * Math.PI);
        ctx.fillStyle = fillColor;
        ctx.fill();
      };

      // Always show all set coordinates. Highlight the active one.
      coordinates.forEach((coord, idx) => {
        const isActive = idx === activeIndex;
        const pt = graphToPixel(coord.x, coord.y);
        drawPointMarker(pt, isActive ? markerFillActive : markerFillIdle, isActive ? 1.05 : 1);
      });
    },
    [activeIndex, coordinates, maxCoord, minCoord],
  );

  useEffect(() => {
    const canvas = canvasRef.current;
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    if (!ctx) return;
    const rect = canvas.getBoundingClientRect();
    const logicalSize = Math.max(1, rect.width || CANVAS_SIZE);
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
  }, [drawGrid]);

  const handleCanvasClick = useCallback(
    (e: React.MouseEvent<HTMLCanvasElement>) => {
      const canvas = canvasRef.current;
      if (!canvas || !option) return;

      const rect = canvas.getBoundingClientRect();
      const size = Math.max(1, rect.width || CANVAS_SIZE);
      const pixelX = e.clientX - rect.left;
      const pixelY = e.clientY - rect.top;

      const drawableWidth = size - 2 * PADDING;
      const drawableHeight = size - 2 * PADDING;
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
        const existingIdx = coordinates.findIndex((p) => p.x === snappedX && p.y === snappedY);
        if (existingIdx !== -1) {
          setActiveIndex(existingIdx);
          return;
        }

        const next = coordinates.slice();
        next[activeIndex] = { x: snappedX, y: snappedY };
        commitCoordinates(next);
      }
    },
    [activeIndex, commitCoordinates, coordinates, maxCoord, minCoord, option],
  );

  const handleAxisRangeChange = useCallback(
    (selectedOption: { value: number | string }) => {
      const nextAxisRange = resolveAxisRange(Number(selectedOption.value));

      // Keep the question "dirty" on range toggles (including 20 -> 10 -> 20),
      // so the Save action remains available until the user saves explicitly.
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
          <span css={styles.answerHeaderTitle}>{__('Coordinations', __TUTOR_TEXT_DOMAIN__)}</span>
        </div>
        {axisRangeControllerProps && (
          <FormSelectInput
            {...axisRangeControllerProps}
            label={__('Graph Range', __TUTOR_TEXT_DOMAIN__)}
            options={AXIS_RANGE_OPTIONS}
            helpText={__('Choose axis limits for both X and Y directions.', __TUTOR_TEXT_DOMAIN__)}
            onChange={handleAxisRangeChange}
          />
        )}
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
                    setActiveIndex(idx);
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
          {__('Add Coordination', __TUTOR_TEXT_DOMAIN__)}
        </Button>
        <div css={styles.canvasWrap}>
          <canvas
            ref={canvasRef}
            width={CANVAS_SIZE}
            height={CANVAS_SIZE}
            css={styles.canvas}
            onClick={handleCanvasClick}
            aria-label={__('Coordinate grid: click to set the correct answer point.', __TUTOR_TEXT_DOMAIN__)}
          />
        </div>
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

    &:focus,
    &:focus-visible {
      border: none;
      outline: none;
      box-shadow: none;
    }

    &:disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }
  `,
  canvasWrap: css`
    display: block;
    width: 100%;
    aspect-ratio: 1 / 1;
    overflow: hidden;
    background-color: ${colorTokens.background.white};
  `,
  canvas: css`
    display: block;
    width: 100%;
    height: 100%;
    cursor: crosshair;
    border: 1px solid ${colorTokens.stroke.border};
    border-radius: ${borderRadius.card};
  `,
};
