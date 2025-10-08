import MagicButton from '@TutorShared/atoms/MagicButton';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import FormImageRadioGroup from '@TutorShared/components/fields/FormImageRadioGroup';
import FormTextareaInput from '@TutorShared/components/fields/FormTextareaInput';
import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import For from '@TutorShared/controls/For';
import Show from '@TutorShared/controls/Show';
import { useMagicImageGenerationMutation } from '@TutorShared/services/magic-ai';

import threeD from '@SharedImages/ai-types/3d.png';
import blackAndWhite from '@SharedImages/ai-types/black-and-white.png';
import concept from '@SharedImages/ai-types/concept.png';
import dreamy from '@SharedImages/ai-types/dreamy.png';
import filmic from '@SharedImages/ai-types/filmic.png';
import illustration from '@SharedImages/ai-types/illustration.png';
import neon from '@SharedImages/ai-types/neon.png';
import none from '@SharedImages/ai-types/none.jpg';
import painting from '@SharedImages/ai-types/painting.png';
import photo from '@SharedImages/ai-types/photo.png';
import retro from '@SharedImages/ai-types/retro.png';
import sketch from '@SharedImages/ai-types/sketch.png';

import { useToast } from '@TutorShared/atoms/Toast';
import type { ErrorResponse } from '@TutorShared/utils/form';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { type OptionWithImage, isDefined } from '@TutorShared/utils/types';
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
    label: __('None', __TUTOR_TEXT_DOMAIN__),
    value: 'none',
    image: none,
  },
  {
    label: __('Photo', __TUTOR_TEXT_DOMAIN__),
    value: 'photo',
    image: photo,
  },
  {
    label: __('Neon', __TUTOR_TEXT_DOMAIN__),
    value: 'neon',
    image: neon,
  },
  {
    label: __('3D', __TUTOR_TEXT_DOMAIN__),
    value: '3d',
    image: threeD,
  },
  {
    label: __('Painting', __TUTOR_TEXT_DOMAIN__),
    value: 'painting',
    image: painting,
  },
  {
    label: __('Sketch', __TUTOR_TEXT_DOMAIN__),
    value: 'sketch',
    image: sketch,
  },
  {
    label: __('Concept', __TUTOR_TEXT_DOMAIN__),
    value: 'concept_art',
    image: concept,
  },
  {
    label: __('Illustration', __TUTOR_TEXT_DOMAIN__),
    value: 'illustration',
    image: illustration,
  },
  {
    label: __('Dreamy', __TUTOR_TEXT_DOMAIN__),
    value: 'dreamy',
    image: dreamy,
  },
  {
    label: __('Filmic', __TUTOR_TEXT_DOMAIN__),
    value: 'filmic',
    image: filmic,
  },
  {
    label: __('Retro', __TUTOR_TEXT_DOMAIN__),
    value: 'retrowave',
    image: retro,
  },
  {
    label: __('Black & White', __TUTOR_TEXT_DOMAIN__),
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

  useEffect(() => {
    if (magicImageGenerationMutation.isError) {
      showToast({
        type: 'danger',
        message: (magicImageGenerationMutation.error as ErrorResponse).response.data.message,
      });
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [magicImageGenerationMutation.isError]);

  useEffect(() => {
    form.setFocus('prompt');
    // eslint-disable-next-line react-hooks/exhaustive-deps
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
        } catch {
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
                  label={__('Visualize Your Course', __TUTOR_TEXT_DOMAIN__)}
                  placeholder={__('Describe the image you want for your course thumbnail', __TUTOR_TEXT_DOMAIN__)}
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
              {__('Inspire Me', __TUTOR_TEXT_DOMAIN__)}
            </button>
          </div>

          <Controller
            control={form.control}
            name="style"
            render={(props) => (
              <FormImageRadioGroup
                {...props}
                label={__('Styles', __TUTOR_TEXT_DOMAIN__)}
                options={styleOptions}
                disabled={magicImageGenerationMutation.isPending}
              />
            )}
          />
        </div>

        <div css={magicAIStyles.rightFooter}>
          <MagicButton type="submit" disabled={magicImageGenerationMutation.isPending || isDisabledButton}>
            <SVGIcon name={hasGeneratedImage ? 'reload' : 'magicAi'} width={24} height={24} />
            {hasGeneratedImage
              ? __('Generate Again', __TUTOR_TEXT_DOMAIN__)
              : __('Generate Now', __TUTOR_TEXT_DOMAIN__)}
          </MagicButton>
        </div>
      </div>
    </form>
  );
};

const styles = {
  images: css`
    display: grid;
    grid-template-columns: repeat(2, minmax(150px, 1fr));
    grid-template-rows: repeat(2, minmax(150px, 1fr));
    gap: ${spacing[12]};
    align-self: start;
    padding: ${spacing[24]};
    width: 100%;
    height: 100%;

    > div {
      aspect-ratio: 1 / 1;
    }
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
