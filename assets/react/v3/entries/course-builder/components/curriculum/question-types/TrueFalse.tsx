import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import SVGIcon from '@Atoms/SVGIcon';

import { typography } from '@Config/typography';
import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { styleUtils } from '@Utils/style-utils';
import type { QuizForm } from '@CourseBuilderComponents/modals/QuizModal';
import { Controller, useFieldArray, useFormContext, useWatch } from 'react-hook-form';
import { useEffect } from 'react';

interface TrueFalseProps {
  activeQuestionIndex: number;
}

const TrueFalse = ({ activeQuestionIndex }: TrueFalseProps) => {
  const form = useFormContext<QuizForm>();

  const { fields: optionsFields } = useFieldArray({
    control: form.control,
    name: `questions.${activeQuestionIndex}.options`,
  });

  const currentOptions = useWatch({
    control: form.control,
    name: `questions.${activeQuestionIndex}.options`,
    defaultValue: [],
  });

  // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
  useEffect(() => {
    form.setValue(`questions.${activeQuestionIndex}.options`, [
      {
        ID: 'true',
        title: __('True', 'tutor'),
        isCorrect: false,
      },
      {
        ID: 'false',
        title: __('False', 'tutor'),
        isCorrect: false,
      },
    ]);
  }, []);

  // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
  useEffect(() => {
    const changedOptions = currentOptions.filter((option) => {
      const index = optionsFields.findIndex((item) => item.ID === option.ID);
      const previousOption = optionsFields[index];
      return option.isCorrect !== previousOption.isCorrect;
    });

    if (changedOptions.length === 0) {
      return;
    }

    const changedOptionIndex = optionsFields.findIndex((item) => item.ID === changedOptions[0].ID);

    const updatedOptions = [...optionsFields];
    updatedOptions[changedOptionIndex] = Object.assign({}, updatedOptions[changedOptionIndex], { isCorrect: true });
    updatedOptions.forEach((_, index) => {
      if (index !== changedOptionIndex) {
        updatedOptions[index] = Object.assign({}, updatedOptions[index], { isCorrect: false });
      }
    });

    form.setValue(`questions.${activeQuestionIndex}.options`, updatedOptions);
  }, [currentOptions]);

  return (
    <div css={styles.optionWrapper}>
      {optionsFields.map((option, index) => (
        <Controller
          key={option.id}
          control={form.control}
          name={`questions.${activeQuestionIndex}.options.${index}` as 'questions.0.options.0'}
          render={({ field }) => (
            <div css={styles.option({ isSelected: !!field.value.isCorrect })}>
              <button
                type="button"
                css={styleUtils.resetButton}
                onClick={() => {
                  field.onChange({
                    ...field.value,
                    isCorrect: !field.value.isCorrect,
                  });
                }}
              >
                <SVGIcon
                  data-check-icon
                  name={field.value.isCorrect ? 'checkFilled' : 'check'}
                  height={32}
                  width={32}
                />
              </button>
              <div
                css={styles.optionLabel({ isSelected: !!field.value.isCorrect })}
                onClick={() => {
                  field.onChange({
                    ...field.value,
                    isCorrect: !field.value.isCorrect,
                  });
                }}
                onKeyDown={(event) => {
                  if (event.key === 'Enter') {
                    field.onChange({
                      ...field.value,
                      isCorrect: !field.value.isCorrect,
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
          fill: ${colorTokens.bg.success};
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
