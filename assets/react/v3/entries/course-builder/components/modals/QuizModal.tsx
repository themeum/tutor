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
import QuestionConditions from '@CourseBuilderComponents/curriculum/QuestionConditions';
import QuestionForm from '@CourseBuilderComponents/curriculum/QuestionForm';
import { type QuizQuestion, useGetQuizDetailsQuery, useSaveQuizMutation } from '@CourseBuilderServices/quiz';
import QuestionList from '@CourseBuilderComponents/curriculum/QuestionList';

import { modal } from '@Config/constants';
import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import { styleUtils } from '@Utils/style-utils';

import { AnimationType } from '@Hooks/useAnimation';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
import type { ID } from '@CourseBuilderServices/curriculum';
import type { ContentDripType } from '@CourseBuilderServices/course';
import { isDefined } from '@Utils/types';

interface QuizModalProps extends ModalProps {
  quizId?: ID;
  topicId: ID;
  closeModal: (props?: { action: 'CONFIRM' | 'CLOSE' }) => void;
  contentDripType: ContentDripType;
}

export type QuizTimeLimit = 'seconds' | 'minutes' | 'hours' | 'days' | 'weeks';
export type QuizFeedbackMode = 'default' | 'reveal' | 'retry';
export type QuizLayoutView = '' | 'single_question' | 'question_pagination' | 'question_below_each_other';
export type QuizQuestionsOrder = 'rand' | 'sorting' | 'asc' | 'desc';

export interface QuizForm {
  quiz_title: string;
  quiz_description: string;
  quiz_option: {
    time_limit: {
      time_value: number;
      time_type: QuizTimeLimit;
    };
    hide_quiz_time_display: boolean;
    feedback_mode: QuizFeedbackMode;
    attempts_allowed: number;
    passing_grade: number;
    max_questions_for_answer: number;
    available_after_days: number;
    quiz_auto_start: boolean;
    question_layout_view: QuizLayoutView;
    questions_order: QuizQuestionsOrder;
    hide_question_number_overview: boolean;
    short_answer_characters_limit: number;
    open_ended_answer_characters_limit: number;
    content_drip_settings: {
      unlock_date: string;
      after_xdays_of_enroll: number;
      prerequisites: [];
    };
  };
  questions: QuizQuestion[];
}

type QuizTabs = 'questions' | 'settings';

const QuizModal = ({ closeModal, icon, title, subtitle, quizId, topicId, contentDripType }: QuizModalProps) => {
  const [isConfirmationOpen, setIsConfirmationOpen] = useState(false);
  const [activeTab, setActiveTab] = useState<QuizTabs>('questions');
  const [localQuizId, setLocalQuizId] = useState<ID>(quizId || '');

  const cancelRef = useRef<HTMLButtonElement>(null);

  const saveQuizMutation = useSaveQuizMutation();
  const getQuizDetailsQuery = useGetQuizDetailsQuery(localQuizId);

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
        quiz_auto_start: false,
        question_layout_view: '',
        questions_order: 'rand',
        hide_question_number_overview: false,
        short_answer_characters_limit: 0,
        open_ended_answer_characters_limit: 0,
        content_drip_settings: {
          unlock_date: '',
          after_xdays_of_enroll: 0,
          prerequisites: [],
        },
      },
      questions: [],
    },
  });

  // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
  useEffect(() => {
    if (!getQuizDetailsQuery.data) {
      return;
    }

    form.reset({
      quiz_title: getQuizDetailsQuery.data.post_title || '',
      quiz_description: getQuizDetailsQuery.data.post_content || '',
      quiz_option: {
        time_limit: {
          time_value: getQuizDetailsQuery.data.quiz_option.time_limit.time_value || 0,
          time_type: getQuizDetailsQuery.data.quiz_option.time_limit.time_type || 'minutes',
        },
        feedback_mode: getQuizDetailsQuery.data.quiz_option.feedback_mode || 'default',
        attempts_allowed: getQuizDetailsQuery.data.quiz_option.attempts_allowed || 0,
        passing_grade: getQuizDetailsQuery.data.quiz_option.passing_grade || 0,
        max_questions_for_answer: getQuizDetailsQuery.data.quiz_option.max_questions_for_answer || 0,
        question_layout_view: getQuizDetailsQuery.data.quiz_option.question_layout_view || '',
        questions_order: getQuizDetailsQuery.data.quiz_option.questions_order || 'rand',
        short_answer_characters_limit: getQuizDetailsQuery.data.quiz_option.short_answer_characters_limit || 0,
        open_ended_answer_characters_limit:
          getQuizDetailsQuery.data.quiz_option.open_ended_answer_characters_limit || 0,
      },
    });
  }, [getQuizDetailsQuery.data]);

  // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
  useEffect(() => {
    if (localQuizId) {
      getQuizDetailsQuery.refetch();
    }
  }, [localQuizId]);

  const [isEdit, setIsEdit] = useState(!isDefined(quizId));

  const onQuizFormSubmit = async (data: QuizForm) => {
    const response = await saveQuizMutation.mutateAsync({
      topic_id: topicId,
      quiz_title: data.quiz_title,
      quiz_description: data.quiz_description,
      'quiz_option[time_limit][time_type]': data.quiz_option.time_limit.time_type,
      'quiz_option[time_limit][time_value]': data.quiz_option.time_limit.time_value,
      'quiz_option[hide_quiz_time_display]': data.quiz_option.hide_quiz_time_display ? 1 : 0,
      'quiz_option[feedback_mode]': data.quiz_option.feedback_mode,
      'quiz_option[attempts_allowed]': data.quiz_option.attempts_allowed,
      'quiz_option[passing_grade]': data.quiz_option.passing_grade,
      'quiz_option[max_questions_for_answer]': data.quiz_option.max_questions_for_answer,
      // 'quiz_option[available_after_days]': data.quiz_option.available_after_days,
      // 'quiz_option[quiz_auto_start]': data.quiz_option.quiz_auto_start,
      'quiz_option[question_layout_view]': data.quiz_option.question_layout_view,
      'quiz_option[questions_order]': data.quiz_option.questions_order,
      // 'quiz_option[hide_question_number_overview]': data.quiz_option.hide_question_number_overview,
      'quiz_option[short_answer_characters_limit]': data.quiz_option.short_answer_characters_limit,
      'quiz_option[open_ended_answer_characters_limit]': data.quiz_option.open_ended_answer_characters_limit,
      // questions: data.questions,
    });

    if (response.data) {
      setIsEdit(false);
      setLocalQuizId(response.data);
    }
  };

  const { isDirty } = form.formState;

  if (getQuizDetailsQuery.isLoading) {
    return <LoadingSection />;
  }

  return (
    <FormProvider {...form}>
      <QuizModalContextProvider>
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
                    {__('Next', 'tutor')}
                  </Button>
                }
              >
                <Button variant="primary" size="small" onClick={form.handleSubmit(onQuizFormSubmit)}>
                  {__('Save', 'tutor')}
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

                  <QuestionList quizId={localQuizId} />
                </Show>
              </div>
            </Show>
            <div css={styles.content({ activeTab })}>
              <Show when={activeTab === 'settings'} fallback={<QuestionForm />}>
                <QuizSettings contentDripType={contentDripType} />
              </Show>
            </div>
            <Show when={activeTab === 'questions'} fallback={<div />}>
              <div css={styles.right}>
                <QuestionConditions />
              </div>
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
