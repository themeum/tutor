import { css } from '@emotion/react';
import { useEffect } from 'react';
import { Controller, useFieldArray, useFormContext } from 'react-hook-form';

import { useQuizModalContext } from '@CourseBuilderContexts/QuizModalContext';
import type { QuizForm } from '@CourseBuilderServices/quiz';
import FormCoordinates from '@TutorShared/components/fields/quiz/questions/FormCoordinates';
import { spacing } from '@TutorShared/config/styles';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { QuizDataStatus, type QuizQuestionOption } from '@TutorShared/utils/types';
import { nanoid } from '@TutorShared/utils/util';

const Coordinates = () => {
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
      is_saved: false,
      answer_id: nanoid(),
      belongs_question_id: activeQuestionId,
      belongs_question_type: 'coordinates' as QuizQuestionOption['belongs_question_type'],
      answer_title: '',
      is_correct: '1',
      image_id: undefined,
      image_url: '',
      answer_two_gap_match: '',
      answer_view_format: 'coordinates',
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
          <FormCoordinates
            {...controllerProps}
            field={{
              ...controllerProps.field,
              value: controllerProps.field.value as QuizQuestionOption,
            }}
            questionId={activeQuestionId}
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
