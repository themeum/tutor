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
import Tooltip from '@TutorShared/atoms/Tooltip';
import Popover from '@TutorShared/molecules/Popover';

import GenerateQuizWithAi from '@CourseBuilderComponents/curriculum/GenerateQuizWithAi';
import Question from '@CourseBuilderComponents/curriculum/Question';
import H5PContentListModal from '@TutorShared/components/modals/H5PContentListModal';
import { useModal } from '@TutorShared/components/modals/Modal';

import CollectionListModal from '@CourseBuilderComponents/modals/ContentBankContentSelectModal';
import { useQuizModalContext } from '@CourseBuilderContexts/QuizModalContext';
import { type QuizForm } from '@CourseBuilderServices/quiz';
import { tutorConfig } from '@TutorShared/config/config';
import { Addons, CURRENT_VIEWPORT } from '@TutorShared/config/constants';
import { borderRadius, Breakpoint, colorTokens, shadow, spacing } from '@TutorShared/config/styles';
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

interface QuestionTypeOption {
  label: string;
  value: QuizQuestionType;
  icon: IconCollection;
  isPro: boolean;
  isLegacyDisabled?: boolean;
}

const basicQuestionTypeOptions: QuestionTypeOption[] = [
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
];

const interactiveQuestionTypeOptions: QuestionTypeOption[] = [
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
  {
    label: __('Mark in the Image', 'tutor'),
    value: 'draw_image',
    icon: 'quizMarkInTheImage',
    isPro: true,
  },
  {
    label: __('Range', 'tutor'),
    value: 'scale',
    icon: 'quizRange',
    isPro: true,
  },
  {
    label: __('Pin', 'tutor'),
    value: 'pin_image',
    icon: 'quizPin',
    isPro: true,
  },
  {
    label: __('Graph', 'tutor'),
    value: 'coordinates',
    icon: 'quizGraph',
    isPro: true,
  },
  {
    label: __('Puzzle', 'tutor'),
    value: 'puzzle',
    icon: 'quizPuzzle',
    isPro: true,
  },
];

const isTutorPro = !!tutorConfig.tutor_pro_url;

