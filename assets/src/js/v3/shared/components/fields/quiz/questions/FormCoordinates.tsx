/**
 * Form field for Coordinates quiz question type (instructor sets target point on grid).
 *
 * @package Tutor
 * @since 4.0.0
 */

import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useCallback, useEffect, useRef, useState } from 'react';

import Button from '@TutorShared/atoms/Button';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import TextInput from '@TutorShared/atoms/TextInput';
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

const PADDING = 45;
const MIN_COORD = -10;
const MAX_COORD = 10;
const SNAP_THRESHOLD = 0.3;
const CANVAS_SIZE = 420;
const MAX_COORDINATES = 5;

interface FormCoordinatesProps extends FormControllerProps<QuizQuestionOption> {
  questionId: ID;
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

function clampIntToRange(n: number): number {
  const rounded = Math.round(n);
  return Math.max(MIN_COORD, Math.min(MAX_COORD, rounded));
}

function sanitizePoint(p: CoordinatePoint): CoordinatePoint {
  return {
    x: clampIntToRange(p.x),
    y: clampIntToRange(p.y),
  };
}

function parseStoredCoordinates(value: string): CoordinatePoint[] {
  if (!value || typeof value !== 'string') return [];
  try {
    const parsed = JSON.parse(value) as unknown;
    if (Array.isArray(parsed)) {
      const pts = parsed
        .map((p) => {
          if (!p || typeof p !== 'object') return null;
          const maybe = p as { x?: unknown; y?: unknown };
          if (typeof maybe.x !== 'number' || typeof maybe.y !== 'number') return null;
          return sanitizePoint({ x: maybe.x, y: maybe.y });
        })
        .filter(Boolean) as CoordinatePoint[];
      return pts.slice(0, MAX_COORDINATES);
    }
    if (parsed && typeof parsed === 'object') {
      const o = parsed as { x?: unknown; y?: unknown };
      if (typeof o.x === 'number' && typeof o.y === 'number') {
        return [sanitizePoint({ x: o.x, y: o.y })];
      }
    }
  } catch {
    // ignore
  }
  return [];
}

function parseCoordinateText(value: string): CoordinatePoint | null {
  const raw = (value ?? '').trim();
  if (!raw) return null;
  const parts = raw.split(',').map((p) => p.trim());
  if (parts.length !== 2) return null;
  const x = Number(parts[0]);
  const y = Number(parts[1]);
  if (!Number.isFinite(x) || !Number.isFinite(y)) return null;
  if (!Number.isInteger(x) || !Number.isInteger(y)) return null;
  if (x < MIN_COORD || x > MAX_COORD || y < MIN_COORD || y > MAX_COORD) return null;
  return { x, y };
}

function formatCoordinateText(pt: CoordinatePoint): string {
  return `${pt.x},${pt.y}`;
}

const FormCoordinates = ({ field }: FormCoordinatesProps) => {
  const option = field.value;
  const canvasRef = useRef<HTMLCanvasElement | null>(null);
  const [coordinates, setCoordinates] = useState<CoordinatePoint[]>(() => {
    const parsed = parseStoredCoordinates(option?.answer_two_gap_match ?? '');
    return parsed.length ? parsed : [{ x: 0, y: 0 }];
  });
  const [drafts, setDrafts] = useState<string[]>(() => coordinates.map(formatCoordinateText));
  const [activeIndex, setActiveIndex] = useState(0);
  const activePoint = coordinates[activeIndex] ?? null;

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
        .map(sanitizePoint);
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
    [option, updateOption],
  );

