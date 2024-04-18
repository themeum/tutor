import { Fragment, useEffect, useRef, useState } from 'react';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';

import { borderRadius, colorTokens, shadow, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { styleUtils } from '@Utils/style-utils';
import SVGIcon from '@Atoms/SVGIcon';
import Show from '@Controls/Show';
import Button from '@Atoms/Button';
import For from '@Controls/For';
import type { FormControllerProps } from '@Utils/form';
import type { QuizQuestionOption } from '@CourseBuilderServices/quiz';
import { isDefined } from '@Utils/types';

interface FormFillinTheBlanksProps extends FormControllerProps<QuizQuestionOption | null> {}

const FormFillinTheBlanks = ({ field }: FormFillinTheBlanksProps) => {
  const inputValue = field.value ?? {
    ID: 0,
    title: '',
  };
  const inputRef = useRef<HTMLInputElement>(null);

  const [isEditing, setIsEditing] = useState(false);
  const [previousValue] = useState<QuizQuestionOption>(inputValue);

  useEffect(() => {
    if (isDefined(inputRef.current) && isEditing) {
      inputRef.current.focus();
    }
  }, [isEditing]);

  return (
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
                <div css={styles.optionPlaceholder({ isTitle: !!inputValue.title })}>
                  {inputValue.title ? inputValue.title.replace(/{dash}/g, '_____') : __('Question title...', 'tutor')}
                </div>
                <div
                  css={styles.optionPlaceholder({ isCorrectAnswer: inputValue.fillinTheBlanksCorrectAnswer?.length })}
                >
                  {inputValue.fillinTheBlanksCorrectAnswer && inputValue.fillinTheBlanksCorrectAnswer.length > 0 ? (
                    <For each={inputValue.fillinTheBlanksCorrectAnswer}>
                      {(answer, index) => (
                        <Fragment key={index}>
                          {answer}
                          <Show
                            when={
                              index <
                              (inputValue.fillinTheBlanksCorrectAnswer
                                ? inputValue.fillinTheBlanksCorrectAnswer.length - 1
                                : 0)
                            }
                          >
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
                <input
                  {...field}
                  ref={inputRef}
                  type="text"
                  css={styles.optionInput}
                  placeholder={__('Question title...', 'tutor')}
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
                    {__(
                      'Please make sure to use the {dash} variable in your question title to show the blanks in your question. You can use multiple {dash} variables in one question.',
                      'tutor'
                    )}
                  </p>
                </div>
              </div>
              <div css={styles.inputWithHints}>
                <input
                  {...field}
                  type="text"
                  css={styles.optionInput}
                  placeholder={__('Correct Answer(s)...')}
                  value={inputValue.fillinTheBlanksCorrectAnswer?.join(' | ')}
                  onChange={(event) => {
                    field.onChange({
                      ...inputValue,
                      fillinTheBlanksCorrectAnswer: event.target.value.split(' | '),
                    });
                  }}
                />
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

export default FormFillinTheBlanks;

const styles = {
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