const QuestionList = ({ isEditing }: { isEditing: boolean }) => {
  const questionTypeOptionsForUi = useMemo(() => {
    const legacyExcluded: QuizQuestionType[] = ['draw_image', 'pin_image', 'scale', 'coordinates', 'puzzle'];
    const markLegacyDisabled = (options: QuestionTypeOption[]) =>
      options.map((o) =>
        tutorConfig.is_legacy_learning_mode && legacyExcluded.includes(o.value) ? { ...o, isLegacyDisabled: true } : o,
      );
    return {
      basic: markLegacyDisabled(basicQuestionTypeOptions),
      interactive: markLegacyDisabled(interactiveQuestionTypeOptions),
    };
  }, []);
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
            : questionType === 'draw_image'
              ? [
                  {
                    _data_status: QuizDataStatus.NEW,
                    is_saved: false,
                    answer_id: nanoid(),
                    answer_title: '',
                    belongs_question_id: questionId,
                    belongs_question_type: 'draw_image',
                    answer_two_gap_match: '',
                    answer_view_format: 'draw_image',
                    answer_order: 0,
                    is_correct: '1',
                  },
                ]
              : questionType === 'coordinates'
                ? [
                    {
                      _data_status: QuizDataStatus.NEW,
                      is_saved: false,
                      answer_id: nanoid(),
                      answer_title: '',
                      belongs_question_id: questionId,
                      belongs_question_type: 'coordinates',
                      answer_two_gap_match: '',
                      answer_view_format: 'coordinates',
                      answer_order: 0,
                      is_correct: '1',
                    },
                  ]
                : questionType === 'pin_image'
                  ? [
                      {
                        _data_status: QuizDataStatus.NEW,
                        is_saved: true,
                        answer_id: nanoid(),
                        answer_title: '',
                        belongs_question_id: questionId,
                        belongs_question_type: 'pin_image',
                        answer_two_gap_match: '',
                        answer_view_format: 'pin_image',
                        answer_order: 0,
                        is_correct: '1',
                      },
                    ]
                  : questionType === 'scale'
                    ? [
                        {
                          _data_status: QuizDataStatus.NEW,
                          // Keep the initial default scale config valid for immediate save flow.
                          is_saved: true,
                          answer_id: nanoid(),
                          answer_title: '',
                          belongs_question_id: questionId,
                          belongs_question_type: 'scale',
                          answer_two_gap_match: JSON.stringify({
                            value: 50,
                            config: {
                              min: 0,
                              max: 100,
                              step: 1,
                              defaultValue: 50,
                              pxPerUnit: 10,
                              labelEvery: 10,
                              minorTickEvery: 5,
                              precision: 0,
                            },
                          }),
                          answer_view_format: 'scale',
                          answer_order: 0,
                          is_correct: '1',
                        },
                      ]
                    : questionType === 'puzzle'
                      ? [
                          {
                            _data_status: QuizDataStatus.NEW,
                            is_saved: true,
                            answer_id: nanoid(),
                            answer_title: '',
                            belongs_question_id: questionId,
                            belongs_question_type: 'puzzle',
                            answer_two_gap_match: '',
                            answer_view_format: 'puzzle',
                            answer_order: 0,
                            is_correct: '1',
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
        ...(questionType === 'draw_image' && {
          draw_image_threshold_percent: 70,
        }),
        ...(questionType === 'puzzle' && {
          puzzle_grid_size: 4,
        }),
        ...(questionType === 'coordinates' && {
          coordinates_axis_range: 10,
        }),
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
    if (question._data_status === QuizDataStatus.NEW) {
      const isMaskQuestionType =
        question.question_type === 'draw_image' ||
        question.question_type === 'pin_image' ||
        question.question_type === 'puzzle';

      if (isMaskQuestionType) {
        const tempMaskValues = (question.question_answers || [])
          .flatMap((answer) => [answer.answer_two_gap_match, answer.image_url])
          .map((value) => (typeof value === 'string' ? value.trim() : ''))
          .filter(Boolean);

        if (tempMaskValues.length > 0) {
          form.setValue('deleted_temp_mask_values', [
            ...(form.getValues('deleted_temp_mask_values') || []),
            ...tempMaskValues,
          ]);
        }
      }
    }

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
        <div css={styles.questionActions}>
          <Show when={contentType !== 'tutor_h5p_quiz'}>
            <GenerateQuizWithAi />
          </Show>
          <button
            data-cy="add-question"
            data-add-question-button
            ref={addButtonRef}
            type="button"
            aria-label={__('Add question', 'tutor')}
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
          maxWidth={'340px'}
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
          border={true}
        >
          <div css={styles.questionOptionsWrapper}>
            <div css={styles.questionTypeColumns}>
              <div css={styles.questionTypeColumn}>
                <span css={styles.questionTypeColumnTitle}>{__('Interactive', 'tutor')}</span>
                {questionTypeOptionsForUi.interactive.map((option) => (
                  <Show
                    key={option.value}
                    when={option.isPro && !isTutorPro}
                    fallback={
                      <button
                        type="button"
                        css={styles.questionTypeOption}
                        title={option.label}
                        disabled={option.isLegacyDisabled}
                        onClick={() => {
                          handleAddQuestion(option.value as QuizQuestionType);
                        }}
                      >
                        <SVGIcon data-question-icon name={option.icon as IconCollection} width={24} height={24} />
                        <div css={styles.questionTypeOptionLabelRow}>
                          <span css={styles.questionTypeOptionLabel}>{option.label}</span>
                          <Show when={option.isLegacyDisabled}>
                            <Tooltip
                              content={__('Not available in legacy learning mode', 'tutor')}
                              placement="top"
                              wrapperCss={styleUtils.flexCenter()}
                            >
                              <span css={styles.legacyInfoIcon}>
                                <SVGIcon name="infoOctagon" width={12} height={12} />
                              </span>
                            </Tooltip>
                          </Show>
                        </div>
                      </button>
                    }
                  >
                    <button type="button" css={styles.questionTypeOption} title={option.label} disabled onClick={noop}>
                      <SVGIcon data-question-icon name={option.icon as IconCollection} width={24} height={24} />
                      <div css={styles.questionTypeOptionLabelRow}>
                        <span css={styles.questionTypeOptionLabel}>{option.label}</span>
                        <ProBadge size="small" content={__('Pro', 'tutor')} />
                      </div>
                    </button>
                  </Show>
                ))}
              </div>

              <div css={styles.questionTypeColumn}>
                <span css={styles.questionTypeColumnTitle}>{__('Basic', 'tutor')}</span>
                {questionTypeOptionsForUi.basic.map((option) => (
                  <Show
                    key={option.value}
                    when={option.isPro && !isTutorPro}
                    fallback={
                      <button
                        type="button"
                        css={styles.questionTypeOption}
                        title={option.label}
                        disabled={option.isLegacyDisabled}
                        onClick={() => {
                          handleAddQuestion(option.value as QuizQuestionType);
                        }}
                      >
                        <SVGIcon data-question-icon name={option.icon as IconCollection} width={24} height={24} />
                        <div css={styles.questionTypeOptionLabelRow}>
                          <span css={styles.questionTypeOptionLabel}>{option.label}</span>
                          <Show when={option.isLegacyDisabled}>
                            <Tooltip
                              content={__('Not available in legacy learning mode', 'tutor')}
                              placement="top"
                              wrapperCss={styleUtils.flexCenter()}
                            >
                              <span css={styles.legacyInfoIcon}>
                                <SVGIcon name="info" width={16} height={16} />
                              </span>
                            </Tooltip>
                          </Show>
                        </div>
                      </button>
                    }
                  >
                    <button type="button" css={styles.questionTypeOption} title={option.label} disabled onClick={noop}>
                      <SVGIcon data-question-icon name={option.icon as IconCollection} width={24} height={24} />
                      <div css={styles.questionTypeOptionLabelRow}>
                        <span css={styles.questionTypeOptionLabel}>{option.label}</span>
                        <ProBadge size="small" content={__('Pro', 'tutor')} />
                      </div>
                    </button>
                  </Show>
                ))}
              </div>
            </div>

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

    ${Breakpoint.smallMobile} {
      padding: ${spacing[16]};
    }
  `,
  questionActions: css`
    display: flex;
    align-items: center;
    gap: ${spacing[4]};

    [data-add-question-button],
    [data-generate-quiz-button] {
      ${styleUtils.resetButton};
      width: 32px;
      height: 32px;
      border-radius: ${borderRadius[6]};
      display: inline-flex;
      align-items: center;
      justify-content: center;

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

      &:focus {
        box-shadow: ${shadow.focus};
      }

      :focus-visible {
        box-shadow: none;
        outline: 2px solid ${colorTokens.stroke.brand};
        outline-offset: 1px;
      }
    }

    [data-generate-quiz-button] {
      border: 1px solid ${colorTokens.stroke.divider};

      svg {
        width: 24px;
        height: 24px;
      }
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
    padding: ${spacing[6]} ${spacing[12]};

    button {
      width: 100%;
    }
  `,
  questionOptionsWrapper: css`
    display: flex;
    flex-direction: column;
    padding-block: ${spacing[6]};
  `,
  questionTypeColumns: css`
    display: grid;
    grid-template-columns: 1fr 1fr;
    padding-block: ${spacing[6]};
  `,
  questionTypeColumn: css`
    display: flex;
    flex-direction: column;
    min-width: 0;
    padding-inline: ${spacing[8]};
  `,
  questionTypeColumnTitle: css`
    ${typography.small('regular')};
    color: ${colorTokens.text.title};
    padding-left: ${spacing[4]};
    margin-bottom: ${spacing[12]};
  `,
  questionTypeOption: css`
    ${styleUtils.resetButton};
    color: ${colorTokens.text.hints};
    width: 100%;
    padding: ${spacing[2]};
    transition: background-color 0.3s ease-in-out;
    display: flex;
    align-items: center;
    gap: ${spacing[8]};
    border: 2px solid transparent;
    border-radius: ${borderRadius[6]};

    &:not(:last-of-type) {
      margin-bottom: ${spacing[4]};
    }

    [data-question-icon] {
      flex-shrink: 0;
    }

    &:enabled {
      &:focus,
      &:active,
      &:hover {
        background-color: ${colorTokens.background.hover};
        color: ${colorTokens.text.title};
      }
    }

    :disabled {
      cursor: not-allowed;

      [data-question-icon] {
        filter: grayscale(100%);
      }
    }

    :focus:enabled,
    :active:enabled {
      border-color: ${colorTokens.stroke.brand};
    }
  `,
  questionTypeOptionLabel: css`
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  `,
  questionTypeOptionLabelRow: css`
    ${styleUtils.display.flex()};
    align-items: center;
    gap: ${spacing[4]};
    width: 100%;
    min-width: 0;

    > div {
      flex-shrink: 0;
    }
  `,
  emptyQuestionText: css`
    ${typography.small()};
    color: ${colorTokens.text.subdued};
    padding: ${spacing[8]} ${spacing[16]} ${spacing[8]} ${spacing[28]};
  `,
  legacyInfoIcon: css`
    display: inline-flex;
    align-items: center;
    flex-shrink: 0;
    color: ${colorTokens.icon.brand};
  `,
};
