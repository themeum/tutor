import MagicButton from '@Atoms/MagicButton';
import SVGIcon from '@Atoms/SVGIcon';
import { Separator } from '@Atoms/Separator';
import FormRangeSliderField from '@Components/fields/FormRangeSliderField';
import FormTextareaInput from '@Components/fields/FormTextareaInput';
import { borderRadius, colorTokens, spacing, zIndex } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import { useMagicFillImageMutation, useStoreAIGeneratedImageMutation } from '@CourseBuilderServices/magic-ai';
import { useDebounce } from '@Hooks/useDebounce';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
import { downloadBase64Image, getCanvas, getImageData } from '@Utils/magic-ai';
import { styleUtils } from '@Utils/style-utils';
import { nanoid } from '@Utils/util';
import { css, keyframes } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useCallback, useEffect, useRef, useState } from 'react';
import { Controller } from 'react-hook-form';
import { DrawingCanvas } from './DrawingCanvas';
import { useMagicImageGeneration } from './ImageContext';
import { magicAIStyles } from './styles';
const width = 620;
const height = 620;

const MagicFill = () => {
  const form = useFormWithGlobalError<{ brush_size: number; prompt: string }>({
    defaultValues: {
      brush_size: 40,
      prompt: '',
    },
  });

  const magicFillImageMutation = useMagicFillImageMutation();
  const canvasRef = useRef<HTMLCanvasElement>(null);

  const { onDropdownMenuChange, currentImage, field, onCloseModal } = useMagicImageGeneration();
  const storeAIGeneratedImageMutation = useStoreAIGeneratedImageMutation();
  const brushSize = useDebounce(form.watch('brush_size', 40));
  const [trackStack, setTrackStack] = useState<ImageData[]>([]);
  const [pointer, setPointer] = useState(1);

  const repaintCanvas = useCallback((index: number, stack: ImageData[]) => {
    const context = canvasRef.current?.getContext('2d');
    if (!context) {
      return;
    }

    for (const image of stack.slice(0, index)) {
      context.putImageData(image, 0, 0);
    }
  }, []);

  useEffect(() => {
    const context = canvasRef.current?.getContext('2d');
    if (!context) {
      return;
    }

    context.lineWidth = brushSize;
  }, [brushSize]);

  useEffect(() => {
    const handleKeyDown = (event: KeyboardEvent) => {
      if (event.metaKey) {
        if (event.shiftKey && event.key.toUpperCase() === 'Z') {
          repaintCanvas(pointer + 1, trackStack);
          setPointer((previous) => Math.min(previous + 1, trackStack.length));
          return;
        }

        if (event.key.toUpperCase() === 'Z') {
          repaintCanvas(pointer - 1, trackStack);
          setPointer((previous) => Math.max(previous - 1, 1));
          return;
        }
      }
    };

    window.addEventListener('keydown', handleKeyDown);

    return () => {
      window.removeEventListener('keydown', handleKeyDown);
    };
  }, [pointer, trackStack, repaintCanvas]);

  if (!currentImage) {
    return null;
  }

  return (
    <form
      css={magicAIStyles.wrapper}
      onSubmit={form.handleSubmit(async (values) => {
        const canvas = canvasRef.current;
        const context = canvas?.getContext('2d');
        if (!canvas || !context) {
          return;
        }

        const data = {
          prompt: values.prompt,
          image: getImageData(canvas),
        };

        const response = await magicFillImageMutation.mutateAsync(data);

        if (response) {
          const image = new Image();
          image.onload = () => {
            canvas.width = width;
            canvas.height = height;
            context.drawImage(image, 0, 0, canvas.width, canvas.height);
            context.lineWidth = brushSize;
            context.lineJoin = 'round';
            context.lineCap = 'round';
          };
          image.src = response;
        }
      })}
    >
      <div css={magicAIStyles.left}>
        <div css={styles.leftWrapper}>
          <div css={styles.actionBar}>
            <div css={styles.backButtonWrapper}>
              <button type="button" css={styles.backButton} onClick={() => onDropdownMenuChange('generation')}>
                <SVGIcon name="arrowLeft" />
              </button>
              {__('Magic Fill', 'tutor')}
            </div>
            <div css={styles.actions}>
              <MagicButton
                variant="ghost"
                disabled={trackStack.length === 0}
                onClick={() => {
                  repaintCanvas(1, trackStack);
                  setTrackStack(trackStack.slice(0, 1));
                  setPointer(1);
                }}
              >
                {__('Revert to Original', 'tutor')}
              </MagicButton>
              <Separator variant="vertical" css={css`min-height: 16px;`} />
              <div css={styles.undoRedo}>
                <MagicButton
                  variant="ghost"
                  size="icon"
                  disabled={pointer <= 1}
                  onClick={() => {
                    repaintCanvas(pointer - 1, trackStack);
                    setPointer((previous) => Math.max(previous - 1, 1));
                  }}
                >
                  <SVGIcon name="undo" width={20} height={20} />
                </MagicButton>
                <MagicButton
                  variant="ghost"
                  size="icon"
                  disabled={pointer === trackStack.length}
                  onClick={() => {
                    repaintCanvas(pointer + 1, trackStack);
                    setPointer((previous) => Math.min(previous + 1, trackStack.length));
                  }}
                >
                  <SVGIcon name="redo" width={20} height={20} />
                </MagicButton>
              </div>
            </div>
          </div>
          <div css={styles.canvasAndLoading}>
            <DrawingCanvas
              ref={canvasRef}
              width={width}
              height={height}
              src={currentImage}
              brushSize={brushSize}
              trackStack={trackStack}
              pointer={pointer}
              setTrackStack={setTrackStack}
              setPointer={setPointer}
            />
            <Show when={magicFillImageMutation.isPending}>
              <div css={styles.loading} />
            </Show>
          </div>
          <div css={styles.footerActions}>
            <div css={styles.footerActionsLeft}>
              <MagicButton variant="secondary" onClick={() => alert('@TODO: will be implemented later.')}>
                <SVGIcon name="magicVariation" width={24} height={24} />
              </MagicButton>
              <MagicButton variant="secondary" onClick={() => alert('@TODO: will be implemented later.')}>
                <SVGIcon name="magicEraser" width={24} height={24} />
              </MagicButton>
            </div>
            <div>
              <MagicButton
                variant="secondary"
                onClick={() => {
                  const filename = `${nanoid()}.png`;
                  const { canvas } = getCanvas(canvasRef);
                  if (!canvas) return;
                  downloadBase64Image(getImageData(canvas), filename);
                }}
              >
                <SVGIcon name="download" width={24} height={24} />
              </MagicButton>
            </div>
          </div>
        </div>
      </div>
      <div css={magicAIStyles.right}>
        <div css={styles.fields}>
          <Controller
            control={form.control}
            name="brush_size"
            render={(props) => (
              <FormRangeSliderField {...props} label="Brush size" min={1} max={100} isMagicAi hasBorder />
            )}
          />
          <Controller
            control={form.control}
            name="prompt"
            render={(props) => (
              <FormTextareaInput
                {...props}
                label={__('Describe the fill', 'tutor')}
                placeholder={__('Write 5 words to describe...', 'tutor')}
                rows={4}
                isMagicAi
              />
            )}
          />
        </div>
        <div css={[magicAIStyles.rightFooter, css`margin-top: auto;`]}>
          <div css={styles.footerButtons}>
            <MagicButton type="submit" disabled={magicFillImageMutation.isPending || !!form.watch('prompt')}>
              <SVGIcon name="magicWand" width={24} height={24} />
              {__('Generative erase', 'tutor')}
            </MagicButton>
            <MagicButton
              variant="primary_outline"
              disabled={magicFillImageMutation.isPending}
              loading={storeAIGeneratedImageMutation.isPending}
              onClick={async () => {
                const { canvas } = getCanvas(canvasRef);
                if (!canvas) return;
                const response = await storeAIGeneratedImageMutation.mutateAsync({ image: getImageData(canvas) });

                if (response.data) {
                  field.onChange(response.data);
                  onCloseModal();
                }
              }}
            >
              {__('Use Image', 'tutor')}
            </MagicButton>
          </div>
        </div>
      </div>
    </form>
  );
};

