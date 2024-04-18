import { useEffect, useRef, useState } from 'react';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';

import SVGIcon from '@Atoms/SVGIcon';
import Button from '@Atoms/Button';
import ImageInput from '@Atoms/ImageInput';

import { borderRadius, colorTokens, fontWeight, shadow, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { styleUtils } from '@Utils/style-utils';
import Show from '@Controls/Show';
import type { FormControllerProps } from '@Utils/form';
import type { QuizQuestionOption } from '@CourseBuilderServices/quiz';
import { isDefined } from '@Utils/types';

interface FormImageAnsweringProps extends FormControllerProps<QuizQuestionOption | null> {
  index: number;
  onRemoveOption: () => void;
}

const FormImageAnswering = ({ index, onRemoveOption, field }: FormImageAnsweringProps) => {
  const inputValue = field.value ?? {
    ID: 0,
    title: '',
  };
  const inputRef = useRef<HTMLInputElement>(null);

  const [selectedAnswer, setSelectedAnswer] = useState<boolean | null>(inputValue.isCorrect || null);
  const [isEditing, setIsEditing] = useState(false);
  const [previousValue] = useState<QuizQuestionOption>(inputValue);

  const wpMedia = window.wp.media({
    library: { type: 'image' },
  });

  const uploadHandler = () => {
    wpMedia.open();
  };

  wpMedia.on('select', () => {
    const attachment = wpMedia.state().get('selection').first().toJSON();
    const { id, url, title } = attachment;

    field.onChange({
      ...inputValue,
      image: { id, url, title },
    });
  });

  const clearHandler = () => {
    field.onChange({
      ...inputValue,
      image: {
        id: null,
        url: '',
        title: '',
      },
    });
  };

  useEffect(() => {
    if (isDefined(inputRef.current) && isEditing) {
      inputRef.current.focus();
    }
  }, [isEditing]);

  return (
    <div css={styles.option({ isSelected: selectedAnswer === true, isEditing })}>
      <div
        onClick={() => setSelectedAnswer((previous) => !previous)}
        onKeyDown={(event) => {
          if (event.key === 'Enter') {
            setSelectedAnswer((previous) => !previous);
          }
        }}
      >
        <SVGIcon data-check-icon name={selectedAnswer === true ? 'checkFilled' : 'check'} height={32} width={32} />
      </div>
      <div
        css={styles.optionLabel({ isSelected: selectedAnswer === true, isEditing })}
        onClick={() => {
          setSelectedAnswer((previous) => !previous);
        }}
        onKeyDown={(event) => {
          if (event.key === 'Enter') {
            setSelectedAnswer((previous) => !previous);
          }
        }}
      >
        <div css={styles.optionHeader}>
          <div css={styles.optionCounter({ isSelected: selectedAnswer === true, isEditing })}>
            {String.fromCharCode(65 + index)}
          </div>

          <button type="button" css={styles.optionDragButton} data-visually-hidden>
            <SVGIcon name="dragVertical" height={24} width={24} />
          </button>

          <div css={styles.optionActions}>
            <button
              type="button"
              css={styles.actionButton}
              data-edit-button
              onClick={(event) => {
                event.stopPropagation();
                setIsEditing(true);
              }}
            >
              <SVGIcon name="edit" width={24} height={24} />
            </button>
            <button
              type="button"
              css={styles.actionButton}
              data-visually-hidden
              onClick={(event) => {
                event.stopPropagation();
                alert('@TODO: will be implemented later');
              }}
            >
              <SVGIcon name="copyPaste" width={24} height={24} />
            </button>
            <button
              type="button"
              css={styles.actionButton}
              data-visually-hidden
              onClick={(event) => {
                event.stopPropagation();
                onRemoveOption();
              }}
            >
              <SVGIcon name="delete" width={24} height={24} />
            </button>
          </div>
        </div>
        <div css={styles.optionBody}>
          <Show
            when={isEditing}
            fallback={
              <div css={styles.placeholderWrapper}>
                <Show
                  when={inputValue.image}
                  fallback={
                    <div css={styles.imagePlaceholder}>
                      <SVGIcon name="imagePreview" height={48} width={48} />
                    </div>
                  }
                >
                  {(image) => (
                    <div css={styles.imagePlaceholder}>
                      <img src={image.url} alt={image.title} />
                    </div>
                  )}
                </Show>
                <div css={styles.optionPlaceholder}>{inputValue.title || __('Write answer option...', 'tutor')}</div>
              </div>
            }
          >
            <div css={styles.optionInputWrapper}>
              <ImageInput
                value={inputValue.image || null}
                buttonText={__('Upload Image', 'tutor')}
                infoText={__('Size: 700x430 pixels', 'tutor')}
                uploadHandler={uploadHandler}
                clearHandler={clearHandler}
                emptyImageCss={styles.emptyImageInput}
                previewImageCss={styles.previewImageInput}
              />
              <div css={styles.inputWithHints}>
                <input
                  {...field}
                  ref={inputRef}
                  type="text"
                  css={styles.optionInput}
                  placeholder={__('Answer input value...', 'tutor')}
                  value={inputValue.title}
                  onChange={(event) => {
                    field.onChange({
                      ...inputValue,
                      title: event.target.value,
                    });
                  }}
                  onKeyDown={(event) => {
                    event.stopPropagation();
                  }}
                />

                <div css={styles.inputHints}>
                  <SVGIcon name="info" height={20} width={20} />
                  <p>
                    {__('The answers that students enter should match with this text. Write in ', 'tutor')}
                    <span css={{ fontWeight: fontWeight.semiBold }}>{__('small caps', 'tutor')}</span>
                  </p>
                </div>
              </div>
              <div css={styles.optionInputButtons}>
                <Button
                  variant="text"
                  size="small"
                  onClick={(event) => {
                    event.stopPropagation();
                    setIsEditing(false);
                    field.onChange(previousValue);
                  }}
                >
                  {__('Cancel', 'tutor')}
                </Button>
                <Button
                  variant="secondary"
                  size="small"
                  onClick={(event) => {
                    event.stopPropagation();
                    setIsEditing(false);
                  }}
                >
                  {__('Ok', 'tutor')}
                </Button>
              </div>
            </div>
          </Show>
        </div>
      </div>
    </div>
  );
};

export default FormImageAnswering;

const styles = {
  option: ({
    isSelected,
    isEditing,
  }: {
    isSelected: boolean;
    isEditing: boolean;
  }) => css`
      ${styleUtils.display.flex()};
      ${typography.caption('medium')};
      align-items: center;
      color: ${colorTokens.text.subdued};
      gap: ${spacing[10]};
      align-items: center;
  
      [data-check-icon] {
        opacity: 0;
        transition: opacity 0.15s ease-in-out;
        fill: none;
      }

      [data-visually-hidden] {
        opacity: 0;
        transition: opacity 0.3s ease-in-out;
      }

      [data-edit-button] {
        opacity: 0;
        transition: opacity 0.3s ease-in-out;
      }
  
      &:hover {
        [data-check-icon] {
          opacity: 1;
        }

        [data-visually-hidden] {
          opacity: 1;
        }

        ${
          !isEditing &&
          css`
          [data-edit-button] {
            opacity: 1;
          }
        `
        }
      }
  
  
      ${
        isSelected &&
        css`
          [data-check-icon] {
            opacity: 1;
            fill: ${colorTokens.bg.success};
          }
        `
      }

      ${
        isEditing &&
        css`
        [data-edit-button] {
          opacity: 0;
        }
      `
      }
    `,
  optionLabel: ({
    isSelected,
    isEditing,
  }: {
    isSelected: boolean;
    isEditing: boolean;
  }) => css`
      ${styleUtils.display.flex('column')}
      gap: ${spacing[20]};
      width: 100%;
      border-radius: ${borderRadius.card};
      padding: ${spacing[12]} ${spacing[16]};
      transition: box-shadow 0.15s ease-in-out;
      background-color: ${colorTokens.background.white};
      cursor: pointer;
  
      &:hover {
        box-shadow: 0 0 0 1px ${colorTokens.stroke.hover};
      }
  
      ${
        isSelected &&
        css`
          background-color: ${colorTokens.background.success.fill40};
          color: ${colorTokens.text.primary};
  
          &:hover {
            box-shadow: 0 0 0 1px ${colorTokens.stroke.success.fill70};
          }
        `
      }

      ${
        isEditing &&
        css`
          background-color: ${colorTokens.background.white};
          box-shadow: 0 0 0 1px ${isSelected ? colorTokens.stroke.success.fill70 : colorTokens.stroke.brand};

          &:hover {
            box-shadow: 0 0 0 1px ${isSelected ? colorTokens.stroke.success.fill70 : colorTokens.stroke.brand};
          }
        `
      }
    `,
  optionHeader: css`
    display: grid;
    grid-template-columns: 1fr auto 1fr;
    align-items: center;
  `,
  optionCounter: ({
    isSelected,
    isEditing,
  }: {
    isSelected: boolean;
    isEditing: boolean;
  }) => css`
    height: ${spacing[24]};
    width: ${spacing[24]};
    border-radius: ${borderRadius.min};
    ${typography.caption('medium')};
    color: ${colorTokens.text.subdued};
    background-color: ${colorTokens.background.default};
    text-align: center;
    place-self: center start;

    ${
      isSelected &&
      !isEditing &&
      css`
        background-color: ${colorTokens.bg.white};
      `
    }
  `,
  optionDragButton: css`
    ${styleUtils.resetButton}
    ${styleUtils.flexCenter()}
    transform: rotate(90deg);
    color: ${colorTokens.icon.default};
    cursor: grab;
    place-self: center center;
  `,
  optionActions: css`
    ${styleUtils.display.flex()}
    gap: ${spacing[8]};
    place-self: center end;
  `,
  actionButton: css`
    ${styleUtils.resetButton};
    color: ${colorTokens.icon.default};
    ${styleUtils.display.flex()}
    cursor: pointer;
  `,
  optionBody: css`
    ${styleUtils.display.flex()}
  `,
  placeholderWrapper: css`
    ${styleUtils.display.flex('column')}
    gap: ${spacing[12]};
    width: 100%;
  `,
  imagePlaceholder: css`
    ${styleUtils.flexCenter()};
    height: 210px;
    width: 100%;
    background-color: ${colorTokens.background.default};
    border-radius: ${borderRadius.card};
    overflow: hidden;
    svg {
      color: ${colorTokens.icon.hints};
    }

    img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      object-position: center;
    }
  `,
  emptyImageInput: css`
    background-color: ${colorTokens.background.default};
    height: 210px;
  `,
  previewImageInput: css`
    height: 210px;
  `,
  optionPlaceholder: css`
    ${typography.body()};
    color: ${colorTokens.text.subdued};
    padding-block: ${spacing[4]};
  `,
  optionInputWrapper: css`
    ${styleUtils.display.flex('column')}
    width: 100%;
    gap: ${spacing[12]};
  `,
  inputWithHints: css`
    ${styleUtils.display.flex('column')}
    gap: ${spacing[8]};
  `,
  inputHints: css`
    ${styleUtils.display.flex()}
    gap: ${spacing[4]};
    ${typography.small()};
    color: ${colorTokens.text.hints};
    align-items: flex-start;

    svg {
      flex-shrink: 0;
    }
  `,
  optionInput: css`
    ${styleUtils.resetButton};
    ${typography.caption()};
    flex: 1;
    color: ${colorTokens.text.subdued};
    padding: ${spacing[4]} ${spacing[10]};
    box-shadow: 0 0 0 1px ${colorTokens.stroke.default};
    border-radius: ${borderRadius[6]};
    resize: vertical;

    &:focus {
      box-shadow: ${shadow.focus};
    }
  `,
  optionInputButtons: css`
    ${styleUtils.display.flex()}
    justify-content: flex-end;
    gap: ${spacing[8]};
  `,
};