  const drawGrid = useCallback(
    (ctx: CanvasRenderingContext2D, width: number, height: number) => {
      const drawableWidth = width - 2 * PADDING;
      const drawableHeight = height - 2 * PADDING;
      const centerX = PADDING + drawableWidth / 2;
      const centerY = PADDING + drawableHeight / 2;
      const pixelsPerUnit = Math.min(drawableWidth, drawableHeight) / (MAX_COORD - MIN_COORD);

      const graphToPixel = (gx: number, gy: number) => ({
        x: centerX + gx * pixelsPerUnit,
        y: centerY - gy * pixelsPerUnit,
      });

      ctx.clearRect(0, 0, width, height);

      const leftEdge = graphToPixel(MIN_COORD, 0).x;
      const rightEdge = graphToPixel(MAX_COORD, 0).x;
      const topEdge = graphToPixel(0, MAX_COORD).y;
      const bottomEdge = graphToPixel(0, MIN_COORD).y;

      ctx.strokeStyle = '#e0e0e0';
      ctx.lineWidth = 0.5;
      for (let i = MIN_COORD; i <= MAX_COORD; i++) {
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

      ctx.strokeStyle = '#000';
      ctx.lineWidth = 1.5;
      ctx.beginPath();
      ctx.moveTo(leftEdge, centerY);
      ctx.lineTo(rightEdge, centerY);
      ctx.stroke();
      ctx.beginPath();
      ctx.moveTo(centerX, topEdge);
      ctx.lineTo(centerX, bottomEdge);
      ctx.stroke();

      ctx.fillStyle = '#666';
      ctx.font = '11px Arial';
      ctx.textAlign = 'center';
      ctx.textBaseline = 'top';
      for (let i = MIN_COORD; i <= MAX_COORD; i++) {
        if (i === 0) continue;
        const p = graphToPixel(i, 0);
        ctx.fillText(String(i), p.x, centerY + 5);
      }
      ctx.textAlign = 'right';
      ctx.textBaseline = 'middle';
      for (let i = MIN_COORD; i <= MAX_COORD; i++) {
        if (i === 0) continue;
        const p = graphToPixel(0, i);
        ctx.fillText(String(i), centerX - 5, p.y);
      }
      ctx.textAlign = 'right';
      ctx.textBaseline = 'top';
      ctx.fillText('0', centerX - 5, centerY + 5);

      if (activePoint !== null) {
        const pt = graphToPixel(activePoint.x, activePoint.y);
        ctx.beginPath();
        ctx.arc(pt.x, pt.y, 8, 0, 2 * Math.PI);
        ctx.fillStyle = '#2196F3';
        ctx.fill();
        ctx.strokeStyle = '#fff';
        ctx.lineWidth = 2;
        ctx.stroke();
        ctx.beginPath();
        ctx.arc(pt.x, pt.y, 3, 0, 2 * Math.PI);
        ctx.fillStyle = '#fff';
        ctx.fill();
      }
    },
    [activePoint],
  );

  useEffect(() => {
    const canvas = canvasRef.current;
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    if (!ctx) return;
    drawGrid(ctx, canvas.width, canvas.height);
  }, [drawGrid]);

  const handleCanvasClick = useCallback(
    (e: React.MouseEvent<HTMLCanvasElement>) => {
      const canvas = canvasRef.current;
      if (!canvas || !option) return;

      const rect = canvas.getBoundingClientRect();
      const scaleX = canvas.width / rect.width;
      const scaleY = canvas.height / rect.height;
      const pixelX = (e.clientX - rect.left) * scaleX;
      const pixelY = (e.clientY - rect.top) * scaleY;

      const width = canvas.width;
      const height = canvas.height;
      const drawableWidth = width - 2 * PADDING;
      const drawableHeight = height - 2 * PADDING;
      const centerX = PADDING + drawableWidth / 2;
      const centerY = PADDING + drawableHeight / 2;
      const pixelsPerUnit = Math.min(drawableWidth, drawableHeight) / (MAX_COORD - MIN_COORD);

      const graphX = (pixelX - centerX) / pixelsPerUnit;
      const graphY = (centerY - pixelY) / pixelsPerUnit;
      const snappedX = Math.round(graphX);
      const snappedY = Math.round(graphY);
      const distX = Math.abs(graphX - snappedX);
      const distY = Math.abs(graphY - snappedY);

      if (
        distX <= SNAP_THRESHOLD &&
        distY <= SNAP_THRESHOLD &&
        snappedX >= MIN_COORD &&
        snappedX <= MAX_COORD &&
        snappedY >= MIN_COORD &&
        snappedY <= MAX_COORD
      ) {
        const next = coordinates.slice();
        next[activeIndex] = { x: snappedX, y: snappedY };
        commitCoordinates(next);
      }
    },
    [activeIndex, commitCoordinates, coordinates, option],
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
        <div css={styles.list}>
          {coordinates.map((pt, idx) => {
            const isActive = idx === activeIndex;
            const isAtLimit = coordinates.length >= MAX_COORDINATES;
            const isOnlyOne = coordinates.length <= 1;

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
                    <Button
                      isIconOnly
                      variant="tertiary"
                      size="small"
                      buttonCss={styles.rowActionButton}
                      icon={<SVGIcon name="copy" width={20} height={20} />}
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
                    />
                    <Button
                      isIconOnly
                      variant="tertiary"
                      size="small"
                      buttonCss={styles.rowActionButton}
                      icon={<SVGIcon name="delete" width={20} height={20} />}
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
                    />
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
                    const parsed = parseCoordinateText(value ?? '');
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
  `,
  rowActionButton: css`
    border: none;
    box-shadow: none;

    &:hover,
    &:focus,
    &:focus-visible,
    &:active {
      border: none;
      box-shadow: none;
    }
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
    display: inline-block;
    border-radius: ${borderRadius.card};
    overflow: hidden;
  `,
  canvas: css`
    display: block;
    max-width: 100%;
    height: auto;
    cursor: crosshair;
    border: 1px solid ${colorTokens.stroke.border};
    border-radius: 6px;
  `,
};
