import { css } from '@emotion/react';
import { useIsFetching } from '@tanstack/react-query';
import { __ } from '@wordpress/i18n';
import { isBefore, startOfDay } from 'date-fns';
import { useState } from 'react';
import { Controller, useFormContext, useWatch } from 'react-hook-form';

import Button from '@TutorShared/atoms/Button';
import FormCheckbox from '@TutorShared/components/fields/FormCheckbox';
import FormDateInput from '@TutorShared/components/fields/FormDateInput';
import FormInput from '@TutorShared/components/fields/FormInput';
import FormSwitch from '@TutorShared/components/fields/FormSwitch';
import FormTimeInput from '@TutorShared/components/fields/FormTimeInput';

import { type CourseFormData } from '@CourseBuilderServices/course';
import { getCourseId } from '@CourseBuilderUtils/utils';
import { tutorConfig } from '@TutorShared/config/config';
import { Addons, DateFormats } from '@TutorShared/config/constants';
import { borderRadius, Breakpoint, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import Show from '@TutorShared/controls/Show';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { isAddonEnabled } from '@TutorShared/utils/util';
import { invalidDateRule, invalidTimeRule, requiredRule } from '@TutorShared/utils/validation';

const courseId = getCourseId();

const EnrollmentSettings = () => {
  const isTutorPro = !!tutorConfig.tutor_pro_url;
  const form = useFormContext<CourseFormData>();
  const isCourseDetailsLoading = useIsFetching({
    queryKey: ['CourseDetails', courseId],
  });
  const isEnrollmentPeriodEnabled = useWatch({
    control: form.control,
    name: 'course_enrollment_period',
  });
  const enrollmentStartsDate = useWatch({
    control: form.control,
    name: 'enrollment_starts_date',
  });
  const enrollmentStartTime = useWatch({
    control: form.control,
    name: 'enrollment_starts_time',
  });
  const enrollmentEndDate = useWatch({
    control: form.control,
    name: 'enrollment_ends_date',
  });
  const isScheduleEnabled = useWatch({
    control: form.control,
    name: 'isScheduleEnabled',
  });
  const scheduleDate = useWatch({
    control: form.control,
    name: 'schedule_date',
  });
  const scheduleTime = useWatch({
    control: form.control,
    name: 'schedule_time',
  });
  const [showEndDate, setShowEndDate] = useState(false);

  const isMembershipOnlyMode = isAddonEnabled(Addons.SUBSCRIPTION) && tutorConfig.settings?.membership_only_mode;
  const isEnrollmentAddonEnabled = isAddonEnabled(Addons.ENROLLMENT);
  const scheduleDateTime = new Date(`${scheduleDate} ${scheduleTime}`);

  return (
    <div css={styles.wrapper}>
      <Controller
        name="maximum_students"
        control={form.control}
        render={(controllerProps) => (
          <FormInput
            {...controllerProps}
            label={__('Maximum Student', 'tutor')}
            helpText={__('Number of students that can enrol in this course. Set 0 for no limits.', 'tutor')}
            placeholder="0"
            type="number"
            isClearable
            selectOnFocus
            loading={!!isCourseDetailsLoading && !controllerProps.field.value}
          />
        )}
      />

      <Show when={isTutorPro && isEnrollmentAddonEnabled}>
        <Show when={!isMembershipOnlyMode && tutorConfig.settings?.enrollment_expiry_enabled === 'on'}>
          <Controller
            name="enrollment_expiry"
            control={form.control}
            render={(controllerProps) => (
              <FormInput
                {...controllerProps}
                label={__('Enrollment Expiration', 'tutor')}
                helpText={
                  // prettier-ignore
                  __("Student's enrollment will be removed after this number of days. Set 0 for lifetime enrollment.", 'tutor')
                }
                placeholder="0"
                type="number"
                isClearable
                selectOnFocus
                loading={!!isCourseDetailsLoading && !controllerProps.field.value}
              />
            )}
          />
        </Show>

        <div css={styles.enrollmentPeriod({ isEnabled: isEnrollmentPeriodEnabled })}>
          <Controller
            name="course_enrollment_period"
            control={form.control}
            rules={{
              deps: [
                'schedule_date',
                'schedule_time',
                ...(enrollmentStartsDate ? ['enrollment_starts_date' as keyof CourseFormData] : []),
                ...(enrollmentStartTime ? ['enrollment_starts_time' as keyof CourseFormData] : []),
                'enrollment_ends_date',
                'enrollment_ends_time',
              ],
            }}
            render={(controllerProps) => (
              <FormSwitch
                {...controllerProps}
                label={__('Course Enrollment Period', 'tutor')}
                loading={!!isCourseDetailsLoading && !controllerProps.field.value}
                onChange={(isTrue) => {
                  if (!isTrue) {
                    form.clearErrors([
                      'enrollment_starts_date',
                      'enrollment_starts_time',
                      'enrollment_ends_date',
                      'enrollment_ends_time',
                    ]);
                  }
                }}
              />
            )}
          />

          <Show when={isEnrollmentPeriodEnabled}>
            <div css={styles.enrollmentDateWrapper}>
              <div css={styles.enrollmentDate}>
                <label htmlFor="enrollment_starts_at">{__('Start Date', 'tutor')}</label>
                <div id="enrollment_starts_at" css={styleUtils.dateAndTimeWrapper}>
                  <Controller
                    name="enrollment_starts_date"
                    control={form.control}
                    rules={{
                      ...requiredRule(),
                      validate: {
                        invalidDate: invalidDateRule,
                        isAfterScheduleDate: (value) => {
                          if (
                            isScheduleEnabled &&
                            scheduleDateTime &&
                            isBefore(startOfDay(new Date(value)), startOfDay(new Date(scheduleDate)))
                          ) {
                            return __('Start date should be after the schedule date', 'tutor');
                          }
                        },
                      },
                      deps: [
                        'schedule_date',
                        'schedule_time',
                        'enrollment_starts_time',
                        'enrollment_ends_date',
                        'enrollment_ends_time',
                      ],
                    }}
                    render={(controllerProps) => (
                      <FormDateInput
                        {...controllerProps}
                        loading={!!isCourseDetailsLoading && !controllerProps.field.value}
                        placeholder={__('Start Date', 'tutor')}
                        dateFormat={DateFormats.monthDayYear}
                      />
                    )}
                  />
                  <Controller
                    name="enrollment_starts_time"
                    control={form.control}
                    rules={{
                      ...requiredRule(),
                      validate: {
                        invalidTime: invalidTimeRule,
                        isAfterScheduleTime: (value) => {
                          if (
                            isScheduleEnabled &&
                            scheduleDateTime &&
                            isBefore(new Date(`${enrollmentStartsDate} ${value}`), scheduleDateTime)
                          ) {
                            return __('Start time should be after the schedule time', 'tutor');
                          }
                        },
                      },
                      deps: ['schedule_date', 'schedule_time', 'enrollment_starts_date', 'enrollment_ends_date'],
                    }}
                    render={(controllerProps) => (
                      <FormTimeInput
                        {...controllerProps}
                        loading={!!isCourseDetailsLoading && !controllerProps.field.value}
                        placeholder={__('hh:mm a', 'tutor')}
                      />
                    )}
                  />
                </div>
              </div>

              <Show
                when={showEndDate || enrollmentEndDate}
                fallback={
                  <div>
                    <Button
                      variant="secondary"
                      size="small"
                      onClick={() => setShowEndDate(true)}
                      disabled={!!isCourseDetailsLoading || !enrollmentStartsDate || !enrollmentStartTime}
                    >
                      {__('Add End Date', 'tutor')}
                    </Button>
                  </div>
                }
              >
                <div css={styles.enrollmentDate}>
                  <label htmlFor="enrollment_ends_at">
                    <span>{__('End Date', 'tutor')}</span>

                    <Button
                      variant="text"
                      size="small"
                      onClick={() => {
                        setShowEndDate(false);
                        form.setValue('enrollment_ends_date', '');
                        form.setValue('enrollment_ends_time', '');
                      }}
                      css={styles.removeButton}
                    >
                      {__('Remove', 'tutor')}
                    </Button>
                  </label>
                  <div id="enrollment_ends_at" css={styleUtils.dateAndTimeWrapper}>
                    <Controller
                      name="enrollment_ends_date"
                      control={form.control}
                      rules={{
                        ...requiredRule(),
                        validate: {
                          invalidDate: invalidDateRule,
                          checkEndDate: (value) => {
                            if (isBefore(startOfDay(new Date(value)), startOfDay(new Date(enrollmentStartsDate)))) {
                              return __('End date should be after the start date', 'tutor');
                            }
                          },
                        },
                        deps: ['enrollment_starts_date', 'enrollment_starts_time', 'enrollment_ends_time'],
                      }}
                      render={(controllerProps) => (
                        <FormDateInput
                          {...controllerProps}
                          loading={!!isCourseDetailsLoading && !controllerProps.field.value}
                          placeholder={__('End Date', 'tutor')}
                          disabledBefore={enrollmentStartsDate}
                          dateFormat={DateFormats.monthDayYear}
                        />
                      )}
                    />
                    <Controller
                      name="enrollment_ends_time"
                      control={form.control}
                      rules={{
                        ...requiredRule(),
                        validate: {
                          invalidTime: invalidTimeRule,
                          checkEndTime: (value) => {
                            if (
                              enrollmentStartsDate &&
                              enrollmentEndDate &&
                              enrollmentStartTime &&
                              !isBefore(
                                new Date(`${enrollmentStartsDate} ${enrollmentStartTime}`),
                                new Date(`${enrollmentEndDate} ${value}`),
                              )
                            ) {
                              return __('End time should be after the start time', 'tutor');
                            }
                          },
                        },
                        deps: ['enrollment_starts_date', 'enrollment_starts_time', 'enrollment_ends_date'],
                      }}
                      render={(controllerProps) => (
                        <FormTimeInput
                          {...controllerProps}
                          loading={!!isCourseDetailsLoading && !controllerProps.field.value}
                          placeholder={__('hh:mm a', 'tutor')}
                        />
                      )}
                    />
                  </div>
                </div>
              </Show>
            </div>
          </Show>
        </div>

        <Controller
          name="pause_enrollment"
          control={form.control}
          render={(controllerProps) => (
            <FormCheckbox
              {...controllerProps}
              label={__('Pause Enrollment', 'tutor')}
              description={
                // prettier-ignore
                __('If you pause enrolment, students will no longer be able to enroll in the course.', 'tutor')
              }
            />
          )}
        />
      </Show>
    </div>
  );
};

export default EnrollmentSettings;

const styles = {
  wrapper: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[16]};
    background-color: ${colorTokens.background.white};
    padding: ${spacing[16]} ${spacing[24]} ${spacing[32]} ${spacing[32]};
    min-height: 400px;

    ${Breakpoint.smallMobile} {
      padding: ${spacing[16]};
    }
  `,
  enrollmentPeriod: ({ isEnabled = false }) => css`
    padding: ${spacing[12]};
    border: 1px solid ${colorTokens.stroke.default};
    border-radius: ${borderRadius[8]};
    background-color: ${colorTokens.bg.white};

    ${isEnabled &&
    css`
      padding-bottom: ${spacing[16]};
    `}
  `,
  enrollmentDateWrapper: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[8]};
    margin-top: ${spacing[16]};
  `,
  enrollmentDate: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[4]};

    label {
      ${styleUtils.display.flex()};
      align-items: center;
      justify-content: space-between;
      ${typography.caption()};
      color: ${colorTokens.text.title};
    }
  `,
  removeButton: css`
    margin-left: auto;
    padding: 0;
  `,
};
