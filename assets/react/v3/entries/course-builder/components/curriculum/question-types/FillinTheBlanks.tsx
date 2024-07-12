import { css } from '@emotion/react';
import { Controller, useFormContext } from 'react-hook-form';

import { styleUtils } from '@Utils/style-utils';
import type { QuizForm } from '@CourseBuilderComponents/modals/QuizModal';
import FormFillinTheBlanks from '@Components/fields/quiz/FormFillinTheBlanks';
import { useQuizModalContext } from '@CourseBuilderContexts/QuizModalContext';

const FillinTheBlanks = () => {
  const form = useFormContext<QuizForm>();
  const { activeQuestionIndex } = useQuizModalContext();

  return (
    <div css={styles.optionWrapper}>
      <Controller
        control={form.control}
        name={`questions.${activeQuestionIndex}.question_answers.0` as 'questions.0.question_answers.0'}
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
