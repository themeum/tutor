import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { Controller } from 'react-hook-form';

import Button from '@Atoms/Button';

import FormCheckbox from '@Components/fields/FormCheckbox';
import FormDateInput from '@Components/fields/FormDateInput';
import FormInput from '@Components/fields/FormInput';
import FormTextareaInput from '@Components/fields/FormTextareaInput';
import FormTimeInput from '@Components/fields/FormTimeInput';

import { borderRadius, colorPalate, colorTokens, fontSize, shadow, spacing, zIndex } from '@Config/styles';
import { typography } from '@Config/typography';

import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';

import { styleUtils } from '@Utils/style-utils';
import {
  useSaveGoogleMeetMeetingMutation,
  type GoogleMeetMeeting,
  type GoogleMeetMeetingFormData,
} from '@CourseBuilderServices/course';
import { getCourseId } from '@CourseBuilderUtils/utils';
import { format } from 'date-fns';
import { DateFormats } from '@Config/constants';
import { useIsScrolling } from '@Hooks/useIsScrolling';
import FormSelectInput from '@Components/fields/FormSelectInput';

export type MeetingType = 'zoom' | 'google_meet' | 'jitsi';

interface GoogleMeetMeetingFormProps {
  onCancel: () => void;
  data: GoogleMeetMeeting | null;
  timezones: {
    [key: string]: string;
  };
  topicId?: string;
}

const courseId = getCourseId();

