import { css } from '@emotion/react';
import { styleUtils } from '@Utils/style-utils';
import type { FormWithGlobalErrorType } from '@Hooks/useFormWithGlobalError';
import type { QuizForm } from '@CourseBuilderComponents/modals/QuizModal';
import { Controller } from 'react-hook-form';
import FormFillinTheBlanks from '@Components/fields/quiz/FormFillinTheBlanks';

interface FillinTheBlanksProps {
  form: FormWithGlobalErrorType<QuizForm>;
  activeQuestionIndex: number;
}

const FillinTheBlanks = ({ form, activeQuestionIndex }: FillinTheBlanksProps) => {
  return (
    <div css={styles.optionWrapper}>
      <Controller
        control={form.control}
        name={`questions.${activeQuestionIndex}.options.0`}
        render={({ field, fieldState }) => <FormFillinTheBlanks field={field} fieldState={fieldState} />}
      />
    </div>
  );
};

export default FillinTheBlanks;

const styles = {
  optionWrapper: css`
      ${styleUtils.display.flex('column')};
      padding-left: 42px;
    `,
};
