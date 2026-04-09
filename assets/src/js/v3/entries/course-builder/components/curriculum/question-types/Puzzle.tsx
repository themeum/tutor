import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useEffect, useMemo } from 'react';
import { Controller, useFieldArray, useFormContext } from 'react-hook-form';

import { useQuizModalContext } from '@CourseBuilderContexts/QuizModalContext';
import type { QuizForm } from '@CourseBuilderServices/quiz';
import FormSelectInput from '@TutorShared/components/fields/FormSelectInput';
import FormPuzzle from '@TutorShared/components/fields/quiz/questions/FormPuzzle';
import { spacing } from '@TutorShared/config/styles';
import { calculateQuizDataStatus } from '@TutorShared/utils/quiz';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { QuizDataStatus, type QuizQuestionOption } from '@TutorShared/utils/types';
import { nanoid } from '@TutorShared/utils/util';

const Puzzle = () => {
  const form = useFormContext<QuizForm>();
  const { activeQuestionId, activeQuestionIndex, validationError, setValidationError } = useQuizModalContext();
  const activeQuestionDataStatus =
    form.watch(`questions.${activeQuestionIndex}._data_status`) ?? QuizDataStatus.NO_CHANGE;

  const answersPath = `questions.${activeQuestionIndex}.question_answers` as 'questions.0.question_answers';
  const gridSizePath =
    `questions.${activeQuestionIndex}.question_settings.puzzle_grid_size` as 'questions.0.question_settings.puzzle_grid_size';

  const { fields: optionsFields } = useFieldArray({
    control: form.control,
    name: answersPath,
  });

  const gridSizeOptions = useMemo(
    () =>
      [2, 3, 4, 5, 6, 7].map((value) => ({
        label: `${value} x ${value}`,
        value,
      })),
    [],
  );

  useEffect(() => {
    if (!activeQuestionId) {
      return;
    }
    if (optionsFields.length > 0) {
      return;
    }
    const baseAnswer: QuizQuestionOption = {
      _data_status: QuizDataStatus.NEW,
      is_saved: true,
      answer_id: nanoid(),
      belongs_question_id: activeQuestionId,
      belongs_question_type: 'puzzle' as QuizQuestionOption['belongs_question_type'],
      answer_title: '',
      is_correct: '1',
      image_id: undefined,
      image_url: '',
      answer_two_gap_match: '',
      answer_view_format: 'puzzle',
      answer_order: 0,
    };
    form.setValue(answersPath, [baseAnswer]);
  }, [activeQuestionId, optionsFields.length, answersPath, form]);

  useEffect(() => {
    const currentValue = form.getValues(gridSizePath);
    if (currentValue === undefined || currentValue === null || Number.isNaN(Number(currentValue))) {
      form.setValue(gridSizePath, 4);
    }
  }, [form, gridSizePath]);

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
            name={gridSizePath}
            render={(gridSizeControllerProps) => (
              <FormPuzzle
                {...answerControllerProps}
                questionId={activeQuestionId}
                validationError={validationError}
                setValidationError={setValidationError}
                gridSizeControl={
                  <FormSelectInput
                    {...gridSizeControllerProps}
                    label={__('Grid Size', 'tutor')}
                    options={gridSizeOptions}
                    helpText={__('Choose puzzle density from 2 x 2 to 7 x 7.', 'tutor')}
                    onChange={(option) => {
                      gridSizeControllerProps.field.onChange(option.value);
                      if (calculateQuizDataStatus(activeQuestionDataStatus, QuizDataStatus.UPDATE)) {
                        form.setValue(
                          `questions.${activeQuestionIndex}._data_status`,
                          calculateQuizDataStatus(activeQuestionDataStatus, QuizDataStatus.UPDATE) as QuizDataStatus,
                        );
                      }
                    }}
                  />
                }
              />
            )}
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
