import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { format } from 'date-fns';
import { useEffect } from 'react';
import { Controller } from 'react-hook-form';

import Button from '@Atoms/Button';
import { LoadingOverlay } from '@Atoms/LoadingSpinner';

import FormCheckbox from '@Components/fields/FormCheckbox';
import FormDateInput from '@Components/fields/FormDateInput';
import FormInput from '@Components/fields/FormInput';
import FormSelectInput from '@Components/fields/FormSelectInput';
import FormTextareaInput from '@Components/fields/FormTextareaInput';
import FormTimeInput from '@Components/fields/FormTimeInput';

import { tutorConfig } from '@Config/config';
import { DateFormats } from '@Config/constants';
import { borderRadius, colorTokens, fontSize, shadow, spacing, zIndex } from '@Config/styles';
import { typography } from '@Config/typography';

import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
import { useIsScrolling } from '@Hooks/useIsScrolling';

import Show from '@Controls/Show';
import {
  type GoogleMeet,
  type GoogleMeetMeetingFormData,
  useSaveGoogleMeetMutation,
} from '@CourseBuilderServices/course';
import { type ID, useGoogleMeetDetailsQuery } from '@CourseBuilderServices/curriculum';
import { getCourseId } from '@CourseBuilderUtils/utils';
import { styleUtils } from '@Utils/style-utils';
import { isDefined } from '@Utils/types';
import { invalidDateRule, invalidTimeRule } from '@Utils/validation';

interface GoogleMeetFormProps {
  onCancel: () => void;
  data: GoogleMeet | null;
  topicId?: ID;
  meetingId?: ID;
}

const courseId = getCourseId();

