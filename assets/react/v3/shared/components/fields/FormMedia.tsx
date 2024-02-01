import React from 'react';
import Button, { ButtonVariant } from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import { useModal } from '@Components/modals/Modal';
import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { css, SerializedStyles } from '@emotion/react';
import { useDebounce } from '@Hooks/useDebounce';
import { FormControllerProps } from '@Utils/form';
import { useEffect, useRef, useState } from 'react';
import FormFieldWrapper from './FormFieldWrapper';
import { typography } from '@Config/typography';

type Media = {
  src: string | undefined;
  width?: number | string;
  height?: number | string;
};

type FormMediaProps = {
  label?: string;
  onChange?: () => void;
  helpText?: string;
  allowedMediaType?: string;
} & FormControllerProps<string | Media | undefined>;

const FormMedia = ({ field, fieldState, label, helpText, allowedMediaType = 'image' }: FormMediaProps) => {
  const videoElementRef = useRef<HTMLVideoElement>(null);

  const [isOpen, setIsOpen] = useState(false);

  const fieldValue = field.value;

  // let mediaSource = fieldValue.src.startsWith('http') ? fieldValue.src : `${Joomla.pagebuilderBase}${fieldValue.src}`;
  let isFieldEmpty = !fieldValue;

  // const debouncedMediaSource = useDebounce(mediaSource);

  // useEffect(() => {
  //   videoElementRef.current?.load();
  // }, [debouncedMediaSource]);

  const uploadHandler = () => {
    console.log('upload');
  };

  const clearHandler = () => {
    field.onChange({
      src: '',
      width: '',
      height: '',
    });
  };

  return (
    <FormFieldWrapper label={label} field={field} fieldState={fieldState} helpText={helpText}>
      {() => {
        return (
          <div css={styles.wrapper}>
            {isFieldEmpty ? (
              <div css={styles.emptyMedia}>
                <SVGIcon name="addImage" width={32} height={32} />
                <Button variant={ButtonVariant.text} onClick={uploadHandler}>
                  Upload Course Thumbnail
                </Button>
                <p css={styles.infoTexts}>Size: 700x430 pixels</p>
              </div>
            ) : (
              <>
                {allowedMediaType === 'image' && (
                  <img src="" />
                  // <img src={debouncedMediaSource} alt={debouncedMediaSource} css={styles.imagePreview} />
                )}
                {allowedMediaType === 'video' && (
                  <div css={styles.videoWrapper}>
                    <video muted ref={videoElementRef}>
                      <source src={debouncedMediaSource} type="video/mp4"></source>
                    </video>

                    {/* <SVGIcon name="playCircle" height={32} width={32} /> */}
                  </div>
                )}
                <div css={styles.hoverPreview} data-hover-buttons-wrapper>
                  <Button
                    icon={<SVGIcon name="delete" height={16} width={16} />}
                    buttonContentCss={styles.buttonContentPrimary}
                    onClick={uploadHandler}
                  >
                    Replace
                  </Button>
                  <Button variant="secondary" onClick={clearHandler}>
                    Clear
                  </Button>
                </div>
              </>
            )}
          </div>
        );
      }}
    </FormFieldWrapper>
  );
};

export default FormMedia;

const styles = {
  wrapper: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[12]};
  `,
  emptyMedia: css`
    height: 168px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: ${spacing[8]};
    border: 1px dashed ${colorTokens.stroke.border};
    border-radius: ${borderRadius[8]};

    svg {
      color: ${colorTokens.icon.default};
    }
  `,
  infoTexts: css`
    ${typography.small()};
    color: ${colorTokens.text.subdued};
  `,
  imagePreview: css`
    height: 100%;
    width: 100%;
    object-fit: contain;
  `,
  hoverPreview: css`
    display: none;
    justify-content: center;
    align-items: center;
    gap: ${spacing[8]};
    opacity: 0;
    position: absolute;
    inset: 0;
    background-color: ${colorTokens.color.black[60]};
    border-radius: ${borderRadius[8]};

    button {
      height: 32px;

      :last-of-type {
        background-color: ${colorTokens.background.white};
      }
    }
  `,
  buttonContent: css`
    :hover {
      svg {
        color: ${colorTokens.icon.brand};
      }
    }

    svg {
      color: ${colorTokens.icon.default};
    }
  `,
  videoWrapper: css`
    position: relative;
    width: 100%;
    height: 100%;

    > video {
      width: 100%;
      height: 100%;
    }

    > svg {
      position: absolute;
      left: 20px;
      top: 20px;
      color: ${colorTokens.icon.white};
    }
  `,
};
