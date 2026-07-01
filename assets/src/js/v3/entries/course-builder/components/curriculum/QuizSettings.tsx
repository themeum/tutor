import { Controller, useFormContext } from 'react-hook-form';
import { css } from '@emotion/react';
import { useQueryClient } from '@tanstack/react-query';
import { __, sprintf } from '@wordpress/i18n';

import SVGIcon from '@TutorShared/atoms/SVGIcon';
import Tooltip from '@TutorShared/atoms/Tooltip';

import FormCheckbox from '@TutorShared/components/fields/FormCheckbox';
import FormDateInput from '@TutorShared/components/fields/FormDateInput';
import FormInput from '@TutorShared/components/fields/FormInput';
import FormInputWithContent from '@TutorShared/components/fields/FormInputWithContent';
import FormInputWithPresets from '@TutorShared/components/fields/FormInputWithPresets';
import FormSelectInput from '@TutorShared/components/fields/FormSelectInput';
import FormSwitch from '@TutorShared/components/fields/FormSwitch';
import FormTopicPrerequisites from '@TutorShared/components/fields/FormTopicPrerequisites';

import { tutorConfig } from '@TutorShared/config/config';
import { Addons } from '@TutorShared/config/constants';
import { borderRadius, Breakpoint, colorTokens, spacing, zIndex } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import Show from '@TutorShared/controls/Show';
import { type IconCollection } from '@TutorShared/icons/types';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { isAddonEnabled } from '@TutorShared/utils/util';
import { requiredRule } from '@TutorShared/utils/validation';

import CourseBuilderInjectionSlot from '@CourseBuilderComponents/CourseBuilderSlot';
import { useQuizModalContext } from '@CourseBuilderContexts/QuizModalContext';
import type { ContentDripType } from '@CourseBuilderServices/course';
import type { CourseTopic } from '@CourseBuilderServices/curriculum';
import type { QuizForm } from '@CourseBuilderServices/quiz';
import { getCourseId } from '@CourseBuilderUtils/utils';

import QuizFullPageSvg from '@SharedImages/quiz-fullpage.svg';
import QuizSingleLayoutSvg from '@SharedImages/quiz-single-question.svg';

import FormQuizLayoutSelect from './FormQuizLayoutSelect';

const courseId = getCourseId();

interface QuizSettingsProps {
  contentDripType: ContentDripType;
}

const getTimeInSeconds = (value: number, type: QuizForm['quiz_option']['time_limit']['time_type']) => {
  const timeUnitMap = {
    seconds: 1,
    minutes: 60,
    hours: 60 * 60,
    days: 60 * 60 * 24,
    weeks: 60 * 60 * 24 * 7,
  } as const;

  return value * timeUnitMap[type];
};

const formatTimePerQuestion = (valueInSeconds: number) => {
  if (valueInSeconds < 60) {
    return sprintf(
      // translators: %s is a second count.
      __('%ss per question', 'tutor'),
      valueInSeconds,
    );
  }

  if (valueInSeconds < 60 * 60) {
    return sprintf(
      // translators: %s is a minute count.
      __('%sm per question', 'tutor'),
      Math.round((valueInSeconds / 60) * 10) / 10,
    );
  }

  return sprintf(
    // translators: %s is an hour count.
    __('%sh per question', 'tutor'),
    Math.round((valueInSeconds / (60 * 60)) * 10) / 10,
  );
};

const getPaginationTypeIcon = (type: string): IconCollection => {
  switch (type) {
    case 'shape':
      return 'quizShape';
    case 'number':
      return 'quizNumber';
    case 'radio':
      return 'quizRadio';
    default:
      return 'quizShape';
  }
};

