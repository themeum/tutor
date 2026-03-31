import { css } from '@emotion/react';
import { useIsFetching } from '@tanstack/react-query';
import { __, sprintf } from '@wordpress/i18n';
import { addHours, format, isBefore, isSameMinute, isValid, parseISO, startOfDay } from 'date-fns';
import { useEffect, useState } from 'react';
import { Controller, useFormContext, useWatch } from 'react-hook-form';

import Button from '@TutorShared/atoms/Button';
import ImageInput from '@TutorShared/atoms/ImageInput';
import SVGIcon from '@TutorShared/atoms/SVGIcon';

import ProBadge from '@TutorShared/atoms/ProBadge';
import FormCheckbox from '@TutorShared/components/fields/FormCheckbox';
import FormDateInput from '@TutorShared/components/fields/FormDateInput';
import FormImageInput from '@TutorShared/components/fields/FormImageInput';
import FormSwitch from '@TutorShared/components/fields/FormSwitch';
import FormTimeInput from '@TutorShared/components/fields/FormTimeInput';

import type { CourseFormData } from '@CourseBuilderServices/course';
import { getCourseId } from '@CourseBuilderUtils/utils';
import { tutorConfig } from '@TutorShared/config/config';
import { DateFormats } from '@TutorShared/config/constants';
import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import Show from '@TutorShared/controls/Show';
import { withVisibilityControl } from '@TutorShared/hoc/withVisibilityControl';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { formatBytes, noop } from '@TutorShared/utils/util';
import { invalidDateRule, invalidTimeRule } from '@TutorShared/utils/validation';

const isTutorPro = !!tutorConfig.tutor_pro_url;
const courseId = getCourseId();

