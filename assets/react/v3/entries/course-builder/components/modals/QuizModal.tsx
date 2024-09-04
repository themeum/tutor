import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useEffect, useRef, useState } from 'react';
import { Controller, FormProvider } from 'react-hook-form';

import Button from '@Atoms/Button';
import { LoadingOverlay } from '@Atoms/LoadingSpinner';
import SVGIcon from '@Atoms/SVGIcon';
import { useToast } from '@Atoms/Toast';

import FormInput from '@Components/fields/FormInput';
import FormTextareaInput from '@Components/fields/FormTextareaInput';
import type { ModalProps } from '@Components/modals/Modal';
import ModalWrapper from '@Components/modals/ModalWrapper';

import ConfirmationPopover from '@Molecules/ConfirmationPopover';
import Tabs from '@Molecules/Tabs';

import QuestionConditions from '@CourseBuilderComponents/curriculum/QuestionConditions';
import QuestionForm from '@CourseBuilderComponents/curriculum/QuestionForm';
import QuestionList from '@CourseBuilderComponents/curriculum/QuestionList';
import QuizSettings from '@CourseBuilderComponents/curriculum/QuizSettings';
import { QuizModalContextProvider } from '@CourseBuilderContexts/QuizModalContext';
import {
  type QuizForm,
  convertQuizFormDataToPayload,
  convertQuizResponseToFormData,
  useGetQuizDetailsQuery,
  useSaveQuizMutation,
} from '@CourseBuilderServices/quiz';

import { modal } from '@Config/constants';
import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import { styleUtils } from '@Utils/style-utils';

import type { ContentDripType } from '@CourseBuilderServices/course';
import type { ContentType, ID } from '@CourseBuilderServices/curriculum';
import { getCourseId, validateQuizQuestion } from '@CourseBuilderUtils/utils';
import { AnimationType } from '@Hooks/useAnimation';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
import { isDefined } from '@Utils/types';

interface QuizModalProps extends ModalProps {
  quizId?: ID;
  topicId: ID;
  closeModal: (props?: { action: 'CONFIRM' | 'CLOSE' }) => void;
  contentDripType: ContentDripType;
  contentType?: ContentType;
}

export type QuizTimeLimit = 'seconds' | 'minutes' | 'hours' | 'days' | 'weeks';
export type QuizFeedbackMode = 'default' | 'reveal' | 'retry';
export type QuizLayoutView = '' | 'single_question' | 'question_pagination' | 'question_below_each_other';
export type QuizQuestionsOrder = 'rand' | 'sorting' | 'asc' | 'desc';

type QuizTabs = 'details' | 'settings';

const courseId = getCourseId();

