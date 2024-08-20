import { css } from '@emotion/react';
import { Controller, useFieldArray, useFormContext } from 'react-hook-form';

import FormFillInTheBlanks from '@Components/fields/quiz/FormFillinTheBlanks';
import { spacing } from '@Config/styles';
import { useQuizModalContext } from '@CourseBuilderContexts/QuizModalContext';
import type { QuizForm } from '@CourseBuilderServices/quiz';
import { styleUtils } from '@Utils/style-utils';

const FillInTheBlanks = () => {
  const form = useFormContext<QuizForm>();
  const { activeQuestionIndex, activeQuestionId } = useQuizModalContext();

  const { fields: optionsFields } = useFieldArray({
    control: form.control,
    name: `questions.${activeQuestionIndex}.question_answers` as 'questions.0.question_answers',
  });

  // const filteredOptionFields = optionsFields.reduce(
  //   (allOptions, option, index) => {
  //     if (option?.belongs_question_type === 'fill_in_the_blank') {
  //       allOptions.push({
  //         ...option,
  //         index: index,
  //       });
  //     }
  //     return allOptions;
  //   },
  //   [] as Array<QuizQuestionOption & { index: number }>,
  // );

  return (
    <div css={styles.optionWrapper}>
      <Controller
        key={optionsFields.length ? JSON.stringify(optionsFields[0]) : ''}
        control={form.control}
        name={`questions.${activeQuestionIndex}.question_answers.0}` as 'questions.0.question_answers.0'}
        render={(controllerProps) => <FormFillInTheBlanks {...controllerProps} />}
      />
    </div>
  );
};

export default FillInTheBlanks;

const styles = {
  optionWrapper: css`
    ${styleUtils.display.flex('column')};
    padding-left: ${spacing[40]};
  `,
};
