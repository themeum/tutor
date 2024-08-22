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
import { useMemo, useRef, useState } from 'react';
import { createPortal } from 'react-dom';
import { useFieldArray, useFormContext } from 'react-hook-form';

import LoadingSpinner from '@Atoms/LoadingSpinner';
import SVGIcon from '@Atoms/SVGIcon';
import { useToast } from '@Atoms/Toast';
import Popover from '@Molecules/Popover';

import Question from '@CourseBuilderComponents/curriculum/Question';
import { useQuizModalContext } from '@CourseBuilderContexts/QuizModalContext';

import { tutorConfig } from '@Config/config';
import { colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import For from '@Controls/For';
import Show from '@Controls/Show';
import {
  type QuizForm,
  type QuizQuestion,
  type QuizQuestionType,
  useCreateQuizQuestionMutation,
} from '@CourseBuilderServices/quiz';
import { AnimationType } from '@Hooks/useAnimation';
import { styleUtils } from '@Utils/style-utils';
import type { IconCollection } from '@Utils/types';
import { nanoid, noop } from '@Utils/util';

const questionTypeOptions: {
  label: string;
  value: QuizQuestionType;
  icon: IconCollection;
  isPro: boolean;
}[] = [
  {
    label: __('True/ False', 'tutor'),
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
    label: __('Open Ended/ Essay', 'tutor'),
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

const QuestionList = () => {
  const [activeSortId, setActiveSortId] = useState<UniqueIdentifier | null>(null);
  const [isOpen, setIsOpen] = useState(false);
  const addButtonRef = useRef<HTMLButtonElement>(null);

  const form = useFormContext<QuizForm>();
  const { activeQuestionIndex, setActiveQuestionId } = useQuizModalContext();
  const createQuizQuestion = useCreateQuizQuestionMutation();

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

  const { showToast } = useToast();
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

  const handleAddQuestion = (questionType: QuizQuestionType) => {
    if (activeQuestionIndex !== -1) {
      const answers =
        form.watch(`questions.${activeQuestionIndex}.question_answers` as 'questions.0.question_answers') || [];

      if (answers.length === 0) {
        showToast({
          message: __('Please add option', 'tutor'),
          type: 'danger',
        });
        setIsOpen(false);
        return;
      }

      const hasCorrectAnswer = answers.some((answer) => answer.is_correct === '1');
      const currentQuestionType = form.watch(`questions.${activeQuestionIndex}.question_type`);

      if (['true_false', 'multiple_choice'].includes(currentQuestionType) && !hasCorrectAnswer) {
        showToast({
          message: __('Please select a correct answer', 'tutor'),
          type: 'danger',
        });
        setIsOpen(false);
        return;
      }
    }

    const questionId = nanoid();
    appendQuestion({
      _data_status: 'new',
      question_id: questionId,
      question_title: `Question ${questionFields.length + 1}`,
      question_description: '',
      question_type: questionType,
      question_answers:
        questionType === 'true_false'
          ? [
              {
                answer_id: nanoid(),
                _data_status: 'new',
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
                _data_status: 'new',
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
                  _data_status: 'new',
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
        question_mark: 1,
        question_type: questionType,
        randomize_options: false,
        show_question_mark: true,
      },
    } as QuizQuestion);
    setActiveQuestionId(questionId);
    setIsOpen(false);
  };

  const handleDuplicateQuestion = (data: QuizQuestion, index: number) => {
    const convertedQuestion: QuizQuestion = {
      ...data,
      question_id: nanoid(),
      _data_status: 'new',
      question_title: `${data.question_title} (copy)`,
      question_answers: data.question_answers.map((answer) => ({
        ...answer,
        answer_id: nanoid(),
        _data_status: 'new',
      })),
    };
    const duplicateQuestionIndex = index + 1;
    insertQuestion(duplicateQuestionIndex, convertedQuestion);
  };

  if (!form.getValues('quiz_title')) {
    return null;
  }

  return (
    <div>
      <div css={styles.questionsLabel}>
        <span>{__('Questions', 'tutor')}</span>
        <Show when={!createQuizQuestion.isPending} fallback={<LoadingSpinner size={32} />}>
          <button
            ref={addButtonRef}
            disabled={createQuizQuestion.isPending}
            type="button"
            onClick={() => setIsOpen(true)}
          >
            <SVGIcon name="plusSquareBrand" width={32} height={32} />
          </button>
        </Show>
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
                const activeIndex = questionFields.findIndex((item) => item.question_id === active.id);
                const overIndex = questionFields.findIndex((item) => item.question_id === over.id);
                moveQuestion(activeIndex, overIndex);
              }

              setActiveSortId(null);
            }}
          >
            <SortableContext
              items={questionFields.map((item) => ({ ...item, id: item.question_id }))}
              strategy={verticalListSortingStrategy}
            >
              <For each={questionFields}>
                {(question, index) => (
                  <Question
                    key={question.question_id}
                    question={question}
                    index={index}
                    onDuplicateQuestion={(data) => {
                      handleDuplicateQuestion(data, index);
                    }}
                    onRemoveQuestion={() => {
                      removeQuestion(index);
                      setActiveQuestionId('');

                      if (question._data_status !== 'new') {
                        form.setValue('deleted_question_ids', [
                          ...form.getValues('deleted_question_ids'),
                          question.question_id,
                        ]);
                      }
                    }}
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
          arrow="top"
          triggerRef={addButtonRef}
          isOpen={isOpen}
          closePopover={() => setIsOpen(false)}
          animationType={AnimationType.slideUp}
        >
          <div css={styles.questionOptionsWrapper}>
            <span css={styles.questionTypeOptionsTitle}>{__('Select Question Types')}</span>
            {questionTypeOptions.map((option) => (
              <button
                key={option.value}
                type="button"
                css={styles.questionTypeOption}
                disabled={option.isPro && !tutorConfig.tutor_pro_url}
                onClick={() => {
                  handleAddQuestion(option.value as QuizQuestionType);
                }}
              >
                <SVGIcon name={option.icon as IconCollection} width={24} height={24} />
                <span>{option.label}</span>

                {/* Need to add lock or pro identifier */}
              </button>
            ))}
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

      svg {
        color: ${colorTokens.action.primary.default};
        width: 100%;
        height: 100%;
      }
    }
  `,
  questionList: css`
    ${styleUtils.overflowYAuto};
    padding: ${spacing[8]} 0 ${spacing[8]} ${spacing[20]};
  `,
  questionTypeOptionsTitle: css`
    ${typography.caption('medium')};
    color: ${colorTokens.text.subdued};
    padding: ${spacing[8]} ${spacing[16]} ${spacing[8]} ${spacing[20]};
    border-bottom: 1px solid ${colorTokens.stroke.divider};
  `,
  questionOptionsWrapper: css`
    display: flex;
    flex-direction: column;
    padding-block: ${spacing[6]};
  `,
  questionTypeOption: css`
    ${styleUtils.resetButton};
    width: 100%;
    padding: ${spacing[8]} ${spacing[16]} ${spacing[8]} ${spacing[20]};
    transition: background-color 0.3s ease-in-out;
    display: flex;
    align-items: center;
    gap: ${spacing[10]};
    border: 2px solid transparent;

    :disabled {
      cursor: not-allowed;
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
};