const QuizModal = ({
  closeModal,
  icon,
  title,
  subtitle,
  quizId,
  topicId,
  contentDripType,
  contentType,
}: QuizModalProps) => {
  const [isConfirmationOpen, setIsConfirmationOpen] = useState(false);
  const [activeTab, setActiveTab] = useState<QuizTabs>('details');
  const [isEdit, setIsEdit] = useState(!isDefined(quizId));

  const cancelRef = useRef<HTMLButtonElement>(null);

  const saveQuizMutation = useSaveQuizMutation();
  const getQuizDetailsQuery = useGetQuizDetailsQuery(quizId || '');

  const { showToast } = useToast();

  const form = useFormWithGlobalError<QuizForm>({
    defaultValues: {
      quiz_option: {
        time_limit: {
          time_value: 0,
          time_type: 'minutes',
        },
        hide_quiz_time_display: false,
        feedback_mode: 'retry',
        attempts_allowed: 10,
        passing_grade: 80,
        max_questions_for_answer: 10,
        quiz_auto_start: false,
        question_layout_view: '',
        questions_order: 'rand',
        hide_question_number_overview: false,
        short_answer_characters_limit: 200,
        open_ended_answer_characters_limit: 500,
        content_drip_settings: {
          unlock_date: '',
          after_xdays_of_enroll: 0,
          prerequisites: [],
        },
      },
      questions: [],
    },
    shouldFocusError: true,
  });

  const isFormDirty = !!Object.values(form.formState.dirtyFields).some((isFieldDirty) => isFieldDirty);

  // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
  useEffect(() => {
    const handleBeforeUnload = (e: BeforeUnloadEvent) => {
      if (isFormDirty) {
        e.preventDefault();
        return;
      }

      form.reset();
    };
    window.addEventListener('beforeunload', handleBeforeUnload);

    return () => {
      window.removeEventListener('beforeunload', handleBeforeUnload);
    };
  }, [isFormDirty]);

  // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
  useEffect(() => {
    if (!getQuizDetailsQuery.data) {
      return;
    }

    const convertedData = convertQuizResponseToFormData(getQuizDetailsQuery.data);

    form.reset(convertedData);
  }, [getQuizDetailsQuery.data]);

  const onQuizFormSubmit = async (
    data: QuizForm,
    activeQuestionIndex: number,
    setValidationError: React.Dispatch<
      React.SetStateAction<{
        message: string;
        type: 'question' | 'quiz' | 'correct_option' | 'add_option' | 'save_option';
      } | null>
    >,
  ) => {
    if (!data.quiz_title) {
      setActiveTab('details');

      Promise.resolve().then(() => {
        form.trigger('quiz_title');
        form.setFocus('quiz_title');
      });

      return;
    }

    if (data.questions.length === 0) {
      setActiveTab('details');
      showToast({
        message: __('Please add a question', 'tutor'),
        type: 'danger',
      });
      return;
    }

    const validation = validateQuizQuestion(activeQuestionIndex, form);

    if (validation !== true) {
      setValidationError(validation);

      setActiveTab('details');

      return;
    }

    setIsEdit(false);
    const payload = convertQuizFormDataToPayload(data, topicId, contentDripType, courseId);

    const response = await saveQuizMutation.mutateAsync(payload);

    if (response.data) {
      setIsEdit(false);
      closeModal({ action: 'CONFIRM' });
    }
  };

  // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
  useEffect(() => {
    if (isEdit) {
      form.setFocus('quiz_title');
    }
  }, [isEdit]);

  return (
    <FormProvider {...form}>
      <QuizModalContextProvider quizId={quizId || ''} contentType={contentType || 'tutor_quiz'}>
        {(activeQuestionIndex, setValidationError) => (
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
                    label: __('Question Details', 'tutor'),
                    value: 'details',
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
                    if (isFormDirty) {
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
                  when={activeTab === 'settings' || quizId}
                  fallback={
                    <Button variant="primary" size="small" onClick={() => setActiveTab('settings')}>
                      {__('Next', 'tutor')}
                    </Button>
                  }
                >
                  <Button
                    loading={saveQuizMutation.isPending}
                    variant="primary"
                    size="small"
                    onClick={async () => {
                      if (activeQuestionIndex < 0) {
                        await form.handleSubmit((data) =>
                          onQuizFormSubmit(data, activeQuestionIndex, setValidationError),
                        )();
                        return;
                      }

                      await form.handleSubmit((data) =>
                        onQuizFormSubmit(data, activeQuestionIndex, setValidationError),
                      )();
                    }}
                  >
                    {__('Save', 'tutor')}
                  </Button>
                </Show>
              </>
            }
          >
            <div css={styles.wrapper({ activeTab })}>
              <Show when={!getQuizDetailsQuery.isLoading} fallback={<LoadingOverlay />}>
                <Show when={activeTab === 'details'}>
                  <div css={styles.left}>
                    <Show when={activeTab === 'details'}>
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
                                <FormInput
                                  {...controllerProps}
                                  placeholder={__('Add quiz title', 'tutor')}
                                  selectOnFocus
                                />
                              )}
                            />
                            <Controller
                              control={form.control}
                              name="quiz_description"
                              render={(controllerProps) => (
                                <FormTextareaInput
                                  {...controllerProps}
                                  placeholder={__('Add a summary', 'tutor')}
                                  enableResize={false}
                                  rows={2}
                                />
                              )}
                            />

                            <div css={styles.quizFormButtonWrapper}>
                              <Button
                                variant="text"
                                type="button"
                                onClick={() => {
                                  if (!form.watch('quiz_title')) {
                                    closeModal();
                                  }
                                  setIsEdit(false);
                                }}
                                size="small"
                              >
                                {__('Cancel', 'tutor')}
                              </Button>
                              <Button
                                loading={saveQuizMutation.isPending}
                                variant="secondary"
                                type="submit"
                                size="small"
                                onClick={() => {
                                  if (!form.getValues('quiz_title')) {
                                    form.trigger('quiz_title');
                                    return;
                                  }
                                  setIsEdit(false);
                                }}
                              >
                                {__('Ok', 'tutor')}
                              </Button>
                            </div>
                          </div>
                        </Show>
                      </div>

                      <QuestionList isEditing={isEdit} />
                    </Show>
                  </div>
                </Show>
                <div css={styles.content({ activeTab })}>
                  <Show when={activeTab === 'settings'} fallback={<QuestionForm />}>
                    <QuizSettings contentDripType={contentDripType} />
                  </Show>
                </div>
                <Show when={activeTab === 'details' && contentType !== 'tutor_h5p_quiz'}>
                  <div css={styles.right}>
                    <QuestionConditions />
                  </div>
                </Show>
              </Show>
            </div>

            <ConfirmationPopover
              isOpen={isConfirmationOpen}
              triggerRef={cancelRef}
              closePopover={() => setIsConfirmationOpen(false)}
              maxWidth="258px"
              title={__('Do you want to cancel the progress without saving?', 'tutor')}
              message={__('There is unsaved changes.', 'tutor')}
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
                form.reset();
                closeModal();
              }}
            />
          </ModalWrapper>
        )}
      </QuizModalContextProvider>
    </FormProvider>
  );
};

export default QuizModal;

const styles = {
  wrapper: ({
    activeTab,
  }: {
    activeTab: QuizTabs;
  }) => css`
    width: 1217px;
    display: grid;
    grid-template-columns: ${activeTab === 'details' ? '352px 1fr 352px' : '1fr'};
    height: 100%;

  `,
  left: css`
    border-right: 1px solid ${colorTokens.stroke.divider};
  `,
  content: ({
    activeTab,
  }: {
    activeTab: QuizTabs;
  }) => css`
    ${styleUtils.overflowYAuto};
    padding: ${spacing[32]} 0 ${spacing[48]} ${spacing[6]};

		${
      activeTab === 'settings' &&
      css`
			  padding-top: ${spacing[24]};
        padding-inline: 352px 352px; // 352px is the width of the left and right side
		  `
    }
  `,
  right: css`
    ${styleUtils.overflowYAuto};
    ${styleUtils.display.flex('column')};
    gap: ${spacing[16]};
    border-left: 1px solid ${colorTokens.stroke.divider};
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
