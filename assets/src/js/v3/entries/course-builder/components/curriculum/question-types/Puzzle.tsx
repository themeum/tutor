import { Controller, useController, useFieldArray, useFormContext } from 'react-hook-form';
import { css } from '@emotion/react';

import FormPuzzle from '@TutorShared/components/fields/quiz/questions/FormPuzzle';

import { spacing } from '@TutorShared/config/styles';
import { styleUtils } from '@TutorShared/utils/style-utils';

import { useQuizModalContext } from '@CourseBuilderContexts/QuizModalContext';
import type { QuizForm } from '@CourseBuilderServices/quiz';

const Puzzle = () => {
  const form = useFormContext<QuizForm>();
  const { activeQuestionId, activeQuestionIndex, validationError, setValidationError } = useQuizModalContext();

  const answersPath = `questions.${activeQuestionIndex}.question_answers` as 'questions.0.question_answers';
  const gridSizePath =
    `questions.${activeQuestionIndex}.question_settings.puzzle_grid_size` as 'questions.0.question_settings.puzzle_grid_size';
  const backgroundPath =
    `questions.${activeQuestionIndex}.question_settings.enable_puzzle_answer_background` as 'questions.0.question_settings.enable_puzzle_answer_background';

  const { fields: optionsFields } = useFieldArray({
    control: form.control,
    name: answersPath,
  });
  const gridSizeControllerProps = useController({
    control: form.control,
    name: gridSizePath,
    defaultValue: 4,
  });
  const backgroundControllerProps = useController({
    control: form.control,
    name: backgroundPath,
    defaultValue: true,
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
            gridSizeControllerProps={gridSizeControllerProps}
            backgroundControllerProps={backgroundControllerProps}
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
