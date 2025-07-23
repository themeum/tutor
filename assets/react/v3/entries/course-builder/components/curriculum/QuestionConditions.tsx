import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { Controller, useFormContext } from 'react-hook-form';

import SVGIcon from '@TutorShared/atoms/SVGIcon';
import FormInput from '@TutorShared/components/fields/FormInput';
import FormSwitch from '@TutorShared/components/fields/FormSwitch';

import CourseBuilderInjectionSlot from '@CourseBuilderComponents/CourseBuilderSlot';
import { useQuizModalContext } from '@CourseBuilderContexts/QuizModalContext';
import { type QuizForm } from '@CourseBuilderServices/quiz';
import { colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import Show from '@TutorShared/controls/Show';
import { type IconCollection } from '@TutorShared/icons/types';
import { calculateQuizDataStatus } from '@TutorShared/utils/quiz';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { QuizDataStatus, type QuizQuestionType } from '@TutorShared/utils/types';

const questionTypes = {
  true_false: {
    label: __('True/False', 'tutor'),
    icon: 'quizTrueFalse',
  },
  multiple_choice: {
    label: __('Multiple Choice', 'tutor'),
    icon: 'quizMultiChoice',
  },
  open_ended: {
    label: __('Open Ended/Essay', 'tutor'),
    icon: 'quizEssay',
  },
  fill_in_the_blank: {
    label: __('Fill in the Blanks', 'tutor'),
    icon: 'quizFillInTheBlanks',
  },
  short_answer: {
    label: __('Short Answer', 'tutor'),
    icon: 'quizShortAnswer',
  },
  matching: {
    label: __('Matching', 'tutor'),
    icon: 'quizImageMatching',
  },
  image_answering: {
    label: __('Image Answering', 'tutor'),
    icon: 'quizImageAnswer',
  },
  ordering: {
    label: __('Ordering', 'tutor'),
    icon: 'quizOrdering',
  },
  h5p: {
    label: __('H5P', 'tutor'),
    icon: 'quizTrueFalse',
  },
};

type QuestionTypes = Omit<QuizQuestionType, 'single_choice' | 'image_matching'>;

const supportRandomize: QuestionTypes[] = ['multiple_choice', 'matching', 'image_answering'];

const QuestionConditions = () => {
  const { activeQuestionIndex, activeQuestionId, validationError, setValidationError } = useQuizModalContext();
  const form = useFormContext<QuizForm>();

  const activeQuestionType = form.watch(`questions.${activeQuestionIndex}.question_type`) as QuestionTypes;
  const activeDataStatus = form.watch(`questions.${activeQuestionIndex}._data_status`);

  if (!activeQuestionId) {
    return <p css={styles.emptyQuestions}>{__('Create/Select a question to view details', 'tutor')}</p>;
  }

  return (
    <div key={`${activeQuestionId}-${activeQuestionIndex}`}>
      <div css={styles.questionTypeWrapper}>
        <div css={typography.caption('medium')}>{__('Question Type', 'tutor')}</div>
        <div css={styles.questionType}>
          <SVGIcon
            name={
              activeQuestionType
                ? (questionTypes[activeQuestionType as keyof typeof questionTypes].icon as IconCollection)
                : 'quizTrueFalse'
            }
            width={32}
            height={32}
          />
          <span>{activeQuestionType ? questionTypes[activeQuestionType as keyof typeof questionTypes].label : ''}</span>
        </div>
      </div>

      <div css={styles.conditions}>
        <p>{__('Conditions:', 'tutor')}</p>

        <div css={styles.conditionControls}>
          <Show when={activeQuestionType === 'multiple_choice'}>
            <Controller
              control={form.control}
              name={
                `questions.${activeQuestionIndex}.question_settings.has_multiple_correct_answer` as 'questions.0.question_settings.has_multiple_correct_answer'
              }
              render={(controllerProps) => (
                <FormSwitch
                  {...controllerProps}
                  label={__('Multiple Correct Answer', 'tutor')}
                  onChange={(value) => {
                    if (calculateQuizDataStatus(activeDataStatus, QuizDataStatus.UPDATE)) {
                      form.setValue(
                        `questions.${activeQuestionIndex}._data_status`,
                        calculateQuizDataStatus(activeDataStatus, QuizDataStatus.UPDATE) as QuizDataStatus,
                      );
                    }

                    // Reset all answers to incorrect on multiple correct answer toggle from true to false
                    if (!value) {
                      form.setValue(
                        `questions.${activeQuestionIndex}.question_answers`,
                        form.getValues(`questions.${activeQuestionIndex}.question_answers`).map((answer) => {
                          return {
                            ...answer,
                            is_correct: '0' as '0' | '1',
                          };
                        }),
                      );
                    }
                  }}
                />
              )}
            />
          </Show>

          <Show when={activeQuestionType === 'matching'}>
            <Controller
              control={form.control}
              name={
                `questions.${activeQuestionIndex}.question_settings.is_image_matching` as 'questions.0.question_settings.is_image_matching'
              }
              render={(controllerProps) => (
                <FormSwitch
                  {...controllerProps}
                  label={__('Image Matching', 'tutor')}
                  onChange={(value) => {
                    if (calculateQuizDataStatus(activeDataStatus, QuizDataStatus.UPDATE)) {
                      form.setValue(
                        `questions.${activeQuestionIndex}._data_status`,
                        calculateQuizDataStatus(activeDataStatus, QuizDataStatus.UPDATE) as QuizDataStatus,
                      );
                    }

                    if (validationError?.type === 'question' && !value) {
                      setValidationError(null);
                    }
                  }}
                />
              )}
            />
          </Show>

          <Controller
            control={form.control}
            name={
              `questions.${activeQuestionIndex}.question_settings.answer_required` as 'questions.0.question_settings.answer_required'
            }
            render={(controllerProps) => (
              <FormSwitch
                {...controllerProps}
                label={__('Answer Required', 'tutor')}
                onChange={() => {
                  if (calculateQuizDataStatus(activeDataStatus, QuizDataStatus.UPDATE)) {
                    form.setValue(
                      `questions.${activeQuestionIndex}._data_status`,
                      calculateQuizDataStatus(activeDataStatus, QuizDataStatus.UPDATE) as QuizDataStatus,
                    );
                  }
                }}
              />
            )}
          />

          <Show when={supportRandomize.includes(activeQuestionType)}>
            <Controller
              control={form.control}
              name={
                `questions.${activeQuestionIndex}.question_settings.randomize_question` as 'questions.0.question_settings.randomize_question'
              }
              render={(controllerProps) => (
                <FormSwitch
                  {...controllerProps}
                  label={__('Randomize Choice', 'tutor')}
                  onChange={() => {
                    if (calculateQuizDataStatus(activeDataStatus, QuizDataStatus.UPDATE)) {
                      form.setValue(
                        `questions.${activeQuestionIndex}._data_status`,
                        calculateQuizDataStatus(activeDataStatus, QuizDataStatus.UPDATE) as QuizDataStatus,
                      );
                    }
                  }}
                />
              )}
            />
          </Show>

          <Controller
            control={form.control}
            name={
              `questions.${activeQuestionIndex}.question_settings.question_mark` as 'questions.0.question_settings.question_mark'
            }
            rules={{
              min: 0,
            }}
            render={(controllerProps) => (
              <FormInput
                {...controllerProps}
                label={__('Point For This Question', 'tutor')}
                type="number"
                isInlineLabel
                placeholder="0"
                style={css`
                  max-width: 80px;
                `}
                onChange={() => {
                  if (calculateQuizDataStatus(activeDataStatus, QuizDataStatus.UPDATE)) {
                    form.setValue(
                      `questions.${activeQuestionIndex}._data_status`,
                      calculateQuizDataStatus(activeDataStatus, QuizDataStatus.UPDATE) as QuizDataStatus,
                    );
                  }
                }}
              />
            )}
          />

          <Controller
            control={form.control}
            name={
              `questions.${activeQuestionIndex}.question_settings.show_question_mark` as 'questions.0.question_settings.show_question_mark'
            }
            render={(controllerProps) => (
              <FormSwitch
                {...controllerProps}
                label={__('Display Points', 'tutor')}
                onChange={() => {
                  if (calculateQuizDataStatus(activeDataStatus, QuizDataStatus.UPDATE)) {
                    form.setValue(
                      `questions.${activeQuestionIndex}._data_status`,
                      calculateQuizDataStatus(activeDataStatus, QuizDataStatus.UPDATE) as QuizDataStatus,
                    );
                  }
                }}
              />
            )}
          />

          <CourseBuilderInjectionSlot
            section="Curriculum.Quiz.bottom_of_question_sidebar"
            namePrefix={`questions.${activeQuestionIndex}.`}
            form={form}
          />
        </div>
      </div>
    </div>
  );
};

export default QuestionConditions;

const styles = {
  emptyQuestions: css`
    padding: ${spacing[12]} ${spacing[32]} ${spacing[24]} ${spacing[24]};
    ${typography.caption('medium')};
  `,

  questionTypeWrapper: css`
    ${styleUtils.display.flex('column')};
    padding: ${spacing[8]} ${spacing[32]} ${spacing[24]} ${spacing[24]};
    gap: ${spacing[10]};
    border-bottom: 1px solid ${colorTokens.stroke.divider};
  `,
  questionType: css`
    display: flex;
    align-items: center;
    gap: ${spacing[10]};
  `,
  conditions: css`
    padding: ${spacing[8]} ${spacing[32]} ${spacing[24]} ${spacing[24]};
    p {
      ${typography.body('medium')};
      color: ${colorTokens.text.primary};
    }
  `,
  conditionControls: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[16]};
    margin-top: ${spacing[16]};
  `,
};
