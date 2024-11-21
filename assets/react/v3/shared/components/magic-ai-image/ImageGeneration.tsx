import MagicButton from '@Atoms/MagicButton';
import SVGIcon from '@Atoms/SVGIcon';
import FormImageRadioGroup from '@Components/fields/FormImageRadioGroup';
import FormTextareaInput from '@Components/fields/FormTextareaInput';
import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import For from '@Controls/For';
import Show from '@Controls/Show';
import { useMagicImageGenerationMutation } from '@CourseBuilderServices/magic-ai';

import threeD from '@Images/ai-types/3d.png';
import blackAndWhite from '@Images/ai-types/black-and-white.png';
import concept from '@Images/ai-types/concept.png';
import dreamy from '@Images/ai-types/dreamy.png';
import filmic from '@Images/ai-types/filmic.png';
import illustration from '@Images/ai-types/illustration.png';
import neon from '@Images/ai-types/neon.png';
import none from '@Images/ai-types/none.jpg';
import painting from '@Images/ai-types/painting.png';
import photo from '@Images/ai-types/photo.png';
import retro from '@Images/ai-types/retro.png';
import sketch from '@Images/ai-types/sketch.png';

import { useToast } from '@Atoms/Toast';
import type { ErrorResponse } from '@Utils/form';
import { styleUtils } from '@Utils/style-utils';
import { type OptionWithImage, isDefined } from '@Utils/types';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useEffect, useState } from 'react';
import { Controller, useForm } from 'react-hook-form';
import {
  type MagicImageGenerationFormFields,
  type StyleType,
  inspirationPrompts,
  useMagicImageGeneration,
} from './ImageContext';
import { AiImageItem } from './ImageItem';
import { magicAIStyles } from './styles';

const styleOptions: OptionWithImage<StyleType>[] = [
  {
    label: __('None', 'tutor'),
    value: 'none',
    image: none,
  },
  {
    label: __('Photo', 'tutor'),
    value: 'photo',
    image: photo,
  },
  {
    label: __('Neon', 'tutor'),
    value: 'neon',
    image: neon,
  },
  {
    label: __('3D', 'tutor'),
    value: '3d',
    image: threeD,
  },
  {
    label: __('Painting', 'tutor'),
    value: 'painting',
    image: painting,
  },
  {
    label: __('Sketch', 'tutor'),
    value: 'sketch',
    image: sketch,
  },
  {
    label: __('Concept', 'tutor'),
    value: 'concept_art',
    image: concept,
  },
  {
    label: __('Illustration', 'tutor'),
    value: 'illustration',
    image: illustration,
  },
  {
    label: __('Dreamy', 'tutor'),
    value: 'dreamy',
    image: dreamy,
  },
  {
    label: __('Filmic', 'tutor'),
    value: 'filmic',
    image: filmic,
  },
  {
    label: __('Retro', 'tutor'),
    value: 'retrowave',
    image: retro,
  },
  {
    label: __('Black & White', 'tutor'),
    value: 'black-and-white',
    image: blackAndWhite,
  },
];

