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
import { Question } from '@CourseBuilderComponents/curriculum/Question';
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
import { Controller, useWatch } from 'react-hook-form';
import TrueFalse from '@CourseBuilderComponents/curriculum/question-types/TrueFalse';
import { moveTo } from '@Utils/util';

interface QuizModalProps extends ModalProps {
  closeModal: (props?: { action: 'CONFIRM' | 'CLOSE' }) => void;
}

interface QuizForm {
  quiz_title: string;
  quiz_description: string;
  question_type: QuizQuestionType;
  answer_required: boolean;
  randomize: boolean;
  point: number;
  display_point: boolean;
}

const questionTypeOptions: Option<QuizQuestionType>[] = [
  {
    label: __('True/ False', 'tutor'),
    value: 'true-false',
    icon: 'quizTrueFalse',
  },
  {
    label: __('Single Choice', 'tutor'),
    value: 'single-choice',
    icon: 'quizSingleChoice',
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
    icon: 'quizMatching',
  },
  {
    label: __('Image Matching', 'tutor'),
    value: 'image-matching',
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

const QuizModal = ({ closeModal, icon, title, subtitle }: QuizModalProps) => {
  const [isConfirmationOpen, setIsConfirmationOpen] = useState(false);
  const [selectedQuestionId, setSelectedQuestionId] = useState<number | null>(null);
  const [activeSortId, setActiveSortId] = useState<UniqueIdentifier | null>(null);
  const [questionsData, setQuestionsData] = useState<QuizQuestion[]>([]);
  const [activeQuestionId, setActiveQuestionId] = useState<number | null>(null);
  const [activeTab, setActiveTab] = useState<'questions' | 'settings'>('questions');
  // @TODO: isEdit will be calculated based on the quiz data form API
  const [isEdit, setIsEdit] = useState(true);

  const cancelRef = useRef<HTMLButtonElement>(null);

  const form = useFormWithGlobalError<QuizForm>({
    defaultValues: {
      question_type: 'true-false',
      answer_required: false,
      randomize: false,
      point: 0,
      display_point: true,
    },
  });

  const sensors = useSensors(
    useSensor(PointerSensor, {
      activationConstraint: {
        distance: 10,
      },
    }),
    useSensor(KeyboardSensor, { coordinateGetter: sortableKeyboardCoordinates })
  );

  const questionType = useWatch({
    control: form.control,
    name: 'question_type',
  });

  const activeSortItem = useMemo(() => {
    if (!activeSortId) {
      return null;
    }

    return questionsData.find((item) => item.ID === activeSortId);
  }, [activeSortId, questionsData]);

  const questionTypeForm = {
    'true-false': <TrueFalse />,
    'single-choice': 'Single Choice',
    'multiple-choice': 'Multiple Choice',
    'open-ended': 'Open Ended/ Essay',
    'fill-in-the-blanks': 'Fill in the Blanks',
    'short-answer': 'Short Answer',
    matching: 'Matching',
    'image-matching': 'Image Matching',
    'image-answering': 'Image Answering',
    ordering: 'Ordering',
  };

  const getQuizQuestionsQuery = useGetQuizQuestionsQuery();

  const onQuizFormSubmit = (data: QuizForm) => {
    // @TODO: will be implemented later
    alert(JSON.stringify(data, null, 2));
    setIsEdit(false);
  };

  // @TODO: Remove this when the API is ready
  useEffect(() => {
    if (getQuizQuestionsQuery.data) {
      return setQuestionsData(getQuizQuestionsQuery.data);
    }
  }, [getQuizQuestionsQuery.data]);

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
        <div css={styles.left}>
          <div css={styles.quizTitleWrapper}>
            <Show
              when={isEdit}
              fallback={
                <div css={styles.quizNameWithButton}>
                  <span css={styles.quizTitle}>General knowledge</span>
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
                  <Button variant="secondary" type="submit" size="small" onClick={form.handleSubmit(onQuizFormSubmit)}>
                    {__('Ok', 'tutor')}
                  </Button>
                </div>
              </div>
            </Show>
          </div>
          <div css={styles.questionsLabel}>
            <span>Questions</span>
            <button type="button" onClick={() => alert('@TODO: will be implemented later')}>
              <SVGIcon name="plusSquareBrand" />
            </button>
          </div>

          <div css={styles.questionList}>
            <Show when={questionsData.length > 0} fallback={<div>No question!</div>}>
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
                    const activeIndex = questionsData.findIndex((item) => item.ID === active.id);
                    const overIndex = questionsData.findIndex((item) => item.ID === over.id);

                    setQuestionsData((previous) => {
                      return moveTo(previous, activeIndex, overIndex);
                    });
                  }

                  setActiveSortId(null);
                }}
              >
                <SortableContext
                  items={questionsData.map((item) => ({ ...item, id: item.ID }))}
                  strategy={verticalListSortingStrategy}
                >
                  <For each={questionsData}>
                    {(question, index) => (
                      <Question
                        key={question.ID}
                        question={question}
                        index={index}
                        activeQuestionId={activeQuestionId}
                        setActiveQuestionId={setActiveQuestionId}
                        selectedQuestionId={selectedQuestionId}
                        setSelectedQuestionId={setSelectedQuestionId}
                      />
                    )}
                  </For>
                </SortableContext>

                {createPortal(
                  <DragOverlay>
                    <Show when={activeSortItem}>
                      {(item) => {
                        const index = questionsData.findIndex((question) => question.ID === item.ID);
                        return (
                          <Question
                            key={item.ID}
                            question={item}
                            index={index}
                            activeQuestionId={activeQuestionId}
                            setActiveQuestionId={setActiveQuestionId}
                            selectedQuestionId={selectedQuestionId}
                            setSelectedQuestionId={setSelectedQuestionId}
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
        </div>

        <div css={styles.content}>{questionTypeForm[questionType]}</div>

        <div css={styles.right}>
          <div css={styles.questionTypeWrapper}>
            <Controller
              control={form.control}
              name="question_type"
              render={(controllerProps) => (
                <FormSelectInput {...controllerProps} label="Question Type" options={questionTypeOptions} />
              )}
            />
          </div>
          <div css={styles.conditions}>
            <p>{__('Conditions', 'tutor')}</p>
            <div css={styles.conditionControls}>
              <Controller
                control={form.control}
                name="answer_required"
                render={(controllerProps) => <FormSwitch {...controllerProps} label={__('Answer Required', 'tutor')} />}
              />
              <Controller
                control={form.control}
                name="randomize"
                render={(controllerProps) => (
                  <FormSwitch {...controllerProps} label={__('Randomize Choice', 'tutor')} />
                )}
              />
              <Controller
                control={form.control}
                name="point"
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
                name="display_point"
                render={(controllerProps) => <FormSwitch {...controllerProps} label={__('Display Points', 'tutor')} />}
              />
            </div>
          </div>
        </div>
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
  content: css`
    padding: ${spacing[32]} ${spacing[48]} ${spacing[48]} ${spacing[6]};
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
