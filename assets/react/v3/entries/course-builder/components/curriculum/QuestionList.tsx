import {
  closestCenter,
  DndContext,
  type DragEndEvent,
  DragOverlay,
  KeyboardSensor,
  PointerSensor,
  type UniqueIdentifier,
  useSensor,
  useSensors,
} from '@dnd-kit/core';
import { restrictToWindowEdges } from '@dnd-kit/modifiers';
import { SortableContext, sortableKeyboardCoordinates, verticalListSortingStrategy } from '@dnd-kit/sortable';
import { css } from '@emotion/react';
import { __, sprintf } from '@wordpress/i18n';
import { useEffect, useMemo, useRef, useState } from 'react';
import { createPortal } from 'react-dom';
import { useFieldArray, useFormContext } from 'react-hook-form';

import Button from '@TutorShared/atoms/Button';
import ProBadge from '@TutorShared/atoms/ProBadge';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import Popover from '@TutorShared/molecules/Popover';

import Question from '@CourseBuilderComponents/curriculum/Question';
import H5PContentListModal from '@TutorShared/components/modals/H5PContentListModal';
import { useModal } from '@TutorShared/components/modals/Modal';

import CollectionListModal from '@CourseBuilderComponents/modals/ContentBankContentSelectModal';
import { useQuizModalContext } from '@CourseBuilderContexts/QuizModalContext';
import { type QuizForm } from '@CourseBuilderServices/quiz';
import { tutorConfig } from '@TutorShared/config/config';
import { Addons, CURRENT_VIEWPORT } from '@TutorShared/config/constants';
import { borderRadius, Breakpoint, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import For from '@TutorShared/controls/For';
import Show from '@TutorShared/controls/Show';
import { AnimationType } from '@TutorShared/hooks/useAnimation';
import { POPOVER_PLACEMENTS } from '@TutorShared/hooks/usePortalPopover';
import { type IconCollection } from '@TutorShared/icons/types';
import { convertedQuestion, validateQuizQuestion } from '@TutorShared/utils/quiz';
import { styleUtils } from '@TutorShared/utils/style-utils';
import {
  type ContentBankContent,
  type H5PContent,
  QuizDataStatus,
  type QuizQuestion,
  type QuizQuestionType,
} from '@TutorShared/utils/types';
import { isAddonEnabled, nanoid, noop } from '@TutorShared/utils/util';

const questionTypeOptions: {
  label: string;
  value: QuizQuestionType;
  icon: IconCollection;
  isPro: boolean;
}[] = [
  {
    label: __('True/False', 'tutor'),
    value: 'true_false',
    icon: 'quizTrueFalse',
    isPro: false,
  },
  {
    label: __('Multiple Choice', 'tutor'),
    value: 'multiple_choice',
    icon: 'quizMultiChoice',
    isPro: false,
  },
  {
    label: __('Open Ended/Essay', 'tutor'),
    value: 'open_ended',
    icon: 'quizEssay',
    isPro: false,
  },
  {
    label: __('Fill in the Blanks', 'tutor'),
    value: 'fill_in_the_blank',
    icon: 'quizFillInTheBlanks',
    isPro: false,
  },
  {
    label: __('Short Answer', 'tutor'),
    value: 'short_answer',
    icon: 'quizShortAnswer',
    isPro: true,
  },
  {
    label: __('Matching', 'tutor'),
    value: 'matching',
    icon: 'quizImageMatching',
    isPro: true,
  },
  {
    label: __('Image Answering', 'tutor'),
    value: 'image_answering',
    icon: 'quizImageAnswer',
    isPro: true,
  },
  {
    label: __('Ordering', 'tutor'),
    value: 'ordering',
    icon: 'quizOrdering',
    isPro: true,
  },
];

const isTutorPro = !!tutorConfig.tutor_pro_url;

const QuestionList = ({ isEditing }: { isEditing: boolean }) => {
  const [activeSortId, setActiveSortId] = useState<UniqueIdentifier | null>(null);
  const [isOpen, setIsOpen] = useState(false);
  const questionListRef = useRef<HTMLDivElement>(null);
  const addButtonRef = useRef<HTMLButtonElement>(null);

  const form = useFormContext<QuizForm>();
  const { contentType, activeQuestionIndex, validationError, setActiveQuestionId, setValidationError } =
    useQuizModalContext();
  const {
    remove: removeQuestion,
    append: appendQuestion,
    insert: insertQuestion,
    move: moveQuestion,
    fields: questionFields,
  } = useFieldArray({
    control: form.control,
    name: 'questions',
  });

  const { showModal } = useModal();
  const sensors = useSensors(
    useSensor(PointerSensor, {
      activationConstraint: {
        distance: 10,
      },
    }),
    useSensor(KeyboardSensor, { coordinateGetter: sortableKeyboardCoordinates }),
  );

  const activeSortItem = useMemo(() => {
    if (!activeSortId) {
      return null;
    }

    return questionFields.find((item) => item.question_id === activeSortId);
  }, [activeSortId, questionFields]);

  const questions = form.watch('questions') || [];
  const activeQuestion = form.getValues(`questions.${activeQuestionIndex}` as 'questions.0');

  const handleAddQuestion = (questionType: QuizQuestionType, content?: H5PContent) => {
    const validation = validateQuizQuestion(activeQuestion);
    if (validation !== true) {
      setValidationError(validation);
      setIsOpen(false);
      return;
    }

    const questionId = nanoid();
    appendQuestion({
      _data_status: QuizDataStatus.NEW,
      question_id: questionId,
      /* translators: %d is the question number */
      question_title:
        questionType === 'h5p' ? content?.title : sprintf(__('Question %d', 'tutor'), questionFields.length + 1),
      question_description: questionType === 'h5p' ? content?.id : '',
      question_type: questionType,
      question_answers:
        questionType === 'true_false'
          ? [
              {
                answer_id: nanoid(),
                _data_status: QuizDataStatus.NEW,
                is_saved: true,
                answer_title: __('True', 'tutor'),
                is_correct: '1',
                answer_order: 1,
                answer_two_gap_match: '',
                answer_view_format: 'text',
                belongs_question_id: questionId,
                belongs_question_type: 'true_false',
              },
              {
                answer_id: nanoid(),
                is_saved: true,
                _data_status: QuizDataStatus.NEW,
                answer_title: __('False', 'tutor'),
                is_correct: '0',
                answer_order: 2,
                answer_two_gap_match: '',
                answer_view_format: 'text',
                belongs_question_id: questionId,
                belongs_question_type: 'true_false',
              },
            ]
          : questionType === 'fill_in_the_blank'
            ? [
                {
                  _data_status: QuizDataStatus.NEW,
                  is_saved: false,
                  answer_id: nanoid(),
                  answer_title: '',
                  belongs_question_id: questionId,
                  belongs_question_type: 'fill_in_the_blank',
                  answer_two_gap_match: '',
                  answer_view_format: '',
                  answer_order: 0,
                  is_correct: '0',
                },
              ]
            : [],
      answer_explanation: '',
      question_mark: 1,
      question_order: questionFields.length + 1,
      question_settings: {
        answer_required: false,
        question_mark: contentType === 'tutor_h5p_quiz' ? 0 : 1,
        question_type: questionType,
        randomize_question: false,
        show_question_mark: false,
      },
    } as QuizQuestion);
    setValidationError(null);
    setActiveQuestionId(questionId);
    setIsOpen(false);
  };

  const handleAddContentBankBulkQuestions = (
    contents: (ContentBankContent & {
      question: QuizQuestion;
    })[],
  ) => {
    const validation = validateQuizQuestion(activeQuestion);
    if (validation !== true) {
      setValidationError(validation);
      setIsOpen(false);
      return;
    }

    const convertedQuestions: QuizQuestion[] = contents.map((content) => {
      // Converts a ContentBankContent question to the QuizQuestion format expected by the quiz builder.
      const question = convertedQuestion(content.question);
      return {
        ...question,
        _data_status: QuizDataStatus.NEW,
        is_cb_question: true,
        // this is to ensure unique question_id for each question
        question_id: `${question.question_id}-${nanoid()}`,
        question_answers: question.question_answers.map((answer) => ({
          ...answer,
          _data_status: QuizDataStatus.NEW,
        })),
      };
    });

    appendQuestion(convertedQuestions);
  };

  const handleH5PBulkQuestion = (contents: H5PContent[]) => {
    for (const content of contents) {
      handleAddQuestion('h5p', content);
    }
  };

  const handleDuplicateQuestion = (data: QuizQuestion, index: number) => {
    const currentQuestion = form.watch(`questions.${index}` as 'questions.0');

    if (!currentQuestion || validationError) {
      return;
    }

    const convertedQuestion: QuizQuestion = {
      ...data,
      question_id: nanoid(),
      _data_status: QuizDataStatus.NEW,
      question_title: `${currentQuestion.question_title} (copy)`,
      question_answers: currentQuestion.question_answers.map((answer) => ({
        ...answer,
        answer_id: nanoid(),
        _data_status: QuizDataStatus.NEW,
      })),
    };
    const duplicateQuestionIndex = index + 1;
    insertQuestion(duplicateQuestionIndex, convertedQuestion);
  };

  const handleDeleteQuestion = (index: number, question: QuizQuestion) => {
    removeQuestion(index);

    if (activeQuestionIndex === index) {
      setActiveQuestionId('');
      setValidationError(null);
    }

    if (question._data_status !== QuizDataStatus.NEW) {
      form.setValue('deleted_question_ids', [...form.getValues('deleted_question_ids'), question.question_id]);
    }
  };

  const handleDragEnd = (event: DragEndEvent) => {
    const { active, over } = event;
    if (!over || active.id === over.id) {
      return;
    }

    const activeIndex = questionFields.findIndex((question) => question.question_id === active.id);
    const overIndex = questionFields.findIndex((question) => question.question_id === over.id);
    moveQuestion(activeIndex, overIndex);
  };

  useEffect(() => {
    if (questionListRef.current) {
      questionListRef.current.style.maxHeight = `${
        window.innerHeight - questionListRef.current.getBoundingClientRect().top
      }px`;
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [questionListRef.current, isEditing]);

  if (!form.getValues('quiz_title')) {
    return null;
  }

  return (
    <div>
      <div css={styles.questionsLabel}>
        <span>{__('Questions', 'tutor')}</span>
        <button
          data-cy="add-question"
          ref={addButtonRef}
          type="button"
          onClick={() => {
            if (contentType === 'tutor_h5p_quiz') {
              showModal({
                component: H5PContentListModal,
                props: {
                  title: __('Select H5P Content', 'tutor'),
                  onAddContent: (contents) => {
                    handleH5PBulkQuestion(contents);
                  },
                  contentType: 'tutor_h5p_quiz',
                  addedContentIds: questions.map((question) => question.question_description),
                },
              });
            } else {
              setIsOpen(true);
            }
          }}
        >
          <SVGIcon name="plusSquareBrand" width={32} height={32} />
        </button>
      </div>

      <div ref={questionListRef} css={styles.questionList}>
        <Show
          when={questions.length > 0}
          fallback={<div css={styles.emptyQuestionText}>{__('No questions added yet.', 'tutor')}</div>}
        >
          <DndContext
            sensors={sensors}
            collisionDetection={closestCenter}
            modifiers={[restrictToWindowEdges]}
            onDragStart={(event) => {
              setActiveSortId(event.active.id);
            }}
            onDragEnd={(event) => handleDragEnd(event)}
          >
            <SortableContext
              items={questions.map((item) => ({ ...item, id: item.question_id }))}
              strategy={verticalListSortingStrategy}
            >
              <For each={questions}>
                {(question, index) => (
                  <Question
                    key={question.question_id}
                    question={question}
                    index={index}
                    onDuplicateQuestion={(data) => {
                      handleDuplicateQuestion(data, index);
                    }}
                    onRemoveQuestion={() => handleDeleteQuestion(index, question)}
                  />
                )}
              </For>
            </SortableContext>

            {createPortal(
              <DragOverlay>
                <Show when={activeSortItem}>
                  {(item) => {
                    const index = questionFields.findIndex((question) => question.question_id === item.question_id);
                    return (
                      <Question
                        key={item.question_id}
                        question={item}
                        index={index}
                        onDuplicateQuestion={noop}
                        onRemoveQuestion={noop}
                        isOverlay
                      />
                    );
                  }}
                </Show>
              </DragOverlay>,
              document.body,
            )}
          </DndContext>
        </Show>
        <Popover
          gap={4}
          maxWidth={'240px'}
          placement={
            CURRENT_VIEWPORT.isAboveTablet
              ? POPOVER_PLACEMENTS.BOTTOM
              : CURRENT_VIEWPORT.isAboveMobile
                ? POPOVER_PLACEMENTS.LEFT
                : POPOVER_PLACEMENTS.ABSOLUTE_CENTER
          }
          triggerRef={addButtonRef}
          isOpen={isOpen}
          closePopover={() => setIsOpen(false)}
          animationType={AnimationType.slideUp}
          arrow={true}
        >
          <div css={styles.questionOptionsWrapper}>
            <span css={styles.questionTypeOptionsTitle}>{__('Select Question Type', 'tutor')}</span>
            {questionTypeOptions.map((option) => (
              <Show
                key={option.value}
                when={option.isPro && !isTutorPro}
                fallback={
                  <button
                    key={option.value}
                    type="button"
                    css={styles.questionTypeOption}
                    onClick={() => {
                      handleAddQuestion(option.value as QuizQuestionType);
                    }}
                  >
                    <SVGIcon name={option.icon as IconCollection} width={24} height={24} />
                    <span>{option.label}</span>
                  </button>
                }
              >
                <button key={option.value} type="button" css={styles.questionTypeOption} disabled onClick={noop}>
                  <SVGIcon data-question-icon name={option.icon as IconCollection} width={24} height={24} />
                  <div>
                    <span>{option.label}</span>
                    <ProBadge size="small" content={__('Pro', 'tutor')} />
                  </div>
                </button>
              </Show>
            ))}
            <Show
              when={!isTutorPro}
              fallback={
                <Show when={isAddonEnabled(Addons.CONTENT_BANK)}>
                  <div css={styles.addFormContentBankButton}>
                    <Button
                      variant="secondary"
                      size="small"
                      onClick={() => {
                        showModal({
                          component: CollectionListModal,
                          props: {
                            title: __('Content Bank', 'tutor'),
                            type: 'question',
                            onAddContent: (contents) => {
                              handleAddContentBankBulkQuestions(
                                contents as (ContentBankContent & {
                                  question: QuizQuestion;
                                })[],
                              );
                            },
                          },
                        });
                        setIsOpen(false);
                      }}
                      icon={<SVGIcon name="contentBank" width={24} height={24} />}
                      data-cy="add-from-content-bank"
                    >
                      {__('Add from Content Bank', 'tutor')}
                    </Button>
                  </div>
                </Show>
              }
            >
              <div css={styles.addFormContentBankButton}>
                <ProBadge size="small">
                  <Button
                    disabled
                    variant="secondary"
                    size="small"
                    onClick={noop}
                    icon={<SVGIcon name="contentBank" width={24} height={24} />}
                  >
                    {__('Add from Content Bank', 'tutor')}
                  </Button>
                </ProBadge>
              </div>
            </Show>
          </div>
        </Popover>
      </div>
    </div>
  );
};

export default QuestionList;

const styles = {
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
      border-radius: ${borderRadius[6]};

      &:focus,
      &:active,
      &:hover {
        background: none;
      }

      svg {
        color: ${colorTokens.action.primary.default};
        width: 100%;
        height: 100%;
      }

      :focus-visible {
        outline: 2px solid ${colorTokens.stroke.brand};
      }
    }

    ${Breakpoint.smallMobile} {
      padding: ${spacing[16]};
    }
  `,
  questionList: css`
    ${styleUtils.overflowYAuto};
    scrollbar-gutter: auto;
    padding: ${spacing[8]} 0 ${spacing[8]} 0;
  `,
  questionTypeOptionsTitle: css`
    ${typography.caption('medium')};
    color: ${colorTokens.text.subdued};
    padding: ${spacing[8]} ${spacing[16]} ${spacing[8]} ${spacing[20]};
    border-bottom: 1px solid ${colorTokens.stroke.divider};
  `,
  addFormContentBankButton: css`
    padding: ${spacing[8]} ${spacing[16]};
    border-top: 1px solid ${colorTokens.stroke.divider};

    button {
      width: 100%;
    }
  `,
  questionOptionsWrapper: css`
    display: flex;
    flex-direction: column;
    padding-block: ${spacing[6]};
  `,
  questionTypeOption: css`
    ${styleUtils.resetButton};
    color: ${colorTokens.text.title};
    width: 100%;
    padding: ${spacing[8]} ${spacing[16]} ${spacing[8]} ${spacing[20]};
    transition: background-color 0.3s ease-in-out;
    display: flex;
    align-items: center;
    gap: ${spacing[10]};
    border: 2px solid transparent;

    div {
      ${styleUtils.display.flex()};
      align-items: center;
      gap: ${spacing[4]};
    }

    &:focus,
    &:active,
    &:hover {
      background: none;
      color: ${colorTokens.text.title};
    }

    :disabled {
      cursor: not-allowed;
      color: ${colorTokens.text.primary};

      [data-question-icon] {
        filter: grayscale(100%);
      }
    }

    :hover:enabled {
      background-color: ${colorTokens.background.hover};
      color: ${colorTokens.text.title};
    }

    :focus:enabled,
    :active:enabled {
      border-color: ${colorTokens.stroke.brand};
    }
  `,
  emptyQuestionText: css`
    ${typography.small()};
    color: ${colorTokens.text.subdued};
    padding: ${spacing[8]} ${spacing[16]} ${spacing[8]} ${spacing[28]};
  `,
};
