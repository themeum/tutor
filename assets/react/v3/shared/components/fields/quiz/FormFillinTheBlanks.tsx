import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { Fragment, useEffect, useRef, useState } from 'react';

import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import Tooltip from '@Atoms/Tooltip';

import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import For from '@Controls/For';
import Show from '@Controls/Show';
import { useQuizModalContext } from '@CourseBuilderContexts/QuizModalContext';
import { type QuizDataStatus, type QuizQuestionOption, calculateQuizDataStatus } from '@CourseBuilderServices/quiz';
import type { FormControllerProps } from '@Utils/form';
import { styleUtils } from '@Utils/style-utils';
import { isDefined } from '@Utils/types';
import { nanoid } from '@Utils/util';

interface FormFillInTheBlanksProps extends FormControllerProps<QuizQuestionOption | null> {}

const FormFillInTheBlanks = ({ field }: FormFillInTheBlanksProps) => {
  const { activeQuestionId, validationError, setValidationError } = useQuizModalContext();
  const inputValue = field.value ?? {
    _data_status: 'new',
    is_saved: false,
    answer_id: nanoid(),
    answer_title: '',
    belongs_question_id: activeQuestionId,
    belongs_question_type: 'fill_in_the_blank',
    answer_two_gap_match: '',
    answer_view_format: 'text',
    answer_order: 0,
    is_correct: '0',
  };
  const inputRef = useRef<HTMLInputElement>(null);
  const fillInTheBlanksCorrectAnswer = inputValue.answer_two_gap_match?.split('|');

  const [isEditing, setIsEditing] = useState(!inputValue.answer_title || !inputValue.answer_two_gap_match);
  const [previousValue, setPreviousValue] = useState<QuizQuestionOption>(inputValue);

  const totalDashesInTitle = inputValue.answer_title?.match(/{dash}/g)?.length || 0;
  const totalAnswers = inputValue.answer_two_gap_match?.split('|').length || 0;

  const hasError = !!(
    inputValue.answer_title &&
    inputValue.answer_two_gap_match &&
    totalDashesInTitle !== totalAnswers
  );

  useEffect(() => {
    if (isDefined(inputRef.current) && isEditing) {
      inputRef.current.focus();
    }
  }, [isEditing]);

  return (
    <div css={styles.option({ isEditing })}>
      <div css={styles.optionLabel({ isEditing })}>
        <div css={styles.optionHeader}>
          <div css={styles.optionTitle}>{__('Fill in the blanks', 'tutor')}</div>

          <Show when={inputValue.is_saved}>
            <div css={styles.optionActions}>
              <Tooltip content={__('Edit', 'tutor')}>
                <button
                  type="button"
                  css={styleUtils.actionButton}
                  data-edit-button
                  onClick={(event) => {
                    event.stopPropagation();
                    setIsEditing(true);
                  }}
                >
                  <SVGIcon name="edit" width={24} height={24} />
                </button>
              </Tooltip>
            </div>
          </Show>
        </div>
        <div css={styles.optionBody}>
          <Show
            when={isEditing}
            fallback={
              <div css={styles.placeholderWrapper}>
                <div css={styles.optionPlaceholder({ isTitle: !!inputValue.answer_title })}>
                  {inputValue.answer_title
                    ? inputValue.answer_title.replace(/{dash}/g, '_____')
                    : __('Question title...', 'tutor')}
                </div>
                <div css={styles.optionPlaceholder({ isCorrectAnswer: fillInTheBlanksCorrectAnswer?.length })}>
                  {fillInTheBlanksCorrectAnswer && fillInTheBlanksCorrectAnswer.length > 0 ? (
                    <For each={fillInTheBlanksCorrectAnswer}>
                      {(answer, index) => (
                        <Fragment key={index}>
                          {answer}
                          <Show
                            when={index < (fillInTheBlanksCorrectAnswer ? fillInTheBlanksCorrectAnswer.length - 1 : 0)}
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
                  value={inputValue.answer_title}
                  onClick={(event) => {
                    event.stopPropagation();
                  }}
                  onChange={(event) => {
                    field.onChange({
                      ...inputValue,
                      ...(calculateQuizDataStatus(inputValue._data_status, 'update') && {
                        _data_status: calculateQuizDataStatus(inputValue._data_status, 'update') as QuizDataStatus,
                      }),
                      answer_title: event.target.value,
                    });
                  }}
                  onKeyDown={async (event) => {
                    event.stopPropagation();
                    if ((event.metaKey || event.ctrlKey) && event.key === 'Enter' && inputValue.answer_title) {
                      field.onChange({
                        ...inputValue,
                        ...(calculateQuizDataStatus(inputValue._data_status, 'update') && {
                          _data_status: calculateQuizDataStatus(inputValue._data_status, 'update') as QuizDataStatus,
                        }),
                      });
                      setIsEditing(false);
                    }
                  }}
                />

                <div css={styles.inputHints}>
                  <SVGIcon name="info" height={20} width={20} />
                  <p>
                    {__(
                      'Please make sure to use the variable {dash} in your question title to show the blanks in your question. You can use multiple {dash} variables in one question.',
                      'tutor',
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
                  value={fillInTheBlanksCorrectAnswer?.join('|')}
                  onClick={(event) => {
                    event.stopPropagation();
                  }}
                  onChange={(event) => {
                    field.onChange({
                      ...inputValue,
                      ...(calculateQuizDataStatus(inputValue._data_status, 'update') && {
                        _data_status: calculateQuizDataStatus(inputValue._data_status, 'update') as QuizDataStatus,
                      }),
                      answer_two_gap_match: event.target.value,
                    });
                  }}
                  onKeyDown={async (event) => {
                    if ((event.metaKey || event.ctrlKey) && event.key === 'Enter' && inputValue.answer_two_gap_match) {
                      field.onChange({
                        ...inputValue,
                        ...(calculateQuizDataStatus(inputValue._data_status, 'update') && {
                          _data_status: calculateQuizDataStatus(inputValue._data_status, 'update') as QuizDataStatus,
                          is_saved: true,
                        }),
                      });

                      if (validationError?.type === 'save_option') {
                        setValidationError(null);
                      }

                      setIsEditing(false);
                    }
                  }}
                />
                <Show when={hasError}>
                  <div css={styles.errorMessage}>
                    <SVGIcon name="info" height={20} width={20} />
                    <p>{__('Match the number of answers to the number of blanks {dash} in your question.', 'tutor')}</p>
                  </div>
                </Show>
                <div css={styles.inputHints}>
                  <SVGIcon name="info" height={20} width={20} />
                  <p>
                    {__(
                      'Separate multiple answers by a vertical bar |. 1 answer per {dash} variable is defined in the question. Example: Apple | Banana | Orange',
                      'tutor',
                    )}
                  </p>
                </div>
              </div>
              <div css={styles.optionInputButtons}>
                <Show when={inputValue.is_saved}>
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
                </Show>
                <Button
                  variant="secondary"
                  size="small"
                  onClick={async (event) => {
                    event.stopPropagation();

                    if (hasError) {
                      return;
                    }
                    field.onChange({
                      ...inputValue,
                      ...(calculateQuizDataStatus(inputValue._data_status, 'update') && {
                        _data_status: calculateQuizDataStatus(inputValue._data_status, 'update') as QuizDataStatus,
                        is_saved: true,
                      }),
                    });

                    setPreviousValue({
                      ...inputValue,
                      ...(calculateQuizDataStatus(inputValue._data_status, 'update') && {
                        _data_status: calculateQuizDataStatus(inputValue._data_status, 'update') as QuizDataStatus,
                        is_saved: true,
                      }),
                    });

                    if (validationError?.type === 'save_option') {
                      setValidationError(null);
                    }

                    setIsEditing(false);
                  }}
                  disabled={!inputValue.answer_title || !inputValue.answer_two_gap_match || hasError}
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

export default FormFillInTheBlanks;

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
  errorMessage: css`
    display: flex;
    gap: ${spacing[4]};
    ${typography.small()};
    color: ${colorTokens.text.error};
    align-items: flex-start;
    color: ${colorTokens.text.error};

    svg {
      flex-shrink: 0;
      color: ${colorTokens.icon.error};
    }
  `,
  optionInput: css`
    ${styleUtils.resetButton};
    ${typography.caption()};
    flex: 1;
    color: ${colorTokens.text.subdued};
    padding: ${spacing[4]} ${spacing[10]};
    border: 1px solid ${colorTokens.stroke.default};
    border-radius: ${borderRadius[6]};
    resize: vertical;
    cursor: text;

    &:focus {
      ${styleUtils.inputFocus};
    }
  `,
  optionInputButtons: css`
    display: flex;
    justify-content: flex-end;
    gap: ${spacing[8]};
  `,
};
