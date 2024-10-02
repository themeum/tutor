import FormInput from '@Components/fields/FormInput';
import { css } from '@emotion/react';
import { useQueryClient } from '@tanstack/react-query';
import { __ } from '@wordpress/i18n';
import { Controller, useFormContext } from 'react-hook-form';

import SVGIcon from '@Atoms/SVGIcon';
import Card from '@Molecules/Card';

import FormDateInput from '@Components/fields/FormDateInput';
import FormInputWithContent from '@Components/fields/FormInputWithContent';
import FormSelectInput from '@Components/fields/FormSelectInput';
import FormSwitch from '@Components/fields/FormSwitch';
import FormTopicPrerequisites from '@Components/fields/FormTopicPrerequisites';

import { Addons } from '@Config/constants';
import { colorTokens, spacing } from '@Config/styles';
import Show from '@Controls/Show';
import { useQuizModalContext } from '@CourseBuilderContexts/QuizModalContext';
import type { ContentDripType } from '@CourseBuilderServices/course';
import type { CourseTopic } from '@CourseBuilderServices/curriculum';
import type { QuizForm } from '@CourseBuilderServices/quiz';
import { getCourseId, isAddonEnabled } from '@CourseBuilderUtils/utils';
import { styleUtils } from '@Utils/style-utils';

const courseId = getCourseId();

interface QuizSettingsProps {
  contentDripType: ContentDripType;
}

