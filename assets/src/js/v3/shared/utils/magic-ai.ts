import type { ForwardedRef, RefObject } from 'react';

export interface Point {
  x: number;
  y: number;
}

export function drawPath(context: CanvasRenderingContext2D, point: Point) {
  context.lineTo(point.x, point.y);
  context.stroke();
}

export function calculateCartesianDistance(start: Point, end: Point) {
  const dx = end.x - start.x;
  const dy = end.y - start.y;

  return Math.sqrt(dx * dx + dy * dy);
}

export function base64ToBlob(base64: string) {
  const byteString = atob(base64.split(',')[1]);
  const mimeString = base64.split(',')[0].split(':')[1].split(';')[0];

  const buffer = new ArrayBuffer(byteString.length);

  const uint8Array = new Uint8Array(buffer);

  for (let i = 0; i < byteString.length; i++) {
    uint8Array[i] = byteString.charCodeAt(i);
  }

  return new Blob([buffer], { type: mimeString });
}

export function downloadBase64Image(src: string, filename: string) {
  const blob = base64ToBlob(src);

  const link = document.createElement('a');
  link.href = URL.createObjectURL(blob);
  link.download = filename;

  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}

export function imageDataToFile(imageData: ImageData, filename: string): Promise<File | null> {
  const canvas = document.createElement('canvas');
  canvas.width = 1024;
  canvas.height = 1024;

  const context = canvas.getContext('2d');
  context?.putImageData(imageData, 0, 0);
  context?.drawImage(canvas, 0, 0, 1024, 1024);

  return new Promise((resolve) => {
    canvas.toBlob((blob) => {
      if (!blob) {
        resolve(null);
        return;
      }
      resolve(new File([blob], filename, { type: 'image/png' }));
    });
  });
}

const getCanvas = (canvasRef: RefObject<HTMLCanvasElement> | ForwardedRef<HTMLCanvasElement>) => {
  if (canvasRef && typeof canvasRef !== 'function' && canvasRef.current) {
    const canvas = canvasRef.current;
    const context = canvas.getContext('2d');

    return { canvas, context };
  }

  return { canvas: null, context: null };
};

const getImageData = (canvas: HTMLCanvasElement) => {
  return canvas.toDataURL('image/png');
};

export { getCanvas, getImageData };
