import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import SVGIcon from '@Atoms/SVGIcon';

import { typography } from '@Config/typography';
import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { styleUtils } from '@Utils/style-utils';
import type { QuizForm } from '@CourseBuilderComponents/modals/QuizModal';
import { Controller, useFieldArray, useFormContext, useWatch } from 'react-hook-form';
import { useEffect } from 'react';
import { nanoid } from '@Utils/util';
import { useQuizModalContext } from '@CourseBuilderContexts/QuizModalContext';

const TrueFalse = () => {
  const form = useFormContext<QuizForm>();
  const { activeQuestionId, activeQuestionIndex } = useQuizModalContext();

  const { fields: optionsFields } = useFieldArray({
    control: form.control,
    name: `questions.${activeQuestionIndex}.question_answers` as 'questions.0.question_answers',
  });

  const currentOptions = useWatch({
    control: form.control,
    name: `questions.${activeQuestionIndex}.question_answers` as 'questions.0.question_answers',
    defaultValue: [],
  });

  // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
  useEffect(() => {
    if (optionsFields.length === 2) {
      return;
    }

    form.setValue(`questions.${activeQuestionIndex}.question_answers`, [
      {
        answer_id: nanoid(),
        answer_title: __('True', 'tutor'),
        is_correct: false,
        belongs_question_id: activeQuestionId,
        belongs_question_type: 'true_false',
      },
      {
        answer_id: 'false',
        answer_title: __('False', 'tutor'),
        is_correct: false,
        belongs_question_id: activeQuestionId,
        belongs_question_type: 'true_false',
      },
    ]);
  }, []);

  // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
  useEffect(() => {
    const changedOptions = currentOptions.filter((option) => {
      const index = optionsFields.findIndex((item) => item.answer_id === option.answer_id);
      const previousOption = optionsFields[index];
      return option.is_correct !== previousOption.is_correct;
    });

    if (changedOptions.length === 0) {
      return;
    }

    const changedOptionIndex = optionsFields.findIndex((item) => item.answer_id === changedOptions[0].answer_id);

    const updatedOptions = [...optionsFields];
    updatedOptions[changedOptionIndex] = Object.assign({}, updatedOptions[changedOptionIndex], { isCorrect: true });

    for (const [index, option] of updatedOptions.entries()) {
      if (index !== changedOptionIndex) {
        updatedOptions[index] = { ...option, is_correct: false };
      }
    }

    form.setValue(`questions.${activeQuestionIndex}.question_answers`, updatedOptions);
  }, [currentOptions]);

  return (
    <div css={styles.optionWrapper}>
      {optionsFields.map((option, index) => (
        <Controller
          key={option.id}
          control={form.control}
          name={`questions.${activeQuestionIndex}.question_answers.${index}` as 'questions.0.question_answers.0'}
          render={({ field }) => (
            <div css={styles.option({ isSelected: !!Number(field.value.is_correct) })}>
              <button
                type="button"
                css={styleUtils.resetButton}
                onClick={() => {
                  field.onChange({
                    ...field.value,
                    isCorrect: Number(field.value.is_correct),
                  });
                }}
              >
                <SVGIcon
                  data-check-icon
                  name={field.value.is_correct ? 'checkFilled' : 'check'}
                  height={32}
                  width={32}
                />
              </button>
              <div
                css={styles.optionLabel({ isSelected: !!Number(field.value.is_correct) })}
                onClick={() => {
                  field.onChange({
                    ...field.value,
                    isCorrect: !field.value.is_correct,
                  });
                }}
                onKeyDown={(event) => {
                  if (event.key === 'Enter') {
                    field.onChange({
                      ...field.value,
                      isCorrect: !field.value.is_correct,
                    });
                  }
                }}
              >
                {index === 0 ? __('True', 'tutor') : __('False', 'tutor')}
              </div>
            </div>
          )}
        />
      ))}
    </div>
  );
};

export default TrueFalse;

const styles = {
  optionWrapper: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[12]};
  `,
  option: ({
    isSelected,
  }: {
    isSelected: boolean;
  }) => css`
    ${styleUtils.display.flex()};
    ${typography.caption('medium')};
    align-items: center;
    color: ${colorTokens.text.subdued};
    gap: ${spacing[10]};
    height: 48px;
    align-items: center;

    [data-check-icon] {
      opacity: 0;
      transition: opacity 0.15s ease-in-out;
      fill: none;
      flex-shrink: 0;
    }

    &:hover {
      [data-check-icon] {
        opacity: 1;
      }
    }


    ${
      isSelected &&
      css`
        [data-check-icon] {
          opacity: 1;
          color: ${colorTokens.bg.success};
        }
      `
    }
  `,
  optionLabel: ({
    isSelected,
  }: {
    isSelected: boolean;
  }) => css`
    width: 100%;
    border-radius: ${borderRadius.card};
    padding: ${spacing[12]} ${spacing[16]};
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
  `,
};
