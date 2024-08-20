import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { Controller, useFormContext } from 'react-hook-form';

import FormInput from '@Components/fields/FormInput';
import FormSwitch from '@Components/fields/FormSwitch';

import SVGIcon from '@Atoms/SVGIcon';
import { colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import { useQuizModalContext } from '@CourseBuilderContexts/QuizModalContext';
import type { QuizForm, QuizQuestionType } from '@CourseBuilderServices/quiz';
import { styleUtils } from '@Utils/style-utils';
import type { IconCollection } from '@Utils/types';

const questionTypes = {
  true_false: {
    label: __('True/ False', 'tutor'),
    icon: 'quizTrueFalse',
  },
  multiple_choice: {
    label: __('Multiple Choice', 'tutor'),
    icon: 'quizMultiChoice',
  },
  open_ended: {
    label: __('Open Ended/ Essay', 'tutor'),
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
};

const QuestionConditions = () => {
  const { activeQuestionIndex, activeQuestionId } = useQuizModalContext();
  const form = useFormContext<QuizForm>();

  const activeQuestionType = form.watch(`questions.${activeQuestionIndex}.question_type`) as Omit<
    QuizQuestionType,
    'single_choice' | 'image_matching'
  >;

  if (!activeQuestionId) {
    return (
      <p css={styles.questionTypeWrapper}>
        {__('Question Type', 'tutor')} (<span css={{ color: colorTokens.text.error }}>{__('Pending', 'tutor')}</span>)
      </p>
    );
  }

  return (
    <div key={`${activeQuestionId}-${activeQuestionIndex}`}>
      <div css={styles.questionTypeWrapper}>
        {/* <Controller
          control={form.control}
          name={`questions.${activeQuestionIndex}.question_type` as 'questions.0.question_type'}
          render={(controllerProps) => (
            <FormSelectInput {...controllerProps} label={__('Question Type', 'tutor')} options={questionTypeOptions} />
          )}
        /> */}
        <div css={typography.caption('medium')}>{__('Question Type', 'tutor')}</div>
        <div css={styles.questionType}>
          <SVGIcon
            name={questionTypes[activeQuestionType as keyof typeof questionTypes].icon as IconCollection}
            width={32}
            height={32}
          />
          <span>{questionTypes[activeQuestionType as keyof typeof questionTypes].label}</span>
        </div>
      </div>

      <div css={styles.conditions}>
        <p>{__('Conditions', 'tutor')}</p>

        <div css={styles.conditionControls}>
          <Show when={activeQuestionType === 'multiple_choice'}>
            <Controller
              control={form.control}
              name={
                `questions.${activeQuestionIndex}.has_multiple_correct_answer` as 'questions.0.has_multiple_correct_answer'
              }
              render={(controllerProps) => (
                <FormSwitch {...controllerProps} label={__('Multiple Correct Answer', 'tutor')} />
              )}
            />
          </Show>

          <Show when={activeQuestionType === 'matching'}>
            <Controller
              control={form.control}
              name={`questions.${activeQuestionIndex}.is_image_matching` as 'questions.0.is_image_matching'}
              render={(controllerProps) => <FormSwitch {...controllerProps} label={__('Image Matching', 'tutor')} />}
            />
          </Show>

          <Controller
            control={form.control}
            name={
              `questions.${activeQuestionIndex}.question_settings.answer_required` as 'questions.0.question_settings.answer_required'
            }
            render={(controllerProps) => <FormSwitch {...controllerProps} label={__('Answer Required', 'tutor')} />}
          />

          <Controller
            control={form.control}
            name={`questions.${activeQuestionIndex}.randomizeQuestion` as 'questions.0.randomizeQuestion'}
            render={(controllerProps) => <FormSwitch {...controllerProps} label={__('Randomize Choice', 'tutor')} />}
          />

          <Controller
            control={form.control}
            name={`questions.${activeQuestionIndex}.question_mark` as 'questions.0.question_mark'}
            render={(controllerProps) => (
              <FormInput
                {...controllerProps}
                label={__('Point For This Answer', 'tutor')}
                type="number"
                isInlineLabel
                placeholder="0"
                selectOnFocus
                style={css`
                  max-width: 72px;
                `}
              />
            )}
          />

          <Controller
            control={form.control}
            name={
              `questions.${activeQuestionIndex}.question_settings.show_question_mark` as 'questions.0.question_settings.show_question_mark'
            }
            render={(controllerProps) => <FormSwitch {...controllerProps} label={__('Display Points', 'tutor')} />}
          />
        </div>
      </div>
    </div>
  );
};

export default QuestionConditions;

const styles = {
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
