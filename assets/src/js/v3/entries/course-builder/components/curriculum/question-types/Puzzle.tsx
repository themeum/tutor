import { css } from '@emotion/react';
import { Controller, useFieldArray, useFormContext } from 'react-hook-form';

import { useQuizModalContext } from '@CourseBuilderContexts/QuizModalContext';
import type { QuizForm } from '@CourseBuilderServices/quiz';
import FormPuzzle from '@TutorShared/components/fields/quiz/questions/FormPuzzle';
import { spacing } from '@TutorShared/config/styles';
import { styleUtils } from '@TutorShared/utils/style-utils';

const Puzzle = () => {
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
        key={optionsFields[0]?.id}
        control={form.control}
        name={`questions.${activeQuestionIndex}.question_answers.0` as 'questions.0.question_answers.0'}
        render={(answerControllerProps) => (
          <FormPuzzle
            {...answerControllerProps}
            questionId={activeQuestionId}
            activeQuestionIndex={activeQuestionIndex}
            validationError={validationError}
            setValidationError={setValidationError}
          />
        )}
      />
    </div>
  );
};

export default Puzzle;

const styles = {
  optionWrapper: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[16]};
    padding-left: ${spacing[40]};
  `,
};
