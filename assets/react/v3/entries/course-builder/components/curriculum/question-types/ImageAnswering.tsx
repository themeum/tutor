import { css } from '@emotion/react';

import { styleUtils } from '@Utils/style-utils';
import type { QuizForm } from '@CourseBuilderComponents/modals/QuizModal';
import For from '@Controls/For';
import FormImageAnswering from '@Components/fields/quiz/FormImageAnswering';
import { Controller, useFieldArray } from 'react-hook-form';
import type { FormWithGlobalErrorType } from '@Hooks/useFormWithGlobalError';
import { colorTokens, spacing } from '@Config/styles';
import { nanoid } from '@Utils/util';
import SVGIcon from '@Atoms/SVGIcon';
import { __ } from '@wordpress/i18n';

interface ImageAnsweringProps {
  form: FormWithGlobalErrorType<QuizForm>;
  activeQuestionIndex: number;
}

const ImageAnswering = ({ form, activeQuestionIndex }: ImageAnsweringProps) => {
  const {
    fields: optionsFields,
    append: appendOption,
    remove: removeOption,
    insert: insertOption,
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
              <FormImageAnswering
                field={field}
                fieldState={fieldState}
                onRemoveOption={() => removeOption(index)}
                index={index}
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

export default ImageAnswering;

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
