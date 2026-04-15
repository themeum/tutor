import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useEffect, useMemo } from 'react';
import { Controller, useFieldArray, useFormContext } from 'react-hook-form';

import { useQuizModalContext } from '@CourseBuilderContexts/QuizModalContext';
import type { QuizForm } from '@CourseBuilderServices/quiz';
import FormDrawImage from '@TutorShared/components/fields/quiz/questions/FormDrawImage';
import FormSelectInput from '@TutorShared/components/fields/FormSelectInput';
import { spacing } from '@TutorShared/config/styles';
import { calculateQuizDataStatus } from '@TutorShared/utils/quiz';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { QuizDataStatus, type QuizQuestionOption } from '@TutorShared/utils/types';
import { nanoid } from '@TutorShared/utils/util';

const DrawImage = () => {
  const form = useFormContext<QuizForm>();
  const { activeQuestionId, activeQuestionIndex, validationError, setValidationError } = useQuizModalContext();
  const activeQuestionDataStatus =
    form.watch(`questions.${activeQuestionIndex}._data_status`) ?? QuizDataStatus.NO_CHANGE;

  const answersPath = `questions.${activeQuestionIndex}.question_answers` as 'questions.0.question_answers';
  const thresholdPath =
    `questions.${activeQuestionIndex}.question_settings.draw_image_threshold_percent` as 'questions.0.question_settings.draw_image_threshold_percent';

  const { fields: optionsFields } = useFieldArray({
    control: form.control,
    name: answersPath,
  });

  // Frontend-only stale cleanup: if legacy/buggy payload brings multiple draw answers,
  // keep the first one and mark the rest for deletion.
  useEffect(() => {
    if (optionsFields.length <= 1) {
      return;
    }

    const removableIds = optionsFields
      .slice(1)
      .filter((answer) => answer._data_status !== QuizDataStatus.NEW)
      .map((answer) => answer.answer_id)
      .filter(Boolean);

    if (removableIds.length > 0) {
      const prevDeleted = form.getValues('deleted_answer_ids') || [];
      const nextDeleted = Array.from(new Set([...prevDeleted, ...removableIds]));
      form.setValue('deleted_answer_ids', nextDeleted, { shouldDirty: true });
    }

    form.setValue(answersPath, [optionsFields[0] as QuizQuestionOption], { shouldDirty: true });
  }, [answersPath, form, optionsFields]);

  const thresholdOptions = useMemo(
    () =>
      [40, 50, 60, 70, 80, 90, 100].map((value) => ({
        label: `${value}%`,
        value,
      })),
    [],
  );

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
                precisionControl={
                  <FormSelectInput
                    {...thresholdControllerProps}
                    label={__('Precision Level', 'tutor')}
                    options={thresholdOptions}
                    helpText={__(
                      'Minimum overlap score between student and instructor markings. Larger or smaller marked areas lower the score.',
                      'tutor',
                    )}
                    onChange={(option) => {
                      thresholdControllerProps.field.onChange(option.value);
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

export default DrawImage;

const styles = {
  optionWrapper: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[16]};
    padding-left: ${spacing[40]};
  `,
};
