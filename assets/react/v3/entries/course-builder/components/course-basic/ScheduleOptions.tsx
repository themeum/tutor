import { css } from '@emotion/react';
import { __, sprintf } from '@wordpress/i18n';
import { addHours, format, isBefore, isSameMinute, isValid, parseISO, startOfDay } from 'date-fns';
import { useEffect, useState } from 'react';
import { Controller, useFormContext, useWatch } from 'react-hook-form';

import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';

import FormDateInput from '@Components/fields/FormDateInput';
import FormSwitch from '@Components/fields/FormSwitch';
import FormTimeInput from '@Components/fields/FormTimeInput';

import { DateFormats } from '@Config/constants';
import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import type { CourseFormData } from '@CourseBuilderServices/course';
import { styleUtils } from '@Utils/style-utils';
import { invalidDateRule, invalidTimeRule } from '@Utils/validation';

const ScheduleOptions = () => {
  const form = useFormContext<CourseFormData>();
  const postDate = useWatch({ name: 'post_date' });
  const scheduleDate = useWatch({ name: 'schedule_date' }) ?? '';
  const scheduleTime = useWatch({ name: 'schedule_time' }) ?? format(addHours(new Date(), 1), DateFormats.hoursMinutes);
  const isScheduleEnabled = useWatch({ name: 'isScheduleEnabled' }) ?? false;
  const showForm = useWatch({ name: 'showScheduleForm' }) ?? false;

  const [previousPostDate, setPreviousPostDate] = useState(
    scheduleDate && scheduleTime && isValid(new Date(`${scheduleDate} ${scheduleTime}`))
      ? format(new Date(`${scheduleDate} ${scheduleTime}`), DateFormats.yearMonthDayHourMinuteSecond24H)
      : '',
  );

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

  // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
  useEffect(() => {
    if (isScheduleEnabled && showForm) {
      form.setFocus('schedule_date');
    }
  }, [showForm, isScheduleEnabled]);

  return (
    <div css={styles.scheduleOptions}>
      <Controller
        name="isScheduleEnabled"
        control={form.control}
        render={(controllerProps) => (
          <FormSwitch
            {...controllerProps}
            label={__('Schedule Options', 'tutor')}
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
                    if (isBefore(new Date(`${value}`), startOfDay(new Date()))) {
                      return __('Schedule date should be in the future.', 'tutor');
                    }
                    return true;
                  },
                },
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
                    if (isBefore(new Date(`${form.watch('schedule_date')} ${value}`), new Date())) {
                      return __('Schedule time should be in the future.', 'tutor');
                    }
                    return true;
                  },
                },
              }}
              render={(controllerProps) => (
                <FormTimeInput {...controllerProps} interval={60} isClearable={false} placeholder="hh:mm A" />
              )}
            />
          </div>
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
            <div css={styles.scheduleLabel}>{__('Scheduled for', 'tutor')}</div>
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
              {sprintf(__('%s at %s', 'tutor'), format(parseISO(scheduleDate), DateFormats.monthDayYear), scheduleTime)}
            </div>
          </Show>
        </div>
      )}
    </div>
  );
};

export default ScheduleOptions;

const styles = {
  scheduleOptions: css`
    padding: ${spacing[12]};
    border: 1px solid ${colorTokens.stroke.default};
    border-radius: ${borderRadius[8]};
    gap: ${spacing[8]};
    background-color: ${colorTokens.bg.white};
  `,
  formWrapper: css`
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
};
