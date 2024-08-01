import { borderRadius, colorTokens, zIndex } from '@Config/styles';
import { drawGradientLine } from '@Utils/magic-ai';
import { css } from '@emotion/react';
import React, { type MouseEvent, useCallback, useEffect, useRef, useState } from 'react';

interface CanvasProps extends React.HTMLAttributes<HTMLCanvasElement> {
  src: string;
  width: number;
  height: number;
  brushSize: number;
  trackStack: ImageData[];
  pointer: number;
  setTrackStack: React.Dispatch<React.SetStateAction<ImageData[]>>;
  setPointer: React.Dispatch<React.SetStateAction<number>>;
}

interface Coordinate {
  x: number;
  y: number;
}

export const DrawingCanvas = React.forwardRef<HTMLCanvasElement, CanvasProps>(
  ({ src, width, height, brushSize, trackStack, pointer, setTrackStack, setPointer }: CanvasProps, canvasRef) => {
    const [isDragging, setIsDragging] = useState(false);
    const [startPosition, setStartPosition] = useState<Coordinate>({ x: 0, y: 0 });
    const cursorRef = useRef<HTMLDivElement>(null);

    const getCanvas = useCallback(() => {
      if (canvasRef && typeof canvasRef !== 'function' && canvasRef.current) {
        const canvas = canvasRef.current;
        const context = canvas.getContext('2d');

        return { canvas, context };
      }
      return { canvas: null, context: null };
    }, [canvasRef]);

    const handleMouseDown = (event: MouseEvent) => {
      const { canvas, context } = getCanvas();

      if (!canvas || !context) {
        return;
      }

      const rect = canvas.getBoundingClientRect();
      const x = event.clientX - rect.left;
      const y = event.clientY - rect.top;

      context.beginPath();
      context.moveTo(x, y);

      setIsDragging(true);
      setStartPosition({ x, y });
    };

    const handleMouseMove = (event: MouseEvent) => {
      const { canvas, context } = getCanvas();

      if (!canvas || !context || !cursorRef.current) {
        return;
      }

      const rect = canvas.getBoundingClientRect();
      const x = event.clientX - rect.left;
      const y = event.clientY - rect.top;

      if (isDragging) {
        drawGradientLine(context, startPosition.x, startPosition.y, x, y);
      }

      cursorRef.current.style.left = `${x}px`;
      cursorRef.current.style.top = `${y}px`;
    };

    const handleMouseUp = () => {
      const { context } = getCanvas();
      if (!context) {
        return;
      }
      setIsDragging(false);
      context.closePath();

      setTrackStack((previous) => {
        const updated = previous.slice(0, pointer);
        return [...updated, context.getImageData(0, 0, width, height)];
      });
      setPointer((previous) => previous + 1);
    };

    const canvasSetup = () => {
      const { canvas, context } = getCanvas();

      if (!canvas || !context) {
        return;
      }

      const image = new Image();
      image.src = src;
      image.onload = () => {
        context.clearRect(0, 0, width, height);

        if (trackStack.length === 0) {
          context.drawImage(image, 0, 0, width, height);
          setTrackStack([context.getImageData(0, 0, width, height)]);
        }
      };

      context.lineJoin = 'round';
      context.lineCap = 'round';
    };

    const handleMouseEnter = () => {
      if (!cursorRef.current) {
        return;
      }

      document.body.style.cursor = 'none';
      cursorRef.current.style.display = 'block';
    };

    const handleMouseLeave = () => {
      if (!cursorRef.current) {
        return;
      }
      document.body.style.cursor = 'auto';
      cursorRef.current.style.display = 'none';
    };

    // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
    useEffect(() => {
      canvasSetup();
    }, []);

    return (
      <div css={styles.wrapper}>
        <canvas
          ref={canvasRef}
          width={width}
          height={height}
          onMouseDown={handleMouseDown}
          onMouseMove={handleMouseMove}
          onMouseUp={handleMouseUp}
          onMouseEnter={handleMouseEnter}
          onMouseLeave={handleMouseLeave}
        />
        <div ref={cursorRef} css={styles.customCursor(brushSize)} />
      </div>
    );
  },
);

const styles = {
  wrapper: css`
		position: relative;
	`,
  customCursor: (size: number) => css`
		position: absolute;
		width: ${size}px;
		height: ${size}px;
		border-radius: ${borderRadius.circle};
		background: linear-gradient(73.09deg, rgba(255, 150, 69, 0.4) 18.05%, rgba(255, 100, 113, 0.4) 30.25%, rgba(207, 110, 189, 0.4) 55.42%, rgba(164, 119, 209, 0.4) 71.66%, rgba(62, 100, 222, 0.4) 97.9%);
		border: 3px solid ${colorTokens.stroke.white};
		pointer-events: none;
		transform: translate(-50%, -50%);
		z-index: ${zIndex.highest};
		display: none;
	`,
};
