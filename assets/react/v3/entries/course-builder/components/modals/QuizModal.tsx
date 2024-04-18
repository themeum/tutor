import Button from '@Atoms/Button';
import { LoadingSection } from '@Atoms/LoadingSpinner';
import SVGIcon from '@Atoms/SVGIcon';
import FormInput from '@Components/fields/FormInput';
import FormSelectInput from '@Components/fields/FormSelectInput';
import FormSwitch from '@Components/fields/FormSwitch';
import FormTextareaInput from '@Components/fields/FormTextareaInput';
import type { ModalProps } from '@Components/modals/Modal';
import ModalWrapper from '@Components/modals/ModalWrapper';
import { modal } from '@Config/constants';
import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import For from '@Controls/For';
import Show from '@Controls/Show';
import Question from '@CourseBuilderComponents/curriculum/Question';
import { type QuizQuestion, type QuizQuestionType, useGetQuizQuestionsQuery } from '@CourseBuilderServices/quiz';
import { AnimationType } from '@Hooks/useAnimation';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
import ConfirmationPopover from '@Molecules/ConfirmationPopover';
import Tabs from '@Molecules/Tabs';
import { styleUtils } from '@Utils/style-utils';
import type { Option } from '@Utils/types';
import {
  DndContext,
  DragOverlay,
  KeyboardSensor,
  PointerSensor,
  type UniqueIdentifier,
  closestCenter,
  useSensor,
  useSensors,
} from '@dnd-kit/core';
import { restrictToVerticalAxis, restrictToWindowEdges } from '@dnd-kit/modifiers';
import { SortableContext, sortableKeyboardCoordinates, verticalListSortingStrategy } from '@dnd-kit/sortable';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useEffect, useMemo, useRef, useState } from 'react';
import { createPortal } from 'react-dom';
import { Controller, useFieldArray } from 'react-hook-form';
import QuizSettings from '@CourseBuilderComponents/curriculum/QuizSettings';
import TrueFalse from '@CourseBuilderComponents/curriculum/question-types/TrueFalse';
import MultipleChoice from '@CourseBuilderComponents/curriculum/question-types/MultipleChoice';
import Matching from '@CourseBuilderComponents/curriculum/question-types/Matching';
import ImageAnswering from '@CourseBuilderComponents/curriculum/question-types/ImageAnswering';
import OpenEnded from '@CourseBuilderComponents/curriculum/question-types/OpenEnded';
import FillinTheBlanks from '@CourseBuilderComponents/curriculum/question-types/FillinTheBlanks';
import FormQuestionTitle from '@Components/fields/FormQuestionTitle';
import FormQuestionDescription from '@Components/fields/FormQuestionDescription';
import { nanoid } from '@Utils/util';

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

