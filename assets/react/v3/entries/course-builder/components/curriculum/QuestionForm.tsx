import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useEffect, useRef } from 'react';
import { Controller, useFormContext } from 'react-hook-form';

import Alert from '@TutorShared/atoms/Alert';

import FormAnswerExplanation from '@TutorShared/components/fields/quiz/FormAnswerExplanation';
import FormQuestionDescription from '@TutorShared/components/fields/quiz/FormQuestionDescription';
import FormQuestionTitle from '@TutorShared/components/fields/quiz/FormQuestionTitle';

import FillInTheBlanks from '@CourseBuilderComponents/curriculum/question-types/FillinTheBlanks';
import ImageAnswering from '@CourseBuilderComponents/curriculum/question-types/ImageAnswering';
import Matching from '@CourseBuilderComponents/curriculum/question-types/Matching';
import MultipleChoiceAndOrdering from '@CourseBuilderComponents/curriculum/question-types/MultipleChoiceAndOrdering';
import OpenEndedAndShortAnswer from '@CourseBuilderComponents/curriculum/question-types/OpenEndedAndShortAnswer';
import TrueFalse from '@CourseBuilderComponents/curriculum/question-types/TrueFalse';
import { useQuizModalContext } from '@CourseBuilderContexts/QuizModalContext';

import { tutorConfig } from '@TutorShared/config/config';
import { borderRadius, Breakpoint, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import Show from '@TutorShared/controls/Show';
import { usePrevious } from '@TutorShared/hooks/usePrevious';
import { styleUtils } from '@TutorShared/utils/style-utils';

import CourseBuilderInjectionSlot from '@CourseBuilderComponents/CourseBuilderSlot';
import { type QuizForm } from '@CourseBuilderServices/quiz';
import { calculateQuizDataStatus } from '@TutorShared/utils/quiz';
import { QuizDataStatus, type QuizQuestionType } from '@TutorShared/utils/types';

import emptyStateImage2x from '@SharedImages/quiz-empty-state-2x.webp';
import emptyStateImage from '@SharedImages/quiz-empty-state.webp';

const isTutorPro = !!tutorConfig.tutor_pro_url;

const QuestionForm = () => {
  const { activeQuestionIndex, activeQuestionId, validationError, contentType } = useQuizModalContext();
  const previousActiveQuestionId = usePrevious(activeQuestionId);
  const form = useFormContext<QuizForm>();

  const alertRef = useRef<HTMLDivElement>(null);

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

  useEffect(() => {
    if (validationError && alertRef.current) {
      alertRef.current.scrollIntoView({
        behavior: 'smooth',
        block: 'center',
        inline: 'center',
      });
    }
  }, [validationError]);

  if (!activeQuestionId && !form.formState.isLoading && questions.length === 0) {
    return (
      <div css={styles.emptyState}>
        <img
          css={styles.emptyStateImage}
          src={emptyStateImage}
          srcSet={`${emptyStateImage} 1x, ${emptyStateImage2x} 2x`}
          alt=""
        />

        <p css={styles.emptyStateText}>
          {
            // prettier-ignore
            __('Enter a quiz title to begin. Choose from a variety of question types to keep things interesting!', 'tutor')
          }
        </p>
      </div>
    );
  }

  return (
    <div key={activeQuestionIndex} css={styles.questionForm(activeQuestionId === previousActiveQuestionId)}>
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
                disabled={contentType === 'tutor_h5p_quiz'}
                onChange={() => {
                  if (calculateQuizDataStatus(dataStatus, QuizDataStatus.UPDATE)) {
                    form.setValue(
                      `questions.${activeQuestionIndex}._data_status`,
                      calculateQuizDataStatus(dataStatus, QuizDataStatus.UPDATE) as QuizDataStatus,
                    );
                  }
                }}
                selectOnFocus={questions[activeQuestionIndex]?._data_status === QuizDataStatus.NEW}
              />
            )}
          />

          <Show
            when={contentType !== 'tutor_h5p_quiz'}
            fallback={
              <div>
                <div css={styles.h5pShortCode}>
                  {`[h5p id: "${form.watch(`questions.${activeQuestionIndex}.question_description`)}"]`}
                </div>
              </div>
            }
          >
            <Controller
              control={form.control}
              name={`questions.${activeQuestionIndex}.question_description` as 'questions.0.question_description'}
              render={(controllerProps) => (
                <FormQuestionDescription
                  {...controllerProps}
                  placeholder={__('Description (optional)', 'tutor')}
                  disabled={contentType === 'tutor_h5p_quiz'}
                  onChange={() => {
                    if (calculateQuizDataStatus(dataStatus, QuizDataStatus.UPDATE)) {
                      form.setValue(
                        `questions.${activeQuestionIndex}._data_status`,
                        calculateQuizDataStatus(dataStatus, QuizDataStatus.UPDATE) as QuizDataStatus,
                      );
                    }
                  }}
                />
              )}
            />
          </Show>

          <CourseBuilderInjectionSlot
            section="Curriculum.Quiz.after_question_description"
            namePrefix={`questions.${activeQuestionIndex}.`}
            form={form}
          />
        </div>
      </div>

      <Show when={validationError}>
        <div key={Math.random()} ref={alertRef} css={styles.alertWrapper}>
          <Alert type="danger" icon="warning">
            {validationError?.message}
          </Alert>
        </div>
      </Show>

      {questionTypeForm[activeQuestionType as Exclude<QuizQuestionType, 'single_choice' | 'image_matching' | 'h5p'>]}

      <Show when={isTutorPro && activeQuestionType !== 'h5p'}>
        <div css={styles.questionAnswer}>
          <Controller
            control={form.control}
            name={`questions.${activeQuestionIndex}.answer_explanation` as 'questions.0.answer_explanation'}
            render={(controllerProps) => (
              <FormAnswerExplanation
                {...controllerProps}
                label={__('Answer Explanation', 'tutor')}
                placeholder={__('Write answer explanation...', 'tutor')}
                onChange={() => {
                  if (calculateQuizDataStatus(dataStatus, QuizDataStatus.UPDATE)) {
                    form.setValue(
                      `questions.${activeQuestionIndex}._data_status`,
                      calculateQuizDataStatus(dataStatus, QuizDataStatus.UPDATE) as QuizDataStatus,
                    );
                  }
                }}
              />
            )}
          />
        </div>
      </Show>
    </div>
  );
};

