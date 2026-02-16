/**
 * Form field for Coordinates quiz question type (instructor sets target point on grid).
 *
 * @package Tutor
 * @since 4.0.0
 */

import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useCallback, useEffect, useRef, useState } from 'react';

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

function parseStoredCoordinate(value: string): { x: number; y: number } | null {
  if (!value || typeof value !== 'string') return null;
  try {
    const o = JSON.parse(value) as { x?: number; y?: number };
    if (typeof o.x === 'number' && typeof o.y === 'number') {
      return {
        x: Math.max(MIN_COORD, Math.min(MAX_COORD, Math.round(o.x))),
        y: Math.max(MIN_COORD, Math.min(MAX_COORD, Math.round(o.y))),
      };
    }
  } catch {
    // ignore
  }
  return null;
}

const FormCoordinates = ({ field }: FormCoordinatesProps) => {
  const option = field.value;
  const canvasRef = useRef<HTMLCanvasElement | null>(null);
  const [targetPoint, setTargetPoint] = useState<{ x: number; y: number } | null>(() =>
    parseStoredCoordinate(option?.answer_two_gap_match ?? ''),
  );

  useEffect(() => {
    const parsed = parseStoredCoordinate(option?.answer_two_gap_match ?? '');
    setTargetPoint(parsed);
  }, [option?.answer_two_gap_match]);

  const updateOption = useCallback(
    (updated: QuizQuestionOption) => {
      field.onChange(updated);
    },
    [field],
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

      if (targetPoint !== null) {
        const pt = graphToPixel(targetPoint.x, targetPoint.y);
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
    [targetPoint],
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
        const point = { x: snappedX, y: snappedY };
        setTargetPoint(point);
        const json = JSON.stringify(point);
        updateOption({
          ...option,
          ...(calculateQuizDataStatus(option._data_status, QuizDataStatus.UPDATE) && {
            _data_status: calculateQuizDataStatus(option._data_status, QuizDataStatus.UPDATE) as QuizDataStatus,
          }),
          answer_two_gap_match: json,
          is_saved: true,
        });
      }
    },
    [option, updateOption],
  );

  if (!option) {
    return null;
  }

  return (
    <div css={styles.wrapper}>
      <div css={styles.card}>
        <div css={styles.answerHeader}>
          <span css={styles.answerHeaderTitle}>{__('Set the correct coordinate', __TUTOR_TEXT_DOMAIN__)}</span>
        </div>
        <p css={styles.hint}>
          {__(
            'Click on a grid intersection to set the target coordinate. Students must select the same point.',
            __TUTOR_TEXT_DOMAIN__,
          )}
        </p>
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
        {targetPoint !== null && (
          <p css={styles.savedHint}>
            {__('Target coordinate:', __TUTOR_TEXT_DOMAIN__)} ({targetPoint.x}, {targetPoint.y})
          </p>
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
    padding-left: ${spacing[40]};

    ${Breakpoint.smallMobile} {
      padding-left: ${spacing[8]};
    }
  `,
  card: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[16]};
    padding: ${spacing[20]};
    background: ${colorTokens.surface.tutor};
    border: 1px solid ${colorTokens.stroke.border};
    border-radius: ${borderRadius.card};
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
    border: 2px solid ${colorTokens.stroke.border};
    border-radius: 8px;
  `,
  savedHint: css`
    ${typography.caption()};
    color: ${colorTokens.text.success};
    margin: 0;
  `,
};
