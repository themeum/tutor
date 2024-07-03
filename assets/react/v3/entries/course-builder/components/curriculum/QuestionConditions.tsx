import { Controller, useFormContext } from 'react-hook-form';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';

import FormInput from '@Components/fields/FormInput';
import FormSelectInput from '@Components/fields/FormSelectInput';
import FormSwitch from '@Components/fields/FormSwitch';

import type { QuizForm } from '@CourseBuilderComponents/modals/QuizModal';
import { colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import { styleUtils } from '@Utils/style-utils';
import { useQuizModalContext } from '@CourseBuilderContexts/QuizModalContext';
import type { Option } from '@Utils/types';
import type { QuizQuestionType } from '@CourseBuilderServices/quiz';

export const questionTypeOptions: Option<QuizQuestionType>[] = [
  {
    label: __('True/ False', 'tutor'),
    value: 'true-false',
    icon: 'quizTrueFalse',
  },
  {
    label: __('Multiple Choice', 'tutor'),
    value: 'multiple-choice',
    icon: 'quizMultiChoice',
  },
  {
    label: __('Open Ended/ Essay', 'tutor'),
    value: 'open-ended',
    icon: 'quizEssay',
  },
  {
    label: __('Fill in the Blanks', 'tutor'),
    value: 'fill-in-the-blanks',
    icon: 'quizFillInTheBlanks',
  },
  {
    label: __('Short Answer', 'tutor'),
    value: 'short-answer',
    icon: 'quizShortAnswer',
  },
  {
    label: __('Matching', 'tutor'),
    value: 'matching',
    icon: 'quizImageMatching',
  },
  {
    label: __('Image Answering', 'tutor'),
    value: 'image-answering',
    icon: 'quizImageAnswer',
  },
  {
    label: __('Ordering', 'tutor'),
    value: 'ordering',
    icon: 'quizOrdering',
  },
];

const QuestionConditions = () => {
  const { activeQuestionIndex, activeQuestionId } = useQuizModalContext();
  const form = useFormContext<QuizForm>();

  const activeQuestionType = form.watch(`questions.${activeQuestionIndex}.type`);

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
        <Controller
          control={form.control}
          name={`questions.${activeQuestionIndex}.type` as 'questions.0.type'}
          render={(controllerProps) => (
            <FormSelectInput {...controllerProps} label={__('Question Type', 'tutor')} options={questionTypeOptions} />
          )}
        />
      </div>

      <div css={styles.conditions}>
        <p>{__('Conditions', 'tutor')}</p>

        <div css={styles.conditionControls}>
          <Show when={activeQuestionType === 'multiple-choice'}>
            <Controller
              control={form.control}
              name={`questions.${activeQuestionIndex}.multipleCorrectAnswer` as 'questions.0.multipleCorrectAnswer'}
              render={(controllerProps) => (
                <FormSwitch {...controllerProps} label={__('Multiple Correct Answer', 'tutor')} />
              )}
            />
          </Show>

          <Show when={activeQuestionType === 'matching'}>
            <Controller
              control={form.control}
              name={`questions.${activeQuestionIndex}.imageMatching` as 'questions.0.imageMatching'}
              render={(controllerProps) => <FormSwitch {...controllerProps} label={__('Image Matching', 'tutor')} />}
            />
          </Show>

          <Controller
            control={form.control}
            name={`questions.${activeQuestionIndex}.answerRequired` as 'questions.0.answerRequired'}
            render={(controllerProps) => <FormSwitch {...controllerProps} label={__('Answer Required', 'tutor')} />}
          />

          <Controller
            control={form.control}
            name={`questions.${activeQuestionIndex}.randomizeQuestion` as 'questions.0.randomizeQuestion'}
            render={(controllerProps) => <FormSwitch {...controllerProps} label={__('Randomize Choice', 'tutor')} />}
          />

          <Controller
            control={form.control}
            name={`questions.${activeQuestionIndex}.questionMark` as 'questions.0.questionMark'}
            render={(controllerProps) => (
              <FormInput
                {...controllerProps}
                label={__('Point For This Answer', 'tutor')}
                type="number"
                isInlineLabel
                style={css`
                  max-width: 72px;
                `}
              />
            )}
          />

          <Controller
            control={form.control}
            name={`questions.${activeQuestionIndex}.showQuestionMark` as 'questions.0.showQuestionMark'}
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
    padding: ${spacing[8]} ${spacing[32]} ${spacing[24]} ${spacing[24]};
    border-bottom: 1px solid ${colorTokens.stroke.divider};
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