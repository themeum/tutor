import { Fragment, useState } from 'react';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';

import { borderRadius, colorTokens, shadow, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { styleUtils } from '@Utils/style-utils';
import SVGIcon from '@Atoms/SVGIcon';
import Show from '@Controls/Show';
import Button from '@Atoms/Button';
import For from '@Controls/For';

const FillinTheBlanks = () => {
  const [isEditing, setIsEditing] = useState<boolean>(false);
  const [data, setData] = useState<{
    title: string;
    correctAnswers: string[];
  }>({
    title: '', // That is {dash} a good idea.
    correctAnswers: [], // That is | a | good | idea.
  });

  return (
    <div css={styles.optionWrapper}>
      <div css={styles.option({ isEditing })}>
        <div
          css={styles.optionLabel({ isEditing })}
          onClick={() => {}}
          onKeyDown={(event) => {
            if (event.key === 'Enter') {
            }
          }}
        >
          <div css={styles.optionHeader}>
            <div css={styles.optionTitle}>Fill in the blanks</div>

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
                  <div css={styles.optionPlaceholder({ isTitle: !!data.title })}>
                    {data.title ? data.title.replace(/{dash}/g, '_____') : __('Question title...', 'tutor')}
                  </div>
                  <div css={styles.optionPlaceholder({ isCorrectAnswer: data.correctAnswers.length })}>
                    {data.correctAnswers.length > 0 ? (
                      <For each={data.correctAnswers}>
                        {(answer, index) => (
                          <Fragment key={index}>
                            {answer}
                            <Show when={index < data.correctAnswers.length - 1}>
                              <span>|</span>
                            </Show>
                          </Fragment>
                        )}
                      </For>
                    ) : (
                      __('Correct Answer(s)...', 'tutor')
                    )}
                  </div>
                </div>
              }
            >
              <div css={styles.optionInputWrapper}>
                <div css={styles.inputWithHints}>
                  <input type="text" css={styles.optionInput} placeholder={__('Question title...', 'tutor')} />
                  <div css={styles.inputHints}>
                    <SVGIcon name="info" height={20} width={20} />
                    <p>
                      {__(
                        'Please make sure to use the {dash} variable in your question title to show the blanks in your question. You can use multiple {dash} variables in one question.',
                        'tutor'
                      )}
                    </p>
                  </div>
                </div>
                <div css={styles.inputWithHints}>
                  <input type="text" css={styles.optionInput} placeholder={__('Correct Answer(s)...')} />
                  <div css={styles.inputHints}>
                    <SVGIcon name="info" height={20} width={20} />
                    <p>
                      {__(
                        'Separate multiple answers by a vertical bar |. 1 answer per {dash} variable is defined in the question. Example: Apple | Banana | Orange',
                        'tutor'
                      )}
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

export default FillinTheBlanks;

const styles = {
  optionWrapper: css`
      ${styleUtils.display.flex('column')};
      padding-left: 42px;
    `,
  option: ({
    isEditing,
  }: {
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
        isEditing &&
        css`
        [data-edit-button] {
          opacity: 0;
        }
      `
      }
    `,
  optionLabel: ({
    isEditing,
  }: {
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
        isEditing &&
        css`
          background-color: ${colorTokens.background.white};
          box-shadow: 0 0 0 1px ${colorTokens.stroke.brand};

          &:hover {
            box-shadow: 0 0 0 1px ${colorTokens.stroke.brand};
          }
        `
      }
    `,
  optionHeader: css`
    display: flex;
    justify-content: space-between;
    align-items: center;
  `,
  optionTitle: css`
    ${typography.caption('medium')};
    color: ${colorTokens.text.subdued};
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
  optionPlaceholder: ({
    isTitle,
    isCorrectAnswer,
  }: {
    isTitle?: boolean;
    isCorrectAnswer?: number;
  }) =>
    css`
    ${typography.body()};
    color: ${colorTokens.text.subdued};
    padding-block: ${spacing[4]};
    display: flex;
    align-items: center;
    gap: ${spacing[4]};

    ${
      isTitle &&
      css`
        color: ${colorTokens.text.hints};
      `
    }

    ${
      isCorrectAnswer &&
      css`
        color: ${colorTokens.text.success};

        span {
          color: ${colorTokens.stroke.border};
        }
      `
    }
  `,
  optionInputWrapper: css`
    display: flex;
    flex-direction: column;
    width: 100%;
    gap: ${spacing[16]};
  `,
  inputWithHints: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[8]};
  `,
  inputHints: css`
    display: flex;
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
    display: flex;
    justify-content: flex-end;
    gap: ${spacing[8]};
  `,
};
