import { useState } from 'react';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';

import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { styleUtils } from '@Utils/style-utils';
import SVGIcon from '@Atoms/SVGIcon';
import Show from '@Controls/Show';
import Button from '@Atoms/Button';

const Matching = () => {
  const [selectedAnswer, setSelectedAnswer] = useState<boolean | null>(null);
  const [isEditing, setIsEditing] = useState<boolean>(false);

  return (
    <div css={styles.optionWrapper}>
      <div css={styles.option({ isSelected: selectedAnswer === true, isEditing })}>
        <div
          onClick={() => setSelectedAnswer(true)}
          onKeyDown={(event) => {
            if (event.key === 'Enter') {
              setSelectedAnswer(true);
            }
          }}
        >
          <SVGIcon data-check-icon name={selectedAnswer === true ? 'checkFilled' : 'check'} height={32} width={32} />
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
            <div css={styles.optionCounter({ isSelected: selectedAnswer === true, isEditing })}>A</div>

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
                  <div css={styles.optionPlaceholder}>{__('Answer title...', 'tutor')}</div>
                  <div css={styles.optionPlaceholder}>{__('Matched answer titile...', 'tutor')}</div>
                </div>
              }
            >
              <div css={styles.optionInputWrapper}>
                <input css={styles.optionInput} placeholder="Write anything" />
                <input css={styles.optionInput} placeholder="Write anything" />
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

export default Matching;

const styles = {
  optionWrapper: css`
      ${styleUtils.display.flex('column')};
      gap: ${spacing[12]};
    `,
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
    display: flex;
    justify-content: space-between;
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
    display: flex;
    justify-content: center;
    align-items: center;
    transform: rotate(90deg);
    color: ${colorTokens.icon.default};
    cursor: grab;
  `,
  optionActions: css`
    display: flex;
    gap: ${spacing[8]};
    align-items: center;
  `,
  actionButton: css`
    ${styleUtils.resetButton};
    color: ${colorTokens.icon.default};
    display: flex;
    cursor: pointer;
  `,
  optionBody: css`
    display: flex;
  `,
  placeholderWrapper: css`
    display: flex;
    flex-direction: column;
  `,
  optionPlaceholder: css`
    ${typography.body()};
    color: ${colorTokens.text.subdued};
    padding-block: ${spacing[4]};
  `,
  optionInputWrapper: css`
    display: flex;
    flex-direction: column;
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
      box-shadow: 0 0 0 1px ${colorTokens.stroke.brand};
    }
  `,
  optionInputButtons: css`
    display: flex;
    justify-content: flex-end;
    gap: ${spacing[8]};
  `,
};
