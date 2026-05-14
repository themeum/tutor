import { __ } from '@wordpress/i18n';
import { useEffect, useRef } from 'react';

import { colorTokens } from '@TutorShared/config/styles';

const CANVAS_SIZE = 420;
const PADDING = 12;
const AXIS_LABEL_STEP = 2;

const shouldRenderAxisLabel = (value: number): boolean => value === 0 || Math.abs(value % AXIS_LABEL_STEP) === 0;

/**
 * Normalizes stored axis range to supported values (10 or 20 units), matching FormCoordinates.
 */
const resolveAxisRange = (value?: number | null): 10 | 20 => (Number(value) === 20 ? 20 : 10);

interface CoordinatesPreviewProps {
  axisRange?: number | null;
}

const CoordinatesPreview = ({ axisRange: axisRangeProp }: CoordinatesPreviewProps) => {
  const canvasRef = useRef<HTMLCanvasElement>(null);
  const axisRange = resolveAxisRange(axisRangeProp);
  const minCoord = -axisRange;
  const maxCoord = axisRange;

  useEffect(() => {
    const canvas = canvasRef.current;
    if (!canvas) {
      return;
    }

    const context = canvas.getContext('2d');
    if (!context) {
      return;
    }

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
    context.setTransform(scaleX, 0, 0, scaleY, 0, 0);

    const width = logicalSize;
    const height = logicalSize;
    const drawableWidth = width - 2 * PADDING;
    const drawableHeight = height - 2 * PADDING;
    const centerX = PADDING + drawableWidth / 2;
    const centerY = PADDING + drawableHeight / 2;
    const pixelsPerUnit = Math.min(drawableWidth, drawableHeight) / (maxCoord - minCoord);

    const graphToPixel = (x: number, y: number) => ({
      x: centerX + x * pixelsPerUnit,
      y: centerY - y * pixelsPerUnit,
    });

    context.clearRect(0, 0, width, height);

    const leftEdge = graphToPixel(minCoord, 0).x;
    const rightEdge = graphToPixel(maxCoord, 0).x;
    const topEdge = graphToPixel(0, maxCoord).y;
    const bottomEdge = graphToPixel(0, minCoord).y;

    context.strokeStyle = colorTokens.stroke.divider;
    context.lineWidth = 0.5;
    for (let i = minCoord; i <= maxCoord; i++) {
      if (i === 0) {
        continue;
      }
      const xPoint = graphToPixel(i, 0);
      context.beginPath();
      context.moveTo(xPoint.x, topEdge);
      context.lineTo(xPoint.x, bottomEdge);
      context.stroke();

      const yPoint = graphToPixel(0, i);
      context.beginPath();
      context.moveTo(leftEdge, yPoint.y);
      context.lineTo(rightEdge, yPoint.y);
      context.stroke();
    }

    context.strokeStyle = colorTokens.color.black[80];
    context.lineWidth = 1.5;
    context.beginPath();
    context.moveTo(leftEdge, centerY);
    context.lineTo(rightEdge, centerY);
    context.stroke();

    context.beginPath();
    context.moveTo(centerX, topEdge);
    context.lineTo(centerX, bottomEdge);
    context.stroke();

    context.fillStyle = colorTokens.text.subdued;
    context.font = '11px Arial';
    context.textAlign = 'center';
    context.textBaseline = 'top';
    for (let i = minCoord; i <= maxCoord; i++) {
      if (i === 0 || !shouldRenderAxisLabel(i)) {
        continue;
      }
      const p = graphToPixel(i, 0);
      context.fillText(String(i), p.x, centerY + 5);
    }
    context.textAlign = 'right';
    context.textBaseline = 'middle';
    for (let i = minCoord; i <= maxCoord; i++) {
      if (i === 0 || !shouldRenderAxisLabel(i)) {
        continue;
      }
      const p = graphToPixel(0, i);
      context.fillText(String(i), centerX - 5, p.y);
    }
    context.textAlign = 'right';
    context.textBaseline = 'top';
    context.fillText('0', centerX - 5, centerY + 5);
  }, [minCoord, maxCoord]);

  return (
    <div className="tutor-quiz-question-options tutor-coordinates-question" data-question-type="coordinates">
      <div className="tutor-coordinates-grid-container">
        <canvas
          ref={canvasRef}
          className="tutor-coordinates-canvas"
          width={CANVAS_SIZE}
          height={CANVAS_SIZE}
          aria-label={__('Coordinate grid preview.', 'tutor')}
        />
      </div>
    </div>
  );
};

export default CoordinatesPreview;
