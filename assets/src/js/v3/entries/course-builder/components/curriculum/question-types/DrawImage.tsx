import { css } from '@emotion/react';
import { Controller, useController, useFieldArray, useFormContext } from 'react-hook-form';

import { useQuizModalContext } from '@CourseBuilderContexts/QuizModalContext';
import type { QuizForm } from '@CourseBuilderServices/quiz';
import FormDrawImage from '@TutorShared/components/fields/quiz/questions/FormDrawImage';
import { spacing } from '@TutorShared/config/styles';
import { styleUtils } from '@TutorShared/utils/style-utils';

const DrawImage = () => {
  const form = useFormContext<QuizForm>();
  const { activeQuestionId, activeQuestionIndex, validationError, setValidationError } = useQuizModalContext();

  const answersPath = `questions.${activeQuestionIndex}.question_answers` as 'questions.0.question_answers';
  const thresholdPath =
    `questions.${activeQuestionIndex}.question_settings.draw_image_threshold_percent` as 'questions.0.question_settings.draw_image_threshold_percent';

  const { fields: optionsFields } = useFieldArray({
    control: form.control,
    name: answersPath,
  });
  const thresholdControllerProps = useController({
    control: form.control,
    name: thresholdPath,
    defaultValue: 70,
  });

  // Only render Controller when the value exists to ensure field.value is always defined
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
          <FormDrawImage
            {...answerControllerProps}
            questionId={activeQuestionId}
            activeQuestionIndex={activeQuestionIndex}
            validationError={validationError}
            setValidationError={setValidationError}
            precisionControllerProps={thresholdControllerProps}
          />
        )}
      />
    </div>
  );
};

export default DrawImage;

const styles = {
  optionWrapper: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[16]};
    padding-left: ${spacing[40]};
  `,
};
