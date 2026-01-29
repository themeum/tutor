import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useEffect, useRef, useState } from 'react';
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

const DrawImage = () => {
  const form = useFormContext<QuizForm>();
  const { activeQuestionIndex, activeQuestionId } = useQuizModalContext();

  const answersPath = `questions.${activeQuestionIndex}.question_answers` as 'questions.0.question_answers';

  const answers = useWatch({
    control: form.control,
    name: answersPath,
    defaultValue: [] as QuizQuestionOption[],
  }) as QuizQuestionOption[];

  const [brushSize, setBrushSize] = useState(15);
  const [isDrawing, setIsDrawing] = useState(false);

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

  const syncCanvasWithImage = () => {
    const img = imageRef.current;
    const canvas = canvasRef.current;

    if (!img || !canvas) {
      return;
    }

    if (!img.complete) {
      return;
    }

    const imgWidth = img.clientWidth || img.offsetWidth || img.naturalWidth;
    const imgHeight = img.clientHeight || img.offsetHeight || img.naturalHeight;

    if (!imgWidth || !imgHeight) {
      return;
    }

    canvas.width = imgWidth;
    canvas.height = imgHeight;

    canvas.style.width = `${imgWidth}px`;
    canvas.style.height = `${imgHeight}px`;

    const parentRect = img.parentElement?.getBoundingClientRect();
    const imgRect = img.getBoundingClientRect();

    if (parentRect) {
      const left = imgRect.left - parentRect.left;
      const top = imgRect.top - parentRect.top;
      canvas.style.position = 'absolute';
      canvas.style.left = `${left}px`;
      canvas.style.top = `${top}px`;
    }

    const ctx = canvas.getContext('2d');
    if (!ctx) {
      return;
    }

    ctx.clearRect(0, 0, canvas.width, canvas.height);
    ctx.strokeStyle = 'rgba(255, 0, 0, 0.9)';
    ctx.fillStyle = 'rgba(255, 0, 0, 0.9)';
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';
    ctx.lineWidth = brushSize;
  };

  useEffect(() => {
    syncCanvasWithImage();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [option?.image_url, brushSize]);

  useEffect(() => {
    const img = imageRef.current;
    if (!img) {
      return;
    }

    const handleLoad = () => {
      syncCanvasWithImage();
    };

    img.addEventListener('load', handleLoad);

    return () => {
      img.removeEventListener('load', handleLoad);
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

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
      ctx.arc(coords.x, coords.y, brushSize / 2, 0, Math.PI * 2);
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
  }, [brushSize, isDrawing, option?.image_url]);

  const handleClearDrawing = () => {
    const canvas = canvasRef.current;
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    if (!ctx) return;

    ctx.clearRect(0, 0, canvas.width, canvas.height);

    if (!option) {
      return;
    }

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

  const handleSaveZone = () => {
    const canvas = canvasRef.current;
    if (!canvas || !option) {
      return;
    }

    const dataUrl = canvas.toDataURL('image/png');

    const updated: QuizQuestionOption = {
      ...option,
      ...(calculateQuizDataStatus(option._data_status, QuizDataStatus.UPDATE) && {
        _data_status: calculateQuizDataStatus(option._data_status, QuizDataStatus.UPDATE) as QuizDataStatus,
      }),
      // Store instructor's mask in the same field used by the prototype.
      answer_two_gap_match: dataUrl,
      is_saved: true,
    };

    form.setValue(answersPath, [updated]);
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
      <div css={styles.controlsRow}>
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

        <div css={styles.brushControls}>
          <label htmlFor="draw-image-brush-size">{__('Brush Size', 'tutor')}</label>
          <div css={styles.brushRangeWrapper}>
            <input
              id="draw-image-brush-size"
              type="range"
              min={5}
              max={50}
              value={brushSize}
              onChange={(event) => setBrushSize(Number(event.target.value))}
            />
            <span css={styles.brushSizeValue}>{brushSize}px</span>
          </div>
        </div>
      </div>

      <Show when={option?.image_url}>
        <div css={styles.canvasContainer}>
          <div css={styles.canvasInner}>
            <img
              ref={imageRef}
              src={option?.image_url}
              alt={__('Background image for draw-on-image question', 'tutor')}
              css={styles.image}
            />
            <canvas ref={canvasRef} css={styles.canvas} />
          </div>
        </div>

        <div css={styles.actionsRow}>
          <Button
            variant="secondary"
            size="small"
            onClick={handleClearDrawing}
            icon={<SVGIcon name="refresh" width={20} height={20} />}
          >
            {__('Clear Drawing', 'tutor')}
          </Button>
          <Button
            variant="primary"
            size="small"
            onClick={handleSaveZone}
            icon={<SVGIcon name="save" width={20} height={20} />}
          >
            {__('Save Answer Zone', 'tutor')}
          </Button>
        </div>

        <Show when={option?.answer_two_gap_match}>
          <p css={styles.hint}>
            {__(
              'An answer zone mask has been saved for this question. Students will be graded against this mask.',
              'tutor',
            )}
          </p>
        </Show>
      </Show>

      <Show when={!option?.image_url}>
        <p css={styles.placeholder}>
          {__(
            'Upload an image to define the area students must draw on. Then paint the correct zone on top of the image.',
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
    gap: ${spacing[16]};
    padding-left: ${spacing[40]};

    ${Breakpoint.smallMobile} {
      padding-left: ${spacing[8]};
    }
  `,
  controlsRow: css`
    ${styleUtils.display.flex('row')};
    flex-wrap: wrap;
    gap: ${spacing[16]};
    align-items: flex-end;
  `,
  imageInputWrapper: css`
    max-width: 320px;
  `,
  imageInput: css`
    border-radius: ${borderRadius.card};
  `,
  brushControls: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[8]};
    min-width: 220px;

    label {
      ${typography.caption('medium')};
      color: ${colorTokens.text.subdued};
    }
  `,
  brushRangeWrapper: css`
    ${styleUtils.display.flex('row')};
    align-items: center;
    gap: ${spacing[8]};

    input[type='range'] {
      flex: 1;
    }
  `,
  brushSizeValue: css`
    ${typography.caption('medium')};
    color: ${colorTokens.text.subdued};
    min-width: 48px;
    text-align: right;
  `,
  canvasContainer: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[8]};
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
  canvas: css`
    top: 0;
    left: 0;
    pointer-events: auto;
  `,
  actionsRow: css`
    ${styleUtils.display.flex('row')};
    gap: ${spacing[12]};
  `,
  hint: css`
    ${typography.caption()};
    color: ${colorTokens.text.subdued};
  `,
  placeholder: css`
    ${typography.caption()};
    color: ${colorTokens.text.subdued};
  `,
};
