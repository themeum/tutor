import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { Fragment, useEffect, useRef, useState } from 'react';

import Button from '@TutorShared/atoms/Button';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import Tooltip from '@TutorShared/atoms/Tooltip';

import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import For from '@TutorShared/controls/For';
import Show from '@TutorShared/controls/Show';
import type { FormControllerProps } from '@TutorShared/utils/form';
import { calculateQuizDataStatus } from '@TutorShared/utils/quiz';
import { styleUtils } from '@TutorShared/utils/style-utils';
import {
  type ID,
  isDefined,
  QuizDataStatus,
  type QuizQuestionOption,
  type QuizValidationErrorType,
} from '@TutorShared/utils/types';
import { nanoid } from '@TutorShared/utils/util';

interface FormFillInTheBlanksProps extends FormControllerProps<QuizQuestionOption> {
  questionId: ID;
  validationError?: {
    message: string;
    type: QuizValidationErrorType;
  } | null;
  setValidationError?: React.Dispatch<
    React.SetStateAction<{
      message: string;
      type: QuizValidationErrorType;
    } | null>
  >;
}

const FormFillInTheBlanks = ({ field, questionId, validationError, setValidationError }: FormFillInTheBlanksProps) => {
  const inputValue = field.value ?? {
    _data_status: QuizDataStatus.NEW,
    is_saved: false,
    answer_id: nanoid(),
    answer_title: '',
    belongs_question_id: questionId,
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
    <div css={styles.option}>
      <div css={styles.optionLabel({ isEditing })}>
        <div css={styles.optionHeader}>
          <div css={styles.optionTitle}>{__('Fill in the blanks', __TUTOR_TEXT_DOMAIN__)}</div>

          <Show when={inputValue.is_saved && !isEditing}>
            <div css={styles.optionActions}>
              <Tooltip content={__('Edit', __TUTOR_TEXT_DOMAIN__)}>
                <button
                  type="button"
                  css={styleUtils.actionButton}
                  data-edit-button
                  onClick={(event) => {
                    event.stopPropagation();
                    setIsEditing(true);
                  }}
                  data-visually-hidden
                >
                  <SVGIcon name="edit" width={24} height={24} />
                </button>
              </Tooltip>
            </div>
          </Show>
        </div>
        <div css={styles.optionBody}>
          <Show
            when={!inputValue.is_saved || isEditing}
            fallback={
              <div css={styles.placeholderWrapper}>
                <div css={styles.optionPlaceholder({ isTitle: !!inputValue.answer_title })}>
                  {inputValue.answer_title
                    ? inputValue.answer_title.replace(/{dash}/g, '_____')
                    : __('Question title...', __TUTOR_TEXT_DOMAIN__)}
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
                    __('Correct Answer(s)...', __TUTOR_TEXT_DOMAIN__)
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
                  placeholder={__('Question title...', __TUTOR_TEXT_DOMAIN__)}
                  value={inputValue.answer_title}
                  onClick={(event) => {
                    event.stopPropagation();
                  }}
                  onChange={(event) => {
                    field.onChange({
                      ...inputValue,
                      ...(calculateQuizDataStatus(inputValue._data_status, QuizDataStatus.UPDATE) && {
                        _data_status: calculateQuizDataStatus(
                          inputValue._data_status,
                          QuizDataStatus.UPDATE,
                        ) as QuizDataStatus,
                      }),
                      answer_title: event.target.value,
                    });
                  }}
                  onKeyDown={async (event) => {
                    event.stopPropagation();
                    if ((event.metaKey || event.ctrlKey) && event.key === 'Enter' && inputValue.answer_title) {
                      field.onChange({
                        ...inputValue,
                        ...(calculateQuizDataStatus(inputValue._data_status, QuizDataStatus.UPDATE) && {
                          _data_status: calculateQuizDataStatus(
                            inputValue._data_status,
                            QuizDataStatus.UPDATE,
                          ) as QuizDataStatus,
                        }),
                      });
                      setIsEditing(false);
                    }
                  }}
                />

                <div css={styles.inputHints}>
                  <SVGIcon name="info" height={20} width={20} />
                  <p>
                    {
                      // prettier-ignore
                      __('Please make sure to use the variable {dash} in your question title to show the blanks in your question. You can use multiple {dash} variables in one question.', __TUTOR_TEXT_DOMAIN__)
                    }
                  </p>
                </div>
              </div>
              <div css={styles.inputWithHints}>
                <input
                  {...field}
                  type="text"
                  placeholder={__('Correct Answer(s)...', __TUTOR_TEXT_DOMAIN__)}
                  value={fillInTheBlanksCorrectAnswer?.join('|')}
                  onClick={(event) => {
                    event.stopPropagation();
                  }}
                  onChange={(event) => {
                    field.onChange({
                      ...inputValue,
                      ...(calculateQuizDataStatus(inputValue._data_status, QuizDataStatus.UPDATE) && {
                        _data_status: calculateQuizDataStatus(
                          inputValue._data_status,
                          QuizDataStatus.UPDATE,
                        ) as QuizDataStatus,
                      }),
                      answer_two_gap_match: event.target.value,
                    });
                  }}
                  onKeyDown={async (event) => {
                    if ((event.metaKey || event.ctrlKey) && event.key === 'Enter' && inputValue.answer_two_gap_match) {
                      field.onChange({
                        ...inputValue,
                        ...(calculateQuizDataStatus(inputValue._data_status, QuizDataStatus.UPDATE) && {
                          _data_status: calculateQuizDataStatus(
                            inputValue._data_status,
                            QuizDataStatus.UPDATE,
                          ) as QuizDataStatus,
                          is_saved: true,
                        }),
                      });

                      if (validationError?.type === 'save_option') {
                        setValidationError?.(null);
                      }

                      setIsEditing(false);
                    }
                  }}
                />
                <Show when={hasError}>
                  <div css={styles.errorMessage}>
                    <SVGIcon name="info" height={20} width={20} />
                    <p>
                      {__(
                        'Match the number of answers to the number of blanks {dash} in your question.',
                        __TUTOR_TEXT_DOMAIN__,
                      )}
                    </p>
                  </div>
                </Show>
                <div css={styles.inputHints}>
                  <SVGIcon name="info" height={20} width={20} />
                  <p>
                    {
                      // prettier-ignore
                      __( 'Separate multiple answers by a vertical bar |. 1 answer per {dash} variable is defined in the question. Example: Apple | Banana | Orange', __TUTOR_TEXT_DOMAIN__)
                    }
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
                    {__('Cancel', __TUTOR_TEXT_DOMAIN__)}
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
                      ...(calculateQuizDataStatus(inputValue._data_status, QuizDataStatus.UPDATE) && {
                        _data_status: calculateQuizDataStatus(
                          inputValue._data_status,
                          QuizDataStatus.UPDATE,
                        ) as QuizDataStatus,
                        is_saved: true,
                      }),
                    });

                    setPreviousValue({
                      ...inputValue,
                      ...(calculateQuizDataStatus(inputValue._data_status, QuizDataStatus.UPDATE) && {
                        _data_status: calculateQuizDataStatus(
                          inputValue._data_status,
                          QuizDataStatus.UPDATE,
                        ) as QuizDataStatus,
                        is_saved: true,
                      }),
                    });

                    if (validationError?.type === 'save_option') {
                      setValidationError?.(null);
                    }

                    setIsEditing(false);
                  }}
                  disabled={!inputValue.answer_title || !inputValue.answer_two_gap_match || hasError}
                >
                  {__('Ok', __TUTOR_TEXT_DOMAIN__)}
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
  option: css`
    ${styleUtils.display.flex()};
    ${typography.caption('medium')};
    align-items: center;
    color: ${colorTokens.text.subdued};
    gap: ${spacing[10]};
    align-items: center;
  `,
  optionLabel: ({ isEditing }: { isEditing: boolean }) => css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[12]};
    width: 100%;
    border-radius: ${borderRadius.card};
    padding: ${spacing[12]} ${spacing[16]};
    transition: box-shadow 0.15s ease-in-out;
    background-color: ${colorTokens.background.white};

    [data-visually-hidden] {
      opacity: 0;
    }

    &:hover {
      box-shadow: 0 0 0 1px ${colorTokens.stroke.hover};
      [data-visually-hidden] {
        opacity: 1;
      }
    }

    ${isEditing &&
    css`
      background-color: ${colorTokens.background.white};
      box-shadow: 0 0 0 1px ${colorTokens.stroke.brand};

      &:hover {
        box-shadow: 0 0 0 1px ${colorTokens.stroke.brand};
      }
    `}
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
  optionInputWrapper: css`
    ${styleUtils.optionInputWrapper};
    gap: ${spacing[16]};
  `,
  placeholderWrapper: css`
    display: flex;
    flex-direction: column;
  `,
  optionPlaceholder: ({ isTitle, isCorrectAnswer }: { isTitle?: boolean; isCorrectAnswer?: number }) => css`
    ${typography.body()};
    color: ${colorTokens.text.subdued};
    padding-block: ${spacing[4]};
    display: flex;
    align-items: center;
    gap: ${spacing[4]};

    ${isTitle &&
    css`
      color: ${colorTokens.text.hints};
    `}

    ${isCorrectAnswer &&
    css`
      color: ${colorTokens.text.success};

      span {
        color: ${colorTokens.stroke.border};
      }
    `}
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
  optionInputButtons: css`
    display: flex;
    justify-content: flex-end;
    gap: ${spacing[8]};
  `,
};