const GoogleMeetForm = ({ onCancel, data, topicId, meetingId }: GoogleMeetFormProps) => {
  const { ref, isScrolling } = useIsScrolling({ defaultValue: true });
  const googleMeetDetailsQuery = useGoogleMeetDetailsQuery(meetingId ? meetingId : '', topicId ? topicId : '');

  const currentMeeting = data ?? googleMeetDetailsQuery.data;

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
      meeting_enrolledAsAttendee: currentMeeting?.meeting_data.attendees === 'Yes',
    },
    shouldFocusError: true,
    mode: 'onChange',
  });

  const saveGoogleMeetMeeting = useSaveGoogleMeetMutation();

  const timezones = tutorConfig.timezones;

  const timeZonesOptions = Object.keys(timezones).map((key) => ({
    label: timezones[key],
    value: key,
  }));

  const onSubmit = async (data: GoogleMeetMeetingFormData) => {
    if (!courseId) {
      return;
    }

    const response = await saveGoogleMeetMeeting.mutateAsync({
      ...(currentMeeting && { 'post-id': Number(currentMeeting.ID), 'event-id': currentMeeting.meeting_data.id }),
      ...(topicId && {
        topic_id: topicId,
      }),
      course_id: courseId,
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

    if (response.status_code === 200 || response.status_code === 201) {
      onCancel();
      meetingForm.reset();
    }
  };

  // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
  useEffect(() => {
    if (isDefined(currentMeeting)) {
      meetingForm.reset({
        meeting_name: currentMeeting.post_title,
        meeting_summary: currentMeeting.post_content,
        meeting_start_date: currentMeeting.meeting_data.start_datetime
          ? format(new Date(currentMeeting.meeting_data.start_datetime), DateFormats.yearMonthDay)
          : '',
        meeting_start_time: currentMeeting.meeting_data.start_datetime
          ? format(new Date(currentMeeting.meeting_data.start_datetime), DateFormats.hoursMinutes)
          : '',
        meeting_end_date: currentMeeting.meeting_data.end_datetime
          ? format(new Date(currentMeeting.meeting_data.end_datetime), DateFormats.yearMonthDay)
          : '',
        meeting_end_time: currentMeeting.meeting_data.end_datetime
          ? format(new Date(currentMeeting.meeting_data.end_datetime), DateFormats.hoursMinutes)
          : '',
        meeting_timezone: currentMeeting.meeting_data.timezone,
        meeting_enrolledAsAttendee: currentMeeting.meeting_data.attendees === 'Yes',
      });
    }

    const timeoutId = setTimeout(() => {
      meetingForm.setFocus('meeting_name');
    }, 250);

    return () => {
      clearTimeout(timeoutId);
    };
  }, [currentMeeting]);

  return (
    <div css={styles.container}>
      <div css={styles.formWrapper} ref={ref}>
        <Show when={!googleMeetDetailsQuery.isLoading} fallback={<LoadingOverlay />}>
          <Controller
            name="meeting_name"
            control={meetingForm.control}
            rules={{ required: __('Name is required', 'tutor') }}
            render={(controllerProps) => (
              <FormInput
                {...controllerProps}
                label={__('Meeting Name', 'tutor')}
                placeholder={__('Enter meeting name', 'tutor')}
                selectOnFocus
              />
            )}
          />

          <Controller
            name="meeting_summary"
            control={meetingForm.control}
            rules={{ required: __('Summary is required', 'tutor') }}
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
                rules={{
                  required: __('Start date is required', 'tutor'),
                  validate: invalidDateRule,
                }}
                render={(controllerProps) => (
                  <FormDateInput
                    {...controllerProps}
                    placeholder={__('Start date', 'tutor')}
                    disabledBefore={new Date().toISOString()}
                  />
                )}
              />

              <Controller
                name="meeting_start_time"
                control={meetingForm.control}
                rules={{
                  required: __('Start time is required', 'tutor'),
                  validate: invalidTimeRule,
                }}
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
                rules={{
                  required: __('End date is required', 'tutor'),
                  validate: {
                    invalidDate: invalidDateRule,
                    checkEndDate: (value) => {
                      const startDate = meetingForm.watch('meeting_start_date');
                      const endDate = value;
                      if (startDate && endDate) {
                        return new Date(startDate) > new Date(endDate)
                          ? __('End date should be greater than start date', 'tutor')
                          : undefined;
                      }
                      return undefined;
                    },
                  },
                  deps: ['meeting_start_date'],
                }}
                render={(controllerProps) => (
                  <FormDateInput
                    {...controllerProps}
                    placeholder={__('End date', 'tutor')}
                    disabledBefore={meetingForm.watch('meeting_start_date') || new Date().toISOString()}
                  />
                )}
              />

              <Controller
                name="meeting_end_time"
                control={meetingForm.control}
                rules={{
                  required: __('End time is required', 'tutor'),
                  validate: {
                    invalidTime: invalidTimeRule,
                    checkEndTime: (value) => {
                      const startDate = meetingForm.watch('meeting_start_date');
                      const startTime = meetingForm.watch('meeting_start_time');
                      const endDate = meetingForm.watch('meeting_end_date');
                      const endTime = value;

                      if (startDate && endDate && startTime && endTime) {
                        return new Date(`${startDate} ${startTime}`) > new Date(`${endDate} ${endTime}`)
                          ? __('End time should be greater than start time', 'tutor')
                          : undefined;
                      }
                      return undefined;
                    },
                  },
                  deps: ['meeting_start_time', 'meeting_start_date', 'meeting_end_date'],
                }}
                render={(controllerProps) => (
                  <FormTimeInput {...controllerProps} placeholder={__('End time', 'tutor')} />
                )}
              />
            </div>
          </div>

          <Controller
            name="meeting_timezone"
            control={meetingForm.control}
            rules={{ required: __('Timezone is required', 'tutor') }}
            render={(controllerProps) => (
              <FormSelectInput
                {...controllerProps}
                label={__('Timezone', 'tutor')}
                placeholder={__('Select time zone', 'tutor')}
                options={timeZonesOptions}
                selectOnFocus
                isSearchable
              />
            )}
          />

          <Controller
            name="meeting_enrolledAsAttendee"
            control={meetingForm.control}
            render={(controllerProps) => (
              <FormCheckbox {...controllerProps} label={__('Add enrolled students as attendees', 'tutor')} />
            )}
          />
        </Show>
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
          {currentMeeting || meetingId ? __('Update Meeting', 'tutor') : __('Create Meeting', 'tutor')}
        </Button>
      </div>
    </div>
  );
};

export default GoogleMeetForm;

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
      color: ${colorTokens.text.title};
    }
  `,
  formWrapper: css`
    ${styleUtils.display.flex('column')};
    ${styleUtils.overflowYAuto};
    padding-inline: ${spacing[12]};
    padding-bottom: ${spacing[8]};
    gap: ${spacing[12]};
    height: 400px;
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
