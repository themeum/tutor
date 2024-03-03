import React from 'react';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { Controller } from 'react-hook-form';

import Button from '@Atoms/Button';

import FormCheckbox from '@Components/fields/FormCheckbox';
import FormDateInput from '@Components/fields/FormDateInput';
import FormInput from '@Components/fields/FormInput';
import FormTextareaInput from '@Components/fields/FormTextareaInput';
import FormTimeInput from '@Components/fields/FormTimeInput';

import { borderRadius, colorPalate, colorTokens, fontSize, shadow, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';

import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';

import { Meeting } from './LiveClass';
import { styleUtils } from '@Utils/style-utils';

export type MeetingType = 'zoom' | 'google_meet' | 'jitsi';

interface MeetingFormFieldProps {
  meeting_name: string;
  meeting_summary: string;
  meeting_date: string;
  meeting_time_from: string;
  meeting_time_to: string;
  meeting_enrolledAsAttendee: boolean;
  meeting_autoRecording: boolean;
  meeting_password: string;
  meeting_host: string;
}

interface MeetingFormProps {
  type: MeetingType;
  setShowMeetingForm: React.Dispatch<React.SetStateAction<MeetingType | null>>;
  setMeetings: React.Dispatch<React.SetStateAction<Meeting[]>>;
}

const MeetingForm = ({ type, setShowMeetingForm, setMeetings }: MeetingFormProps) => {
  const meetingForm = useFormWithGlobalError<MeetingFormFieldProps>();

  const onCancel = () => {
    setShowMeetingForm(null);
  };

  const onSubmit = (data: MeetingFormFieldProps) => {
    setShowMeetingForm(null);
    meetingForm.reset();
    let dataToSubmit: Omit<Meeting, 'id'>;

    if (!data.meeting_date || !data.meeting_time_from || !data.meeting_time_to) {
      return;
    }

    if (type === 'google_meet') {
      dataToSubmit = {
        type: type,
        meeting_title: data.meeting_name,
        meeting_date: data.meeting_date,
        meeting_start_time: data.meeting_time_from,
        meeting_link: 'https://meet.google.com/abc-xyz',
      };
    } else if (type === 'zoom') {
      dataToSubmit = {
        type: type,
        meeting_title: data.meeting_name,
        meeting_date: data.meeting_date,
        meeting_start_time: data.meeting_time_from,
        meeting_link: 'https://meet.google.com/abc-xyz',
        meeting_token: 'abc-xyz',
        meeting_password: data.meeting_password,
      };
    }

    setMeetings(prev => [
      ...prev,
      {
        id: prev.length + 1,
        ...dataToSubmit,
      },
    ]);
  };

  return (
    <div css={styles.container}>
      <div css={styles.formWrapper}>
        <Controller
          name="meeting_name"
          control={meetingForm.control}
          render={controllerProps => (
            <FormInput
              {...controllerProps}
              label={__('Meeting Name', 'tutor')}
              placeholder={__('Enter meeting name', 'tutor')}
            />
          )}
        />

        <Controller
          name="meeting_summary"
          control={meetingForm.control}
          render={controllerProps => (
            <FormTextareaInput
              {...controllerProps}
              label={__('Meeting Summary', 'tutor')}
              placeholder={__('Enter meeting summary', 'tutor')}
              rows={3}
              enableResize
            />
          )}
        />

        <div css={styles.meetingDateTimeWrapper}>
          <Controller
            name="meeting_date"
            control={meetingForm.control}
            render={controllerProps => (
              <FormDateInput
                {...controllerProps}
                label={__('Meeting Date', 'tutor')}
                placeholder={__('Enter meeting date', 'tutor')}
              />
            )}
          />

          <div css={styles.meetingTimeWrapper}>
            <Controller
              name="meeting_time_from"
              control={meetingForm.control}
              render={controllerProps => <FormTimeInput {...controllerProps} placeholder={__('Start time', 'tutor')} />}
            />
            <div
              css={{
                width: '10px',
                height: '2px',
                backgroundColor: colorTokens.stroke.default,
              }}
            />
            <Controller
              name="meeting_time_to"
              control={meetingForm.control}
              render={controllerProps => <FormTimeInput {...controllerProps} placeholder={__('End time', 'tutor')} />}
            />
          </div>
        </div>

        <Show when={type === 'google_meet'}>
          <Controller
            name="meeting_enrolledAsAttendee"
            control={meetingForm.control}
            render={controllerProps => (
              <FormCheckbox {...controllerProps} label={__('Add enrolled students as attendees', 'tutor')} />
            )}
          />
        </Show>

        <Show when={type === 'zoom'}>
          <Controller
            name="meeting_autoRecording"
            control={meetingForm.control}
            render={controllerProps => <FormCheckbox {...controllerProps} label={__('Auto recording', 'tutor')} />}
          />

          <Controller
            name="meeting_password"
            control={meetingForm.control}
            render={controllerProps => (
              <FormInput
                {...controllerProps}
                label={__('Meeting Password', 'tutor')}
                placeholder={__('Enter meeting password', 'tutor')}
              />
            )}
          />

          <Controller
            name="meeting_host"
            control={meetingForm.control}
            render={controllerProps => (
              <FormInput
                {...controllerProps}
                label={__('Meeting Host', 'tutor')}
                placeholder={__('Enter meeting host', 'tutor')}
              />
            )}
          />
        </Show>
      </div>

      <div css={styles.buttonWrapper}>
        <Button variant="text" size="small" onClick={onCancel}>
          {__('Cancel', 'tutor')}
        </Button>
        <Button variant="primary" size="small" onClick={meetingForm.handleSubmit(onSubmit)}>
          {__('Create meeting', 'tutor')}
        </Button>
      </div>
    </div>
  );
};

export default MeetingForm;

const styles = {
  container: css`
    ${styleUtils.display.flex('column')}
    gap: ${spacing[16]};
    padding: ${spacing[12]};
    border-radius: ${borderRadius.card};
    box-shadow: ${shadow.popover};
    ${typography.caption('regular')};

    * > label {
      font-size: ${fontSize[15]};
      color: ${colorPalate.text.default};
    }
  `,
  formWrapper: css`
    ${styleUtils.display.flex('column')}
    gap: ${spacing[12]};
  `,
  meetingDateTimeWrapper: css`
    ${styleUtils.display.flex('column')}
    gap: ${spacing[6]};
  `,
  meetingTimeWrapper: css`
    ${styleUtils.display.flex()}
    justify-content: space-between;
    align-items: center;
    gap: ${spacing[6]};
  `,
  buttonWrapper: css`
    ${styleUtils.display.flex()}
    justify-content: flex-end;
    gap: ${spacing[8]};
  `,
};