const ScheduleOptions = () => {
  const form = useFormContext<CourseFormData>();
  const postDate = useWatch({ name: 'post_date' });
  const scheduleDate = useWatch({ name: 'schedule_date' }) ?? '';
  const scheduleTime = useWatch({ name: 'schedule_time' }) ?? format(addHours(new Date(), 1), DateFormats.hoursMinutes);
  const isScheduleEnabled = useWatch({ name: 'isScheduleEnabled' }) ?? false;
  const showForm = useWatch({ name: 'showScheduleForm' }) ?? false;
  const isComingSoonEnabled = useWatch({ name: 'enable_coming_soon' }) ?? false;
  const isEnrollmentPeriodEnabled = useWatch({ name: 'course_enrollment_period' }) ?? false;
  const enrollmentStartDate = useWatch({ name: 'enrollment_starts_date' }) ?? '';
  const enrollmentStartTime = useWatch({ name: 'enrollment_starts_time' }) ?? '';
  const comingSoonThumbnail = useWatch({ name: 'coming_soon_thumbnail' });
  const isCourseDetailsFetching = useIsFetching({
    queryKey: ['CourseDetails', courseId],
  });

  const [previousPostDate, setPreviousPostDate] = useState(
    scheduleDate && scheduleTime && isValid(new Date(`${scheduleDate} ${scheduleTime}`))
      ? format(new Date(`${scheduleDate} ${scheduleTime}`), DateFormats.yearMonthDayHourMinuteSecond24H)
      : '',
  );

  const enrollmentStartDateTime = new Date(`${enrollmentStartDate} ${enrollmentStartTime}`);

  const handleDelete = () => {
    form.setValue('schedule_date', '', { shouldDirty: true });
    form.setValue('schedule_time', '', { shouldDirty: true });
    form.setValue('showScheduleForm', true, { shouldDirty: true });
  };

  const handleCancel = () => {
    const isPreviousDateInFuture = isBefore(new Date(postDate), new Date());

    form.setValue(
      'schedule_date',
      isPreviousDateInFuture && previousPostDate ? format(parseISO(previousPostDate), DateFormats.yearMonthDay) : '',
      {
        shouldDirty: true,
      },
    );

    form.setValue(
      'schedule_time',
      isPreviousDateInFuture && previousPostDate ? format(parseISO(previousPostDate), DateFormats.hoursMinutes) : '',
      {
        shouldDirty: true,
      },
    );
  };

  const handleSave = () => {
    if (!scheduleDate || !scheduleTime) {
      return;
    }

    form.setValue('showScheduleForm', false, { shouldDirty: true });
    setPreviousPostDate(
      format(new Date(`${scheduleDate} ${scheduleTime}`), DateFormats.yearMonthDayHourMinuteSecond24H),
    );
  };

  useEffect(() => {
    if (isScheduleEnabled && showForm) {
      form.setFocus('schedule_date');
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [showForm, isScheduleEnabled]);

  return (
    <div css={styles.scheduleOptions}>
      <Controller
        name="isScheduleEnabled"
        control={form.control}
        rules={{
          deps: ['enrollment_starts_date', 'enrollment_starts_time'],
        }}
        render={(controllerProps) => (
          <FormSwitch
            {...controllerProps}
            loading={!!isCourseDetailsFetching}
            label={__('Schedule', 'tutor')}
            onChange={(value) => {
              if (!value && scheduleDate && scheduleTime) {
                form.setValue('showScheduleForm', false, {
                  shouldDirty: true,
                });
              }
            }}
          />
        )}
      />
      {isScheduleEnabled && showForm && (
        <div css={styles.formWrapper}>
          <div css={styleUtils.dateAndTimeWrapper}>
            <Controller
              name="schedule_date"
              control={form.control}
              rules={{
                required: __('Schedule date is required.', 'tutor'),
                validate: {
                  invalidDateRule: invalidDateRule,
                  futureDate: (value) => {
                    if (isBefore(new Date(`${value} +T00:00:00`), startOfDay(new Date()))) {
                      return __('Schedule date should be in the future.', 'tutor');
                    }
                    return true;
                  },
                  isBeforeEnrollmentStartDate: (value) => {
                    if (
                      isEnrollmentPeriodEnabled &&
                      isBefore(enrollmentStartDateTime, new Date(`${value} ${scheduleTime}`))
                    ) {
                      return __('Schedule date should be before enrollment start date.', 'tutor');
                    }

                    return true;
                  },
                },
                deps: ['enrollment_starts_date', 'enrollment_starts_time', 'schedule_time'],
              }}
              render={(controllerProps) => (
                <FormDateInput
                  {...controllerProps}
                  isClearable={false}
                  placeholder={__('Select date', 'tutor')}
                  disabledBefore={format(new Date(), DateFormats.yearMonthDay)}
                  onChange={() => {
                    form.setFocus('schedule_time');
                  }}
                  dateFormat={DateFormats.monthDayYear}
                />
              )}
            />
            <Controller
              name="schedule_time"
              control={form.control}
              rules={{
                required: __('Schedule time is required.', 'tutor'),
                validate: {
                  invalidTimeRule: invalidTimeRule,
                  futureDate: (value) => {
                    if (isBefore(new Date(`${scheduleDate} ${value}`), new Date())) {
                      return __('Schedule time should be in the future.', 'tutor');
                    }
                    return true;
                  },
                  isBeforeEnrollmentStartDate: (value) => {
                    if (
                      isEnrollmentPeriodEnabled &&
                      isBefore(enrollmentStartDateTime, new Date(`${scheduleDate} ${value}`))
                    ) {
                      return __('Schedule time should be before enrollment start date.', 'tutor');
                    }

                    return true;
                  },
                },
                deps: ['schedule_date', 'enrollment_starts_date', 'enrollment_starts_time'],
              }}
              render={(controllerProps) => (
                <FormTimeInput {...controllerProps} interval={60} isClearable={false} placeholder="hh:mm A" />
              )}
            />
          </div>

          <Controller
            name="enable_coming_soon"
            control={form.control}
            render={(controllerProps) => (
              <FormCheckbox
                {...controllerProps}
                label={
                  <>
                    {__('Show coming soon in course list & details page', 'tutor')}
                    <Show when={!isTutorPro}>
                      <div data-pro-badge>
                        <ProBadge content={__('Pro', 'tutor')} size="small" />
                      </div>
                    </Show>
                  </>
                }
                disabled={!isTutorPro}
                labelCss={styles.checkboxStartAlign}
              />
            )}
          />

          <Show when={isTutorPro}>
            <Show when={isComingSoonEnabled}>
              <Controller
                name="coming_soon_thumbnail"
                control={form.control}
                render={(controllerProps) => (
                  <FormImageInput
                    {...controllerProps}
                    label={__('Coming Soon Thumbnail', 'tutor')}
                    buttonText={__('Upload Thumbnail', 'tutor')}
                    infoText={
                      /* translators: %s is the maximum allowed upload file size (e.g., "2MB") */
                      sprintf(
                        __('JPEG, PNG, GIF, and WebP formats, up to %s', 'tutor'),
                        formatBytes(Number(tutorConfig?.max_upload_size || 0)),
                      )
                    }
                  />
                )}
              />

              <Controller
                name="enable_curriculum_preview"
                control={form.control}
                render={(controllerProps) => (
                  <FormCheckbox {...controllerProps} label={__('Preview Course Curriculum', 'tutor')} />
                )}
              />
            </Show>
          </Show>

          <div css={styles.scheduleButtonsWrapper}>
            <Button
              variant="tertiary"
              size="small"
              onClick={handleCancel}
              disabled={
                (!scheduleDate && !scheduleTime) ||
                (isValid(new Date(`${scheduleDate} ${scheduleTime}`)) &&
                  isSameMinute(new Date(`${scheduleDate} ${scheduleTime}`), new Date(previousPostDate)))
              }
            >
              {__('Cancel', 'tutor')}
            </Button>
            <Button
              variant="secondary"
              size="small"
              onClick={form.handleSubmit(handleSave)}
              disabled={!scheduleDate || !scheduleTime}
            >
              {__('Ok', 'tutor')}
            </Button>
          </div>
        </div>
      )}
      {isScheduleEnabled && !showForm && (
        <div css={styles.scheduleInfoWrapper}>
          <div css={styles.scheduledFor}>
            <div css={styles.scheduleLabel}>
              {!isComingSoonEnabled ? __('Scheduled for', 'tutor') : __('Scheduled with coming soon', 'tutor')}
            </div>
            <div css={styles.scheduleInfoButtons}>
              <button type="button" css={styleUtils.actionButton} onClick={handleDelete}>
                <SVGIcon name="delete" width={24} height={24} />
              </button>
              <button
                type="button"
                css={styleUtils.actionButton}
                onClick={() => {
                  form.setValue('showScheduleForm', true, {
                    shouldDirty: true,
                  });
                }}
              >
                <SVGIcon name="edit" width={24} height={24} />
              </button>
            </div>
          </div>
          <Show when={scheduleDate && scheduleTime && isValid(new Date(`${scheduleDate} ${scheduleTime}`))}>
            <div css={styles.scheduleInfo}>
              {
                /* translators: %1$s is the date and %2$s is the time */
                sprintf(
                  __('%1$s at %2$s', 'tutor'),
                  format(parseISO(scheduleDate), DateFormats.monthDayYear),
                  scheduleTime,
                )
              }
            </div>

            <Show when={comingSoonThumbnail?.url}>
              <ImageInput value={comingSoonThumbnail} uploadHandler={noop} clearHandler={noop} disabled />
            </Show>
          </Show>
        </div>
      )}
    </div>
  );
};

export default withVisibilityControl(ScheduleOptions);

const styles = {
  scheduleOptions: css`
    padding: ${spacing[12]};
    border: 1px solid ${colorTokens.stroke.default};
    border-radius: ${borderRadius[8]};
    gap: ${spacing[8]};
    background-color: ${colorTokens.bg.white};
  `,
  formWrapper: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[8]};
    margin-top: ${spacing[16]};
  `,
  scheduleButtonsWrapper: css`
    display: flex;
    gap: ${spacing[12]};
    margin-top: ${spacing[8]};

    button {
      width: 100%;

      span {
        justify-content: center;
      }
    }
  `,
  scheduleInfoWrapper: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[8]};
    margin-top: ${spacing[12]};
  `,
  scheduledFor: css`
    display: flex;
    align-items: center;
    justify-content: space-between;
  `,
  scheduleLabel: css`
    ${typography.caption()};
    color: ${colorTokens.text.subdued};
  `,
  scheduleInfoButtons: css`
    display: flex;
    align-items: center;
    gap: ${spacing[8]};
  `,
  scheduleInfo: css`
    ${typography.caption()};
    background-color: ${colorTokens.background.status.processing};
    padding: ${spacing[8]};
    border-radius: ${borderRadius[4]};
    text-align: center;
  `,
  checkboxStartAlign: css`
    span:first-of-type {
      gap: ${spacing[4]};
      align-self: flex-start;
      margin-top: ${spacing[4]};
    }

    [data-pro-badge] {
      display: inline-flex;
      vertical-align: middle;
      padding-left: ${spacing[4]};
    }
  `,
};
