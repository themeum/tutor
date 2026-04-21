import { css } from '@emotion/react';
import { Controller, useFieldArray, useFormContext } from 'react-hook-form';

import { useQuizModalContext } from '@CourseBuilderContexts/QuizModalContext';
import type { QuizForm } from '@CourseBuilderServices/quiz';
import FormScale from '@TutorShared/components/fields/quiz/questions/FormScale';
import { spacing } from '@TutorShared/config/styles';
import { styleUtils } from '@TutorShared/utils/style-utils';

const Scale = () => {
  const form = useFormContext<QuizForm>();
  const { activeQuestionId, activeQuestionIndex, validationError, setValidationError } = useQuizModalContext();

  const answersPath = `questions.${activeQuestionIndex}.question_answers` as 'questions.0.question_answers';

  const { fields: optionsFields } = useFieldArray({
    control: form.control,
    name: answersPath,
  });

  if (optionsFields.length === 0) {
    return null;
  }

  return (
    <div css={styles.optionWrapper}>
      <Controller
        key={JSON.stringify(optionsFields[0])}
        control={form.control}
        name={`questions.${activeQuestionIndex}.question_answers.0` as 'questions.0.question_answers.0'}
        render={(controllerProps) => (
          <FormScale
            {...controllerProps}
            questionId={activeQuestionId}
            validationError={validationError}
            setValidationError={setValidationError}
          />
        )}
      />
    </div>
  );
};

export default Scale;

const styles = {
  optionWrapper: css`
    ${styleUtils.display.flex('column')};
    padding-left: ${spacing[40]};
  `,
};