const questionTypeOptions: Option<QuizQuestionType>[] = [
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

type QuizTabs = 'questions' | 'settings';

const QuizModal = ({ closeModal, icon, title, subtitle }: QuizModalProps) => {
  const [isConfirmationOpen, setIsConfirmationOpen] = useState(false);
  const [selectedQuestionId, setSelectedQuestionId] = useState<string | null>(null);
  const [activeSortId, setActiveSortId] = useState<UniqueIdentifier | null>(null);
  const [activeQuestionId, setActiveQuestionId] = useState<string | null>(null);
  const [activeTab, setActiveTab] = useState<QuizTabs>('questions');
  // @TODO: isEdit will be calculated based on the quiz data form API
  const [isEdit, setIsEdit] = useState(true);

  const cancelRef = useRef<HTMLButtonElement>(null);

  const getQuizQuestionsQuery = useGetQuizQuestionsQuery();

  useEffect(() => {
    if (getQuizQuestionsQuery.data) {
      setActiveQuestionId(getQuizQuestionsQuery.data[0].ID);
    }
  }, [getQuizQuestionsQuery.data]);

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

  const activeQuestionIdIndex = form.watch('questions').findIndex((question) => question.ID === activeQuestionId);

  const {
    append: addQuestion,
    remove: removeQustion,
    move: moveQuestion,
    fields: questionFields,
  } = useFieldArray({
    control: form.control,
    name: 'questions',
  });

  const sensors = useSensors(
    useSensor(PointerSensor, {
      activationConstraint: {
        distance: 10,
      },
    }),
    useSensor(KeyboardSensor, { coordinateGetter: sortableKeyboardCoordinates })
  );

  const activeSortItem = useMemo(() => {
    if (!activeSortId) {
      return null;
    }

    return questionFields.find((item) => item.ID === activeSortId);
  }, [activeSortId, questionFields]);

  const questionTypeForm = {
    'true-false': <TrueFalse key={activeQuestionId} form={form} activeQuestionIndex={activeQuestionIdIndex} />,
    'multiple-choice': (
      <MultipleChoice key={activeQuestionId} form={form} activeQuestionIndex={activeQuestionIdIndex} />
    ),
    'open-ended': <OpenEnded key={activeQuestionId} />,
    'fill-in-the-blanks': (
      <FillinTheBlanks key={activeQuestionId} form={form} activeQuestionIndex={activeQuestionIdIndex} />
    ),
    'short-answer': <OpenEnded key={activeQuestionId} />,
    matching: <Matching key={activeQuestionId} form={form} activeQuestionIndex={activeQuestionIdIndex} />,
    'image-answering': (
      <ImageAnswering key={activeQuestionId} form={form} activeQuestionIndex={activeQuestionIdIndex} />
    ),
    ordering: <MultipleChoice key={activeQuestionId} form={form} activeQuestionIndex={activeQuestionIdIndex} />,
  } as const;

  const onQuizFormSubmit = (data: QuizForm) => {
    // @TODO: will be implemented later
    setIsEdit(false);
  };

  const { isDirty } = form.formState;

  if (getQuizQuestionsQuery.isLoading) {
    return <LoadingSection />;
  }

  return (
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
              <div css={styles.questionsLabel}>
                <span>{__('Questions', 'tutor')}</span>
                <button
                  type="button"
                  onClick={() => {
                    const questionId = nanoid();
                    addQuestion({
                      ID: questionId,
                      title: 'Write anything here..',
                      description: '',
                      type: 'true-false',
                      answer_required: true,
                      image_matching: false,
                      muliple_correct_answer: false,
                      options: [
                        {
                          ID: nanoid(),
                          title: 'True',
                          isCorrect: true,
                        },
                        {
                          ID: nanoid(),
                          title: 'False',
                          isCorrect: false,
                        },
                      ],
                      question_mark: 1,
                      randomize_question: false,
                      show_question_mark: false,
                    });
                    setActiveQuestionId(questionId);
                  }}
                >
                  <SVGIcon name="plusSquareBrand" />
                </button>
              </div>

              <div css={styles.questionList}>
                <Show when={questionFields.length > 0} fallback={<div>{__('No questions added yet.', 'tutor')}</div>}>
                  <DndContext
                    sensors={sensors}
                    collisionDetection={closestCenter}
                    modifiers={[restrictToVerticalAxis, restrictToWindowEdges]}
                    onDragStart={(event) => {
                      setActiveSortId(event.active.id);
                    }}
                    onDragEnd={(event) => {
                      const { active, over } = event;
                      if (!over) {
                        return;
                      }

                      if (active.id !== over.id) {
                        const activeIndex = questionFields.findIndex((item) => item.ID === active.id);
                        const overIndex = questionFields.findIndex((item) => item.ID === over.id);

                        moveQuestion(activeIndex, overIndex);
                      }

                      setActiveSortId(null);
                    }}
                  >
                    <SortableContext
                      items={questionFields.map((item) => ({ ...item, id: item.ID }))}
                      strategy={verticalListSortingStrategy}
                    >
                      <For each={form.getValues('questions')}>
                        {(question, index) => (
                          <Question
                            key={question.ID}
                            question={question}
                            index={index}
                            activeQuestionId={activeQuestionId}
                            setActiveQuestionId={setActiveQuestionId}
                            selectedQuestionId={selectedQuestionId}
                            setSelectedQuestionId={setSelectedQuestionId}
                            onRemoveQuestion={() => removeQustion(index)}
                          />
                        )}
                      </For>
                    </SortableContext>

                    {createPortal(
                      <DragOverlay>
                        <Show when={activeSortItem}>
                          {(item) => {
                            const index = questionFields.findIndex((question) => question.ID === item.ID);
                            return (
                              <Question
                                key={item.ID}
                                question={item}
                                index={index}
                                activeQuestionId={activeQuestionId}
                                setActiveQuestionId={setActiveQuestionId}
                                selectedQuestionId={selectedQuestionId}
                                setSelectedQuestionId={setSelectedQuestionId}
                                onRemoveQuestion={() => removeQustion(index)}
                              />
                            );
                          }}
                        </Show>
                      </DragOverlay>,
                      document.body
                    )}
                  </DndContext>
                </Show>
              </div>
            </Show>
          </div>
        </Show>
        <div css={styles.content({ activeTab })}>
          <Show
            when={activeTab === 'settings'}
            fallback={
              <Show when={activeQuestionId}>
                <div css={styles.questionForm}>
                  <div css={styles.questionWithIndex}>
                    <div css={styles.questionIndex}>{activeQuestionIdIndex + 1}.</div>
                    <div css={styles.questionTitleAndDesc}>
                      <Controller
                        key={`${activeQuestionId}-title`}
                        control={form.control}
                        name={`questions.${activeQuestionIdIndex}.title`}
                        render={(controllerProps) => (
                          <FormQuestionTitle
                            {...controllerProps}
                            placeholder={__('Write your question here..', 'tutor')}
                          />
                        )}
                      />

                      <Controller
                        key={`${activeQuestionId}-description`}
                        control={form.control}
                        name={`questions.${activeQuestionIdIndex}.description`}
                        render={(controllerProps) => (
                          <FormQuestionDescription
                            {...controllerProps}
                            placeholder={__('Description (optional)', 'tutor')}
                            enableResize
                            rows={2}
                          />
                        )}
                      />
                    </div>
                  </div>

                  {questionTypeForm[form.watch(`questions.${activeQuestionIdIndex}.type`)]}
                </div>
              </Show>
            }
          >
            <QuizSettings form={form} />
          </Show>
        </div>
        <Show when={activeTab === 'questions'} fallback={<div />}>
          <div css={styles.right}>
            <div css={styles.questionTypeWrapper}>
              <Controller
                key={`${activeQuestionId}-type`}
                control={form.control}
                name={`questions.${activeQuestionIdIndex}.type`}
                render={(controllerProps) => (
                  <FormSelectInput {...controllerProps} label="Question Type" options={questionTypeOptions} />
                )}
              />
            </div>
            <div css={styles.conditions}>
              <p>{__('Conditions', 'tutor')}</p>
              <div css={styles.conditionControls}>
                <Show when={form.watch(`questions.${activeQuestionIdIndex}.type`) === 'multiple-choice'}>
                  <Controller
                    key={`${activeQuestionId}-muliple_correct_answer`}
                    control={form.control}
                    name={`questions.${activeQuestionIdIndex}.muliple_correct_answer`}
                    render={(controllerProps) => (
                      <FormSwitch {...controllerProps} label={__('Multiple Correct Answer', 'tutor')} />
                    )}
                  />
                </Show>
                <Show when={form.watch(`questions.${activeQuestionIdIndex}.type`) === 'matching'}>
                  <Controller
                    key={`${activeQuestionId}-image_matching`}
                    control={form.control}
                    name={`questions.${activeQuestionIdIndex}.image_matching`}
                    render={(controllerProps) => (
                      <FormSwitch {...controllerProps} label={__('Image Matching', 'tutor')} />
                    )}
                  />
                </Show>
                <Controller
                  key={`${activeQuestionId}-answer_required`}
                  control={form.control}
                  name={`questions.${activeQuestionIdIndex}.answer_required`}
                  render={(controllerProps) => (
                    <FormSwitch {...controllerProps} label={__('Answer Required', 'tutor')} />
                  )}
                />
                <Controller
                  key={`${activeQuestionId}-randomize_question`}
                  control={form.control}
                  name={`questions.${activeQuestionIdIndex}.randomize_question`}
                  render={(controllerProps) => (
                    <FormSwitch {...controllerProps} label={__('Randomize Choice', 'tutor')} />
                  )}
                />
                <Controller
                  key={`${activeQuestionId}-question_mark`}
                  control={form.control}
                  name={`questions.${activeQuestionIdIndex}.question_mark`}
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
                  key={`${activeQuestionId}-show_question_mark`}
                  control={form.control}
                  name={`questions.${activeQuestionIdIndex}.show_question_mark`}
                  render={(controllerProps) => (
                    <FormSwitch {...controllerProps} label={__('Display Points', 'tutor')} />
                  )}
                />
              </div>
            </div>
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
  `,
  content: ({
    activeTab,
  }: {
    activeTab: QuizTabs;
  }) => css`
    padding: ${spacing[32]} ${spacing[48]} ${spacing[48]} ${spacing[6]};

		${
      activeTab === 'settings' &&
      css`
			padding-top: ${spacing[24]};
		`
    }
  `,
  questionForm: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[16]};
  `,
  questionWithIndex: css`
    ${styleUtils.display.flex('row')};
    align-items: flex-start;
    padding-left: 42px; // This is outside of the design
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
  right: css`
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
  questionsLabel: css`
    display: flex;
    gap: ${spacing[4]};
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid ${colorTokens.stroke.divider};
    padding: ${spacing[16]} ${spacing[16]} ${spacing[16]} ${spacing[28]};

    ${typography.caption('medium')};
    color: ${colorTokens.text.subdued};

    button {
      ${styleUtils.resetButton};
      width: 32px;
      height: 32px;

      svg {
        color: ${colorTokens.action.primary.default};
        width: 100%;
        height: 100%;
      }
    }
  `,
  questionList: css`
    padding: ${spacing[8]} ${spacing[20]};
  `,
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
