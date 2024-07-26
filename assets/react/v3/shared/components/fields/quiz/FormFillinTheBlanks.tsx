import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { Fragment, useEffect, useRef, useState } from 'react';
import { useFormContext } from 'react-hook-form';

import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';

import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import For from '@Controls/For';
import Show from '@Controls/Show';
import { useQuizModalContext } from '@CourseBuilderContexts/QuizModalContext';
import { type QuizForm, type QuizQuestionOption, useCreateQuizAnswerMutation } from '@CourseBuilderServices/quiz';
import type { FormControllerProps } from '@Utils/form';
import { styleUtils } from '@Utils/style-utils';
import { isDefined } from '@Utils/types';

interface FormFillInTheBlanksProps extends FormControllerProps<QuizQuestionOption | null> {}

const FormFillInTheBlanks = ({ field }: FormFillInTheBlanksProps) => {
  const { activeQuestionId, activeQuestionIndex, quizId } = useQuizModalContext();
  const form = useFormContext<QuizForm>();
  const inputValue = field.value ?? {
    answer_id: '',
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

  const createQuizAnswerMutation = useCreateQuizAnswerMutation(quizId);

  const [isEditing, setIsEditing] = useState(!inputValue.answer_title || !inputValue.answer_two_gap_match);
  const [previousValue] = useState<QuizQuestionOption>(inputValue);

  const createQuizAnswer = async () => {
    const response = await createQuizAnswerMutation.mutateAsync({
      ...(inputValue.answer_id && { answer_id: inputValue.answer_id }),
      question_id: inputValue.belongs_question_id,
      answer_title: inputValue.answer_title,
      image_id: inputValue.image_id || '',
      answer_view_format: 'text_image',
      ...(!inputValue.answer_id && { question_type: 'fill_in_the_blank' }),
    });

    const currentAnswerIndex = form
      .getValues(`questions.${activeQuestionIndex}.question_answers`)
      .findIndex((answer) => answer.answer_id === inputValue.answer_id);

    if (response.status_code === 201 || response.status_code === 200) {
      form.setValue(`questions.${activeQuestionIndex}.question_answers.${currentAnswerIndex}`, {
        ...inputValue,
        answer_id: response.data,
      });
      setIsEditing(false);
    }
  };

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
                      answer_title: event.target.value,
                    });
                  }}
                  onKeyDown={async (event) => {
                    event.stopPropagation();
                    if ((event.metaKey || event.ctrlKey) && event.key === 'Enter' && inputValue.answer_title) {
                      await createQuizAnswer();
                      setIsEditing(false);
                    }
                  }}
                />

                <div css={styles.inputHints}>
                  <SVGIcon name="info" height={20} width={20} />
                  <p>
                    {__(
                      'Please make sure to use the {dash} variable in your question title to show the blanks in your question. You can use multiple {dash} variables in one question.',
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
                      answer_two_gap_match: event.target.value,
                    });
                  }}
                  onKeyDown={async (event) => {
                    if ((event.metaKey || event.ctrlKey) && event.key === 'Enter' && inputValue.answer_two_gap_match) {
                      await createQuizAnswer();
                      setIsEditing(false);
                    }
                  }}
                />
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
                <Show when={inputValue.answer_id}>
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
                    await createQuizAnswer();
                  }}
                  disabled={!inputValue.answer_title || !inputValue.answer_two_gap_match}
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
  optionInput: css`
    ${styleUtils.resetButton};
    ${typography.caption()};
    flex: 1;
    color: ${colorTokens.text.subdued};
    padding: ${spacing[4]} ${spacing[10]};
    border: 1px solid ${colorTokens.stroke.default};
    border-radius: ${borderRadius[6]};
    resize: vertical;

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
