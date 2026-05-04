import { css } from '@emotion/react';
import { useQueryClient } from '@tanstack/react-query';
import { __ } from '@wordpress/i18n';
import { useEffect, useMemo, useRef, useState } from 'react';
import { Controller, useFieldArray, useFormContext } from 'react-hook-form';

import Button from '@TutorShared/atoms/Button';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import { useToast } from '@TutorShared/atoms/Toast';
import Popover from '@TutorShared/molecules/Popover';

import { useQuizModalContext } from '@CourseBuilderContexts/QuizModalContext';
import type { CourseTopic } from '@CourseBuilderServices/curriculum';
import type { QuizForm } from '@CourseBuilderServices/quiz';
import { getCourseId } from '@CourseBuilderUtils/utils';
import MagicButton from '@TutorShared/atoms/MagicButton';
import FormCheckbox from '@TutorShared/components/fields/FormCheckbox';
import FormInput from '@TutorShared/components/fields/FormInput';
import FormSelectInput from '@TutorShared/components/fields/FormSelectInput';
import { useModal } from '@TutorShared/components/modals/Modal';
import ProIdentifierModal from '@TutorShared/components/modals/ProIdentifierModal';
import SetupOpenAiModal from '@TutorShared/components/modals/SetupOpenAiModal';
import { tutorConfig } from '@TutorShared/config/config';
import { borderRadius, Breakpoint, colorTokens, fontSize, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import Show from '@TutorShared/controls/Show';
import { AnimationType } from '@TutorShared/hooks/useAnimation';
import { useFormWithGlobalError } from '@TutorShared/hooks/useFormWithGlobalError';
import { POPOVER_PLACEMENTS } from '@TutorShared/hooks/usePortalPopover';
import type { IconCollection } from '@TutorShared/icons/types';
import { useGenerateAiQuizQuestionsMutation } from '@TutorShared/services/magic-ai';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { QuizDataStatus, type Option, type QuizQuestion, type QuizQuestionType } from '@TutorShared/utils/types';
import { nanoid } from '@TutorShared/utils/util';

import generateCourse2x from '@SharedImages/pro-placeholders/generate-course-2x.webp';
import generateCourse from '@SharedImages/pro-placeholders/generate-course.webp';

const courseId = getCourseId();
const emptyTopicIds: string[] = [];

type AiQuestionType = Extract<QuizQuestionType, 'true_false' | 'multiple_choice' | 'short_answer' | 'open_ended'>;

interface GenerateQuizWithAiForm {
  topic_ids: string[];
  question_types: Record<AiQuestionType, boolean>;
  difficulty_level: string;
  question_count: number | null;
}

interface TopicSelectForm {
  selected_topic_id: string | null;
}

const questionTypeOptions: {
  label: string;
  value: AiQuestionType;
  icon: IconCollection;
}[] = [
  {
    label: __('True/False', 'tutor'),
    value: 'true_false',
    icon: 'quizTrueFalse',
  },
  {
    label: __('Multiple Choice', 'tutor'),
    value: 'multiple_choice',
    icon: 'quizMultiChoice',
  },
  {
    label: __('Short Answer', 'tutor'),
    value: 'short_answer',
    icon: 'quizShortAnswer',
  },
  {
    label: __('Open Ended/Essay', 'tutor'),
    value: 'open_ended',
    icon: 'quizEssay',
  },
];

const difficultyOptions = [
  { label: __('Easy', 'tutor'), value: 'easy', icon: 'difficultyEasy' },
  { label: __('Medium', 'tutor'), value: 'medium', icon: 'difficultyNormal' },
  { label: __('Hard', 'tutor'), value: 'hard', icon: 'difficultyHard' },
];

const GenerateQuizWithAi = () => {
  const queryClient = useQueryClient();
  const { topicId } = useQuizModalContext();
  const { showModal } = useModal();
  const { showToast } = useToast();
  const [isOpen, setIsOpen] = useState(false);
  const triggerRef = useRef<HTMLButtonElement>(null);
  const isTutorPro = !!tutorConfig.tutor_pro_url;
  const hasOpenAiAPIKey = tutorConfig.settings?.chatgpt_key_exist;

  const quizForm = useFormContext<QuizForm>();
  const { append: appendQuestion } = useFieldArray({
    control: quizForm.control,
    name: 'questions',
  });

  const generateMutation = useGenerateAiQuizQuestionsMutation();

  const form = useFormWithGlobalError<GenerateQuizWithAiForm>({
    defaultValues: {
      topic_ids: [String(topicId)],
      question_types: {
        true_false: true,
        multiple_choice: true,
        short_answer: true,
        open_ended: true,
      },
      difficulty_level: 'medium',
      question_count: 10,
    },
  });
  const topicSelectForm = useFormWithGlobalError<TopicSelectForm>({
    defaultValues: {
      selected_topic_id: null,
    },
  });

  const topics = queryClient.getQueryData(['Topic', courseId]) as CourseTopic[] | undefined;
  const topicOptions = useMemo(() => {
    const currentTopic = topics?.find((topic) => String(topic.id) === String(topicId));

    return topics?.length
      ? topics
      : [
          {
            id: topicId,
            title: currentTopic?.title || __('Current Topic', 'tutor'),
            summary: '',
            contents: [],
          },
        ];
  }, [topicId, topics]);

  const selectedTopicIds = form.watch('topic_ids') ?? emptyTopicIds;
  const questionTypes = form.watch('question_types');
  const questionCount = Number(form.watch('question_count'));
  const difficulty_level = form.watch('difficulty_level');
  const selectedQuestionTypes = questionTypeOptions
    .filter((option) => questionTypes?.[option.value])
    .map((option) => option.value);

  const selectableTopicOptions = useMemo(() => {
    return topicOptions
      .filter((topic) => !selectedTopicIds.includes(String(topic.id)))
      .map((topic) => ({
        label: topic.title,
        value: String(topic.id),
      }));
  }, [selectedTopicIds, topicOptions]);

  const selectedTopics = topicOptions.filter((topic) => selectedTopicIds.includes(String(topic.id)));

  const handleTopicSelect = (option: Option<string>) => {
    const updatedTopicIds = Array.from(new Set([...form.getValues('topic_ids'), String(option.value)]));

    form.setValue('topic_ids', updatedTopicIds, { shouldDirty: true });
    topicSelectForm.setValue('selected_topic_id', null);
  };

  const handleRemoveTopic = (topicId: string) => {
    form.setValue(
      'topic_ids',
      form.getValues('topic_ids').filter((previousTopicId) => previousTopicId !== topicId),
      { shouldDirty: true },
    );
  };

  const handleGenerate = async (data: GenerateQuizWithAiForm) => {
    try {
      const payload = {
        topic_ids: data.topic_ids.join(','),
        question_types: questionTypeOptions
          .filter((option) => data.question_types[option.value])
          .map((option) => option.value)
          .join(','),
        difficulty_level: data.difficulty_level,
        number_of_questions: Number(data.question_count),
      };

      const response = await generateMutation.mutateAsync(payload);
      const generatedQuestions = response.data ?? [];

      const existingCount = quizForm.getValues('questions')?.length ?? 0;

      generatedQuestions.forEach((item, index) => {
        const questionId = nanoid();
        const question = {
          _data_status: QuizDataStatus.NEW,
          question_id: questionId,
          question_title: item.title,
          question_description: item.description ?? '',
          question_type: item.type,
          question_mark: 1,
          answer_explanation: '',
          question_order: existingCount + index,
          question_settings: {
            question_type: item.type,
            answer_required: false,
            randomize_question: false,
            question_mark: 1,
            show_question_mark: true,
            has_multiple_correct_answer: false,
            is_image_matching: false,
          },
          question_answers: (item.options ?? []).map((opt, i) => ({
            _data_status: QuizDataStatus.NEW,
            is_saved: true,
            answer_id: nanoid(),
            belongs_question_id: questionId,
            belongs_question_type: item.type,
            answer_title: opt.name,
            is_correct: opt.is_correct ? '1' : '0',
            answer_order: i,
            answer_two_gap_match: '',
            answer_view_format: 'text',
          })),
        } satisfies QuizQuestion;

        appendQuestion(question);
      });

      showToast({ type: 'success', message: __('Questions generated successfully', 'tutor') });
      setIsOpen(false);
    } catch (error) {
      showToast({
        type: 'danger',
        message: error instanceof Error ? error.message : __('Something went wrong!', 'tutor'),
      });
    }
  };

  const handleAiButtonClick = () => {
    if (!isTutorPro) {
      showModal({
        component: ProIdentifierModal,
        props: {
          image: generateCourse,
          image2x: generateCourse2x,
        },
      });
    } else if (!hasOpenAiAPIKey) {
      showModal({
        component: SetupOpenAiModal,
        props: {
          image: generateCourse,
          image2x: generateCourse2x,
        },
      });
    } else {
      setIsOpen(true);
    }
  };

  useEffect(() => {
    form.setValue('topic_ids', [String(topicId)]);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [topicId]);

  return (
    <>
      <Button
        isIconOnly
        variant="text"
        size="small"
        icon={<SVGIcon name="magicAiColorize" width={24} height={24} />}
        data-generate-quiz-button
        ref={triggerRef}
        type="button"
        aria-label={__('Generate quiz using AI', 'tutor')}
        onClick={handleAiButtonClick}
      />

      <Popover
        gap={4}
        maxWidth={'705px'}
        placement={POPOVER_PLACEMENTS.BOTTOM}
        triggerRef={triggerRef}
        isOpen={isOpen}
        closePopover={() => setIsOpen(false)}
        animationType={AnimationType.slideUp}
        border={true}
      >
        <form onSubmit={form.handleSubmit(handleGenerate)}>
          <div css={styles.generateQuizHeader}>
            <div css={styles.generateQuizTitleWrapper}>
              <SVGIcon name="magicAiColorize" width={24} height={24} />
              <div css={styles.generateQuizTitle}>{__('Generate Quiz Component', 'tutor')}</div>
            </div>
            <Button
              type="button"
              variant="text"
              size="small"
              icon={<SVGIcon name="cross" width={24} height={24} />}
              isIconOnly
              aria-label={__('Close Popover', 'tutor')}
              onClick={() => setIsOpen(false)}
            />
          </div>

          <div css={styles.generateQuizBody}>
            <div>
              <div css={styles.generateQuizFieldGroup}>
                <Controller
                  control={topicSelectForm.control}
                  name="selected_topic_id"
                  render={(controllerProps) => (
                    <FormSelectInput
                      {...controllerProps}
                      isSearchable
                      label={__('Select Topics', 'tutor')}
                      options={selectableTopicOptions}
                      placeholder={__('Search topics...', 'tutor')}
                      onChange={handleTopicSelect}
                      leftIcon={<SVGIcon name="search" width={20} height={20} />}
                      hideCaret
                      wrapperCss={styles.selectInputStyle}
                      disabled={selectableTopicOptions.length === 0}
                    />
                  )}
                />

                <Show when={selectedTopics.length > 0}>
                  <div css={styles.selectedTopicList}>
                    {selectedTopics.map((topic) => (
                      <div key={String(topic.id)} css={styles.selectedTopic}>
                        <span title={topic.title}>{topic.title}</span>
                        <button
                          type="button"
                          css={styles.removeTopicButton}
                          onClick={() => handleRemoveTopic(String(topic.id))}
                        >
                          <SVGIcon name="cross2" width={12} height={12} />
                        </button>
                      </div>
                    ))}
                  </div>
                </Show>
              </div>
            </div>
            <div css={styles.generateQuizBodyRight}>
              <div css={[styles.generateQuizFieldGroup, styles.generateQuizFieldGroupTypes]}>
                <span css={styles.generateQuizLabel}>{__('Question Types', 'tutor')}</span>
                <div css={styles.generateQuizCheckboxList}>
                  {questionTypeOptions.map((option) => (
                    <Controller
                      key={option.value}
                      control={form.control}
                      name={`question_types.${option.value}`}
                      render={(controllerProps) => (
                        <FormCheckbox
                          {...controllerProps}
                          label={
                            <span css={styles.generateQuizQuestionTypeLabelText}>
                              <SVGIcon name={option.icon} width={24} height={24} />
                              {option.label}
                            </span>
                          }
                          labelCss={styles.generateQuizQuestionTypeLabel}
                        />
                      )}
                    />
                  ))}
                </div>
              </div>

              <div css={styleUtils.fieldGroups(6)}>
                <Controller
                  control={form.control}
                  name="difficulty_level"
                  render={(controllerProps) => (
                    <FormSelectInput
                      {...controllerProps}
                      label={__('Difficulty Level', 'tutor')}
                      iconSize={20}
                      options={difficultyOptions}
                      wrapperCss={styles.selectInputStyle}
                      placeholder={__('Select difficulty level', 'tutor')}
                    />
                  )}
                />

                <Controller
                  control={form.control}
                  name="question_count"
                  rules={{
                    min: {
                      value: 1,
                      message: __('Number of questions must be greater than 0', 'tutor'),
                    },
                  }}
                  render={(controllerProps) => (
                    <FormInput
                      {...controllerProps}
                      type="number"
                      label={__('Number of Questions', 'tutor')}
                      placeholder={__('Number of questions', 'tutor')}
                    />
                  )}
                />
              </div>

              <div css={styles.generateQuizButtonWrapper}>
                <MagicButton
                  type="submit"
                  size="default"
                  roundedFull={false}
                  disabled={
                    generateMutation.isPending ||
                    !selectedTopicIds.length ||
                    !selectedQuestionTypes.length ||
                    questionCount < 1 ||
                    Number.isNaN(questionCount) ||
                    !difficulty_level
                  }
                >
                  <SVGIcon name="magicAi" width={24} height={24} />
                  {generateMutation.isPending ? __('Generating...', 'tutor') : __('Generate Now', 'tutor')}
                </MagicButton>
                <div css={styles.generateQuizButtonHelperText}>
                  {__('AI will generate questions based on your selected topics and types.', 'tutor')}
                </div>
              </div>
            </div>
          </div>
        </form>
      </Popover>
    </>
  );
};

export default GenerateQuizWithAi;

const styles = {
  generateQuizHeader: css`
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: ${spacing[8]};
    padding: ${spacing[12]} ${spacing[16]};
    border-bottom: 1px solid ${colorTokens.stroke.divider};
  `,
  generateQuizTitleWrapper: css`
    display: flex;
    align-items: center;
    gap: ${spacing[8]};
  `,
  generateQuizTitle: css`
    ${typography.body('medium')};
  `,
  generateQuizBody: css`
    display: grid;
    grid-template-columns: 1fr 360px;
    gap: ${spacing[32]};
    padding: ${spacing[16]} ${spacing[20]};

    ${Breakpoint.mobile} {
      grid-template-columns: 1fr;
      max-height: calc(100vh - 62px);
      overflow: auto;
    }
  `,
  selectInputStyle: css`
    padding-left: ${spacing[32]}!important;
  `,
  generateQuizBodyRight: css`
    position: relative;

    &::before {
      content: '';
      position: absolute;
      top: 0;
      left: -${spacing[16]};
      width: 1px;
      height: 100%;
      background-color: ${colorTokens.stroke.divider};

      ${Breakpoint.mobile} {
        display: none;
      }
    }
  `,
  generateQuizFieldGroup: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[8]};
  `,
  generateQuizFieldGroupTypes: css`
    margin-bottom: ${spacing[40]};
  `,
  generateQuizLabel: css`
    ${typography.caption('medium')};
    color: ${colorTokens.text.title};
  `,
  generateQuizCheckboxList: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[10]};
  `,
  generateQuizQuestionTypeLabelText: css`
    display: inline-flex;
    align-items: center;
    gap: ${spacing[8]};
    min-width: 0;

    svg {
      flex: 0 0 auto;
    }
  `,
  generateQuizQuestionTypeLabel: css`
    display: flex;
    align-items: center;
  `,
  selectedTopicList: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[4]};
    max-height: 340px;
    overflow-y: auto;
  `,
  selectedTopic: css`
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: ${spacing[8]};
    min-height: 28px;
    border-radius: ${borderRadius[4]};
    padding: ${spacing[4]} ${spacing[8]};
    color: ${colorTokens.text.title};
    background-color: ${colorTokens.action.secondary.gray};

    span {
      ${typography.small('medium')};
      font-size: ${fontSize[12]};
      ${styleUtils.text.ellipsis(1)};
    }
  `,
  removeTopicButton: css`
    ${styleUtils.crossButton};
    background-color: transparent;
    width: 12px;
    height: 12px;
    flex: 0 0 auto;

    svg {
      color: ${colorTokens.text.primary};
    }
  `,
  generateQuizButtonWrapper: css`
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    gap: ${spacing[6]};
    margin-top: ${spacing[20]};

    button {
      height: 40px;

      span {
        gap: ${spacing[4]};
      }
    }
  `,
  generateQuizButtonHelperText: css`
    ${typography.tiny('regular')};
    color: ${colorTokens.text.hints};
  `,
};