const QuizSettings = ({ contentDripType }: QuizSettingsProps) => {
  const { quizId, contentType } = useQuizModalContext();
  const form = useFormContext<QuizForm>();
  const isLegacyLearningMode = tutorConfig.settings?.learning_mode === 'legacy';

  const questions = form.watch('questions');
  const questionsCount = questions.length;
  const hasOpenEndedQuestions = questions.some((question) => question.question_type === 'open_ended');
  const hasShortAnswerQuestions = questions.some((question) => question.question_type === 'short_answer');
  const hasAttemptsLimit = form.watch('quiz_option.limit_attempts_allowed');
  const showPassRequired =
    isAddonEnabled(Addons.CONTENT_DRIP) && contentDripType === 'unlock_sequentially' && hasAttemptsLimit;
  const hasQuestionLimit = form.watch('quiz_option.limit_questions_to_answer');
  const hasTimeLimit = form.watch('quiz_option.enable_time_limit');
  const questionsOrder = form.watch('quiz_option.questions_order');
  const availableQuestionInPool = hasQuestionLimit
    ? Math.min(Number(form.watch('quiz_option.max_questions_for_answer')), questionsCount) || questionsCount
    : questionsCount;
  const usedQuestionCountPercentage = (availableQuestionInPool / questionsCount) * 100;
  const orderedQuestions = (() => {
    if (questionsOrder === 'rand') {
      return questions;
    }

    if (questionsOrder === 'sorting') {
      return questions;
    }

    if (questionsOrder === 'asc') {
      return [...questions].sort((a, b) => Number(a.question_id) - Number(b.question_id));
    }

    if (questionsOrder === 'desc') {
      return [...questions].sort((a, b) => Number(b.question_id) - Number(a.question_id));
    }

    return questions;
  })();
  const questionsForStats = hasQuestionLimit ? orderedQuestions.slice(0, availableQuestionInPool) : orderedQuestions;
  const questionCountForStats = questionsForStats.length;
  const passingGrade = Number(form.watch('quiz_option.passing_grade'));
  const totalMarksForStats = questionsForStats.reduce(
    (sum, question) => sum + Number(question.question_settings.question_mark || 0),
    0,
  );
  const requiredPassMarks = Math.ceil((passingGrade / 100) * totalMarksForStats);
  const requiredCorrectAnswers = (() => {
    if (questionsOrder === 'rand') {
      return '-';
    }

    if (requiredPassMarks <= 0) {
      return 0;
    }

    let accumulatedMarks = 0;
    for (const [index, question] of questionsForStats.entries()) {
      accumulatedMarks += Number(question.question_settings.question_mark || 0);
      if (accumulatedMarks >= requiredPassMarks) {
        return index + 1;
      }
    }

    return questionCountForStats;
  })();
  const timeLimitValue = Number(form.watch('quiz_option.time_limit.time_value'));
  const timeLimitType = form.watch('quiz_option.time_limit.time_type');
  const timePerQuestionInSeconds =
    questionCountForStats > 0 ? Math.floor(getTimeInSeconds(timeLimitValue, timeLimitType) / questionCountForStats) : 0;

  const queryClient = useQueryClient();

  const topics = queryClient.getQueryData(['Topic', courseId]) as CourseTopic[];

  return (
    <div css={styles.settings}>
      <div css={styles.left}>
        <div css={styles.card}>
          <h5>{__('Quiz scope', 'tutor')}</h5>

          <div css={styles.innerCard}>
            <Controller
              name="quiz_option.passing_grade"
              control={form.control}
              rules={{
                ...requiredRule(),
                validate: (value) => {
                  if (value > 100) {
                    return __('Passing grade cannot be greater than 100', 'tutor');
                  }

                  if (value < 0) {
                    return __('Passing grade cannot be less than 0', 'tutor');
                  }

                  return true;
                },
              }}
              render={(controllerProps) => (
                <FormInputWithContent
                  {...controllerProps}
                  isInlineLabel
                  size="small"
                  type="number"
                  label={__('Passing Grade', 'tutor')}
                  helpText={__('Set the minimum score percentage required to pass this quiz', 'tutor')}
                  wrapperCss={styles.maxWidth('67px')}
                  content="%"
                  contentPosition="right"
                  showVerticalBar={false}
                />
              )}
            />

            <Controller
              name="quiz_option.questions_order"
              control={form.control}
              render={(controllerProps) => (
                <FormSelectInput
                  {...controllerProps}
                  isInlineLabel
                  size="small"
                  wrapperCss={styles.maxWidth('124px')}
                  label={__('Question Order', 'tutor')}
                  placeholder={__('Select an option', 'tutor')}
                  options={[
                    { label: __('Random', 'tutor'), value: 'rand' },
                    { label: __('Sorting', 'tutor'), value: 'sorting' },
                    { label: __('Ascending', 'tutor'), value: 'asc' },
                    { label: __('Descending', 'tutor'), value: 'desc' },
                  ]}
                />
              )}
            />

            <hr />

            <div css={styles.inlineForm({ minHeight: '32px' })}>
              <Controller
                name="quiz_option.limit_attempts_allowed"
                control={form.control}
                render={(controllerProps) => (
                  <FormCheckbox
                    {...controllerProps}
                    label={__('Allow multiple attempts', 'tutor')}
                    helpText={__('Set the number of attempts allowed for this quiz. 0 means unlimited.', 'tutor')}
                  />
                )}
              />

              <Show when={form.watch('quiz_option.limit_attempts_allowed')}>
                <Controller
                  name="quiz_option.attempts_allowed"
                  control={form.control}
                  rules={{
                    ...requiredRule(),
                    validate: (value) => {
                      if (value >= 0) {
                        return true;
                      }
                      return __('Allowed attempts must be greater than or equal to 0', 'tutor');
                    },
                  }}
                  render={(controllerProps) => (
                    <FormInput
                      {...controllerProps}
                      type="number"
                      size="small"
                      selectOnFocus
                      isInlineLabel
                      style={styles.maxWidth('99px')}
                      formFieldWrapperCss={styles.width('auto')}
                      inputContainerCss={styles.justifyContent('flex-end')}
                    />
                  )}
                />
              </Show>
            </div>

            <Show when={contentType !== 'tutor_h5p_quiz'}>
              <div css={styles.inlineForm({ minHeight: '32px' })}>
                <Controller
                  name="quiz_option.limit_questions_to_answer"
                  control={form.control}
                  render={(controllerProps) => (
                    <FormCheckbox
                      {...controllerProps}
                      label={__('Set maximum questions per quiz', 'tutor')}
                      helpText={__(
                        'Set the number of quiz questions randomly from your question pool. If the set number exceeds available questions, all questions will be included',
                        'tutor',
                      )}
                    />
                  )}
                />

                <Show when={hasQuestionLimit}>
                  <Controller
                    name="quiz_option.max_questions_for_answer"
                    rules={{
                      ...requiredRule(),
                      validate: (value) => {
                        if (value <= 0) {
                          return __('Question limit must be greater than 0', 'tutor');
                        }
                        return true;
                      },
                    }}
                    control={form.control}
                    render={(controllerProps) => (
                      <FormInput
                        {...controllerProps}
                        type="number"
                        size="small"
                        isInlineLabel
                        selectOnFocus
                        style={styles.maxWidth('99px')}
                        formFieldWrapperCss={styles.width('auto')}
                        inputContainerCss={styles.justifyContent('flex-end')}
                      />
                    )}
                  />
                </Show>
              </div>

              <Show when={showPassRequired}>
                <Controller
                  control={form.control}
                  name="quiz_option.pass_is_required"
                  render={(controllerProps) => (
                    <FormSwitch {...controllerProps} label={__('Pass is required', 'tutor')} />
                  )}
                />
              </Show>
            </Show>
          </div>

          <h5>{__('Timing', 'tutor')}</h5>

          <div css={styles.innerCard}>
            <Show when={contentType !== 'tutor_h5p_quiz'}>
              <div css={styles.inlineForm({ minHeight: '32px' })}>
                <Controller
                  name="quiz_option.enable_time_limit"
                  control={form.control}
                  render={(controllerProps) => (
                    <FormCheckbox {...controllerProps} label={__('Set time limit', 'tutor')} />
                  )}
                />
                <Show when={form.watch('quiz_option.enable_time_limit')}>
                  <div css={styles.timeLimit}>
                    <Controller
                      name="quiz_option.time_limit.time_value"
                      control={form.control}
                      rules={{
                        ...requiredRule(),
                        validate: (value) => {
                          if (value <= 0) {
                            return __('Time limit must be greater than 0', 'tutor');
                          }
                          return true;
                        },
                      }}
                      render={(controllerProps) => (
                        <FormInput
                          {...controllerProps}
                          size="small"
                          type="number"
                          selectOnFocus
                          dataAttribute="data-time-limit"
                        />
                      )}
                    />

                    <Controller
                      name="quiz_option.time_limit.time_type"
                      control={form.control}
                      render={(controllerProps) => (
                        <FormSelectInput
                          {...controllerProps}
                          dataAttribute="data-time-limit-unit"
                          size="small"
                          options={[
                            { label: __('Sec', 'tutor'), value: 'seconds' },
                            { label: __('Min', 'tutor'), value: 'minutes' },
                            { label: __('Hour', 'tutor'), value: 'hours' },
                            { label: __('Days', 'tutor'), value: 'days' },
                            { label: __('Weeks', 'tutor'), value: 'weeks' },
                          ]}
                        />
                      )}
                    />
                  </div>
                </Show>
              </div>

              <Show when={form.watch('quiz_option.enable_time_limit')}>
                <Controller
                  name="quiz_option.hide_quiz_time_display"
                  control={form.control}
                  render={(controllerProps) => (
                    <FormSwitch {...controllerProps} label={__('Hide countdown timer', 'tutor')} />
                  )}
                />
              </Show>

              <hr />
            </Show>

            <div css={styles.inlineForm({ minHeight: '34px' })}>
              <Controller
                name="quiz_option.quiz_auto_start"
                control={form.control}
                render={(controllerProps) => (
                  <FormCheckbox {...controllerProps} label={__('Auto start quiz', 'tutor')} />
                )}
              />

              <Show when={form.watch('quiz_option.quiz_auto_start')}>
                <div css={styles.inlineForm({ withPrefix: true })}>
                  <div data-prefix>{__('After', 'tutor')}</div>
                  <Controller
                    name="quiz_option.auto_start_delay"
                    control={form.control}
                    render={(controllerProps) => (
                      <FormInputWithPresets
                        {...controllerProps}
                        size="small"
                        content={__('secs', 'tutor')}
                        contentPosition="right"
                        wrapperCss={styles.maxWidth('80px')}
                        contentCss={styles.minWidth('fit-content')}
                        formFieldWrapperCss={styles.width('auto')}
                        showVerticalBar={false}
                        presetOptions={[
                          {
                            label: __('2', 'tutor'),
                            value: '2',
                          },
                          {
                            label: __('5', 'tutor'),
                            value: '5',
                          },
                          {
                            label: __('7', 'tutor'),
                            value: '7',
                          },
                          {
                            label: __('10', 'tutor'),
                            value: '10',
                          },
                        ]}
                      />
                    )}
                  />
                </div>
              </Show>
            </div>
          </div>
        </div>
        <Show when={contentType !== 'tutor_h5p_quiz'}>
          <div css={styles.card}>
            <h5>{__('Navigation & Display', 'tutor')}</h5>

            <div css={styles.innerCard}>
              <Controller
                control={form.control}
                name="quiz_option.question_layout_view"
                render={(controllerProps) => (
                  <FormQuizLayoutSelect
                    {...controllerProps}
                    label={__('Layout', 'tutor')}
                    description={__('Choose how students will answer the questions.', 'tutor')}
                    options={[
                      {
                        label: __('Single Question', 'tutor'),
                        value: 'single_question',
                        image: <QuizSingleLayoutSvg width={72} height={92} />,
                      },
                      {
                        label: __('Full Page', 'tutor'),
                        value: 'question_below_each_other',
                        image: <QuizFullPageSvg width={72} height={92} />,
                      },
                    ]}
                  />
                )}
              />

              <Show when={form.watch('quiz_option.question_layout_view') === 'single_question'}>
                <hr />

                <div css={styles.inlineForm({ minHeight: '34px' })}>
                  <Controller
                    control={form.control}
                    name="quiz_option.enable_pagination"
                    render={(controllerProps) => (
                      <FormCheckbox
                        {...controllerProps}
                        label={__('Show pagination', 'tutor')}
                        helpText={
                          isLegacyLearningMode
                            ? __('Pagination style is unavailable while learning mode is set to Legacy.', 'tutor')
                            : undefined
                        }
                      />
                    )}
                  />

                  <Show when={form.watch('quiz_option.enable_pagination')}>
                    <div css={styles.paginationIcons}>
                      <SVGIcon
                        name={getPaginationTypeIcon(form.watch('quiz_option.pagination_type'))}
                        width={40}
                        height={32}
                      />
                    </div>
                    <Controller
                      control={form.control}
                      name="quiz_option.pagination_type"
                      render={(controllerProps) => (
                        <FormSelectInput
                          {...controllerProps}
                          size="small"
                          isInlineLabel
                          disabled={isLegacyLearningMode}
                          options={[
                            {
                              label: __('Shapes', 'tutor'),
                              value: 'shape',
                            },
                            {
                              label: __('Numbers', 'tutor'),
                              value: 'number',
                            },
                            {
                              label: __('Radio', 'tutor'),
                              value: 'radio',
                            },
                          ]}
                        />
                      )}
                    />
                  </Show>
                </div>

                <div css={styles.inlineForm}>
                  <Controller
                    control={form.control}
                    name="quiz_option.enable_answer_reveal"
                    render={(controllerProps) => (
                      <FormCheckbox {...controllerProps} label={__('Reveal answers after submission', 'tutor')} />
                    )}
                  />

                  <Show when={form.watch('quiz_option.enable_answer_reveal')}>
                    <div css={styles.inlineForm({ withPrefix: true })}>
                      <div data-prefix>{__('For', 'tutor')}</div>
                      <Controller
                        name="quiz_option.answers_reveal_duration"
                        control={form.control}
                        render={(controllerProps) => (
                          <FormInputWithPresets
                            {...controllerProps}
                            size="small"
                            content={__('secs', 'tutor')}
                            contentPosition="right"
                            wrapperCss={styles.maxWidth('80px')}
                            contentCss={styles.minWidth('fit-content')}
                            formFieldWrapperCss={styles.width('auto')}
                            showVerticalBar={false}
                            presetOptions={[
                              {
                                label: __('2', 'tutor'),
                                value: '2',
                              },
                              {
                                label: __('5', 'tutor'),
                                value: '5',
                              },
                              {
                                label: __('7', 'tutor'),
                                value: '7',
                              },
                              {
                                label: __('10', 'tutor'),
                                value: '10',
                              },
                            ]}
                          />
                        )}
                      />
                    </div>
                  </Show>
                </div>

                <hr />

                <Show when={!form.watch('quiz_option.enable_pagination')}>
                  <Controller
                    control={form.control}
                    name="quiz_option.hide_previous_button"
                    render={(controllerProps) => (
                      <FormSwitch {...controllerProps} label={__('Hide "Previous" button', 'tutor')} />
                    )}
                  />
                </Show>

                <Controller
                  control={form.control}
                  name="quiz_option.hide_question_number_overview"
                  render={(controllerProps) => (
                    <FormSwitch {...controllerProps} label={__('Hide question number', 'tutor')} />
                  )}
                />
              </Show>
            </div>
          </div>
        </Show>

        <Show
          when={
            (isAddonEnabled(Addons.CONTENT_DRIP) && contentDripType) || hasOpenEndedQuestions || hasShortAnswerQuestions
          }
        >
          <div css={styles.card}>
            <Show when={hasOpenEndedQuestions || hasShortAnswerQuestions}>
              <h5>{__('Character Limits', 'tutor')}</h5>

              <div css={styles.innerCard}>
                <Show when={hasOpenEndedQuestions}>
                  <div css={styles.inlineForm}>
                    <Controller
                      name="quiz_option.open_ended_answer_characters_limit"
                      control={form.control}
                      render={(controllerProps) => (
                        <FormInput
                          {...controllerProps}
                          type="number"
                          isInlineLabel
                          size="small"
                          style={styles.maxWidth('80px')}
                          label={__('Open-Ended/Essay Answer', 'tutor')}
                          helpText={__(
                            'Set the number of characters allowed for open-ended/essay answers. Leave empty to disable.',
                            'tutor',
                          )}
                          selectOnFocus
                        />
                      )}
                    />
                  </div>
                </Show>

                <Show when={hasShortAnswerQuestions}>
                  <div css={styles.inlineForm}>
                    <Controller
                      name="quiz_option.short_answer_characters_limit"
                      control={form.control}
                      render={(controllerProps) => (
                        <FormInput
                          {...controllerProps}
                          type="number"
                          size="small"
                          style={styles.maxWidth('80px')}
                          isInlineLabel
                          label={__('Short Answer', 'tutor')}
                          helpText={__(
                            'Set the number of characters allowed for short answers. Leave empty to disable.',
                            'tutor',
                          )}
                          selectOnFocus
                        />
                      )}
                    />
                  </div>
                </Show>
              </div>
            </Show>

            <Show when={isAddonEnabled(Addons.CONTENT_DRIP) && contentType !== 'tutor_h5p_quiz'}>
              <Show when={contentDripType === 'unlock_by_date'}>
                <h5 css={styles.contentDripLabel}>
                  <SVGIcon name="contentDrip" height={24} width={24} />
                  {__('Unlock Date', 'tutor')}
                  <Tooltip content={__('Set the date when the quiz will be available.', 'tutor')}>
                    <SVGIcon name="info" width={20} height={20} />
                  </Tooltip>
                </h5>

                <div css={styles.innerCard}>
                  <Controller
                    name="quiz_option.content_drip_settings.unlock_date"
                    control={form.control}
                    render={(controllerProps) => (
                      <FormDateInput {...controllerProps} placeholder={__('Select Unlock Date', 'tutor')} />
                    )}
                  />
                </div>
              </Show>

              <Show when={contentDripType === 'after_finishing_prerequisites'}>
                <h5 css={styles.contentDripLabel}>
                  <SVGIcon name="contentDrip" height={24} width={24} />
                  {__('Prerequisites', 'tutor')}
                  <Tooltip content={__('Select items that should be complete before this item', 'tutor')}>
                    <SVGIcon name="info" width={20} height={20} />
                  </Tooltip>
                </h5>

                <div css={styles.innerCard}>
                  <Controller
                    name="quiz_option.content_drip_settings.prerequisites"
                    control={form.control}
                    render={(controllerProps) => (
                      <FormTopicPrerequisites
                        {...controllerProps}
                        placeholder={__('Select Prerequisite', 'tutor')}
                        options={
                          topics.reduce((topics, topic) => {
                            topics.push({
                              ...topic,
                              contents: topic.contents.filter((content) => String(content.ID) !== String(quizId)),
                            });

                            return topics;
                          }, [] as CourseTopic[]) || []
                        }
                        isSearchable
                      />
                    )}
                  />
                </div>
              </Show>

              <Show when={contentDripType === 'specific_days'}>
                <h5 css={styles.contentDripLabel}>
                  <SVGIcon name="contentDrip" height={24} width={24} />
                  {__('Available after days', 'tutor')}
                  <Tooltip content={__('This quiz will be available after the given number of days.', 'tutor')}>
                    <SVGIcon name="info" width={20} height={20} />
                  </Tooltip>
                </h5>

                <div css={styles.innerCard}>
                  <Controller
                    name="quiz_option.content_drip_settings.after_xdays_of_enroll"
                    control={form.control}
                    render={(controllerProps) => (
                      <FormInput {...controllerProps} type="number" placeholder="0" selectOnFocus />
                    )}
                  />
                </div>
              </Show>
            </Show>
          </div>
        </Show>

        <CourseBuilderInjectionSlot section="Curriculum.Quiz.bottom_of_settings" form={form} />
      </div>

      <div>
        <div css={styles.overview}>
          <div css={styles.questionPool}>
            <div data-question-pool>
              <div data-question-pool-label>{__('Total questions in pool', 'tutor')}</div>
              <div data-question-count>
                <span>{questions.length}</span>
                <span>{__('Q', 'tutor')}</span>
              </div>
            </div>

            <SVGIcon name="arrowRight2" width={24} height={24} />

            <div data-question-pool>
              <div data-question-pool-label>{__('Available to answer', 'tutor')}</div>
              <div data-question-count data-available-count>
                <span>{availableQuestionInPool}</span>
                <span>{__('Q', 'tutor')}</span>
              </div>
            </div>
          </div>

          <div css={styles.questionPoolBar(usedQuestionCountPercentage + '%')}>
            <div data-question-pool-bar />
            <div>
              {sprintf(
                // translators: %1$s is the number of available questions, %2$s is the total number of questions.
                __('Students will have %1$s out of %2$s questions available to answer.', 'tutor'),
                availableQuestionInPool,
                questionsCount,
              )}
            </div>
          </div>

          <div css={styles.infoCardWrapper}>
            <Show when={hasTimeLimit}>
              <div css={styles.infoCard}>
                <div data-title>
                  {form.watch('quiz_option.time_limit.time_value')} {form.watch('quiz_option.time_limit.time_type')}
                </div>
                <div data-subtitle>{__('Time limit', 'tutor')}</div>

                <div data-footer>
                  <SVGIcon name="stopwatch" width={12} height={12} />
                  {timePerQuestionInSeconds > 0 ? formatTimePerQuestion(timePerQuestionInSeconds) : __('N/A', 'tutor')}
                </div>
              </div>
            </Show>

            <div css={styles.infoCard}>
              <div data-title>
                {form.watch('quiz_option.passing_grade')}
                {__('%', 'tutor')}
              </div>
              <div data-subtitle>{__('Passing grade', 'tutor')}</div>

              <div data-footer>
                <SVGIcon name="starLine" width={12} height={12} />
                {questionsOrder === 'rand'
                  ? '-'
                  : sprintf(
                      // translators: %1$s is the number of required correct answers, %2$s is total available questions.
                      __('%1$s of %2$s correct', 'tutor'),
                      requiredCorrectAnswers,
                      questionCountForStats,
                    )}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default QuizSettings;

const styles = {
  maxWidth: (width: string) => css`
    max-width: ${width};
  `,
  minWidth: (width: string) => css`
    min-width: ${width};
  `,
  justifyContent: (justifyContent: string) => css`
    justify-content: ${justifyContent};
  `,
  width: (width: string) => css`
    width: ${width};
  `,
  settings: css`
    display: grid;
    grid-template-columns: 1.5fr 1fr;
    gap: ${spacing[12]};

    ${Breakpoint.smallMobile} {
      grid-template-columns: 1fr;
    }
  `,
  left: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[8]};
  `,
  overview: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[8]};
    position: sticky;
    top: 0;
    align-self: start;
    border-radius: ${borderRadius[12]};
    border: 1px solid ${colorTokens.stroke.divider};
    padding: ${spacing[16]};
    background-color: ${colorTokens.background.white};

    ${Breakpoint.smallMobile} {
      position: static;
    }
  `,
  questionPool: css`
    ${styleUtils.display.flex()};
    align-items: center;
    justify-content: space-between;
    gap: ${spacing[8]};

    svg {
      margin-top: ${spacing[20]};
      color: ${colorTokens.icon.default};
    }

    [data-question-pool] {
      ${styleUtils.display.flex('column')};
      gap: ${spacing[4]};

      [data-question-pool-label] {
        ${typography.tiny()};
        color: ${colorTokens.text.subdued};
      }

      [data-question-count] {
        ${typography.heading5('medium')};
        color: ${colorTokens.text.subdued};

        span:last-of-type {
          ${typography.tiny()};
          margin-left: ${spacing[2]};
          color: ${colorTokens.text.hints};
        }

        &[data-available-count] {
          color: ${colorTokens.text.success};
        }
      }
    }
  `,
  questionPoolBar: (width: string) => css`
    ${styleUtils.display.flex('column')};
    ${typography.tiny()};
    color: ${colorTokens.text.subdued};
    gap: ${spacing[4]};

    [data-question-pool-bar] {
      position: relative;
      width: 100%;
      height: 5px;
      background-color: ${colorTokens.action.secondary.gray};
      border-radius: ${borderRadius[50]};
      box-shadow: 0px 0px 1.75px 0px #00000029 inset;

      &::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: ${width};
        height: 100%;
        background-color: ${colorTokens.action.primary.default};
        border-radius: ${borderRadius[50]};
      }
    }
  `,
  infoCardWrapper: css`
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: ${spacing[8]};
  `,
  infoCard: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[4]};
    padding: ${spacing[8]};
    border-radius: ${borderRadius[12]};
    background-color: ${colorTokens.surface.courseBuilder};
    height: 106px;

    [data-title] {
      ${typography.caption('medium')};
      color: ${colorTokens.text.title};
    }

    [data-subtitle] {
      ${typography.tiny()};
      color: ${colorTokens.text.subdued};
    }

    [data-footer] {
      ${styleUtils.display.flex()};
      align-items: center;
      ${typography.tiny()};
      color: ${colorTokens.text.subdued};
      margin-top: auto;

      svg {
        color: ${colorTokens.icon.default};
        margin-right: ${spacing[4]};
      }
    }
  `,
  card: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[8]};
    border-radius: ${borderRadius[12]};
    border: 1px solid ${colorTokens.stroke.divider};
    padding: ${spacing[12]};
    background-color: ${colorTokens.background.white};

    h5 {
      ${typography.caption()};
      color: ${colorTokens.text.title};
    }

    hr {
      width: 100%;
      background-color: ${colorTokens.stroke.divider};
    }
  `,
  innerCard: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[8]};
    padding: ${spacing[12]};
    border-radius: ${borderRadius[8]};
    background-color: ${colorTokens.surface.courseBuilder};
  `,
  inlineForm: ({ withPrefix, minHeight }: { withPrefix?: boolean; minHeight?: string } = {}) => css`
    ${styleUtils.display.flex('row')};
    width: 100%;
    align-items: center;
    justify-content: space-between;
    gap: ${spacing[8]};
    ${minHeight &&
    css`
      min-height: ${minHeight};
    `}

    ${withPrefix &&
    css`
      justify-content: flex-end;

      [data-prefix] {
        ${typography.body('regular')};
        color: ${colorTokens.text.hints};
      }
    `}
  `,
  paginationIcons: css`
    ${styleUtils.flexCenter()};
    background-color: ${colorTokens.bg.white};
    border: 1px solid ${colorTokens.stroke.divider};
    border-radius: ${borderRadius[4]};
  `,
  timeLimit: css`
    display: grid;
    align-items: start;
    grid-template-columns: 48px 84px;

    & input {
      border: 1px solid ${colorTokens.stroke.default};

      &[data-time-limit] {
        border-radius: ${borderRadius[6]} 0 0 ${borderRadius[6]};
        border-right: none;

        &:focus {
          border-right: 1px solid ${colorTokens.stroke.default};
          z-index: ${zIndex.positive};
        }
      }
      &[data-time-limit-unit] {
        border-radius: 0 ${borderRadius[6]} ${borderRadius[6]} 0;
      }
    }
  `,
  contentDripLabel: css`
    ${styleUtils.display.flex()};
    align-items: center;

    svg {
      margin-right: ${spacing[4]};
      color: ${colorTokens.icon.success};
    }

    * > svg {
      ${styleUtils.flexCenter()};
      margin-left: ${spacing[4]};
      color: ${colorTokens.color.black[30]};
    }
  `,
};
