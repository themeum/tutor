import { type SerializedStyles, css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import rgba from 'polished/lib/color/rgba';

import type { ButtonSize } from '@TutorShared/atoms/Button';
import Button from '@TutorShared/atoms/Button';
import SVGIcon from '@TutorShared/atoms/SVGIcon';

import { borderRadius, colorTokens, shadow, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import Show from '@TutorShared/controls/Show';
import { type WPMedia } from '@TutorShared/hooks/useWpMedia';

import { LoadingOverlay } from './LoadingSpinner';

export type ImageInputSize = 'large' | 'regular' | 'small';

interface ImageInputProps {
  buttonText?: string;
  infoText?: string;
  size?: ImageInputSize;
  value: WPMedia | null;
  uploadHandler: () => void;
  clearHandler: () => void;
  emptyImageCss?: SerializedStyles;
  previewImageCss?: SerializedStyles;
  overlayCss?: SerializedStyles;
  replaceButtonText?: string;
  loading?: boolean;
  disabled?: boolean;
  isClearAble?: boolean;
}

const sizeMap: Record<ImageInputSize, ButtonSize> = {
  large: 'regular',
  regular: 'small',
  small: 'small',
};

const ImageInput = ({
  buttonText = __('Upload Media', __TUTOR_TEXT_DOMAIN__),
  infoText,
  size = 'regular',
  value,
  uploadHandler,
  clearHandler,
  emptyImageCss,
  previewImageCss,
  overlayCss,
  replaceButtonText,
  loading,
  disabled = false,
  isClearAble = true,
}: ImageInputProps) => {
  return (
    <Show
      when={!loading}
      fallback={
        <div
          css={styles.emptyMedia({
            size,
            isDisabled: disabled,
          })}
        >
          <LoadingOverlay />
        </div>
      }
    >
      <Show
        when={value?.url}
        fallback={
          <div
            aria-disabled={disabled}
            css={[
              styles.emptyMedia({
                size,
                isDisabled: disabled,
              }),
              emptyImageCss,
            ]}
            onClick={(event) => {
              event.stopPropagation();

              if (disabled) {
                return;
              }

              uploadHandler();
            }}
            onKeyDown={(event) => {
              if (!disabled && event.key === 'Enter') {
                event.preventDefault();
                uploadHandler();
              }
            }}
          >
            <SVGIcon name="addImage" width={32} height={32} />
            <Button
              disabled={disabled}
              size={sizeMap[size]}
              variant="secondary"
              buttonContentCss={styles.buttonText}
              data-cy="upload-media"
            >
              {buttonText}
            </Button>
            <Show when={infoText}>
              <p css={styles.infoTexts}>{infoText}</p>
            </Show>
          </div>
        }
      >
        {(url) => {
          return (
            <div
              css={[
                styles.previewWrapper({
                  size,
                  isDisabled: disabled,
                }),
                previewImageCss,
              ]}
              data-cy="media-preview"
            >
              <img src={url} alt={value?.title} css={styles.imagePreview} />
              <div css={[styles.hoverPreview, overlayCss]} data-hover-buttons-wrapper>
                <Button
                  disabled={disabled}
                  variant="secondary"
                  size={sizeMap[size]}
                  buttonCss={css`
                    margin-top: ${isClearAble && spacing[16]};
                  `}
                  onClick={(event) => {
                    event.stopPropagation();
                    uploadHandler();
                  }}
                  data-cy="replace-media"
                >
                  {replaceButtonText || __('Replace Image', __TUTOR_TEXT_DOMAIN__)}
                </Button>
                <Show when={isClearAble}>
                  <Button
                    disabled={disabled}
                    variant="text"
                    size={sizeMap[size]}
                    onClick={(event) => {
                      event.stopPropagation();
                      clearHandler();
                    }}
                    data-cy="clear-media"
                  >
                    {__('Remove', __TUTOR_TEXT_DOMAIN__)}
                  </Button>
                </Show>
              </div>
            </div>
          );
        }}
      </Show>
    </Show>
  );
};

export default ImageInput;

const styles = {
  emptyMedia: ({ size, isDisabled }: { size: ImageInputSize; isDisabled: boolean }) => css`
    width: 100%;
    height: 168px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: ${spacing[8]};
    border: 1px dashed ${colorTokens.stroke.border};
    border-radius: ${borderRadius[8]};
    background-color: ${colorTokens.bg.white};
    overflow: hidden;
    cursor: ${isDisabled ? 'not-allowed' : 'pointer'};

    ${size === 'small' &&
    css`
      width: 168px;
    `}

    svg {
      color: ${colorTokens.icon.default};
    }

    &:hover svg {
      color: ${!isDisabled && colorTokens.brand.blue};
    }
  `,
  buttonText: css`
    color: ${colorTokens.text.brand};
  `,
  infoTexts: css`
    ${typography.tiny()};
    color: ${colorTokens.text.subdued};
    text-align: center;
  `,
  previewWrapper: ({ size, isDisabled }: { size: ImageInputSize; isDisabled: boolean }) => css`
    width: 100%;
    height: 168px;
    border: 1px solid ${colorTokens.stroke.default};
    border-radius: ${borderRadius[8]};
    overflow: hidden;
    position: relative;
    background-color: ${colorTokens.bg.white};

    ${size === 'small' &&
    css`
      width: 168px;
    `}

    &:hover {
      [data-hover-buttons-wrapper] {
        display: ${isDisabled ? 'none' : 'flex'};
        opacity: ${isDisabled ? 0 : 1};
      }
    }
  `,
  imagePreview: css`
    height: 100%;
    width: 100%;
    object-fit: contain;
  `,
  hoverPreview: css`
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    gap: ${spacing[8]};
    opacity: 0;
    position: absolute;
    inset: 0;
    background-color: ${rgba(colorTokens.color.black.main, 0.6)};

    button:first-of-type {
      box-shadow: ${shadow.button};
    }

    button:last-of-type:not(:only-of-type) {
      color: ${colorTokens.text.white};
      box-shadow: none;
    }
  `,
};
