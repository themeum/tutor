import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { Controller, useFormContext } from 'react-hook-form';

import FormAnswerExplanation from '@Components/fields/FormAnswerExplanation';
import FormQuestionDescription from '@Components/fields/FormQuestionDescription';
import FormQuestionTitle from '@Components/fields/FormQuestionTitle';

import ImageAnswering from '@CourseBuilderComponents/curriculum/question-types/ImageAnswering';
import Matching from '@CourseBuilderComponents/curriculum/question-types/Matching';
import MultipleChoiceAndOrdering from '@CourseBuilderComponents/curriculum/question-types/MultipleChoiceAndOrdering';
import OpenEndedAndShortAnswer from '@CourseBuilderComponents/curriculum/question-types/OpenEndedAndShortAnswer';
import TrueFalse from '@CourseBuilderComponents/curriculum/question-types/TrueFalse';
import { useQuizModalContext } from '@CourseBuilderContexts/QuizModalContext';

import { colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import EmptyState from '@Molecules/EmptyState';
import { styleUtils } from '@Utils/style-utils';

import {
  type QuizDataStatus,
  type QuizForm,
  type QuizQuestionType,
  calculateQuizDataStatus,
} from '@CourseBuilderServices/quiz';
import emptyStateImage2x from '@Images/empty-state-illustration-2x.webp';
import emptyStateImage from '@Images/empty-state-illustration.webp';
import FillInTheBlanks from './question-types/FillinTheBlanks';

const QuestionForm = () => {
  const { activeQuestionIndex, activeQuestionId } = useQuizModalContext();
  const form = useFormContext<QuizForm>();

  const activeQuestionType = form.watch(`questions.${activeQuestionIndex}.question_type`);
  const questions = form.watch('questions') || [];
  const dataStatus = form.watch(`questions.${activeQuestionIndex}._data_status`);

  const questionTypeForm = {
    true_false: <TrueFalse key={activeQuestionId} />,
    multiple_choice: <MultipleChoiceAndOrdering key={activeQuestionId} />,
    open_ended: <OpenEndedAndShortAnswer key={activeQuestionId} />,
    fill_in_the_blank: <FillInTheBlanks key={activeQuestionId} />,
    short_answer: <OpenEndedAndShortAnswer key={activeQuestionId} />,
    matching: <Matching key={activeQuestionId} />,
    image_answering: <ImageAnswering key={activeQuestionId} />,
    ordering: <MultipleChoiceAndOrdering key={activeQuestionId} />,
  } as const;

  if (!activeQuestionId && !form.formState.isLoading && questions.length === 0) {
    return (
      <div css={styles.emptyState}>
        <EmptyState
          emptyStateImage={emptyStateImage}
          emptyStateImage2x={emptyStateImage2x}
          title={__('Write the quiz name to start creating questions', 'tutor')}
          description={__('You can add questions to the quiz once you have written the quiz name.', 'tutor')}
          imageAltText={__('No Question Image', 'tutor')}
          size="small"
        />
      </div>
    );
  }

  return (
    <div key={activeQuestionId} css={styles.questionForm}>
      <div css={styles.questionWithIndex}>
        <div css={styles.questionIndex}>{activeQuestionIndex + 1}.</div>
        <div css={styles.questionTitleAndDesc}>
          <Controller
            control={form.control}
            rules={{
              required: __('Question title is required', 'tutor'),
            }}
            name={`questions.${activeQuestionIndex}.question_title` as 'questions.0.question_title'}
            render={(controllerProps) => (
              <FormQuestionTitle
                {...controllerProps}
                placeholder={__('Write your question here..', 'tutor')}
                onChange={() => {
                  calculateQuizDataStatus(dataStatus, 'update') &&
                    form.setValue(
                      `questions.${activeQuestionIndex}._data_status`,
                      calculateQuizDataStatus(dataStatus, 'update') as QuizDataStatus,
                    );
                }}
              />
            )}
          />

          <Controller
            control={form.control}
            name={`questions.${activeQuestionIndex}.question_description` as 'questions.0.question_description'}
            render={(controllerProps) => (
              <FormQuestionDescription
                {...controllerProps}
                placeholder={__('Description (optional)', 'tutor')}
                onChange={() => {
                  calculateQuizDataStatus(dataStatus, 'update') &&
                    form.setValue(
                      `questions.${activeQuestionIndex}._data_status`,
                      calculateQuizDataStatus(dataStatus, 'update') as QuizDataStatus,
                    );
                }}
              />
            )}
          />
        </div>
      </div>

      {questionTypeForm[activeQuestionType as Exclude<QuizQuestionType, 'single_choice' | 'image_matching'>]}

      <div css={styles.questionAnswer}>
        <Controller
          control={form.control}
          name={`questions.${activeQuestionIndex}.answer_explanation` as 'questions.0.answer_explanation'}
          render={(controllerProps) => (
            <FormAnswerExplanation
              {...controllerProps}
              placeholder={__('Write answer explanation...', 'tutor')}
              onChange={() => {
                calculateQuizDataStatus(dataStatus, 'update') &&
                  form.setValue(
                    `questions.${activeQuestionIndex}._data_status`,
                    calculateQuizDataStatus(dataStatus, 'update') as QuizDataStatus,
                  );
              }}
            />
          )}
        />
      </div>
    </div>
  );
};

export default QuestionForm;

const styles = {
  questionForm: css`
    ${styleUtils.display.flex('column')};
    padding-right: ${spacing[48]};
    gap: ${spacing[16]};
    animation: fadeIn 0.25s ease-in-out;

    @keyframes fadeIn {
      from {
        opacity: 0;
      }
      to {
        opacity: 1;
      }
    }
  `,
  questionWithIndex: css`
    ${styleUtils.display.flex('row')};
    align-items: flex-start;
    padding-left: ${spacing[40]};
    gap: ${spacing[4]};
  `,
  questionIndex: css`
    margin-top: ${spacing[8]};
    ${typography.heading6()};
    color: ${colorTokens.text.hints};
  `,
  questionTitleAndDesc: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[8]};
    width: 100%;
  `,
  questionAnswer: css`
    padding-left: ${spacing[40]};
  `,
  emptyState: css`
    padding-left: ${spacing[40]}; 
    padding-right: ${spacing[48]};
  `,
};
