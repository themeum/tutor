import React from 'react';
import Button from '@Atoms/Button';
import FormSwitch from '@Components/fields/FormSwitch';
import { borderRadius, colorTokens, shadow, spacing } from '@Config/styles';
import { css } from '@emotion/react';
import { Controller, useFormContext, useWatch } from 'react-hook-form';
import FormDateInput from '@Components/fields/FormDateInput';
import FormTimeInput from '@Components/fields/FormTimeInput';
import { __ } from '@wordpress/i18n';

const ScheduleOptions = () => {
  const form = useFormContext();

  const scheduleOptions = useWatch({ name: 'schedule_options' });

  return (
    <div css={styles.scheduleOptions}>
      <Controller
        name="schedule_options"
        control={form.control}
        render={(controllerProps) => <FormSwitch {...controllerProps} label={__('Schedule Options', 'tutor')} />}
      />

      {scheduleOptions && (
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
            <Button variant="tertiary" size="small">
              {__('Cancel')}
            </Button>
            <Button variant="secondary" size="small">
              {__('Save')}
            </Button>
          </div>
        </>
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
};
