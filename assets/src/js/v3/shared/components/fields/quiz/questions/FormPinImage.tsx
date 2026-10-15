import { useCallback, useId, useRef, useState } from 'react';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';

import Button from '@TutorShared/atoms/Button';
import ImageInput from '@TutorShared/atoms/ImageInput';
import SVGIcon from '@TutorShared/atoms/SVGIcon';

import { borderRadius, Breakpoint, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import Show from '@TutorShared/controls/Show';
import { useDrawOnImageField } from '@TutorShared/hooks/useDrawOnImageField';
import useWPMedia from '@TutorShared/hooks/useWpMedia';
import type { FormControllerProps } from '@TutorShared/utils/form';
import { calculateQuizDataStatus } from '@TutorShared/utils/quiz';
import { quizBuilderInteractionFocusCss, quizBuilderSrOnlyCss } from '@TutorShared/utils/quizBuilderA11y';
import { styleUtils } from '@TutorShared/utils/style-utils';
import {
  type ID,
  QuizDataStatus,
  type QuizQuestionOption,
  type QuizValidationErrorType,
} from '@TutorShared/utils/types';

/** Instructor mask stroke/fill for valid pin region (same draw core as learning-area draw-on-image). */
const INSTRUCTOR_MASK_STYLE = 'rgba(220, 53, 69, 0.95)';

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

  const a11yBaseId = useId();
  const instructionId = `${a11yBaseId}-instruction`;
  const liveRegionId = `${a11yBaseId}-live-region`;
  const [hasStartedDraw, setHasStartedDraw] = useState(false);

  const imageRef = useRef<HTMLImageElement | null>(null);
  const canvasRef = useRef<HTMLCanvasElement | null>(null);
  const canvasInnerRef = useRef<HTMLDivElement | null>(null);
  const liveRegionRef = useRef<HTMLDivElement | null>(null);

  const updateOption = useCallback(
    (updated: QuizQuestionOption) => {
      field.onChange(updated);
    },
    [field],
  );

  const persistMaskToOption = useCallback(
    (maskDataUrl: string) => {
      if (!option) {
        return;
      }

      updateOption({
        ...option,
        ...(calculateQuizDataStatus(option._data_status, QuizDataStatus.UPDATE) && {
          _data_status: calculateQuizDataStatus(option._data_status, QuizDataStatus.UPDATE) as QuizDataStatus,
        }),
        answer_two_gap_match: maskDataUrl,
        is_saved: true,
      });
      setHasStartedDraw(Boolean(maskDataUrl));
    },
    [option, updateOption],
  );

  const { clearMask, announceStatus } = useDrawOnImageField({
    imageRef,
    canvasRef,
    interactionRootRef: canvasInnerRef,
    liveRegionRef,
    instructionId,
    liveRegionId,
    imageUrl: option?.image_url,
    initialMaskUrl: option?.answer_two_gap_match || undefined,
    strokeStyle: INSTRUCTOR_MASK_STYLE,
    onMaskChange: persistMaskToOption,
    onDrawStart: () => setHasStartedDraw(true),
  });

  const { openMediaLibrary, resetFiles } = useWPMedia({
    options: {
      type: 'image',
    },
    onChange: (file) => {
      if (file && !Array.isArray(file) && option) {
        const { id, url } = file;
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
        setHasStartedDraw(false);
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

  const handleClear = () => {
    if (!option) {
      return;
    }

    clearMask();
    announceStatus(__('Valid pin area cleared.', __TUTOR_TEXT_DOMAIN__));
  };

  const clearImage = () => {
    if (!option) {
      return;
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
    setHasStartedDraw(false);
    clearMask();
  };

  if (!option) {
    return null;
  }

  const canClearSelection = hasStartedDraw || Boolean(option?.answer_two_gap_match);

  return (
    <div css={styles.wrapper}>
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

      <Show when={option?.image_url}>
        <div css={styles.card}>
          <div css={styles.answerHeader}>
            <span css={styles.answerHeaderTitle}>
              <span css={styles.headerIcon}>
                <SVGIcon name="edit" width={20} height={20} aria-hidden />
              </span>
              {__('Mark the correct area', __TUTOR_TEXT_DOMAIN__)}
            </span>
            <Show when={canClearSelection}>
              <div css={styles.actionsRow}>
                <Button
                  type="button"
                  variant="secondary"
                  size="small"
                  icon={<SVGIcon name="eraser" style={styles.clearButtonIcon} width={18} height={18} />}
                  onClick={handleClear}
                >
                  {__('Clear', __TUTOR_TEXT_DOMAIN__)}
                </Button>
              </div>
            </Show>
          </div>
          <p id={instructionId} css={quizBuilderSrOnlyCss}>
            {__(
              'Use arrow keys to move the drawing pointer. Press Space or Enter to start a freehand stroke, trace with arrow keys, then press Space or Enter again to finish and fill the selection. Escape cancels an in-progress stroke. C clears the saved selection.',
              __TUTOR_TEXT_DOMAIN__,
            )}
          </p>
          <div
            id={liveRegionId}
            ref={liveRegionRef}
            css={quizBuilderSrOnlyCss}
            aria-live="polite"
            aria-atomic="true"
            role="status"
          />
          <div css={styles.canvasOuter}>
            <div css={styles.canvasInner} ref={canvasInnerRef} className="tutor-draw-image-wrapper">
              <img
                ref={imageRef}
                src={option?.image_url}
                alt={__('Background image for pin area', __TUTOR_TEXT_DOMAIN__)}
                css={[styles.image, styles.answerImage]}
              />
              <canvas
                ref={canvasRef}
                tabIndex={0}
                role="application"
                aria-describedby={`${instructionId} ${liveRegionId}`}
                css={[styles.canvas, quizBuilderInteractionFocusCss]}
                aria-label={__(
                  'Mark the valid pin area: use pointer or keyboard to draw the accepted region.',
                  __TUTOR_TEXT_DOMAIN__,
                )}
              />
            </div>
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
    background-color: ${colorTokens.background.default};
    height: 210px;
    border-radius: ${borderRadius.card};
  `,
  imageInputPreview: css`
    height: 210px;
  `,
  answerHeader: css`
    ${styleUtils.display.flex('row')};
    align-items: center;
    justify-content: space-between;
    gap: ${spacing[12]};
    min-height: 32px;
  `,
  answerHeaderTitle: css`
    ${typography.body('medium')};
    color: ${colorTokens.text.primary};
    ${styleUtils.display.flex('row')};
    align-items: center;
    gap: ${spacing[8]};
  `,
  headerIcon: css`
    display: flex;
    flex-shrink: 0;
    color: ${colorTokens.text.subdued};
  `,
  canvasOuter: css`
    ${styleUtils.display.flex()};
    justify-content: center;
    width: 100%;
  `,
  canvasInner: css`
    position: relative;
    display: inline-block;
    max-width: 100%;
    border-radius: ${borderRadius.card};
    overflow: hidden;

    ${Breakpoint.smallMobile} {
      display: block;
      width: 100%;
    }

    img {
      display: block;
      width: auto;
      max-width: 100%;
      height: auto;

      ${Breakpoint.smallMobile} {
        width: 100%;
      }
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
  actionsRow: css`
    ${styleUtils.display.flex('row')};
    align-items: center;
    justify-content: flex-end;
    min-width: 94px;
    min-height: 32px;
    gap: ${spacing[12]};
    flex-wrap: wrap;
    color: ${colorTokens.text.brand};
  `,
  clearButtonIcon: css`
    color: ${colorTokens.text.brand};
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