export const ImageGeneration = () => {
  const form = useForm<MagicImageGenerationFormFields>({
    defaultValues: {
      style: 'none',
      prompt: '',
    },
  });
  const { images, setImages } = useMagicImageGeneration();
  const magicImageGenerationMutation = useMagicImageGenerationMutation();
  const { showToast } = useToast();
  const [showEmptyState, setShowEmptyState] = useState(images.every((image) => image === null));
  const [imageLoading, setImageLoading] = useState([false, false, false, false]);

  const styleValue = form.watch('style');
  const promptValue = form.watch('prompt');

  const isDisabledButton = !styleValue || !promptValue;
  const hasGeneratedImage = images.some(isDefined);

  // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
  useEffect(() => {
    if (magicImageGenerationMutation.isError) {
      showToast({
        type: 'danger',
        message: (magicImageGenerationMutation.error as ErrorResponse).response.data.message,
      });
    }
  }, [magicImageGenerationMutation.isError]);

  // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
  useEffect(() => {
    form.setFocus('prompt');
  }, []);

  return (
    <form
      css={magicAIStyles.wrapper}
      onSubmit={form.handleSubmit(async (values) => {
        setImageLoading([true, true, true, true]);
        setShowEmptyState(false);
        try {
          await Promise.all(
            Array.from({ length: 4 }).map((_, index) => {
              return magicImageGenerationMutation
                .mutateAsync(values)
                .then((response) => {
                  setImages((previous) => {
                    const copy = [...previous];
                    copy[index] = response.data.data?.[0]?.b64_json ?? null;
                    return copy;
                  });

                  setImageLoading((previous) => {
                    const copy = [...previous];
                    copy[index] = false;
                    return copy;
                  });
                })
                .catch((error) => {
                  setImageLoading((previous) => {
                    const copy = [...previous];
                    copy[index] = false;
                    return copy;
                  });
                  throw error;
                });
            }),
          );
        } catch (error) {
          setImageLoading([false, false, false, false]);
          setShowEmptyState(true);
        }
      })}
    >
      <div css={magicAIStyles.left}>
        <Show when={!showEmptyState} fallback={<SVGIcon name="magicAiPlaceholder" width={72} height={72} />}>
          <div css={styles.images}>
            <For each={images}>
              {(src, index) => {
                return <AiImageItem key={index} src={src} loading={imageLoading[index]} index={index} />;
              }}
            </For>
          </div>
        </Show>
      </div>
      <div css={magicAIStyles.right}>
        <div css={styles.fields}>
          <div css={styles.promptWrapper}>
            <Controller
              control={form.control}
              name="prompt"
              render={(props) => (
                <FormTextareaInput
                  {...props}
                  label={__('Visualize Your Course', 'tutor')}
                  placeholder={__('Describe the image you want for your course thumbnail', 'tutor')}
                  rows={4}
                  isMagicAi
                  disabled={magicImageGenerationMutation.isPending}
                  enableResize={false}
                />
              )}
            />
            <button
              type="button"
              css={styles.inspireButton}
              onClick={() => {
                const length = inspirationPrompts.length;
                const index = Math.floor(Math.random() * length);
                form.reset({ ...form.getValues(), prompt: inspirationPrompts[index] });
              }}
              disabled={magicImageGenerationMutation.isPending}
            >
              <SVGIcon name="bulbLine" />
              {__('Inspire Me', 'tutor')}
            </button>
          </div>

          <Controller
            control={form.control}
            name="style"
            render={(props) => (
              <FormImageRadioGroup
                {...props}
                label={__('Styles', 'tutor')}
                options={styleOptions}
                disabled={magicImageGenerationMutation.isPending}
              />
            )}
          />
        </div>

        <div css={magicAIStyles.rightFooter}>
          <MagicButton type="submit" disabled={magicImageGenerationMutation.isPending || isDisabledButton}>
            <SVGIcon name={hasGeneratedImage ? 'reload' : 'magicAi'} width={24} height={24} />
            {hasGeneratedImage ? __('Generate Again', 'tutor') : __('Generate Now', 'tutor')}
          </MagicButton>
        </div>
      </div>
    </form>
  );
};

const styles = {
  images: css`
		display: grid;
		grid-template-columns: repeat(2, minmax(300px, 1fr));
    grid-template-rows: repeat(2, minmax(300px, 1fr));
		gap: ${spacing[12]};
    align-self: start;
    margin-block: ${spacing[24]};
	`,
  fields: css`
		display: flex;
		flex-direction: column;
		gap: ${spacing[12]};
	`,
  promptWrapper: css`
		position: relative;
    textarea {
      padding-bottom: ${spacing[40]} !important;
    }
	`,
  inspireButton: css`
		${styleUtils.resetButton};	
		${typography.small()};
		position: absolute;
		height: 28px;
		bottom: ${spacing[12]};
		left: ${spacing[12]};
		border: 1px solid ${colorTokens.stroke.brand};
		border-radius: ${borderRadius[4]};
		display: flex;
		align-items: center;
		gap: ${spacing[4]};
		color: ${colorTokens.text.brand};
		padding-inline: ${spacing[12]};
		background-color: ${colorTokens.background.white};

		&:hover {
			background-color: ${colorTokens.background.brand};
			color: ${colorTokens.text.white};
		}

    &:focus-visible {
      outline: 2px solid ${colorTokens.stroke.brand};
      outline-offset: 1px;
    }

		&:disabled {
			background-color: ${colorTokens.background.disable};
			color: ${colorTokens.text.disable};
		}
	`,
};
