import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useCallback, useEffect, useRef, useState } from 'react';
import { useFormContext, useWatch } from 'react-hook-form';

import Button from '@TutorShared/atoms/Button';
import ImageInput from '@TutorShared/atoms/ImageInput';
import SVGIcon from '@TutorShared/atoms/SVGIcon';

import { borderRadius, Breakpoint, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import Show from '@TutorShared/controls/Show';
import useWPMedia from '@TutorShared/hooks/useWpMedia';
import { calculateQuizDataStatus } from '@TutorShared/utils/quiz';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { QuizDataStatus, type QuizQuestionOption } from '@TutorShared/utils/types';
import { nanoid } from '@TutorShared/utils/util';

import { useQuizModalContext } from '@CourseBuilderContexts/QuizModalContext';
import { type QuizForm } from '@CourseBuilderServices/quiz';

const BRUSH_SIZE = 15;

const DrawImage = () => {
  const form = useFormContext<QuizForm>();
  const { activeQuestionIndex, activeQuestionId } = useQuizModalContext();

  const answersPath = `questions.${activeQuestionIndex}.question_answers` as 'questions.0.question_answers';

  const answers = useWatch({
    control: form.control,
    name: answersPath,
    defaultValue: [] as QuizQuestionOption[],
  }) as QuizQuestionOption[];

  const [isDrawing, setIsDrawing] = useState(false);
  const [isDrawModeActive, setIsDrawModeActive] = useState(false);

  const imageRef = useRef<HTMLImageElement | null>(null);
  const canvasRef = useRef<HTMLCanvasElement | null>(null);
  const lastPointRef = useRef<{ x: number; y: number } | null>(null);

  // Ensure there is always a single option for this question type.
  useEffect(() => {
    if (!activeQuestionId) {
      return;
    }

    if (!answers || answers.length === 0) {
      const baseAnswer: QuizQuestionOption = {
        _data_status: QuizDataStatus.NEW,
        // Mark as saved so core validation doesn't block on this synthetic option.
        is_saved: true,
        answer_id: nanoid(),
        belongs_question_id: activeQuestionId,
        belongs_question_type: 'draw_image' as QuizQuestionOption['belongs_question_type'],
        answer_title: '',
        is_correct: '1',
        image_id: undefined,
        image_url: '',
        answer_two_gap_match: '',
        answer_view_format: 'draw_image',
        answer_order: 0,
      };

      form.setValue(answersPath, [baseAnswer]);
    }
  }, [activeQuestionId, answers, answersPath, form]);

  const option = (answers && answers[0]) as QuizQuestionOption | undefined;

  const { openMediaLibrary, resetFiles } = useWPMedia({
    options: {
      type: 'image',
    },
    onChange: (file) => {
      if (!option) {
        return;
      }

      if (file && !Array.isArray(file)) {
        const { id, url } = file;
        const updated: QuizQuestionOption = {
          ...option,
          ...(calculateQuizDataStatus(option._data_status, QuizDataStatus.UPDATE) && {
            _data_status: calculateQuizDataStatus(option._data_status, QuizDataStatus.UPDATE) as QuizDataStatus,
          }),
          image_id: id,
          image_url: url,
        };

        form.setValue(answersPath, [updated]);
      }
    },
    initialFiles:
      option && option.image_id
        ? {
            id: Number(option.image_id),
            url: option.image_url || '',
            title: option.image_url || '',
          }
        : null,
  });

  const syncCanvasWithImage = useCallback((maskUrl?: string) => {
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
    const w = Math.round(rect.width);
    const h = Math.round(rect.height);

    if (!w || !h) {
      return;
    }

    canvas.width = w;
    canvas.height = h;
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
    ctx.strokeStyle = 'rgba(255, 0, 0, 0.9)';
    ctx.fillStyle = 'rgba(255, 0, 0, 0.9)';
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';
    ctx.lineWidth = BRUSH_SIZE;

    if (maskUrl) {
      const maskImg = new Image();
      maskImg.onload = () => {
        ctx.drawImage(maskImg, 0, 0, canvas.width, canvas.height);
      };
      maskImg.src = maskUrl;
    }
  }, []);

  useEffect(() => {
    syncCanvasWithImage(option?.answer_two_gap_match || undefined);
  }, [option?.image_url, option?.answer_two_gap_match, syncCanvasWithImage]);

  useEffect(() => {
    const img = imageRef.current;
    if (!img) {
      return;
    }

    const handleLoad = () => {
      syncCanvasWithImage(option?.answer_two_gap_match || undefined);
    };

    img.addEventListener('load', handleLoad);

    return () => {
      img.removeEventListener('load', handleLoad);
    };
  }, [option?.answer_two_gap_match, syncCanvasWithImage]);

  useEffect(() => {
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
      syncCanvasWithImage(option?.answer_two_gap_match || undefined);
    });

    resizeObserver.observe(container);

    return () => {
      resizeObserver.disconnect();
    };
  }, [option?.image_url, option?.answer_two_gap_match, syncCanvasWithImage]);

  useEffect(() => {
    const canvas = canvasRef.current;
    if (!canvas) {
      return;
    }

    const getCoords = (event: MouseEvent | TouchEvent) => {
      const rect = canvas.getBoundingClientRect();
      const scaleX = canvas.width / rect.width;
      const scaleY = canvas.height / rect.height;

      let clientX: number;
      let clientY: number;

      if (event instanceof TouchEvent) {
        const touch = event.touches[0] || event.changedTouches[0];
        clientX = touch.clientX;
        clientY = touch.clientY;
      } else {
        clientX = event.clientX;
        clientY = event.clientY;
      }

      return {
        x: (clientX - rect.left) * scaleX,
        y: (clientY - rect.top) * scaleY,
      };
    };

    const handlePointerDown = (event: MouseEvent | TouchEvent) => {
      if (!isDrawModeActive) return;

      event.preventDefault();
      if (!option?.image_url) {
        return;
      }

      const ctx = canvas.getContext('2d');
      if (!ctx) return;

      const coords = getCoords(event);
      if (!coords) return;

      setIsDrawing(true);
      lastPointRef.current = coords;

      ctx.beginPath();
      ctx.arc(coords.x, coords.y, BRUSH_SIZE / 2, 0, Math.PI * 2);
      ctx.fill();
    };

    const handlePointerMove = (event: MouseEvent | TouchEvent) => {
      if (!isDrawing) return;

      const ctx = canvas.getContext('2d');
      if (!ctx) return;

      const coords = getCoords(event);
      const lastPoint = lastPointRef.current;
      if (!coords || !lastPoint) return;

      ctx.beginPath();
      ctx.moveTo(lastPoint.x, lastPoint.y);
      ctx.lineTo(coords.x, coords.y);
      ctx.stroke();

      lastPointRef.current = coords;
    };

    const handlePointerUp = (event: MouseEvent | TouchEvent) => {
      event.preventDefault();
      setIsDrawing(false);
      lastPointRef.current = null;
    };

    const handleMouseDown = (event: MouseEvent) => handlePointerDown(event);
    const handleMouseMove = (event: MouseEvent) => handlePointerMove(event);
    const handleMouseUp = (event: MouseEvent) => handlePointerUp(event);

    const handleTouchStart = (event: TouchEvent) => handlePointerDown(event);
    const handleTouchMove = (event: TouchEvent) => handlePointerMove(event);
    const handleTouchEnd = (event: TouchEvent) => handlePointerUp(event);

    canvas.addEventListener('mousedown', handleMouseDown);
    canvas.addEventListener('mousemove', handleMouseMove);
    canvas.addEventListener('mouseup', handleMouseUp);
    canvas.addEventListener('mouseleave', handleMouseUp);

    canvas.addEventListener('touchstart', handleTouchStart, { passive: false });
    canvas.addEventListener('touchmove', handleTouchMove, { passive: false });
    canvas.addEventListener('touchend', handleTouchEnd, { passive: false });

    return () => {
      canvas.removeEventListener('mousedown', handleMouseDown);
      canvas.removeEventListener('mousemove', handleMouseMove);
      canvas.removeEventListener('mouseup', handleMouseUp);
      canvas.removeEventListener('mouseleave', handleMouseUp);

      canvas.removeEventListener('touchstart', handleTouchStart);
      canvas.removeEventListener('touchmove', handleTouchMove);
      canvas.removeEventListener('touchend', handleTouchEnd);
    };
  }, [isDrawing, isDrawModeActive, option?.image_url]);

  const handleSave = () => {
    const canvas = canvasRef.current;
    if (!canvas || !option) return;

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
    form.setValue(answersPath, [updated]);
  };

  const handleClear = () => {
    const canvas = canvasRef.current;
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    if (!ctx) return;

    ctx.clearRect(0, 0, canvas.width, canvas.height);

    if (!option) return;

    const updated: QuizQuestionOption = {
      ...option,
      ...(calculateQuizDataStatus(option._data_status, QuizDataStatus.UPDATE) && {
        _data_status: calculateQuizDataStatus(option._data_status, QuizDataStatus.UPDATE) as QuizDataStatus,
      }),
      answer_two_gap_match: '',
      is_saved: true,
    };
    form.setValue(answersPath, [updated]);
  };

  const handleDraw = () => {
    setIsDrawModeActive(true);

    const canvas = canvasRef.current;
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    if (!ctx) return;

    ctx.clearRect(0, 0, canvas.width, canvas.height);
  };

  const clearImage = () => {
    if (!option) return;

    const updated: QuizQuestionOption = {
      ...option,
      ...(calculateQuizDataStatus(option._data_status, QuizDataStatus.UPDATE) && {
        _data_status: calculateQuizDataStatus(option._data_status, QuizDataStatus.UPDATE) as QuizDataStatus,
      }),
      image_id: undefined,
      image_url: '',
    };

    form.setValue(answersPath, [updated]);
    resetFiles();

    const canvas = canvasRef.current;
    if (canvas) {
      const ctx = canvas.getContext('2d');
      ctx?.clearRect(0, 0, canvas.width, canvas.height);
    }
  };

  return (
    <div css={styles.wrapper}>
      {/* Section 1: Image upload only — one reference shown in Mark the correct area */}
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
            buttonText={__('Upload Image', 'tutor')}
            infoText={__('Upload the base image students will draw on.', 'tutor')}
            uploadHandler={openMediaLibrary}
            clearHandler={clearImage}
            emptyImageCss={styles.imageInput}
            previewImageCss={styles.imageInput}
          />
        </div>
      </div>

      {/* Section 2: Mark the correct area — single reference image + drawing canvas; Save / Clear / Draw buttons */}
      <Show when={option?.image_url}>
        <div css={styles.card}>
          <div css={styles.answerHeader}>
            <span css={styles.answerHeaderTitle}>
              <SVGIcon name="edit" width={20} height={20} css={styles.headerIcon} aria-hidden />
              {__('Mark the correct area', 'tutor')}
            </span>
          </div>
          <div css={styles.canvasInner}>
            <img
              ref={imageRef}
              src={option?.image_url}
              alt={__('Background image for marking correct area', 'tutor')}
              css={[styles.image, styles.answerImage]}
            />
            <canvas
              ref={canvasRef}
              css={[styles.canvas, isDrawModeActive ? styles.canvasDrawMode : styles.canvasIdleMode]}
              aria-label={__('Draw the correct area with the brush', 'tutor')}
            />
          </div>
          <div css={styles.actionsRow}>
            <Button
              variant="primary"
              size="small"
              onClick={handleSave}
              icon={<SVGIcon name="save" width={20} height={20} />}
            >
              {__('Save', 'tutor')}
            </Button>
            <Button
              variant="secondary"
              size="small"
              onClick={handleClear}
              icon={<SVGIcon name="delete" width={20} height={20} />}
            >
              {__('Clear', 'tutor')}
            </Button>
            <Button
              variant="tertiary"
              size="small"
              onClick={handleDraw}
              icon={<SVGIcon name="edit" width={20} height={20} />}
            >
              {__('Draw', 'tutor')}
            </Button>
          </div>
          <p css={styles.brushHint}>
            {__('Use the brush to draw on the image, then click Save to store the answer zone.', 'tutor')}
          </p>
          <Show when={option?.answer_two_gap_match}>
            <p css={styles.savedHint}>{__('Answer zone saved. Students will be graded against this area.', 'tutor')}</p>
          </Show>
        </div>
      </Show>

      <Show when={!option?.image_url}>
        <p css={styles.placeholder}>
          {__(
            'Upload an image to define the area students must draw on. Then mark the correct zone in the next section.',
            'tutor',
          )}
        </p>
      </Show>
    </div>
  );
};

export default DrawImage;

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
  imageInputWrapper: css`
    max-width: 100%;
  `,
  imageInput: css`
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
    ${styleUtils.display.flex('row')};
    align-items: center;
    gap: ${spacing[8]};
  `,
  headerIcon: css`
    flex-shrink: 0;
    color: ${colorTokens.text.subdued};
  `,
  deleteButton: css`
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: ${spacing[8]};
    background: transparent;
    border: none;
    border-radius: ${borderRadius.button};
    color: ${colorTokens.text.subdued};
    cursor: pointer;
    transition:
      color 0.15s ease,
      background 0.15s ease;

    &:hover {
      color: ${colorTokens.text.primary};
      background: ${colorTokens.background.hover};
    }

    &:focus-visible {
      outline: 2px solid ${colorTokens.action.primary.focus};
      outline-offset: 2px;
    }
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
    filter: grayscale(0.15);
  `,
  canvas: css`
    position: absolute;
    top: 0;
    left: 0;
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
    gap: ${spacing[12]};
    flex-wrap: wrap;
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
