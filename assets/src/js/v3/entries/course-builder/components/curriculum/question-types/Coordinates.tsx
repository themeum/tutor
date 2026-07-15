import { Controller, useController, useFieldArray, useFormContext } from 'react-hook-form';
import { css } from '@emotion/react';

import FormCoordinates from '@TutorShared/components/fields/quiz/questions/FormCoordinates';

import { spacing } from '@TutorShared/config/styles';
import { styleUtils } from '@TutorShared/utils/style-utils';

import { useQuizModalContext } from '@CourseBuilderContexts/QuizModalContext';
import type { QuizForm } from '@CourseBuilderServices/quiz';

const Coordinates = () => {
  const form = useFormContext<QuizForm>();
  const { activeQuestionId, activeQuestionIndex, validationError, setValidationError } = useQuizModalContext();

  const answersPath = `questions.${activeQuestionIndex}.question_answers` as 'questions.0.question_answers';
  const axisRangePath =
    `questions.${activeQuestionIndex}.question_settings.coordinates_axis_range` as 'questions.0.question_settings.coordinates_axis_range';

  const { fields: optionsFields } = useFieldArray({
    control: form.control,
    name: answersPath,
  });
  const axisRangeControllerProps = useController({
    control: form.control,
    name: axisRangePath,
    defaultValue: 10,
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
          <FormCoordinates
            {...controllerProps}
            questionId={activeQuestionId}
            activeQuestionIndex={activeQuestionIndex}
            axisRangeControllerProps={axisRangeControllerProps}
            validationError={validationError}
            setValidationError={setValidationError}
          />
        )}
      />
    </div>
  );
};

export default Coordinates;

const styles = {
  optionWrapper: css`
    ${styleUtils.display.flex('column')};
    padding-left: ${spacing[40]};
  `,
};
