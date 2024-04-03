import { useState } from 'react';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';

import SVGIcon from '@Atoms/SVGIcon';
import Button from '@Atoms/Button';
import ImageInput from '@Atoms/ImageInput';

import { borderRadius, colorTokens, shadow, spacing } from '@Config/styles';
import Show from '@Controls/Show';
import { typography } from '@Config/typography';
import { styleUtils } from '@Utils/style-utils';
import { noop } from '@Utils/util';

const MultipleChoice = () => {
  const [hasMultipleCorrectAnswers, setHasMultipleCorrectAnswers] = useState(false);
  const [selectedAnswer, setSelectedAnswer] = useState<boolean | null>(null);
  const [isEditing, setIsEditing] = useState(false);
  const [isUploadImageVisible, setIsUploadImageVisible] = useState(false);

  return (
    <div css={styles.optionWrapper}>
      <div
        css={styles.option({
          isSelected: selectedAnswer === true,
          isEditing,
          isMultipleChoice: hasMultipleCorrectAnswers,
        })}
      >
        <div
          onClick={() => setSelectedAnswer(true)}
          onKeyDown={(event) => {
            if (event.key === 'Enter') {
              setSelectedAnswer(true);
            }
          }}
        >
          <Show
            when={hasMultipleCorrectAnswers}
            fallback={
              <SVGIcon
                data-check-icon
                name={selectedAnswer === true ? 'checkFilled' : 'check'}
                height={32}
                width={32}
              />
            }
          >
            <SVGIcon
              data-check-icon
              name={selectedAnswer === true ? 'checkSquareFilled' : 'checkSquare'}
              height={32}
              width={32}
            />
          </Show>
        </div>
        <div
          css={styles.optionLabel({ isSelected: selectedAnswer === true, isEditing })}
          onClick={() => {
            setSelectedAnswer(true);
          }}
          onKeyDown={(event) => {
            if (event.key === 'Enter') {
              setSelectedAnswer(true);
            }
          }}
        >
          <div css={styles.optionHeader}>
            <div css={styles.optionCounterAndButton}>
              <div css={styles.optionCounter({ isSelected: selectedAnswer === true, isEditing })}>A</div>
              <Show when={isEditing}>
                <Show
                  when={!isUploadImageVisible}
                  fallback={
                    <Button
                      variant="text"
                      icon={<SVGIcon name="removeImage" width={24} height={24} />}
                      onClick={(event) => {
                        event.stopPropagation();
                        setIsUploadImageVisible(false);
                      }}
                    >
                      Remove Image
                    </Button>
                  }
                >
                  <Button
                    variant="text"
                    icon={<SVGIcon name="addImage" width={24} height={24} />}
                    onClick={(event) => {
                      event.stopPropagation();
                      setIsUploadImageVisible(true);
                    }}
                  >
                    Add Image
                  </Button>
                </Show>
              </Show>
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
                  alert('@TODO: will be implemented later');
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
                  <Show when={isUploadImageVisible}>
                    <div css={styles.imagePlaceholder}>
                      <SVGIcon name="imagePreview" height={48} width={48} />
                    </div>
                  </Show>
                  <div css={styles.optionPlaceholder}>{__('Write answer option...', 'tutor')}</div>
                </div>
              }
            >
              <div css={styles.optionInputWrapper}>
                <Show when={isUploadImageVisible}>
                  <ImageInput
                    value={null}
                    infoText={__('Size: 700x430 pixels', 'tutor')}
                    uploadHandler={noop}
                    clearHandler={noop}
                    emptyImageCss={styles.emptyImageInput}
                    previewImageCss={styles.previewImageInput}
                  />
                </Show>
                <textarea css={styles.optionInput} placeholder="Write anything" />
                <div css={styles.optionInputButtons}>
                  <Button
                    variant="text"
                    size="small"
                    onClick={(event) => {
                      event.stopPropagation();
                      setIsEditing(false);
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
    </div>
  );
};

export default MultipleChoice;

const styles = {
  optionWrapper: css`
      ${styleUtils.display.flex('column')};
      gap: ${spacing[12]};
    `,
  option: ({
    isSelected,
    isEditing,
    isMultipleChoice,
  }: {
    isSelected: boolean;
    isEditing: boolean;
    isMultipleChoice: boolean;
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

        ${
          !isMultipleChoice &&
          css`
            fill: none;
          `
        }
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
            color: ${colorTokens.bg.success};

            ${
              !isMultipleChoice &&
              css`
                fill: ${colorTokens.bg.success};
              `
            }
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
      display: flex;
      flex-direction: column;
      gap: ${spacing[12]};
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
  optionCounterAndButton: css`
    ${styleUtils.display.flex()}
    gap: ${spacing[8]};
    place-self: center start;

    button {
      padding: 0;
    }
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

    svg {
      color: ${colorTokens.icon.hints};
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
