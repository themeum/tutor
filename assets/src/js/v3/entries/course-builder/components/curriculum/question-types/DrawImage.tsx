import { css } from '@emotion/react';
import { useEffect } from 'react';
import { Controller, useFieldArray, useFormContext } from 'react-hook-form';

import { useQuizModalContext } from '@CourseBuilderContexts/QuizModalContext';
import type { QuizForm } from '@CourseBuilderServices/quiz';
import FormDrawImage from '@TutorShared/components/fields/quiz/questions/FormDrawImage';
import { spacing } from '@TutorShared/config/styles';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { QuizDataStatus, type QuizQuestionOption } from '@TutorShared/utils/types';
import { nanoid } from '@TutorShared/utils/util';

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

  // Ensure there is always a single option for this question type.
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
      belongs_question_type: 'draw_image' as QuizQuestionOption['belongs_question_type'],
      answer_title: '',
      is_correct: '1',
      image_id: undefined,
      image_url: '',
      answer_two_gap_match: '',
      answer_view_format: 'draw_image',
      answer_order: 0,
    };
    form.setValue(answersPath, [baseAnswer]);
  }, [activeQuestionId, optionsFields.length, answersPath, form]);

  // Default threshold for draw-image questions if not set.
  useEffect(() => {
    const currentValue = form.getValues(thresholdPath);
    if (currentValue === undefined || currentValue === null || Number.isNaN(Number(currentValue))) {
      form.setValue(thresholdPath, 70);
    }
  }, [form, thresholdPath]);

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
          <Controller
            control={form.control}
            name={thresholdPath}
            render={(thresholdControllerProps) => (
              <FormDrawImage
                {...answerControllerProps}
                questionId={activeQuestionId}
                validationError={validationError}
                setValidationError={setValidationError}
                precisionControllerProps={thresholdControllerProps}
                questionDataStatusPath={`questions.${activeQuestionIndex}._data_status`}
              />
            )}
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
