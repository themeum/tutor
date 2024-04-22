import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useEffect, useRef, useState } from 'react';
import { Controller, FormProvider } from 'react-hook-form';

import Button from '@Atoms/Button';
import { LoadingSection } from '@Atoms/LoadingSpinner';
import SVGIcon from '@Atoms/SVGIcon';

import FormInput from '@Components/fields/FormInput';
import type { ModalProps } from '@Components/modals/Modal';
import ModalWrapper from '@Components/modals/ModalWrapper';
import FormTextareaInput from '@Components/fields/FormTextareaInput';

import ConfirmationPopover from '@Molecules/ConfirmationPopover';
import Tabs from '@Molecules/Tabs';

import QuizSettings from '@CourseBuilderComponents/curriculum/QuizSettings';
import { QuizModalContextProvider } from '@CourseBuilderContexts/QuizModalContext';
import QuestionCondition from '@CourseBuilderComponents/curriculum/QuestionCondition';
import QuestionForm from '@CourseBuilderComponents/curriculum/QuestionForm';
import { type QuizQuestion, useGetQuizQuestionsQuery } from '@CourseBuilderServices/quiz';
import QuestionList from '@CourseBuilderComponents/curriculum/QuestionList';

import { modal } from '@Config/constants';
import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import { styleUtils } from '@Utils/style-utils';

import { AnimationType } from '@Hooks/useAnimation';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';

interface QuizModalProps extends ModalProps {
  closeModal: (props?: { action: 'CONFIRM' | 'CLOSE' }) => void;
}

export type QuizTimeLimit = 'seconds' | 'minutes' | 'hours' | 'days' | 'weeks';

export interface QuizForm {
  quiz_title: string;
  quiz_description: string;
  quiz_option: {
    time_limit: {
      time_value: number;
      time_type: QuizTimeLimit;
    };
    hide_quiz_time_display: boolean;
    feedback_mode: 'default' | 'reveal' | 'retry';
    attempts_allowed: number;
    passing_grade: number;
    max_questions_for_answer: number;
    available_after_days: number;
    quiz_auto_start: boolean;
    question_layout_view: '' | 'single_question' | 'question_pagination' | 'question_below_each_other';
    questions_order: 'rand' | 'sorting' | 'asc' | 'desc';
    hide_question_number_overview: boolean;
    short_answer_characters_limit: number;
    open_ended_answer_characters_limit: number;
  };
  questions: QuizQuestion[];
}

type QuizTabs = 'questions' | 'settings';

