import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import React from 'react';
import { Controller } from 'react-hook-form';

import Button from '@Atoms/Button';

import FormCheckbox from '@Components/fields/FormCheckbox';
import FormDateInput from '@Components/fields/FormDateInput';
import FormInput from '@Components/fields/FormInput';
import FormTextareaInput from '@Components/fields/FormTextareaInput';
import FormTimeInput from '@Components/fields/FormTimeInput';

import { borderRadius, colorPalate, colorTokens, fontSize, shadow, spacing, zIndex } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';

import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';

import FormSelectInput from '@Components/fields/FormSelectInput';
import { useIsScrolling } from '@Hooks/useIsScrolling';
import { styleUtils } from '@Utils/style-utils';
import { Meeting } from './LiveClass';

export type MeetingType = 'zoom' | 'google_meet' | 'jitsi';

interface MeetingFormField {
  meeting_name: string;
  meeting_summary: string;
  meeting_date: string;
  meeting_time_from: string;
  meeting_time_to: string;
  meeting_enrolledAsAttendee: boolean;
  meeting_autoRecording: string;
  meeting_password: string;
  meeting_host: string;
}

interface MeetingFormProps {
  type: MeetingType;
  setShowMeetingForm: React.Dispatch<React.SetStateAction<MeetingType | null>>;
  setMeetings: React.Dispatch<React.SetStateAction<Meeting[]>>;
}

const MeetingForm = ({ type, setShowMeetingForm, setMeetings }: MeetingFormProps) => {
  const { ref, isScrolling } = useIsScrolling({ defaultValue: true });
  const meetingForm = useFormWithGlobalError<MeetingFormField>({
    defaultValues: {
      meeting_name: '',
      meeting_summary: '',
      meeting_date: '',
      meeting_time_from: '',
      meeting_time_to: '',
      meeting_enrolledAsAttendee: false,
      meeting_autoRecording: 'disabled',
      meeting_password: '',
      meeting_host: '',
    },
  });

  const onCancel = () => {
    setShowMeetingForm(null);
  };

  // @TODO: will come from app config api later.
  const onSubmit = (data: MeetingFormField) => {
    setShowMeetingForm(null);
    meetingForm.reset();

    if (!data.meeting_date || !data.meeting_time_from || !data.meeting_time_to) {
      return;
    }

    const dataToSubmit: Omit<Meeting, 'id'> = {
      type: type,
      meeting_title: data.meeting_name,
      meeting_date: data.meeting_date,
      meeting_start_time: data.meeting_time_from,
      meeting_link: type === 'zoom' ? 'https://zoom.us/abc-xyz' : 'https://meet.google.com/abc-xyz',
      ...(type === 'zoom' && {
        meeting_token: 'abc-xyz',
        meeting_password: data.meeting_password,
      }),
    };

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
      <div css={styles.formWrapper} ref={ref}>
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
            render={controllerProps => (
              <FormSelectInput
                {...controllerProps}
                label={__('Auto recording', 'tutor')}
                placeholder="Select auto recording option"
                options={[
                  { label: 'Disabled', value: 'disabled' },
                  { label: 'Local', value: 'local' },
                  { label: 'Cloud', value: 'cloud' },
                ]}
              />
            )}
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

      <div css={styles.buttonWrapper({ isScrolling })}>
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
    background: ${colorTokens.background.white};
    padding-block: ${spacing[12]};
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
    padding-inline: ${spacing[12]};
    padding-bottom: ${spacing[8]};
    gap: ${spacing[12]};
    max-height: 400px;
    height: 100%;
    overflow-y: auto;
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
  buttonWrapper: ({ isScrolling = false }) => css`
    ${styleUtils.display.flex()}
    padding-top: ${spacing[8]};
    padding-inline: ${spacing[12]};
    justify-content: flex-end;
    gap: ${spacing[8]};
    z-index: ${zIndex.positive};
    ${isScrolling &&
    css`
      box-shadow: ${shadow.scrollable};
    `}
  `,
};
