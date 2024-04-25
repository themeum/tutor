import { type SerializedStyles, css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { rgba } from 'polished';

import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';

import { borderRadius, colorTokens, shadow, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';

export type Media = {
  id: number | null;
  url: string;
  title?: string;
};

interface ImageInputProps {
  buttonText?: string;
  infoText?: string;
  value: Media | null;
  uploadHandler: () => void;
  clearHandler: () => void;
  emptyImageCss?: SerializedStyles;
  previewImageCss?: SerializedStyles;
}

const ImageInput = ({
  buttonText = __('Upload Media', 'tutor'),
  infoText,
  value,
  uploadHandler,
  clearHandler,
  emptyImageCss,
  previewImageCss,
}: ImageInputProps) => {
  return (
    <Show
      when={value?.url}
      fallback={
        <div
          css={[styles.emptyMedia, emptyImageCss]}
          onClick={(event) => {
            event.stopPropagation();
            uploadHandler();
          }}
          onKeyDown={(event) => {
            if (event.key === 'Enter') {
              event.preventDefault();
              uploadHandler();
            }
          }}
        >
          <SVGIcon name="addImage" width={32} height={32} />
          <Button variant="text" buttonContentCss={styles.buttonText}>
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
          <div css={[styles.previewWrapper, previewImageCss]}>
            <img src={url} alt={value?.title} css={styles.imagePreview} />
            <div css={styles.hoverPreview} data-hover-buttons-wrapper>
              <Button
                variant="secondary"
                onClick={(event) => {
                  event.stopPropagation();
                  uploadHandler();
                }}
              >
                {__('Replace Image', 'tutor')}
              </Button>
              <Button
                variant="text"
                onClick={(event) => {
                  event.stopPropagation();
                  clearHandler();
                }}
              >
                {__('Remove', 'tutor')}
              </Button>
            </div>
          </div>
        );
      }}
    </Show>
  );
};

export default ImageInput;

const styles = {
  emptyMedia: css`
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
    cursor: pointer;

    svg {
      color: ${colorTokens.icon.default};
    }

    &:hover svg {
      color: ${colorTokens.brand.blue};
    }
  `,
  buttonText: css`
    color: ${colorTokens.text.brand};
  `,
  infoTexts: css`
    ${typography.small()};
    color: ${colorTokens.text.subdued};
  `,
  previewWrapper: css`
    width: 100%;
    height: 168px;
    border: 1px solid ${colorTokens.stroke.default};
    border-radius: ${borderRadius[8]};
    overflow: hidden;
    position: relative;
    background-color: ${colorTokens.bg.white};

    &:hover {
      [data-hover-buttons-wrapper] {
        opacity: 1;
      }
    }
  `,
  imagePreview: css`
    height: 100%;
    width: 100%;
    object-fit: cover;
    object-position: center;
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
    border-radius: ${borderRadius[8]};

    button:first-of-type {
      box-shadow: ${shadow.button};
    }

    button:last-of-type {
      color: ${colorTokens.text.white};
      box-shadow: none;
    }
  `,
};
