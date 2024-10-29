import { css } from '@emotion/react';
import { __, sprintf } from '@wordpress/i18n';
import { format, isBefore, parseISO } from 'date-fns';
import { useEffect, useState } from 'react';
import { Controller, useFormContext, useWatch } from 'react-hook-form';

import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import Tooltip from '@Atoms/Tooltip';

import FormDateInput from '@Components/fields/FormDateInput';
import FormTimeInput from '@Components/fields/FormTimeInput';

import { DateFormats } from '@Config/constants';
import { borderRadius, colorTokens, shadow, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import type { CourseFormData } from '@CourseBuilderServices/course';
import { getCourseId } from '@CourseBuilderUtils/utils';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
import { styleUtils } from '@Utils/style-utils';

interface ScheduleForm {
  schedule_date: string;
  schedule_time: string;
}

const courseId = getCourseId();

const ScheduleOptions = () => {
  const form = useFormContext<CourseFormData>();
  const postDate = useWatch({ name: 'post_date' });
  const postStatus = useWatch({ name: 'post_status' });

  const [previousPostDate, setPreviousPostDate] = useState(postDate);
  const scheduleForm = useFormWithGlobalError<ScheduleForm>({
    defaultValues: {
      schedule_date: format(new Date(), DateFormats.yearMonthDay),
      schedule_time: format(new Date(), DateFormats.hoursMinutes),
    },
  });

  const [showForm, setShowForm] = useState(!courseId);

  const scheduleDate = scheduleForm.watch('schedule_date')
    ? format(new Date(scheduleForm.watch('schedule_date')), DateFormats.monthDayYear)
    : '';
  const scheduleTime = scheduleForm.watch('schedule_time') ?? '';

  const handleCancel = () => {
    scheduleForm.reset({
      schedule_date: format(parseISO(previousPostDate), DateFormats.yearMonthDay),
      schedule_time: format(parseISO(previousPostDate), DateFormats.hoursMinutes),
    });

    setShowForm(false);
  };

  const handleSave = (data: ScheduleForm) => {
    if (!data.schedule_date || !data.schedule_time) {
      return;
    }

    setShowForm(false);
    form.setValue(
      'post_date',
      format(new Date(`${data.schedule_date} ${data.schedule_time}`), DateFormats.yearMonthDayHourMinuteSecond24H),
      {
        shouldDirty: true,
      },
    );
    setPreviousPostDate(
      format(new Date(`${data.schedule_date} ${data.schedule_time}`), DateFormats.yearMonthDayHourMinuteSecond),
    );
  };

  // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
  useEffect(() => {
    if (postDate) {
      scheduleForm.reset({
        schedule_date: format(parseISO(postDate), DateFormats.yearMonthDay),
        schedule_time: format(parseISO(postDate), DateFormats.hoursMinutes),
      });
      setPreviousPostDate(postDate);
    }
  }, [postDate]);

  return (
    <div css={styles.scheduleOptions}>
      <div css={styles.scheduleInfoWrapper}>
        <div css={styles.scheduledFor}>
          <div css={styles.scheduleLabel}>
            <Show
              when={postStatus === 'draft' && isBefore(parseISO(postDate), new Date())}
              fallback={
                isBefore(parseISO(postDate), new Date()) ? __('Published on', 'tutor') : __('Scheduled for', 'tutor')
              }
            >
              {__('Publish Immediately', 'tutor')}
            </Show>
          </div>
          <div css={styles.scheduleInfoButtons}>
            <Show
              when={
                postStatus !== 'draft' || isBefore(new Date(), parseISO(postDate)) || scheduleForm.formState.isDirty
              }
            >
              <Tooltip content={__('Reset to current time', 'tutor')} delay={200}>
                <button
                  type="button"
                  onClick={() => {
                    scheduleForm.reset({
                      schedule_date: format(new Date(), DateFormats.yearMonthDay),
                      schedule_time: format(new Date(), DateFormats.hoursMinutes),
                    });

                    form.setValue('post_date', format(new Date(), DateFormats.yearMonthDayHourMinuteSecond24H), {
                      shouldDirty: true,
                    });
                  }}
                >
                  <SVGIcon name="cross" width={24} height={24} />
                </button>
              </Tooltip>
            </Show>
            <Tooltip content={__('Edit', 'tutor')} delay={200}>
              <button type="button" onClick={() => setShowForm(true)}>
                <SVGIcon name="edit" width={24} height={24} />
              </button>
            </Tooltip>
          </div>
        </div>
        <Show
          when={showForm}
          fallback={
            <Show
              when={
                postStatus !== 'draft' || isBefore(new Date(), parseISO(postDate)) || scheduleForm.formState.isDirty
              }
            >
              <div css={styles.scheduleInfo}>{sprintf(__('%s at %s', 'tutor'), scheduleDate, scheduleTime)}</div>
            </Show>
          }
        >
          <div css={styleUtils.dateAndTimeWrapper}>
            <Controller
              name="schedule_date"
              control={scheduleForm.control}
              rules={{
                required: __('Schedule date is required', 'tutor'),
              }}
              render={(controllerProps) => (
                <FormDateInput
                  {...controllerProps}
                  isClearable={false}
                  placeholder={__('yyyy-mm-dd', 'tutor')}
                  disabledBefore={postStatus === 'draft' ? format(new Date(), DateFormats.yearMonthDay) : undefined}
                />
              )}
            />

            <Controller
              name="schedule_time"
              control={scheduleForm.control}
              rules={{
                required: __('Schedule time is required', 'tutor'),
              }}
              render={(controllerProps) => (
                <FormTimeInput
                  {...controllerProps}
                  interval={60}
                  isClearable={false}
                  placeholder={__('hh:mm A', 'tutor')}
                />
              )}
            />
          </div>

          <div css={styles.scheduleButtonsWrapper}>
            <Button variant="tertiary" size="small" onClick={handleCancel}>
              {__('Cancel', 'tutor')}
            </Button>
            <Button variant="secondary" size="small" onClick={scheduleForm.handleSubmit(handleSave)}>
              {__('Ok', 'tutor')}
            </Button>
          </div>
        </Show>
      </div>
    </div>
  );
};

export default ScheduleOptions;

const styles = {
  scheduleOptions: css`
    padding: ${spacing[12]};
    border: 1px solid ${colorTokens.stroke.default};
    border-radius: ${borderRadius[8]};
    display: flex;
    flex-direction: column;
    gap: ${spacing[8]};
    background-color: ${colorTokens.bg.white};
  `,

  scheduleButtonsWrapper: css`
    display: flex;
    gap: ${spacing[12]};

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

    button {
      ${styleUtils.resetButton};
      color: ${colorTokens.icon.default};
      ${styleUtils.flexCenter()};
      height: 24px;
      width: 24px;
      border-radius: ${borderRadius[2]};

      &:hover {
        color: ${colorTokens.icon.hover};
      }

      &:focus {
        box-shadow: ${shadow.focus};
      }
    }
  `,
  scheduleInfo: css`
    ${typography.caption()};
    background-color: ${colorTokens.background.status.processing};
    padding: ${spacing[8]};
    border-radius: ${borderRadius[4]};
    text-align: center;
  `,
};
