import FormInput from '@TutorShared/components/fields/FormInput';
import { css } from '@emotion/react';
import { useQueryClient } from '@tanstack/react-query';
import { __ } from '@wordpress/i18n';
import { Controller, useFormContext } from 'react-hook-form';

import SVGIcon from '@TutorShared/atoms/SVGIcon';
import Card from '@TutorShared/molecules/Card';

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
import { Addons } from '@TutorShared/config/constants';
import { Breakpoint, colorTokens, spacing } from '@TutorShared/config/styles';
import Show from '@TutorShared/controls/Show';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { isAddonEnabled } from '@TutorShared/utils/util';
import { requiredRule } from '@TutorShared/utils/validation';

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
  const quizSettingsValidationErrorLength = Object.keys(form.formState.errors).length;

  return (
    <div css={styles.settings}>
      <Card
        title={__('Basic Settings', 'tutor')}
        collapsedAnimationDependencies={[feedbackMode, prerequisites?.length, quizSettingsValidationErrorLength]}
      >
        <div css={styles.formWrapper}>
          <Show when={contentType !== 'tutor_h5p_quiz'}>
            <div css={styles.timeWrapper}>
              <Controller
                name="quiz_option.time_limit.time_value"
                control={form.control}
                rules={requiredRule()}
                render={(controllerProps) => (
                  <FormInput
                    {...controllerProps}
                    type="number"
                    label={__('Time Limit', 'tutor')}
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
                    label={<>&nbsp;</>}
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
          </Show>

          <Show when={contentType !== 'tutor_h5p_quiz'}>
            <Controller
              name="quiz_option.hide_quiz_time_display"
              control={form.control}
              render={(controllerProps) => <FormSwitch {...controllerProps} label={__('Hide Quiz Time', 'tutor')} />}
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
                      description: __('Answers are shown after finishing the quiz.', 'tutor'),
                    },
                    {
                      label: __('Reveal Mode', 'tutor'),
                      value: 'reveal',
                      description: __('Show answer after attempting the question.', 'tutor'),
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
              rules={{
                ...requiredRule(),
                validate: (value) => {
                  if (value >= 0 && value <= 20) {
                    return true;
                  }
                  return __('Allowed attempts must be between 0 and 20', 'tutor');
                },
              }}
              render={(controllerProps) => (
                <FormInput
                  {...controllerProps}
                  type="number"
                  label={__('Attempts Allowed', 'tutor')}
                  helpText={
                    // prettier-ignore
                    __('Define how many times a student can retake this quiz. Setting it to "0" allows unlimited attempts', 'tutor')
                  }
                  selectOnFocus
                />
              )}
            />
          </Show>

          <Show when={showPassRequired && contentType !== 'tutor_h5p_quiz'}>
            <Controller
              name="quiz_option.pass_is_required"
              control={form.control}
              rules={requiredRule()}
              render={(controllerProps) => (
                <FormSwitch
                  {...controllerProps}
                  label={__('Passing is Required', 'tutor')}
                  helpText={
                    // prettier-ignore
                    __( 'By enabling this option, the student must have to pass it to access the next quiz', 'tutor')
                  }
                />
              )}
            />
          </Show>

          <Controller
            name="quiz_option.passing_grade"
            control={form.control}
            rules={requiredRule()}
            render={(controllerProps) => (
              <FormInputWithContent
                {...controllerProps}
                type="number"
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
              rules={requiredRule()}
              control={form.control}
              render={(controllerProps) => (
                <FormInput
                  {...controllerProps}
                  type="number"
                  label={__('Max Question Allowed to Answer', 'tutor')}
                  helpText={
                    // prettier-ignore
                    __('Set the number of quiz questions randomly from your question pool. If the set number exceeds available questions, all questions will be included', 'tutor')
                  }
                  selectOnFocus
                />
              )}
            />
          </Show>

          <Show when={isAddonEnabled(Addons.CONTENT_DRIP) && contentType !== 'tutor_h5p_quiz'}>
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
                    helpText={__('This quiz will be available after the given number of days.', 'tutor')}
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
                    helpText={
                      // prettier-ignore
                      __('This quiz will be available from the given date. Leave empty to make it available immediately.', 'tutor')
                    }
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
                          contents: topic.contents.filter((content) => String(content.ID) !== String(quizId)),
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

      <Card
        title={__('Advanced Settings', 'tutor')}
        collapsedAnimationDependencies={[quizSettingsValidationErrorLength]}
      >
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
                      { label: __('Question pagination', 'tutor'), value: 'question_pagination' },
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
                <FormSwitch {...controllerProps} label={__('Hide Question Number', 'tutor')} />
              )}
            />

            <Controller
              name="quiz_option.short_answer_characters_limit"
              control={form.control}
              render={(controllerProps) => (
                <FormInput
                  {...controllerProps}
                  type="number"
                  label={__('Set Character Limit for Short Answers', 'tutor')}
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
                  type="number"
                  label={__('Set Character Limit for Open-Ended/Essay Answers', 'tutor')}
                  selectOnFocus
                />
              )}
            />
          </Show>
        </div>
      </Card>

      <CourseBuilderInjectionSlot section="Curriculum.Quiz.bottom_of_settings" form={form} />
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
    align-items: flex-start;
    gap: ${spacing[8]};
  `,
  questionLayoutAndOrder: css`
    ${styleUtils.display.flex()}
    gap: ${spacing[20]};

    ${Breakpoint.smallMobile} {
      flex-direction: column;
    }
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
