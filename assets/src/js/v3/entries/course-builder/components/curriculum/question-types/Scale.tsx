import { css } from '@emotion/react';
import { useEffect } from 'react';
import { Controller, useFieldArray, useFormContext } from 'react-hook-form';

import { useQuizModalContext } from '@CourseBuilderContexts/QuizModalContext';
import type { QuizForm } from '@CourseBuilderServices/quiz';
import FormScale from '@TutorShared/components/fields/quiz/questions/FormScale';
import { spacing } from '@TutorShared/config/styles';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { QuizDataStatus, type QuizQuestionOption } from '@TutorShared/utils/types';
import { nanoid } from '@TutorShared/utils/util';

const Scale = () => {
  const form = useFormContext<QuizForm>();
  const { activeQuestionId, activeQuestionIndex, validationError, setValidationError } = useQuizModalContext();

  const answersPath = `questions.${activeQuestionIndex}.question_answers` as 'questions.0.question_answers';

  const { fields: optionsFields } = useFieldArray({
    control: form.control,
    name: answersPath,
  });

  useEffect(() => {
    if (!activeQuestionId) {
      return;
    }
    if (optionsFields.length > 0) {
      return;
    }
    const baseAnswer: QuizQuestionOption = {
      _data_status: QuizDataStatus.NEW,
      // Treat the initial default configuration as already saved so that
      // validation doesn’t block adding another question when the instructor
      // hasn’t interacted with the scale form yet.
      is_saved: true,
      answer_id: nanoid(),
      belongs_question_id: activeQuestionId,
      belongs_question_type: 'scale' as QuizQuestionOption['belongs_question_type'],
      answer_title: '',
      is_correct: '1',
      image_id: undefined,
      image_url: '',
      answer_two_gap_match: JSON.stringify({
        value: 50,
        config: {
          min: 0,
          max: 100,
          step: 1,
          defaultValue: 50,
          pxPerUnit: 10,
          labelEvery: 10,
          minorTickEvery: 5,
          precision: 0,
        },
      }),
      answer_view_format: 'scale',
      answer_order: 0,
    };
    form.setValue(answersPath, [baseAnswer]);
  }, [activeQuestionId, optionsFields.length, answersPath, form]);

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
