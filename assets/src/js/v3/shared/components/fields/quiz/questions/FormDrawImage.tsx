import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useCallback, useEffect, useRef } from 'react';

import Button from '@TutorShared/atoms/Button';
import ImageInput from '@TutorShared/atoms/ImageInput';
import SVGIcon from '@TutorShared/atoms/SVGIcon';

import {
  borderRadius,
  Breakpoint,
  colorTokens,
  spacing,
  fontFamily,
  fontSize,
  fontWeight,
  lineHeight,
  letterSpacing,
} from '@TutorShared/config/styles';
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

const INSTRUCTOR_STROKE_STYLE = 'rgba(255, 0, 0, 0.9)';

interface FormDrawImageProps extends FormControllerProps<QuizQuestionOption> {
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
  precisionControl?: React.ReactNode;
}

const FormDrawImage = ({ field, precisionControl }: FormDrawImageProps) => {
  const option = field.value;

  const imageRef = useRef<HTMLImageElement | null>(null);
  const canvasRef = useRef<HTMLCanvasElement | null>(null);
  const drawInstanceRef = useRef<{ destroy: () => void } | null>(null);

  const updateOption = useCallback(
    (updated: QuizQuestionOption) => {
      field.onChange(updated);
    },
    [field],
  );

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

  useEffect(() => {
    const imageUrl = option?.image_url;
    if (!imageUrl) {
      return;
    }
    const img = imageRef.current;
    const canvas = canvasRef.current;
    const api = typeof window !== 'undefined' ? window.TutorCore?.drawOnImage : undefined;
    if (!img || !canvas || !api?.init) {
      return;
    }
    // Only initialize once per mount; keep the same instance while the instructor is drawing.
    if (drawInstanceRef.current) {
      return;
    }
    const brushSize = 1;
    const currentOption: QuizQuestionOption | null = option ?? null;
    const instance = api.init({
      image: img,
      canvas,
      brushSize,
      strokeStyle: INSTRUCTOR_STROKE_STYLE,
      initialMaskUrl: currentOption?.answer_two_gap_match || undefined,
      onMaskChange: (maskValue: string) => {
        if (!currentOption) {
          return;
        }
        updateOption({
          ...currentOption,
          ...(calculateQuizDataStatus(currentOption._data_status, QuizDataStatus.UPDATE) && {
            _data_status: calculateQuizDataStatus(currentOption._data_status, QuizDataStatus.UPDATE) as QuizDataStatus,
          }),
          answer_two_gap_match: maskValue,
          is_saved: true,
        });
      },
    });
    drawInstanceRef.current = instance;
  }, [option, updateOption]);

  // Cleanup shared instance on unmount.
  useEffect(() => {
    return () => {
      if (drawInstanceRef.current) {
        drawInstanceRef.current.destroy();
        drawInstanceRef.current = null;
      }
    };
  }, []);

  if (!option) {
    return null;
  }

  const handleClear = () => {
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
  };

  const clearImage = () => {
    if (drawInstanceRef.current) {
      drawInstanceRef.current.destroy();
      drawInstanceRef.current = null;
    }
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
            buttonText={__('Upload Image', __TUTOR_TEXT_DOMAIN__)}
            infoText={__('Upload the base image students will draw on.', __TUTOR_TEXT_DOMAIN__)}
            uploadHandler={openMediaLibrary}
            clearHandler={clearImage}
            emptyImageCss={styles.imageInput}
            previewImageCss={styles.imageInput}
          />
        </div>
      </div>

      {/* Section 2: Mark the correct area — single reference image + interactive drawing canvas */}
      <Show when={option?.image_url}>
        <div css={styles.card}>
          <div css={styles.answerHeader}>
            <span css={styles.answerHeaderTitle}>
              <span css={styles.headerIcon}>
                <SVGIcon name="edit" width={20} height={20} aria-hidden />
              </span>
              {__('Mark the correct area', __TUTOR_TEXT_DOMAIN__)}
            </span>
            <Button
              variant="tertiary"
              size="small"
              onClick={handleClear}
              icon={<SVGIcon name="eraser" width={18} height={18} style={styles.clearIcon} />}
              css={styles.clearButton}
            >
              {__('Clear', __TUTOR_TEXT_DOMAIN__)}
            </Button>
          </div>
          <div css={styles.canvasInner}>
            <img
              ref={imageRef}
              src={option?.image_url}
              alt={__('Background image for marking correct area', __TUTOR_TEXT_DOMAIN__)}
              css={[styles.image, styles.answerImage]}
            />
            <canvas
              ref={canvasRef}
              css={[styles.canvas, styles.canvasDrawMode]}
              aria-label={__('Draw the correct area with the brush', __TUTOR_TEXT_DOMAIN__)}
            />
            <div css={styles.drawBadge}>
              <SVGIcon name="edit" width={18} height={18} aria-hidden />
            </div>
          </div>
          {precisionControl && <div>{precisionControl}</div>}
          <Show when={option?.answer_two_gap_match}>
            <p css={styles.savedHint}>
              {__('Answer zone saved. Students will be graded against this area.', __TUTOR_TEXT_DOMAIN__)}
            </p>
          </Show>
        </div>
      </Show>

      <Show when={!option?.image_url}>
        <p css={styles.placeholder}>
          {__(
            'Upload an image to define the area students must draw on. Then mark the correct zone in the next section.',
            __TUTOR_TEXT_DOMAIN__,
          )}
        </p>
      </Show>
    </div>
  );
};

export default FormDrawImage;

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
  drawBadge: css`
    position: absolute;
    top: ${spacing[12]};
    right: ${spacing[12]};
    width: 32px;
    height: 32px;
    border-radius: 999px;
    background: ${colorTokens.surface.tutor};
    border: 1px solid ${colorTokens.stroke.border};
    ${styleUtils.display.flex('row')};
    align-items: center;
    justify-content: center;
    color: ${colorTokens.text.subdued};
    box-shadow: 0 2px 6px rgba(15, 23, 42, 0.16);
  `,
  actionsRow: css`
    ${styleUtils.display.flex('row')};
    gap: ${spacing[12]};
    flex-wrap: wrap;
  `,
  clearButton: css`
    width: 94px;
    height: 32px;
    border-radius: 6px;
    border: none;
    box-shadow: none;
    padding: 4px 12px;
    gap: 4px;
    background: ${colorTokens.action.secondary.default};
    color: ${colorTokens.text.brand};
    font-family: ${fontFamily.sfProDisplay};
    font-weight: ${fontWeight.medium};
    font-size: ${fontSize[13]};
    line-height: ${lineHeight[20]};
    letter-spacing: ${letterSpacing.normal};
    text-align: center;
    &:hover {
      background: ${colorTokens.action.secondary.hover};
      border: none;
      box-shadow: none;
    }
  `,
  clearIcon: css`
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