const QuizSettings = ({ contentDripType }: QuizSettingsProps) => {
  const { quizId, contentType } = useQuizModalContext();
  const form = useFormContext<QuizForm>();
  const feedbackMode = form.watch('quiz_option.feedback_mode');
  const showPassRequired =
    isAddonEnabled(Addons.CONTENT_DRIP) && contentDripType === 'unlock_sequentially' && feedbackMode === 'retry';
  const prerequisites = form.watch('quiz_option.content_drip_settings.prerequisites');

  const queryClient = useQueryClient();

  const topics = queryClient.getQueryData(['Topic', courseId]) as CourseTopic[];

  return (
    <div css={styles.settings}>
      <Card title={__('Basic Settings', 'tutor')} collapsedAnimationDependencies={[feedbackMode, prerequisites.length]}>
        <div css={styles.formWrapper}>
          <div css={styles.timeWrapper}>
            <Controller
              name="quiz_option.time_limit.time_value"
              control={form.control}
              render={(controllerProps) => (
                <FormInput
                  {...controllerProps}
                  type="number"
                  label={__('Time limit', 'tutor')}
                  helpText={__('Set a time limit for this quiz. A value of “0” indicates no time limit', 'tutor')}
                  selectOnFocus
                />
              )}
            />
            <Controller
              name="quiz_option.time_limit.time_type"
              control={form.control}
              render={(controllerProps) => (
                <FormSelectInput
                  {...controllerProps}
                  options={[
                    { label: __('Seconds', 'tutor'), value: 'seconds' },
                    { label: __('Minutes', 'tutor'), value: 'minutes' },
                    { label: __('Hours', 'tutor'), value: 'hours' },
                    { label: __('Days', 'tutor'), value: 'days' },
                    { label: __('Weeks', 'tutor'), value: 'weeks' },
                  ]}
                />
              )}
            />
          </div>

          <Show when={contentType !== 'tutor_h5p_quiz'}>
            <Controller
              name="quiz_option.hide_quiz_time_display"
              control={form.control}
              render={(controllerProps) => (
                <FormSwitch
                  {...controllerProps}
                  label={__('Display Quiz time', 'tutor')}
                  helpText={__('Show the time limit during the quiz', 'tutor')}
                />
              )}
            />

            <Controller
              name="quiz_option.feedback_mode"
              control={form.control}
              render={(controllerProps) => (
                <FormSelectInput
                  {...controllerProps}
                  label={__('Feedback Mode', 'tutor')}
                  leftIcon={<SVGIcon name="eye" width={32} height={32} />}
                  options={[
                    {
                      label: __('Default', 'tutor'),
                      value: 'default',
                      description: __('Answers shown after quiz is finished', 'tutor'),
                    },
                    {
                      label: __('Reveal Mode', 'tutor'),
                      value: 'reveal',
                      description: __('Show the answer after attempting the quiz.', 'tutor'),
                    },
                    {
                      label: __('Retry', 'tutor'),
                      value: 'retry',
                      description: __('Allows students to retake the quiz after their first attempt.', 'tutor'),
                    },
                  ]}
                />
              )}
            />
          </Show>

          <Show when={feedbackMode === 'retry'}>
            <Controller
              name="quiz_option.attempts_allowed"
              control={form.control}
              rules={{ max: 20, min: 0 }}
              render={(controllerProps) => (
                <FormInput
                  {...controllerProps}
                  type="number"
                  label={__('Attempts Allowed', 'tutor')}
                  helpText={__(
                    'Define how many times a student can retake this quiz. Setting it to "0" allows unlimited attempts',
                    'tutor',
                  )}
                  selectOnFocus
                />
              )}
            />
          </Show>

          <Show when={showPassRequired && contentType !== 'tutor_h5p_quiz'}>
            <Controller
              name="quiz_option.pass_is_required"
              control={form.control}
              render={(controllerProps) => (
                <FormSwitch
                  {...controllerProps}
                  label={__('Passing is Required', 'tutor')}
                  helpText={__(
                    'By enabling this option, the student must have to pass it to access the next quiz',
                    'tutor',
                  )}
                />
              )}
            />
          </Show>

          <Controller
            name="quiz_option.passing_grade"
            control={form.control}
            render={(controllerProps) => (
              <FormInputWithContent
                {...controllerProps}
                label={__('Passing Grade', 'tutor')}
                helpText={__('Set the minimum score percentage required to pass this quiz', 'tutor')}
                content="%"
                contentPosition="right"
                contentCss={styleUtils.inputCurrencyStyle}
              />
            )}
          />

          <Show when={contentType !== 'tutor_h5p_quiz'}>
            <Controller
              name="quiz_option.max_questions_for_answer"
              control={form.control}
              render={(controllerProps) => (
                <FormInput
                  {...controllerProps}
                  type="number"
                  label={__('Max Question Allowed to Answer', 'tutor')}
                  helpText={__(
                    'Set the number of quiz questions randomly from your question pool. If the set number exceeds available questions, all questions will be included',
                    'tutor',
                  )}
                  selectOnFocus
                />
              )}
            />
          </Show>

          <Show when={isAddonEnabled(Addons.CONTENT_DRIP)}>
            <Show when={contentDripType === 'specific_days'}>
              <Controller
                name="quiz_option.content_drip_settings.after_xdays_of_enroll"
                control={form.control}
                render={(controllerProps) => (
                  <FormInput
                    {...controllerProps}
                    type="number"
                    label={
                      <div css={styles.contentDripLabel}>
                        <SVGIcon name="contentDrip" height={24} width={24} />
                        {__('Available after days', 'tutor')}
                      </div>
                    }
                    helpText={__('This lesson will be available after the given number of days', 'tutor')}
                    placeholder="0"
                    selectOnFocus
                  />
                )}
              />
            </Show>

            <Show when={contentDripType === 'unlock_by_date'}>
              <Controller
                name="quiz_option.content_drip_settings.unlock_date"
                control={form.control}
                render={(controllerProps) => (
                  <FormDateInput
                    {...controllerProps}
                    label={
                      <div css={styles.contentDripLabel}>
                        <SVGIcon name="contentDrip" height={24} width={24} />
                        {__('Unlock Date', 'tutor')}
                      </div>
                    }
                    placeholder={__('Select Unlock Date', 'tutor')}
                    helpText={__(
                      'This lesson will be available from the given date. Leave empty to make it available immediately',
                      'tutor',
                    )}
                  />
                )}
              />
            </Show>

            <Show when={contentDripType === 'after_finishing_prerequisites'}>
              <Controller
                name="quiz_option.content_drip_settings.prerequisites"
                control={form.control}
                render={(controllerProps) => (
                  <FormTopicPrerequisites
                    {...controllerProps}
                    label={
                      <div css={styles.contentDripLabel}>
                        <SVGIcon name="contentDrip" height={24} width={24} />
                        {__('Prerequisites', 'tutor')}
                      </div>
                    }
                    placeholder={__('Select Prerequisite', 'tutor')}
                    options={
                      topics.reduce((topics, topic) => {
                        topics.push({
                          ...topic,
                          contents: topic.contents.filter((content) => content.ID !== quizId),
                        });

                        return topics;
                      }, [] as CourseTopic[]) || []
                    }
                    isSearchable
                    helpText={__('Select items that should be complete before this item', 'tutor')}
                  />
                )}
              />
            </Show>
          </Show>
        </div>
      </Card>

      <Card title={__('Advanced Settings', 'tutor')}>
        <div css={styles.formWrapper}>
          <Controller
            name="quiz_option.quiz_auto_start"
            control={form.control}
            render={(controllerProps) => (
              <FormSwitch
                {...controllerProps}
                label={__('Quiz Auto Start', 'tutor')}
                helpText={__('When enabled, the quiz begins immediately as soon as the page loads', 'tutor')}
              />
            )}
          />

          <div css={styles.questionLayoutAndOrder}>
            <Show when={contentType !== 'tutor_h5p_quiz'}>
              <Controller
                name="quiz_option.question_layout_view"
                control={form.control}
                render={(controllerProps) => (
                  <FormSelectInput
                    {...controllerProps}
                    label={__('Question Layout', 'tutor')}
                    placeholder={__('Select an option', 'tutor')}
                    options={[
                      { label: __('Single question', 'tutor'), value: 'single_question' },
                      { label: __('Question Pagination', 'tutor'), value: 'question_pagination' },
                      { label: __('Question below each other', 'tutor'), value: 'question_below_each_other' },
                    ]}
                  />
                )}
              />
            </Show>

            <Controller
              name="quiz_option.questions_order"
              control={form.control}
              render={(controllerProps) => (
                <FormSelectInput
                  {...controllerProps}
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
          </div>

          <Show when={contentType !== 'tutor_h5p_quiz'}>
            <Controller
              name="quiz_option.hide_question_number_overview"
              control={form.control}
              render={(controllerProps) => (
                <FormSwitch
                  {...controllerProps}
                  label={__('Question number visibility', 'tutor')}
                  helpText={__('Hide question number overview', 'tutor')}
                />
              )}
            />

            <Controller
              name="quiz_option.short_answer_characters_limit"
              control={form.control}
              render={(controllerProps) => (
                <FormInput
                  {...controllerProps}
                  type="number"
                  label={__('Set a character limit for Short Answers', 'tutor')}
                  helpText={__(
                    'Student will place answer in short answer question type within this characters limit.',
                    'tutor',
                  )}
                  selectOnFocus
                />
              )}
            />

            <Controller
              name="quiz_option.open_ended_answer_characters_limit"
              control={form.control}
              render={(controllerProps) => (
                <FormInput
                  {...controllerProps}
                  label={__('Open-Ended/Essay questions answer character limit', 'tutor')}
                  helpText={__('Set a character limit for open-ended/essay answers', 'tutor')}
                  selectOnFocus
                />
              )}
            />
          </Show>
        </div>
      </Card>
    </div>
  );
};

export default QuizSettings;

const styles = {
  settings: css`
    ${styleUtils.display.flex('column')}
    gap: ${spacing[24]};
  `,
  formWrapper: css`
    ${styleUtils.display.flex('column')}
    gap: ${spacing[20]};
  `,
  timeWrapper: css`
    ${styleUtils.display.flex()}
    align-items: flex-end;
    gap: ${spacing[8]};
  `,
  questionLayoutAndOrder: css`
    ${styleUtils.display.flex()}
    gap: ${spacing[20]};
  `,
  contentDripLabel: css`
    display: flex;
    align-items: center;

    svg {
      margin-right: ${spacing[4]};
      color: ${colorTokens.icon.success};
    }
  `,
};
