import React, { useState } from 'react';
import Button from '@Atoms/Button';
import FormSwitch from '@Components/fields/FormSwitch';
import { borderRadius, colorTokens, shadow, spacing } from '@Config/styles';
import { css } from '@emotion/react';
import { Controller, useFormContext, useWatch } from 'react-hook-form';
import FormDateInput from '@Components/fields/FormDateInput';
import FormTimeInput from '@Components/fields/FormTimeInput';
import { __ } from '@wordpress/i18n';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
import SVGIcon from '@Atoms/SVGIcon';
import { typography } from '@Config/typography';
import { styleUtils } from '@Utils/style-utils';
import { DateFormats } from '@Config/constants';
import { format } from 'date-fns';

interface ScheduleForm {
  schedule_date: string;
  schedule_time: string;
}

const ScheduleOptions = () => {
  const contextForm = useFormContext();
  const form = useFormWithGlobalError<ScheduleForm>();

  const [showForm, setShowForm] = useState(true);

  const scheduleOptions = useWatch({ control: contextForm.control, name: 'schedule_options' });

  const scheduleDate = form.getValues('schedule_date')
    ? format(new Date(form.getValues('schedule_date')), DateFormats.monthDayYear)
    : '';
  const scheduleTime = form.getValues('schedule_time') ?? '';

  const handleDelete = () => {
    contextForm.setValue('schedule_options', false);
    setShowForm(true);
    form.reset();
  };

  const handleCancel = () => {
    contextForm.setValue('schedule_options', false);
    form.reset();
  };

  const handleSave = (data: ScheduleForm) => {
    if (!data.schedule_date || !data.schedule_time) {
      return;
    }

    setShowForm(false);
    contextForm.setValue(
      'post_date',
      format(new Date(`${data.schedule_date} ${data.schedule_time}`), DateFormats.yearMonthDayHourMinuteSecond)
    );
  };

  return (
    <div css={styles.scheduleOptions}>
      <Controller
        name="schedule_options"
        control={contextForm.control}
        render={(controllerProps) => <FormSwitch {...controllerProps} label={__('Schedule Options', 'tutor')} />}
      />

      {scheduleOptions && showForm && (
        <>
          <div css={styles.dateAndTimeWrapper}>
            <Controller
              name="schedule_date"
              control={form.control}
              render={(controllerProps) => <FormDateInput {...controllerProps} isClearable={false} />}
            />

            <Controller
              name="schedule_time"
              control={form.control}
              render={(controllerProps) => <FormTimeInput {...controllerProps} interval={60} isClearable={false} />}
            />
          </div>

          <div css={styles.scheduleButtonsWrapper}>
            <Button variant="tertiary" size="small" onClick={handleCancel}>
              {__('Cancel', 'tutor')}
            </Button>
            <Button variant="secondary" size="small" onClick={form.handleSubmit(handleSave)}>
              {__('Ok', 'tutor')}
            </Button>
          </div>
        </>
      )}

      {scheduleOptions && !showForm && (
        <div css={styles.scheduleInfoWrapper}>
          <div css={styles.scheduledFor}>
            <div css={styles.scheduleLabel}>{__('Scheduled for', 'tutor')}</div>
            <div css={styles.scheduleInfoButtons}>
              <button onClick={handleDelete}>
                <SVGIcon name="delete" width={24} height={24} />
              </button>
              <button onClick={() => setShowForm(true)}>
                <SVGIcon name="edit" width={24} height={24} />
              </button>
            </div>
          </div>
          <div css={styles.scheduleInfo}>{__(`${scheduleDate} at ${scheduleTime}`, 'tutor')}</div>
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
    display: flex;
    flex-direction: column;
    gap: ${spacing[8]};
  `,
  dateAndTimeWrapper: css`
    display: grid;
    grid-template-columns: 1fr 124px;
    gap: 1px;
    background-image: linear-gradient(to right, transparent, ${colorTokens.stroke.default}, transparent);
    margin-top: ${spacing[12]};
    border-radius: ${borderRadius[6]};

    &:focus-within {
      box-shadow: ${shadow.focus};
    }

    > div {
      &:first-of-type {
        input {
          border-top-right-radius: 0;
          border-bottom-right-radius: 0;
          border-right: none;
          box-shadow: none;
        }
      }
      &:last-of-type {
        input {
          border-top-left-radius: 0;
          border-bottom-left-radius: 0;
          border-left: none;
          box-shadow: none;
        }
      }
    }
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

    button {
      ${styleUtils.resetButton};
      color: ${colorTokens.icon.default};
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
