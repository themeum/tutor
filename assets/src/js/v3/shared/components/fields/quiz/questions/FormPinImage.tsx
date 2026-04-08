import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useCallback, useEffect, useRef, useState } from 'react';

import ImageInput from '@TutorShared/atoms/ImageInput';
import SVGIcon from '@TutorShared/atoms/SVGIcon';

import { borderRadius, Breakpoint, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import Show from '@TutorShared/controls/Show';
import useWPMedia from '@TutorShared/hooks/useWpMedia';
import type { FormControllerProps } from '@TutorShared/utils/form';
import { calculateQuizDataStatus } from '@TutorShared/utils/quiz';
import { styleUtils } from '@TutorShared/utils/style-utils';
import {
  type ID,
  QuizDataStatus,
  type QuizQuestionOption,
  type QuizValidationErrorType,
} from '@TutorShared/utils/types';

const LASSO_FILL_STYLE = 'rgba(220, 53, 69, 0.45)';
const LASSO_STROKE_STYLE = 'rgba(220, 53, 69, 0.95)';
const LASSO_DASH_PATTERN = [8, 6];
const LASSO_MIN_POINT_DISTANCE = 4;

interface FormPinImageProps extends FormControllerProps<QuizQuestionOption> {
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

const FormPinImage = ({ field }: FormPinImageProps) => {
  const option = field.value;

  const [isDrawModeActive, setIsDrawModeActive] = useState(false);
  const [hasStartedLassoDraw, setHasStartedLassoDraw] = useState(false);

  const imageRef = useRef<HTMLImageElement | null>(null);
  const canvasRef = useRef<HTMLCanvasElement | null>(null);
  const drawInstanceRef = useRef<{ destroy: () => void } | null>(null);
  const isLassoDrawingRef = useRef(false);
  const lassoPointsRef = useRef<Array<{ x: number; y: number }>>([]);
  const baseImageDataRef = useRef<ImageData | null>(null);

  const updateOption = useCallback(
    (updated: QuizQuestionOption) => {
      field.onChange(updated);
    },
    [field],
  );

  /** Display-only: sync canvas size and draw saved mask when not in draw mode. */
  const syncCanvasDisplay = useCallback((maskUrl?: string) => {
    const img = imageRef.current;
    const canvas = canvasRef.current;

    if (!img || !canvas) {
      return;
    }

    if (!img.complete) {
      return;
    }

    const container = img.parentElement;
    if (!container) {
      return;
    }

    const rect = container.getBoundingClientRect();
    const width = Math.round(rect.width);
    const height = Math.round(rect.height);

    if (!width || !height) {
      return;
    }

    canvas.width = width;
    canvas.height = height;
    canvas.style.position = 'absolute';
    canvas.style.top = '0';
    canvas.style.left = '0';
    canvas.style.width = '100%';
    canvas.style.height = '100%';

    const ctx = canvas.getContext('2d');
    if (!ctx) {
      return;
    }

    ctx.clearRect(0, 0, canvas.width, canvas.height);

    if (maskUrl) {
      const maskImg = new Image();
      maskImg.onload = () => {
        ctx.drawImage(maskImg, 0, 0, canvas.width, canvas.height);
      };
      maskImg.src = maskUrl;
    }
  }, []);

  const { openMediaLibrary, resetFiles } = useWPMedia({
    options: {
      type: 'image',
    },
    onChange: (file) => {
      if (file && !Array.isArray(file) && option) {
        const { id, url } = file;
        // Clear previous draw when image is replaced — the saved mask was for the old image.
        const updated: QuizQuestionOption = {
          ...option,
          ...(calculateQuizDataStatus(option._data_status, QuizDataStatus.UPDATE) && {
            _data_status: calculateQuizDataStatus(option._data_status, QuizDataStatus.UPDATE) as QuizDataStatus,
          }),
          image_id: id,
          image_url: url,
          answer_two_gap_match: '',
        };
        updateOption(updated);
        // Clean up draw instance and canvas so the new image shows without the old mask.
        if (drawInstanceRef.current) {
          drawInstanceRef.current.destroy();
          drawInstanceRef.current = null;
        }
        setIsDrawModeActive(false);
        setHasStartedLassoDraw(false);
      }
    },
    initialFiles: option?.image_id
      ? {
          id: Number(option.image_id),
          url: option.image_url || '',
          title: option.image_url || '',
        }
      : null,
  });

  // Display-only sync when not in draw mode (saved mask + canvas size).
  useEffect(() => {
    if (isDrawModeActive) {
      return;
    }
    syncCanvasDisplay(option?.answer_two_gap_match || undefined);
  }, [isDrawModeActive, option?.image_url, option?.answer_two_gap_match, syncCanvasDisplay]);

  useEffect(() => {
    if (isDrawModeActive) {
      return;
    }
    const img = imageRef.current;
    if (!img) {
      return;
    }
    const handleLoad = () => {
      syncCanvasDisplay(option?.answer_two_gap_match || undefined);
    };
    img.addEventListener('load', handleLoad);
    return () => {
      img.removeEventListener('load', handleLoad);
    };
  }, [isDrawModeActive, option?.answer_two_gap_match, syncCanvasDisplay]);

  useEffect(() => {
    if (isDrawModeActive) {
      return;
    }
    const img = imageRef.current;
    const canvas = canvasRef.current;
    if (!img || !canvas) {
      return;
    }
    const container = img.parentElement;
    if (!container) {
      return;
    }
    const resizeObserver = new ResizeObserver(() => {
      syncCanvasDisplay(option?.answer_two_gap_match || undefined);
    });
    resizeObserver.observe(container);
    return () => {
      resizeObserver.disconnect();
    };
  }, [isDrawModeActive, option?.image_url, option?.answer_two_gap_match, syncCanvasDisplay]);

  // Pin image uses lasso-style polygon drawing for marking the valid pin zone.
  useEffect(() => {
    if (!isDrawModeActive || !option?.image_url) {
      return;
    }
    const canvas = canvasRef.current;
    if (!canvas) {
      return;
    }

    if (drawInstanceRef.current) {
      drawInstanceRef.current.destroy();
      drawInstanceRef.current = null;
    }

    const getPointFromEvent = (event: PointerEvent) => {
      const rect = canvas.getBoundingClientRect();
      const scaleX = canvas.width / rect.width;
      const scaleY = canvas.height / rect.height;
      const x = (event.clientX - rect.left) * scaleX;
      const y = (event.clientY - rect.top) * scaleY;
      return {
        x: Math.max(0, Math.min(canvas.width, x)),
        y: Math.max(0, Math.min(canvas.height, y)),
      };
    };

    const renderPathPreview = () => {
      const ctx = canvas.getContext('2d');
      if (!ctx) {
        return;
      }

      const points = lassoPointsRef.current;
      if (!baseImageDataRef.current || points.length < 2) {
        return;
      }

      ctx.putImageData(baseImageDataRef.current, 0, 0);
      ctx.beginPath();
      ctx.moveTo(points[0]?.x || 0, points[0]?.y || 0);
      points.forEach((point, index) => {
        if (index > 0) {
          ctx.lineTo(point.x, point.y);
        }
      });
      ctx.lineTo(points[0]?.x || 0, points[0]?.y || 0);
      ctx.closePath();

      ctx.fillStyle = LASSO_FILL_STYLE;
      ctx.fill();

      ctx.setLineDash(LASSO_DASH_PATTERN);
      ctx.lineWidth = 2;
      ctx.strokeStyle = LASSO_STROKE_STYLE;
      ctx.stroke();
      ctx.setLineDash([]);
    };

    const onPointerDown = (event: PointerEvent) => {
      if (event.button !== 0) {
        return;
      }
      const ctx = canvas.getContext('2d');
      if (!ctx) {
        return;
      }
      // Only one lasso at a time: starting a new stroke clears any previous polygon.
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      canvas.setPointerCapture(event.pointerId);
      isLassoDrawingRef.current = true;
      lassoPointsRef.current = [getPointFromEvent(event)];
      baseImageDataRef.current = ctx.getImageData(0, 0, canvas.width, canvas.height);
      setHasStartedLassoDraw(true);
    };

    const onPointerMove = (event: PointerEvent) => {
      if (!isLassoDrawingRef.current) {
        return;
      }
      const nextPoint = getPointFromEvent(event);
      const points = lassoPointsRef.current;
      const lastPoint = points[points.length - 1];
      if (!lastPoint) {
        points.push(nextPoint);
        renderPathPreview();
        return;
      }
      const dx = nextPoint.x - lastPoint.x;
      const dy = nextPoint.y - lastPoint.y;
      if (Math.hypot(dx, dy) < LASSO_MIN_POINT_DISTANCE) {
        return;
      }
      points.push(nextPoint);
      renderPathPreview();
    };

    const finishLasso = () => {
      if (!isLassoDrawingRef.current) {
        return;
      }
      isLassoDrawingRef.current = false;
      const points = lassoPointsRef.current;
      if (points.length >= 3) {
        renderPathPreview();
      } else if (baseImageDataRef.current) {
        const ctx = canvas.getContext('2d');
        ctx?.putImageData(baseImageDataRef.current, 0, 0);
      }
      lassoPointsRef.current = [];
      baseImageDataRef.current = null;
    };

    const onPointerUp = (event: PointerEvent) => {
      if (canvas.hasPointerCapture(event.pointerId)) {
        canvas.releasePointerCapture(event.pointerId);
      }
      finishLasso();
    };

    const onPointerCancel = (event: PointerEvent) => {
      if (canvas.hasPointerCapture(event.pointerId)) {
        canvas.releasePointerCapture(event.pointerId);
      }
      finishLasso();
    };

    canvas.addEventListener('pointerdown', onPointerDown);
    canvas.addEventListener('pointermove', onPointerMove);
    canvas.addEventListener('pointerup', onPointerUp);
    canvas.addEventListener('pointercancel', onPointerCancel);

    const instance = {
      destroy: () => {
        canvas.removeEventListener('pointerdown', onPointerDown);
        canvas.removeEventListener('pointermove', onPointerMove);
        canvas.removeEventListener('pointerup', onPointerUp);
        canvas.removeEventListener('pointercancel', onPointerCancel);
        isLassoDrawingRef.current = false;
        lassoPointsRef.current = [];
        baseImageDataRef.current = null;
      },
    };
    drawInstanceRef.current = instance;

    return () => {
      instance.destroy();
      drawInstanceRef.current = null;
    };
  }, [isDrawModeActive, option?.image_url, option?.answer_two_gap_match]);

  // Cleanup shared instance on unmount.
  useEffect(() => {
    return () => {
      if (drawInstanceRef.current) {
        drawInstanceRef.current.destroy();
        drawInstanceRef.current = null;
      }
    };
  }, []);

  const persistCanvasMask = useCallback(() => {
    const canvas = canvasRef.current;
    if (!canvas || !option) {
      return;
    }

    const dataUrl = canvas.toDataURL('image/png');
    const blank = document.createElement('canvas');
    blank.width = canvas.width;
    blank.height = canvas.height;
    const isEmpty = dataUrl === blank.toDataURL();

    const updated: QuizQuestionOption = {
      ...option,
      ...(calculateQuizDataStatus(option._data_status, QuizDataStatus.UPDATE) && {
        _data_status: calculateQuizDataStatus(option._data_status, QuizDataStatus.UPDATE) as QuizDataStatus,
      }),
      answer_two_gap_match: isEmpty ? '' : dataUrl,
      is_saved: true,
    };
    updateOption(updated);
  }, [option, updateOption]);

  const handleClear = () => {
    if (!option) {
      return;
    }

    if (drawInstanceRef.current) {
      drawInstanceRef.current.destroy();
      drawInstanceRef.current = null;
    }

    const canvas = canvasRef.current;
    if (canvas) {
      const ctx = canvas.getContext('2d');
      ctx?.clearRect(0, 0, canvas.width, canvas.height);
    }

    const updated: QuizQuestionOption = {
      ...option,
      ...(calculateQuizDataStatus(option._data_status, QuizDataStatus.UPDATE) && {
        _data_status: calculateQuizDataStatus(option._data_status, QuizDataStatus.UPDATE) as QuizDataStatus,
      }),
      answer_two_gap_match: '',
      is_saved: true,
    };
    updateOption(updated);
    setHasStartedLassoDraw(false);
  };

  const handleCanvasMouseEnter = () => {
    setIsDrawModeActive(true);
  };

  const handleCanvasMouseLeave = () => {
    if (!isLassoDrawingRef.current) {
      setIsDrawModeActive(false);
    }
  };

  const clearImage = () => {
    if (!option) {
      return;
    }

    if (drawInstanceRef.current) {
      drawInstanceRef.current.destroy();
      drawInstanceRef.current = null;
    }
    setIsDrawModeActive(false);

    const updated: QuizQuestionOption = {
      ...option,
      ...(calculateQuizDataStatus(option._data_status, QuizDataStatus.UPDATE) && {
        _data_status: calculateQuizDataStatus(option._data_status, QuizDataStatus.UPDATE) as QuizDataStatus,
      }),
      image_id: undefined,
      image_url: '',
    };

    updateOption(updated);
    resetFiles();
    setHasStartedLassoDraw(false);

    const canvas = canvasRef.current;
    if (canvas) {
      const ctx = canvas.getContext('2d');
      ctx?.clearRect(0, 0, canvas.width, canvas.height);
    }
  };

  useEffect(() => {
    if (!isDrawModeActive || !option?.image_url) {
      return;
    }
    const canvas = canvasRef.current;
    if (!canvas) {
      return;
    }

    const onPointerUp = () => {
      persistCanvasMask();
    };

    canvas.addEventListener('pointerup', onPointerUp);
    canvas.addEventListener('pointercancel', onPointerUp);

    return () => {
      canvas.removeEventListener('pointerup', onPointerUp);
      canvas.removeEventListener('pointercancel', onPointerUp);
    };
  }, [isDrawModeActive, option?.image_url, persistCanvasMask]);

  if (!option) {
    return null;
  }

  return (
    <div css={styles.wrapper}>
      {/* Section 1: Image upload only — one reference shown for pin-area quizzes */}
      <div css={styles.card}>
        <div css={styles.imageInputWrapper}>
          <ImageInput
            value={
              option?.image_id
                ? {
                    id: Number(option.image_id),
                    url: option.image_url || '',
                    title: option.image_url || '',
                  }
                : null
            }
            buttonText={__('Upload Image', __TUTOR_TEXT_DOMAIN__)}
            infoText={__('Upload the base image students will pin on.', __TUTOR_TEXT_DOMAIN__)}
            uploadHandler={openMediaLibrary}
            clearHandler={clearImage}
            emptyImageCss={styles.imageInputEmpty}
            previewImageCss={styles.imageInputPreview}
          />
        </div>
      </div>

      {/* Section 2: Mark the valid pin area — drawing auto-enables on image hover */}
      <Show when={option?.image_url}>
        <div css={styles.card}>
          <div css={styles.answerHeader}>
            <span css={styles.answerHeaderTitle}>
              <span css={styles.headerIcon}>
                <SVGIcon name="edit" width={20} height={20} aria-hidden />
              </span>
              {__('Mark the correct area', __TUTOR_TEXT_DOMAIN__)}
            </span>
            <div css={styles.actionsRow}>
              <button
                type="button"
                css={[
                  styles.clearButton,
                  !(hasStartedLassoDraw || Boolean(option?.answer_two_gap_match)) && styles.clearButtonHidden,
                ]}
                onClick={handleClear}
                disabled={!(hasStartedLassoDraw || Boolean(option?.answer_two_gap_match))}
                aria-hidden={!(hasStartedLassoDraw || Boolean(option?.answer_two_gap_match))}
                tabIndex={hasStartedLassoDraw || Boolean(option?.answer_two_gap_match) ? 0 : -1}
              >
                <SVGIcon name="eraser" style={styles.clearButtonIcon} width={18} height={18} />
                {__('Clear', __TUTOR_TEXT_DOMAIN__)}
              </button>
            </div>
          </div>
          <div css={styles.canvasInner} onMouseEnter={handleCanvasMouseEnter} onMouseLeave={handleCanvasMouseLeave}>
            <img
              ref={imageRef}
              src={option?.image_url}
              alt={__('Background image for pin area', __TUTOR_TEXT_DOMAIN__)}
              css={[styles.image, styles.answerImage]}
            />
            <canvas
              ref={canvasRef}
              css={[styles.canvas, isDrawModeActive ? styles.canvasDrawMode : styles.canvasIdleMode]}
              aria-label={__('Draw a lasso around the valid pin area', __TUTOR_TEXT_DOMAIN__)}
            />
          </div>
          <Show when={option?.answer_two_gap_match}>
            <p css={styles.savedHint}>
              {__('Pin area saved. Students will be graded against this region.', __TUTOR_TEXT_DOMAIN__)}
            </p>
          </Show>
        </div>
      </Show>

      <Show when={!option?.image_url}>
        <p css={styles.placeholder}>
          {__(
            'Upload an image to define where students must drop a pin. Then mark the valid area in the next section.',
            __TUTOR_TEXT_DOMAIN__,
          )}
        </p>
      </Show>
    </div>
  );
};

export default FormPinImage;

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
    background: ${colorTokens.surface.tutor};
    border-radius: ${borderRadius.card};
  `,
  imageInputWrapper: css`
    max-width: 100%;
  `,
  imageInputEmpty: css`
    border-radius: ${borderRadius.card};
  `,
  imageInputPreview: css`
    width: fit-content;
    max-width: 100%;
    height: auto;
    border-radius: ${borderRadius.card};

    img {
      width: auto;
      max-width: 100%;
      height: auto;
      object-fit: initial;
    }
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
    ${styleUtils.display.flex('row')};
    align-items: center;
    gap: ${spacing[8]};
  `,
  headerIcon: css`
    flex-shrink: 0;
    color: ${colorTokens.text.subdued};
  `,
  canvasInner: css`
    position: relative;
    display: inline-block;
    border-radius: ${borderRadius.card};
    overflow: hidden;

    img {
      display: block;
      max-width: 100%;
      height: auto;
    }
  `,
  image: css`
    display: block;
    max-width: 100%;
    height: auto;
  `,
  answerImage: css`
    filter: grayscale(0.1);
  `,
  canvas: css`
    position: absolute;
    top: 0;
    left: 0;
    z-index: 1;
  `,
  canvasIdleMode: css`
    pointer-events: none;
    cursor: default;
  `,
  canvasDrawMode: css`
    pointer-events: auto;
    cursor: crosshair;
  `,
  actionsRow: css`
    ${styleUtils.display.flex('row')};
    justify-content: flex-end;
    min-width: 94px;
    gap: ${spacing[12]};
    flex-wrap: wrap;
    color: ${colorTokens.text.brand};
  `,
  clearButton: css`
    width: 94px;
    border: none;
    border-radius: ${borderRadius.input};
    background: ${colorTokens.action.secondary.default};
    ${typography.caption('medium')};
    color: ${colorTokens.text.brand};
    display: flex;
    justify-content: center;
    align-items: center;
    gap: ${spacing[8]};
    padding: ${spacing[4]} 0;
    cursor: pointer;
  `,
  clearButtonHidden: css`
    visibility: hidden;
    pointer-events: none;
  `,
  clearButtonIcon: css`
    color: ${colorTokens.text.brand};
  `,
  brushHint: css`
    ${typography.caption()};
    color: ${colorTokens.text.subdued};
    margin: 0;
  `,
  savedHint: css`
    ${typography.caption()};
    color: ${colorTokens.text.success};
    margin: 0;
  `,
  placeholder: css`
    ${typography.caption()};
    color: ${colorTokens.text.subdued};
  `,
};