export default MagicFill;
const animations = {
  loading: keyframes`
    0% {
      opacity: 0;
    }
    50% {
      opacity: 0.6;
    }
    100% {
      opacity: 0;
    }
  `,
  walker: keyframes`
    0% {
      left: 0%;
    }
    100% {
      left: 100%;
    }
  `,
};
const styles = {
  canvasAndLoading: css`
    position: relative;
    z-index: ${zIndex.positive};
  `,
  loading: css`
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: ${colorTokens.ai.gradient_1};
    opacity: 0.6;
    transition: 0.5s ease opacity;
    animation: ${animations.loading} 1s linear infinite;
    z-index: 0;

    &::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 200px;
      height: 100%;
      background: linear-gradient(270deg, rgba(255, 255, 255, 0) 0%, rgba(255, 255, 255, 0.6) 51.13%, rgba(255, 255, 255, 0) 100%);
      animation: ${animations.walker} 1s linear infinite;
    }
  `,
  actionBar: css`
		display: flex;
		align-items: center;
		justify-content: space-between;
	`,
  fields: css`
		display: flex;
		flex-direction: column;
		gap: ${spacing[12]};
	`,
  leftWrapper: css`
		display: flex;
		flex-direction: column;
		gap: ${spacing[8]};
		padding-block: ${spacing[16]};
	`,
  footerButtons: css`
		display: flex;
		flex-direction: column;
		gap: ${spacing[8]};
	`,
  footerActions: css`
		display: flex;
		justify-content: space-between;
	`,
  footerActionsLeft: css`
		display: flex;
		align-items: center;
		gap: ${spacing[12]};
	`,
  actions: css`
		display: flex;
		align-items: center;
		gap: ${spacing[16]};
	`,
  undoRedo: css`
		display: flex;
		align-items: center;
		gap: ${spacing[12]};
	`,
  backButtonWrapper: css`
		display: flex;
		align-items: center;
		gap: ${spacing[8]};
		${typography.body('medium')};
		color: ${colorTokens.text.title};
	`,
  backButton: css`
		${styleUtils.resetButton};
		width: 24px;
		height: 24px;
		border-radius: ${borderRadius[4]};
		border: 1px solid ${colorTokens.stroke.default};
		display: flex;
		align-items: center;
		justify-content: center;
	`,
  image: css`
		width: 492px;
		height: 498px;
		position: relative;

		img {
			position: absolute;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			object-fit: cover;
		}
	`,
  canvasWrapper: css`
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
