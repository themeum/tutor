import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import SVGIcon from '@Atoms/SVGIcon';

import { typography } from '@Config/typography';
import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { styleUtils } from '@Utils/style-utils';
import type { QuizForm } from '@CourseBuilderComponents/modals/QuizModal';
import { Controller, useFieldArray, useFormContext, useWatch } from 'react-hook-form';

interface TrueFalseProps {
  activeQuestionIndex: number;
}

const TrueFalse = ({ activeQuestionIndex }: TrueFalseProps) => {
  const form = useFormContext<QuizForm>();
  const markAsCorrect = useWatch({
    control: form.control,
    name: `questions.${activeQuestionIndex}.markAsCorrect`,
  });

  const { fields: options } = useFieldArray({
    control: form.control,
    name: `questions.${activeQuestionIndex}.options`,
  });

  return (
    <div css={styles.optionWrapper}>
      {options.map((option, index) => (
        <Controller
          control={form.control}
          name={`questions.${activeQuestionIndex}.options.${index}`}
          render={({ field }) => (
            <div css={styles.option({ isSelected: markAsCorrect === field.value.ID })}>
              <SVGIcon
                data-check-icon
                name={markAsCorrect === field.value.ID ? 'checkFilled' : 'check'}
                height={32}
                width={32}
              />
              <div
                css={styles.optionLabel({ isSelected: markAsCorrect === field.value.ID })}
                onClick={() => {
                  form.setValue(`questions.${activeQuestionIndex}.markAsCorrect`, field.value.ID);
                }}
                onKeyDown={(event) => {
                  if (event.key === 'Enter') {
                    form.setValue(`questions.${activeQuestionIndex}.markAsCorrect`, field.value.ID);
                  }
                }}
              >
                {__(option.title, 'tutor')}
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
  `,
};