const QuizModal = ({ closeModal, icon, title, subtitle }: QuizModalProps) => {
  const [isConfirmationOpen, setIsConfirmationOpen] = useState(false);
  const [activeQuestionId, setActiveQuestionId] = useState<string | null>(null);
  const [activeTab, setActiveTab] = useState<QuizTabs>('questions');
  // @TODO: isEdit will be calculated based on the quiz data form API
  const [isEdit, setIsEdit] = useState(true);

  const cancelRef = useRef<HTMLButtonElement>(null);

  const getQuizQuestionsQuery = useGetQuizQuestionsQuery();

  const form = useFormWithGlobalError<QuizForm>({
    defaultValues: {
      quiz_option: {
        time_limit: {
          time_value: 0,
          time_type: 'minutes',
        },
        hide_quiz_time_display: false,
        feedback_mode: 'default',
        attempts_allowed: 0,
        passing_grade: 0,
        max_questions_for_answer: 0,
        available_after_days: 0,
        quiz_auto_start: false,
        question_layout_view: '',
        questions_order: 'rand',
        hide_question_number_overview: false,
        short_answer_characters_limit: 0,
        open_ended_answer_characters_limit: 0,
      },
      questions: [],
    },
    values: {
      quiz_title: 'New Quiz',
      quiz_description: 'Quiz description',
      quiz_option: {
        time_limit: {
          time_value: 0,
          time_type: 'minutes',
        },
        hide_quiz_time_display: false,
        feedback_mode: 'default',
        attempts_allowed: 0,
        passing_grade: 0,
        max_questions_for_answer: 0,
        available_after_days: 0,
        quiz_auto_start: false,
        question_layout_view: '',
        questions_order: 'rand',
        hide_question_number_overview: false,
        short_answer_characters_limit: 0,
        open_ended_answer_characters_limit: 0,
      },
      questions: getQuizQuestionsQuery.data || [],
    },
  });

  useEffect(() => {
    if (getQuizQuestionsQuery.data) {
      setActiveQuestionId(getQuizQuestionsQuery.data[0].ID);
    }
  }, [getQuizQuestionsQuery.data]);

  const onQuizFormSubmit = (data: QuizForm) => {
    // @TODO: will be implemented later
    setIsEdit(false);
  };

  const { isDirty } = form.formState;

  if (getQuizQuestionsQuery.isLoading) {
    return <LoadingSection />;
  }

  return (
    <FormProvider {...form}>
      <QuizModalContextProvider activeQuestionId={activeQuestionId} setActiveQuestionId={setActiveQuestionId}>
        <ModalWrapper
          onClose={() => closeModal({ action: 'CLOSE' })}
          icon={icon}
          title={title}
          subtitle={subtitle}
          headerChildren={
            <Tabs
              wrapperCss={css`
            height: ${modal.HEADER_HEIGHT}px;
          `}
              activeTab={activeTab}
              tabList={[
                {
                  label: __('Questions', 'tutor'),
                  value: 'questions',
                },
                { label: __('Settings', 'tutor'), value: 'settings' },
              ]}
              onChange={(tab) => setActiveTab(tab)}
            />
          }
          actions={
            <>
              <Button
                variant="text"
                size="small"
                onClick={() => {
                  if (isDirty) {
                    setIsConfirmationOpen(true);
                    return;
                  }

                  closeModal();
                }}
                ref={cancelRef}
              >
                {__('Cancel', 'tutor')}
              </Button>
              <Show
                when={activeTab === 'settings'}
                fallback={
                  <Button variant="primary" size="small" onClick={() => setActiveTab('settings')}>
                    Next
                  </Button>
                }
              >
                <Button variant="primary" size="small" onClick={form.handleSubmit(onQuizFormSubmit)}>
                  Save
                </Button>
              </Show>
            </>
          }
        >
          <div css={styles.wrapper}>
            <Show when={activeTab === 'questions'} fallback={<div />}>
              <div css={styles.left}>
                <Show when={activeTab === 'questions'}>
                  <div css={styles.quizTitleWrapper}>
                    <Show
                      when={isEdit}
                      fallback={
                        <div css={styles.quizNameWithButton}>
                          <span css={styles.quizTitle}>{form.getValues('quiz_title')}</span>
                          <Button variant="text" type="button" onClick={() => setIsEdit(true)}>
                            <SVGIcon name="edit" width={24} height={24} />
                          </Button>
                        </div>
                      }
                    >
                      <div css={styles.quizForm}>
                        <Controller
                          control={form.control}
                          name="quiz_title"
                          rules={{ required: __('Quiz title is required', 'tutor') }}
                          render={(controllerProps) => (
                            <FormInput {...controllerProps} placeholder={__('Add quiz title', 'tutor')} />
                          )}
                        />
                        <Controller
                          control={form.control}
                          name="quiz_description"
                          render={(controllerProps) => (
                            <FormTextareaInput
                              {...controllerProps}
                              placeholder={__('Add a summary', 'tutor')}
                              enableResize
                              rows={2}
                            />
                          )}
                        />

                        <div css={styles.quizFormButtonWrapper}>
                          <Button variant="text" type="button" onClick={() => setIsEdit(false)} size="small">
                            {__('Cancel', 'tutor')}
                          </Button>
                          <Button
                            variant="secondary"
                            type="submit"
                            size="small"
                            onClick={form.handleSubmit(onQuizFormSubmit)}
                          >
                            {__('Ok', 'tutor')}
                          </Button>
                        </div>
                      </div>
                    </Show>
                  </div>

                  <QuestionList />
                </Show>
              </div>
            </Show>
            <div css={styles.content({ activeTab })}>
              <Show
                when={activeTab === 'settings'}
                fallback={
                  <Show when={activeQuestionId}>{(activeQuestionId) => <QuestionForm key={activeQuestionId} />}</Show>
                }
              >
                <QuizSettings form={form} />
              </Show>
            </div>
            <Show when={activeTab === 'questions' && activeQuestionId} fallback={<div />}>
              {(activeQuestionId) => (
                <div css={styles.right}>
                  <QuestionCondition key={activeQuestionId} />
                </div>
              )}
            </Show>
          </div>

          <ConfirmationPopover
            isOpen={isConfirmationOpen}
            triggerRef={cancelRef}
            closePopover={() => setIsConfirmationOpen(false)}
            maxWidth="258px"
            title={__('Do you want to cancel the progress without saving?', 'tutor')}
            message="There is unsaved changes."
            animationType={AnimationType.slideUp}
            arrow="top"
            positionModifier={{ top: -50, left: 0 }}
            hideArrow
            confirmButton={{
              text: __('Yes', 'tutor'),
              variant: 'primary',
            }}
            cancelButton={{
              text: __('No', 'tutor'),
              variant: 'text',
            }}
            onConfirmation={() => {
              closeModal();
            }}
          />
        </ModalWrapper>
      </QuizModalContextProvider>
    </FormProvider>
  );
};

export default QuizModal;

const styles = {
  wrapper: css`
    width: 1217px;
    display: grid;
    grid-template-columns: 352px 1fr 352px;
    height: 100%;
  `,
  left: css`
    border-right: 1px solid ${colorTokens.stroke.divider};
    overflow-y: auto;
  `,
  content: ({
    activeTab,
  }: {
    activeTab: QuizTabs;
  }) => css`
    padding: ${spacing[32]} ${spacing[48]} ${spacing[48]} ${spacing[6]};
    overflow-y: auto;

		${
      activeTab === 'settings' &&
      css`
			padding-top: ${spacing[24]};
		`
    }
  `,
  right: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[16]};
    border-left: 1px solid ${colorTokens.stroke.divider};
    overflow-y: auto;
  `,
  quizTitleWrapper: css`
    ${typography.caption()};
    color: ${colorTokens.text.subdued};
    padding: ${spacing[16]} ${spacing[32]} ${spacing[16]} ${spacing[28]};
    border-bottom: 1px solid ${colorTokens.stroke.divider};
  `,
  quizNameWithButton: css`
    display: inline-flex;
    width: 100%;
    transition: all 0.3s ease-in-out;

    button {
      display: none;
    }

    :hover {
      button {
        display: block;
      }
    }
  `,
  quizTitle: css`
    flex: 1;
    padding: ${spacing[8]} ${spacing[16]} ${spacing[8]} ${spacing[8]};
    background-color: ${colorTokens.background.white};
    border-radius: ${borderRadius[6]};
  `,
  quizForm: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[12]};
  `,
  quizFormButtonWrapper: css`
    display: flex;
    justify-content: end;
    margin-top: ${spacing[4]};
    gap: ${spacing[8]};
  `,
};
