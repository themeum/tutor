import FormInput from '@TutorShared/components/fields/FormInput';
import { css } from '@emotion/react';
import { useQueryClient } from '@tanstack/react-query';
import { __ } from '@wordpress/i18n';
import { Controller, useFormContext } from 'react-hook-form';

import SVGIcon from '@TutorShared/atoms/SVGIcon';

import FormDateInput from '@TutorShared/components/fields/FormDateInput';
import FormInputWithContent from '@TutorShared/components/fields/FormInputWithContent';
import FormSelectInput from '@TutorShared/components/fields/FormSelectInput';
import FormSwitch from '@TutorShared/components/fields/FormSwitch';
import FormTopicPrerequisites from '@TutorShared/components/fields/FormTopicPrerequisites';

import CourseBuilderInjectionSlot from '@CourseBuilderComponents/CourseBuilderSlot';
import { useQuizModalContext } from '@CourseBuilderContexts/QuizModalContext';
import type { ContentDripType } from '@CourseBuilderServices/course';
import type { CourseTopic } from '@CourseBuilderServices/curriculum';
import type { QuizForm } from '@CourseBuilderServices/quiz';
import { getCourseId } from '@CourseBuilderUtils/utils';
import FormCheckbox from '@TutorShared/components/fields/FormCheckbox';
import FormInputWithPresets from '@TutorShared/components/fields/FormInputWithPresets';
import { borderRadius, Breakpoint, colorTokens, spacing, zIndex } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import Show from '@TutorShared/controls/Show';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { requiredRule } from '@TutorShared/utils/validation';

import FormQuizLayoutSelect from './FormQuizLayoutSelect';

import QuizFullPageSvg from '@SharedImages/quiz-fullpage.svg';
import QuizSingleLayoutSvg from '@SharedImages/quiz-single-question.svg';
import Tooltip from '@TutorShared/atoms/Tooltip';

const courseId = getCourseId();

interface QuizSettingsProps {
  contentDripType: ContentDripType;
}

