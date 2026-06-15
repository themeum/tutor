/**
 * Draw-on-image field interaction for the course builder (parity with learning-area draw-image-question.js).
 *
 * @package Tutor
 * @since 4.0.0
 */

import { useCallback, useEffect, useRef } from 'react';
import { __ } from '@wordpress/i18n';

import {
  announceQuizBuilderPolite,
  bindQuizBuilderDescribedBy,
  isQuizBuilderGridMoveKey,
  moveQuizBuilderPixelCursor,
  normalizeQuizBuilderKey,
} from '@TutorShared/utils/quizBuilderA11y';

import { DEFAULT_BRUSH_SIZE, initDrawOnImage } from '@TutorProQuiz/shared/draw-on-image.js';

const PREVIEW_CURSOR = 'rgba(0, 0, 255, 0.65)';

type DrawOnImageInstance = {
  destroy: () => void;
  clear: () => void;
  isDrawing: () => boolean;
  startStrokeAt: (x: number, y: number) => boolean;
  continueStrokeAt: (x: number, y: number) => boolean;
  finishStroke: () => boolean;
  cancelStroke: () => boolean;
  renderWithOverlay: (overlayFn: (ctx: CanvasRenderingContext2D) => void) => void;
  syncCanvas: () => void;
};

type UseDrawOnImageFieldOptions = {
  imageRef: React.RefObject<HTMLImageElement | null>;
  canvasRef: React.RefObject<HTMLCanvasElement | null>;
  interactionRootRef: React.RefObject<HTMLElement | null>;
  liveRegionRef: React.RefObject<HTMLElement | null>;
  instructionId: string;
  liveRegionId: string;
  imageUrl?: string;
  initialMaskUrl?: string;
  strokeStyle: string;
  onMaskChange: (maskDataUrl: string) => void;
  onDrawStart?: () => void;
};

