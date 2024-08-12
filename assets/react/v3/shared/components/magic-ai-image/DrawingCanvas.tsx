import { borderRadius, colorTokens, zIndex } from '@Config/styles';
import { calculateCartesianDistance, drawPath, getCanvas } from '@Utils/magic-ai';
import { css } from '@emotion/react';
import React, { type MouseEvent, useEffect, useRef, useState } from 'react';

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
    const [startPoint, setStartPoint] = useState<Coordinate>({ x: 0, y: 0 });
    const cursorRef = useRef<HTMLDivElement>(null);

    const handleMouseDown = (event: MouseEvent) => {
      const { canvas, context } = getCanvas(canvasRef);

      if (!canvas || !context) {
        return;
      }

      const rect = canvas.getBoundingClientRect();
      const x = (event.clientX - rect.left) * (canvas.width / rect.width);
      const y = (event.clientY - rect.top) * (canvas.height / rect.height);

      context.globalCompositeOperation = 'destination-out';
      context.beginPath();
      context.moveTo(x, y);

      setIsDragging(true);
      setStartPoint({ x, y });
    };

    const handleMouseMove = (event: MouseEvent) => {
      const { canvas, context } = getCanvas(canvasRef);

      if (!canvas || !context || !cursorRef.current) {
        return;
      }

      const rect = canvas.getBoundingClientRect();
      const point = {
        x: (event.clientX - rect.left) * (canvas.width / rect.width),
        y: (event.clientY - rect.top) * (canvas.height / rect.height),
      };

      if (isDragging) {
        drawPath(context, point);
      }

      cursorRef.current.style.left = `${point.x}px`;
      cursorRef.current.style.top = `${point.y}px`;
    };

    const handleMouseUp = (event: MouseEvent) => {
      const { canvas, context } = getCanvas(canvasRef);
      if (!context || !canvas) {
        return;
      }
      setIsDragging(false);
      context.closePath();

      const rect = canvas.getBoundingClientRect();
      const endPoint = {
        x: (event.clientX - rect.left) * (canvas.width / rect.width),
        y: (event.clientY - rect.top) * (canvas.height / rect.height),
      };

      // Check if the mouse is just clicked but not drag for drawing a path, then draw a circle
      if (calculateCartesianDistance(startPoint, endPoint) === 0) {
        drawPath(context, { x: endPoint.x + 1, y: endPoint.y + 1 });
      }

      setTrackStack((previous) => {
        const updated = previous.slice(0, pointer);
        return [...updated, context.getImageData(0, 0, 1024, 1024)];
      });
      setPointer((previous) => previous + 1);
    };

    const canvasSetup = () => {
      const { canvas, context } = getCanvas(canvasRef);

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