export default QuestionForm;

const styles = {
  questionForm: (isSameQuestion: boolean) => css`
    ${styleUtils.display.flex('column')};
    padding-right: ${spacing[48]};
    gap: ${spacing[16]};
    animation: ${isSameQuestion ? undefined : 'fadeIn 0.25s ease-in-out'};

    @keyframes fadeIn {
      from {
        opacity: 0;
      }
      to {
        opacity: 1;
      }
    }

    ${Breakpoint.smallMobile} {
      padding-right: ${spacing[8]};
    }
  `,
  questionWithIndex: css`
    ${styleUtils.display.flex('row')};
    align-items: flex-start;
    padding-left: ${spacing[40]};
    gap: ${spacing[4]};

    ${Breakpoint.smallMobile} {
      padding-left: ${spacing[8]};
    }
  `,
  questionIndex: css`
    margin-top: ${spacing[10]};
    ${typography.heading6()};
    color: ${colorTokens.text.hints};
  `,
  questionTitleAndDesc: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[8]};
    width: 100%;
  `,
  h5pShortCode: css`
    margin-left: ${spacing[8]};
    padding: ${spacing[4]} ${spacing[8]};
    ${typography.caption()};
    color: ${colorTokens.text.white};
    background-color: #2575be;
    border-radius: ${borderRadius.card};
    width: fit-content;
    font-family: 'Fire Code', monospace;
  `,
  questionAnswer: css`
    padding-left: ${spacing[40]};

    ${Breakpoint.smallMobile} {
      padding-left: ${spacing[8]};
    }
  `,
  emptyState: css`
    ${styleUtils.flexCenter('column')};
    padding-left: ${spacing[40]};
    padding-right: ${spacing[48]};
    gap: ${spacing[16]};
  `,
  emptyStateImage: css`
    width: 220px;
    height: auto;
  `,
  emptyStateText: css`
    ${typography.small()};
    color: ${colorTokens.text.subdued};
    text-align: center;
    max-width: 330px;
  `,
  alertWrapper: css`
    padding-left: ${spacing[40]};
    animation: shake 0.3s linear;

    @keyframes shake {
      0% {
        transform: translateX(0);
      }
      25% {
        transform: translateX(-5px);
      }
      50% {
        transform: translateX(5px);
      }
      75% {
        transform: translateX(-5px);
      }
      100% {
        transform: translateX(0);
      }
    }
  `,
};