const QuizSettings = ({ contentDripType }: QuizSettingsProps) => {
  const { quizId, contentType } = useQuizModalContext();
  const form = useFormContext<QuizForm>();
  const hasOpenEndedQuestions = form.watch('questions').some((question) => question.question_type === 'open_ended');
  const hasShortAnswerQuestions = form.watch('questions').some((question) => question.question_type === 'short_answer');

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

            <div css={styles.inlineForm}>
              <Controller
                name="quiz_option.limit_attempts_allowed"
                control={form.control}
                render={(controllerProps) => (
                  <FormCheckbox {...controllerProps} label={__('Limit attempts allowed', 'tutor')} />
                )}
              />

              <Show when={form.watch('quiz_option.limit_attempts_allowed')}>
                <Controller
                  name="quiz_option.attempts_allowed"
                  control={form.control}
                  rules={{
                    ...requiredRule(),
                    validate: (value) => {
                      if (value >= 1 && value <= 20) {
                        return true;
                      }
                      return __('Allowed attempts must be between 0 and 20', 'tutor');
                    },
                  }}
                  render={(controllerProps) => (
                    <FormInput
                      {...controllerProps}
                      type="number"
                      size="small"
                      label={<>&nbsp;</>}
                      selectOnFocus
                      isInlineLabel
                      style={styles.maxWidth('99px')}
                    />
                  )}
                />
              </Show>
            </div>

            <Show when={contentType !== 'tutor_h5p_quiz'}>
              <div css={styles.inlineForm}>
                <Controller
                  name="quiz_option.limit_questions_to_answer"
                  rules={requiredRule()}
                  control={form.control}
                  render={(controllerProps) => (
                    <FormCheckbox
                      {...controllerProps}
                      label={__('Limit questions to answer', 'tutor')}
                      helpText={__(
                        'Set the number of quiz questions randomly from your question pool. If the set number exceeds available questions, all questions will be included',
                        'tutor',
                      )}
                    />
                  )}
                />

                <Show when={form.watch('quiz_option.limit_questions_to_answer')}>
                  <Controller
                    name="quiz_option.max_questions_for_answer"
                    rules={requiredRule()}
                    control={form.control}
                    render={(controllerProps) => (
                      <FormInput
                        {...controllerProps}
                        type="number"
                        size="small"
                        label={<>&nbsp;</>}
                        isInlineLabel
                        selectOnFocus
                        style={styles.maxWidth('99px')}
                      />
                    )}
                  />
                </Show>
              </div>
            </Show>
          </div>

          <h5>{__('Timing', 'tutor')}</h5>

          <div css={styles.innerCard}>
            <Show when={contentType !== 'tutor_h5p_quiz'}>
              <div css={styles.inlineForm}>
                <Controller
                  name="quiz_option.enable_time_limit"
                  control={form.control}
                  rules={requiredRule()}
                  render={(controllerProps) => (
                    <FormCheckbox {...controllerProps} label={__('Set Time Limit', 'tutor')} />
                  )}
                />
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
              </div>

              <Show when={form.watch('quiz_option.enable_time_limit')}>
                <Controller
                  name="quiz_option.hide_quiz_time_display"
                  control={form.control}
                  render={(controllerProps) => (
                    <FormSwitch {...controllerProps} label={__('Hide quiz timer from students', 'tutor')} />
                  )}
                />
              </Show>

              <hr />
            </Show>

            <div css={styles.inlineForm}>
              <Controller
                name="quiz_option.quiz_auto_start"
                control={form.control}
                render={(controllerProps) => (
                  <FormCheckbox {...controllerProps} label={__('Auto start quiz', 'tutor')} />
                )}
              />

              <Show when={form.watch('quiz_option.quiz_auto_start')}>
                <div css={styles.inlineForm}>
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
                        wrapperCss={styles.maxWidth('120px')}
                        contentCss={styles.minWidth('fit-content')}
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
                      value: 'question_pagination',
                      image: <QuizFullPageSvg width={72} height={92} />,
                    },
                  ]}
                />
              )}
            />

            <Show when={form.watch('quiz_option.question_layout_view') === 'single_question'}>
              <hr />

              <div css={styles.inlineForm}>
                <Controller
                  control={form.control}
                  name="quiz_option.enable_pagination"
                  render={(controllerProps) => (
                    <FormCheckbox {...controllerProps} label={__('Show pagination', 'tutor')} />
                  )}
                />

                <Show when={form.watch('quiz_option.enable_pagination')}>
                  <Controller
                    control={form.control}
                    name="quiz_option.pagination_type"
                    render={(controllerProps) => (
                      <FormSelectInput
                        {...controllerProps}
                        size="small"
                        isInlineLabel
                        label={<>&nbsp;</>}
                        options={[
                          {
                            label: __('Shapes', 'tutor'),
                            value: 'shape',
                            icon: 'quizShape',
                          },
                          {
                            label: __('Numbers', 'tutor'),
                            value: 'number',
                            icon: 'quizNumber',
                          },
                          {
                            label: __('Radio', 'tutor'),
                            value: 'radio',
                            icon: 'quizRadio',
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
                  <div css={styles.inlineForm}>
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
                          wrapperCss={styles.maxWidth('120px')}
                          contentCss={styles.minWidth('fit-content')}
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

              <Controller
                control={form.control}
                name="quiz_option.hide_previous_button"
                render={(controllerProps) => (
                  <FormSwitch {...controllerProps} label={__('Hide previous button from students', 'tutor')} />
                )}
              />

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

        <Show when={contentDripType || hasOpenEndedQuestions || hasShortAnswerQuestions}>
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
          </div>
        </Show>
      </div>

      <CourseBuilderInjectionSlot section="Curriculum.Quiz.bottom_of_settings" form={form} />
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
  settings: css`
    display: grid;
    grid-template-columns: 439px 305px;
    gap: ${spacing[12]};

    ${Breakpoint.smallMobile} {
      grid-template-columns: 1fr;
    }
  `,
  left: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[8]};
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
  inlineForm: css`
    ${styleUtils.display.flex('row')};
    align-items: center;
    justify-content: space-between;
    gap: ${spacing[8]};

    [data-prefix] {
      ${typography.body('regular')};
      color: ${colorTokens.text.hints};
    }
  `,
  formWrapper: css`
    ${styleUtils.display.flex('column')}
    gap: ${spacing[20]};
  `,
  timeWrapper: css`
    ${styleUtils.display.flex()}
    align-items: flex-start;
    gap: ${spacing[8]};
  `,
  timeLimit: css`
    display: grid;
    align-items: end;
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
  questionLayoutAndOrder: css`
    ${styleUtils.display.flex()}
    gap: ${spacing[20]};

    ${Breakpoint.smallMobile} {
      flex-direction: column;
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