export const useDrawOnImageField = ({
  imageRef,
  canvasRef,
  interactionRootRef,
  liveRegionRef,
  instructionId,
  liveRegionId,
  imageUrl,
  initialMaskUrl,
  strokeStyle,
  onMaskChange,
  onDrawStart,
}: UseDrawOnImageFieldOptions) => {
  const drawInstanceRef = useRef<DrawOnImageInstance | null>(null);
  const keyboardCursorRef = useRef({ x: 0, y: 0 });
  const keyboardPreviewActiveRef = useRef(false);
  const focusFromPointerRef = useRef(false);
  const mountedImageUrlRef = useRef<string | undefined>();
  const keyboardPreviewRafRef = useRef<number | null>(null);

  const onMaskChangeRef = useRef(onMaskChange);
  const onDrawStartRef = useRef(onDrawStart);
  const initialMaskUrlRef = useRef(initialMaskUrl);

  onMaskChangeRef.current = onMaskChange;
  onDrawStartRef.current = onDrawStart;
  initialMaskUrlRef.current = initialMaskUrl;

  const announceStatus = useCallback(
    (message: string) => {
      announceQuizBuilderPolite(liveRegionRef.current, message);
    },
    [liveRegionRef],
  );

  const getCanvasBounds = useCallback(() => {
    const canvas = canvasRef.current;
    return {
      width: canvas?.width || 0,
      height: canvas?.height || 0,
    };
  }, [canvasRef]);

  const initKeyboardCursor = useCallback(() => {
    const bounds = getCanvasBounds();
    keyboardCursorRef.current = {
      x: bounds.width / 2,
      y: bounds.height / 2,
    };
  }, [getCanvasBounds]);

  const drawKeyboardCursorPreview = useCallback(() => {
    const drawInstance = drawInstanceRef.current;
    if (!drawInstance || !keyboardPreviewActiveRef.current || drawInstance.isDrawing()) {
      return;
    }

    drawInstance.renderWithOverlay((ctx: CanvasRenderingContext2D) => {
      ctx.fillStyle = PREVIEW_CURSOR;
      ctx.beginPath();
      ctx.arc(keyboardCursorRef.current.x, keyboardCursorRef.current.y, 5, 0, Math.PI * 2);
      ctx.fill();
    });
  }, []);

  const scheduleKeyboardPreview = useCallback(() => {
    if (keyboardPreviewRafRef.current !== null) {
      cancelAnimationFrame(keyboardPreviewRafRef.current);
    }
    keyboardPreviewRafRef.current = requestAnimationFrame(() => {
      keyboardPreviewRafRef.current = null;
      drawKeyboardCursorPreview();
    });
  }, [drawKeyboardCursorPreview]);

  const toggleKeyboardStroke = useCallback(() => {
    const drawInstance = drawInstanceRef.current;
    if (!drawInstance) {
      return;
    }

    if (drawInstance.isDrawing()) {
      drawInstance.finishStroke();
      keyboardPreviewActiveRef.current = true;
      scheduleKeyboardPreview();
      announceStatus(__('Selection completed.', __TUTOR_TEXT_DOMAIN__));
      return;
    }

    if (drawInstance.startStrokeAt(keyboardCursorRef.current.x, keyboardCursorRef.current.y)) {
      onDrawStartRef.current?.();
      announceStatus(
        __('Drawing started. Use arrow keys to trace, then Space or Enter to finish.', __TUTOR_TEXT_DOMAIN__),
      );
    }
  }, [announceStatus, scheduleKeyboardPreview]);

  useEffect(() => {
    const image = imageRef.current;
    const canvas = canvasRef.current;
    const interactionRoot = interactionRootRef.current;

    if (!imageUrl || !image || !canvas) {
      return;
    }

    // Re-init only when the background image changes — not when the saved mask updates after each stroke.
    if (drawInstanceRef.current && mountedImageUrlRef.current === imageUrl) {
      return;
    }

    mountedImageUrlRef.current = imageUrl;

    if (drawInstanceRef.current) {
      drawInstanceRef.current.destroy();
      drawInstanceRef.current = null;
    }

    const drawInstance = initDrawOnImage({
      image,
      canvas,
      brushSize: DEFAULT_BRUSH_SIZE,
      strokeStyle,
      interactionRoot: interactionRoot || canvas.parentElement || undefined,
      // Builder: keep canvas interactive (avoid pointer-events hover toggling flicker).
      activateOnHover: false,
      clearOnDrawStart: true,
      initialMaskUrl: initialMaskUrlRef.current || undefined,
      onDrawStart: () => {
        keyboardPreviewActiveRef.current = false;
        onDrawStartRef.current?.();
      },
      onMaskChange: (value: string) => {
        onMaskChangeRef.current(value);
      },
    }) as DrawOnImageInstance;

    drawInstanceRef.current = drawInstance;

    const onCanvasMouseDown = () => {
      focusFromPointerRef.current = true;
      keyboardPreviewActiveRef.current = false;
      onDrawStartRef.current?.();
    };

    const onCanvasFocus = () => {
      bindQuizBuilderDescribedBy(canvas, [instructionId, liveRegionId]);

      if (focusFromPointerRef.current) {
        focusFromPointerRef.current = false;
        return;
      }

      drawInstance.syncCanvas();
      initKeyboardCursor();
      keyboardPreviewActiveRef.current = true;
      scheduleKeyboardPreview();
    };

    const onCanvasBlur = () => {
      keyboardPreviewActiveRef.current = false;
      if (drawInstance.isDrawing()) {
        drawInstance.finishStroke();
      }
    };

    const onCanvasKeyDown = (event: KeyboardEvent) => {
      const key = normalizeQuizBuilderKey(event);

      if (key === ' ' || key === 'Spacebar' || key === 'Enter') {
        event.preventDefault();
        toggleKeyboardStroke();
        return;
      }

      if (key === 'Escape') {
        if (!drawInstance.isDrawing()) {
          return;
        }
        event.preventDefault();
        drawInstance.cancelStroke();
        keyboardPreviewActiveRef.current = true;
        scheduleKeyboardPreview();
        announceStatus(__('Drawing cancelled.', __TUTOR_TEXT_DOMAIN__));
        return;
      }

      if (key === 'Backspace' || key === 'Delete') {
        if (!drawInstance.isDrawing()) {
          announceStatus(__('Nothing to cancel.', __TUTOR_TEXT_DOMAIN__));
          return;
        }
        event.preventDefault();
        drawInstance.cancelStroke();
        keyboardPreviewActiveRef.current = true;
        scheduleKeyboardPreview();
        announceStatus(__('Drawing cancelled.', __TUTOR_TEXT_DOMAIN__));
        return;
      }

      if (key === 'c' || key === 'C') {
        event.preventDefault();
        if (drawInstance.isDrawing()) {
          drawInstance.cancelStroke();
        }
        drawInstance.clear();
        keyboardPreviewActiveRef.current = true;
        scheduleKeyboardPreview();
        announceStatus(__('Selection cleared.', __TUTOR_TEXT_DOMAIN__));
        return;
      }

      if (!isQuizBuilderGridMoveKey(key)) {
        return;
      }

      event.preventDefault();
      const nextCursor = moveQuizBuilderPixelCursor(keyboardCursorRef.current, key, getCanvasBounds(), {
        shiftKey: event.shiftKey,
      });
      if (!nextCursor) {
        return;
      }
      keyboardCursorRef.current = nextCursor;

      if (drawInstance.isDrawing()) {
        drawInstance.continueStrokeAt(keyboardCursorRef.current.x, keyboardCursorRef.current.y);
        return;
      }

      keyboardPreviewActiveRef.current = true;
      scheduleKeyboardPreview();
    };

    canvas.addEventListener('mousedown', onCanvasMouseDown);
    canvas.addEventListener('focus', onCanvasFocus);
    canvas.addEventListener('blur', onCanvasBlur);
    canvas.addEventListener('keydown', onCanvasKeyDown);

    return () => {
      if (keyboardPreviewRafRef.current !== null) {
        cancelAnimationFrame(keyboardPreviewRafRef.current);
        keyboardPreviewRafRef.current = null;
      }
      canvas.removeEventListener('mousedown', onCanvasMouseDown);
      canvas.removeEventListener('focus', onCanvasFocus);
      canvas.removeEventListener('blur', onCanvasBlur);
      canvas.removeEventListener('keydown', onCanvasKeyDown);
      drawInstance.destroy();
      drawInstanceRef.current = null;
      if (mountedImageUrlRef.current === imageUrl) {
        mountedImageUrlRef.current = undefined;
      }
    };
  }, [
    announceStatus,
    getCanvasBounds,
    imageUrl,
    imageRef,
    canvasRef,
    instructionId,
    interactionRootRef,
    initKeyboardCursor,
    liveRegionId,
    scheduleKeyboardPreview,
    strokeStyle,
    toggleKeyboardStroke,
  ]);

  const clearMask = useCallback(() => {
    drawInstanceRef.current?.clear();
    keyboardPreviewActiveRef.current = true;
    scheduleKeyboardPreview();
  }, [scheduleKeyboardPreview]);

  return {
    clearMask,
    announceStatus,
  };
};
