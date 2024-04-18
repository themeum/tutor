import { css } from '@emotion/react';

import { colorTokens, spacing } from '@Config/styles';
import { styleUtils } from '@Utils/style-utils';
import type { FormWithGlobalErrorType } from '@Hooks/useFormWithGlobalError';
import type { QuizForm } from '@CourseBuilderComponents/modals/QuizModal';
import { Controller, useFieldArray } from 'react-hook-form';
import For from '@Controls/For';
import FormMatching from '@Components/fields/quiz/FormMatching';
import SVGIcon from '@Atoms/SVGIcon';
import { nanoid } from '@Utils/util';
import { __ } from '@wordpress/i18n';

interface MatchingProps {
  form: FormWithGlobalErrorType<QuizForm>;
  activeQuestionIndex: number;
}

const Matching = ({ form, activeQuestionIndex }: MatchingProps) => {
  const {
    fields: optionsFields,
    append: appendOption,
    remove: removeOption,
  } = useFieldArray({
    control: form.control,
    name: `questions.${activeQuestionIndex}.options`,
  });

  return (
    <div css={styles.optionWrapper}>
      <For each={optionsFields}>
        {(option, index) => (
          <Controller
            key={option.id}
            control={form.control}
            name={`questions.${activeQuestionIndex}.options.${index}`}
            render={({ field, fieldState }) => (
              <FormMatching
                field={field}
                fieldState={fieldState}
                onRemoveOption={() => removeOption(index)}
                index={index}
                imageMatching={form.watch(`questions.${activeQuestionIndex}.image_matching`)}
              />
            )}
          />
        )}
      </For>

      <button
        type="button"
        onClick={() => appendOption({ ID: Number.parseInt(nanoid()), title: '' })}
        css={styles.addOptionButton}
      >
        <SVGIcon name="plus" height={24} width={24} />
        {__('Add Option', 'tutor')}
      </button>
    </div>
  );
};

export default Matching;

const styles = {
  optionWrapper: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[12]};
  `,
  addOptionButton: css`
    ${styleUtils.resetButton}
    ${styleUtils.display.flex()}
    align-items: center;
    gap: ${spacing[8]};
    color: ${colorTokens.text.brand};
    margin-left: ${spacing[48]};
    margin-top: ${spacing[28]};

    svg {
      color: ${colorTokens.icon.brand};
    }
  `,
};