const GoogleMeetMeetingForm = ({ onCancel, data, timezones, topicId }: GoogleMeetMeetingFormProps) => {
  const { ref, isScrolling } = useIsScrolling({ defaultValue: true });
  const currentMeeting = data;

  const meetingForm = useFormWithGlobalError<GoogleMeetMeetingFormData>({
    defaultValues: {
      meeting_name: currentMeeting?.post_title ?? '',
      meeting_summary: currentMeeting?.post_content ?? '',
      meeting_start_date: currentMeeting?.meeting_data.start_datetime
        ? format(new Date(currentMeeting.meeting_data.start_datetime), DateFormats.yearMonthDay)
        : '',
      meeting_start_time: currentMeeting?.meeting_data.start_datetime
        ? format(new Date(currentMeeting.meeting_data.start_datetime), DateFormats.hoursMinutes)
        : '',
      meeting_end_date: currentMeeting?.meeting_data.end_datetime
        ? format(new Date(currentMeeting.meeting_data.end_datetime), DateFormats.yearMonthDay)
        : '',
      meeting_end_time: currentMeeting?.meeting_data.end_datetime
        ? format(new Date(currentMeeting.meeting_data.end_datetime), DateFormats.hoursMinutes)
        : '',
      meeting_timezone: currentMeeting?.meeting_data.timezone ?? '',
      meeting_enrolledAsAttendee: currentMeeting?.meeting_data.attendees === 'Yes' ? true : false,
    },
    shouldFocusError: true,
  });

  const saveGoogleMeetMeeting = useSaveGoogleMeetMeetingMutation(String(courseId));
  const timeZonesOptions = Object.keys(timezones).map((key) => ({
    label: timezones[key],
    value: key,
  }));

  const onSubmit = async (data: GoogleMeetMeetingFormData) => {
    if (!courseId) {
      return;
    }

    const response = await saveGoogleMeetMeeting.mutateAsync({
      ...(currentMeeting && { 'post-id': currentMeeting.ID, 'event-id': currentMeeting.meeting_data.id }),
      ...(topicId && { 'topic-id': topicId }),
      // @ts-ignore
      'course-id': courseId,
      meeting_title: data.meeting_name,
      meeting_summary: data.meeting_summary,
      meeting_start_date: data.meeting_start_date,
      meeting_start_time: data.meeting_start_time,
      meeting_end_date: data.meeting_end_date,
      meeting_end_time: data.meeting_end_time,
      meeting_attendees_enroll_students: data.meeting_enrolledAsAttendee ? 'Yes' : 'No',
      meeting_timezone: data.meeting_timezone,
      attendees: data.meeting_enrolledAsAttendee ? 'Yes' : 'No',
    });

    if (response.data) {
      onCancel();
      meetingForm.reset();
    }
  };

  return (
    <div css={styles.container}>
      <div css={styles.formWrapper} ref={ref}>
        <Controller
          name="meeting_name"
          control={meetingForm.control}
          rules={{ required: __('Meeting name is required', 'tutor') }}
          render={(controllerProps) => (
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
          rules={{ required: __('Meeting summary is required', 'tutor') }}
          render={(controllerProps) => (
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
          <div css={styles.dateLabel}>{__('Meeting Start Date', 'tutor')}</div>
          <div css={styles.meetingDateTime}>
            <Controller
              name="meeting_start_date"
              control={meetingForm.control}
              rules={{ required: __('Meeting start date is required', 'tutor') }}
              render={(controllerProps) => (
                <FormDateInput {...controllerProps} placeholder={__('Start date', 'tutor')} />
              )}
            />

            <Controller
              name="meeting_start_time"
              control={meetingForm.control}
              rules={{ required: __('Meeting start time is required', 'tutor') }}
              render={(controllerProps) => (
                <FormTimeInput {...controllerProps} placeholder={__('Start time', 'tutor')} />
              )}
            />
          </div>
        </div>

        <div css={styles.meetingDateTimeWrapper}>
          <div css={styles.dateLabel}>{__('Meeting End Date', 'tutor')}</div>

          <div css={styles.meetingDateTime}>
            <Controller
              name="meeting_end_date"
              control={meetingForm.control}
              rules={{ required: __('Meeting end date is required', 'tutor') }}
              render={(controllerProps) => <FormDateInput {...controllerProps} placeholder={__('End date', 'tutor')} />}
            />

            <Controller
              name="meeting_end_time"
              control={meetingForm.control}
              rules={{ required: __('Meeting end time is required', 'tutor') }}
              render={(controllerProps) => <FormTimeInput {...controllerProps} placeholder={__('End time', 'tutor')} />}
            />
          </div>
        </div>

        <Controller
          name="meeting_timezone"
          control={meetingForm.control}
          rules={{ required: __('Meeting timezone is required', 'tutor') }}
          render={(controllerProps) => (
            <FormSelectInput {...controllerProps} label={__('Timezone', 'tutor')} options={timeZonesOptions} />
          )}
        />

        <Controller
          name="meeting_enrolledAsAttendee"
          control={meetingForm.control}
          rules={{ required: __('Meeting enrolled as attendee is required', 'tutor') }}
          render={(controllerProps) => (
            <FormCheckbox {...controllerProps} label={__('Add enrolled students as attendees', 'tutor')} />
          )}
        />
      </div>

      <div css={styles.buttonWrapper({ isScrolling })}>
        <Button variant="text" size="small" onClick={onCancel}>
          {__('Cancel', 'tutor')}
        </Button>
        <Button
          loading={saveGoogleMeetMeeting.isPending}
          variant="primary"
          size="small"
          onClick={meetingForm.handleSubmit(onSubmit)}
        >
          {data ? __('Update Meeting', 'tutor') : __('Create Meeting', 'tutor')}
        </Button>
      </div>
    </div>
  );
};

export default GoogleMeetMeetingForm;
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
  dateLabel: css`
    ${typography.caption('medium')}
    color: ${colorTokens.text.title};
  `,
  meetingDateTimeWrapper: css`
    ${styleUtils.display.flex('column')}
    gap: ${spacing[6]};
  `,
  meetingDateTime: css`
    ${styleUtils.display.flex()}
    justify-content: space-between;
    align-items: flex-start;
    gap: ${spacing[6]};
  `,
  buttonWrapper: ({ isScrolling = false }) => css`
    ${styleUtils.display.flex()}
    padding-top: ${spacing[8]};
    padding-inline: ${spacing[12]};
    justify-content: flex-end;
    gap: ${spacing[8]};
    z-index: ${zIndex.positive};
    ${
      isScrolling &&
      css`
      box-shadow: ${shadow.scrollable};
    `
    }
  `,
};
