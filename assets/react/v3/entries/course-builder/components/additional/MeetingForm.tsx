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
import Show from '@Controls/Show';

import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';

import FormSelectInput from '@Components/fields/FormSelectInput';
import { useIsScrolling } from '@Hooks/useIsScrolling';
import { styleUtils } from '@Utils/style-utils';
import {
  type MeetingFormData,
  type MeetingType,
  useCourseDetailsQuery,
  useSaveZoomMeetingMutation,
} from '@CourseBuilderServices/course';
import { convertMeetingFormDataToPayload, getCourseId } from '@CourseBuilderUtils/utils';
import { useEffect } from 'react';
import { format } from 'date-fns';
import { DateFormats } from '@Config/constants';

interface MeetingFormProps {
  type: MeetingType;
  onCancel: () => void;
  currentMeetingId: string;
}

const courseId = getCourseId();

const MeetingForm = ({ type, currentMeetingId, onCancel }: MeetingFormProps) => {
  const { ref, isScrolling } = useIsScrolling({ defaultValue: true });
  const courseDetailsQuery = useCourseDetailsQuery(courseId);
  const zoomMeetings = courseDetailsQuery.data?.zoom_meetings ?? [];

  const meetingForm = useFormWithGlobalError<MeetingFormData>({
    defaultValues: {
      meeting_name: '',
      meeting_summary: '',
      meeting_date: '',
      meeting_time: '',
      meeting_duration: '',
      meeting_duration_unit: 'min',
      meeting_enrolledAsAttendee: false,
      meeting_timezone: '',
      auto_recording: 'none',
      meeting_password: '',
      meeting_host: '',
    },
  });

  const saveZoomMeeting = useSaveZoomMeetingMutation(String(courseId));

  const timeZones = courseDetailsQuery.data?.zoom_timezones ?? {};
  const timeZonesOptions = Object.keys(timeZones).map((key) => ({
    label: timeZones[key],
    value: key,
  }));

  // @TODO: will come from app config api later.
  const onSubmit = async (data: MeetingFormData) => {
    const payload = convertMeetingFormDataToPayload(data, type, 'metabox');

    if (!courseId) {
      return;
    }

    if (type === 'zoom') {
      const zoomUsers = courseDetailsQuery.data?.zoom_users ?? {};
      const response = await saveZoomMeeting.mutateAsync({
        ...payload,
        course_id: courseId,
        meeting_host: Object.keys(zoomUsers)[0],
        ...(currentMeetingId && { meeting_id: currentMeetingId }),
      });

      if (response.data) {
        onCancel();
        meetingForm.reset();
      }
    }
  };

  // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
  useEffect(() => {
    if (!courseDetailsQuery.data) {
      return;
    }

    if (type === 'zoom') {
      const zoomUsers = courseDetailsQuery.data?.zoom_users ?? {};
      meetingForm.setValue('meeting_host', Object.values(zoomUsers)[0]);

      if (!currentMeetingId) {
        return;
      }

      const currentMeeting = zoomMeetings.find((meeting) => meeting.ID === currentMeetingId);

      if (currentMeeting) {
        meetingForm.setValue('meeting_name', currentMeeting.post_title);
        meetingForm.setValue('meeting_summary', currentMeeting.post_content);
        meetingForm.setValue(
          'meeting_date',
          format(new Date(currentMeeting.meeting_data.start_time), DateFormats.yearMonthDay)
        );
        meetingForm.setValue(
          'meeting_time',
          format(new Date(currentMeeting.meeting_data.start_time), DateFormats.hoursMinutes)
        );
        meetingForm.setValue('meeting_duration', String(currentMeeting.meeting_data.duration));
        meetingForm.setValue('meeting_duration_unit', currentMeeting.meeting_data.duration_unit);
        meetingForm.setValue('meeting_timezone', currentMeeting.meeting_data.timezone);
        meetingForm.setValue('auto_recording', currentMeeting.meeting_data.settings.auto_recording);
        meetingForm.setValue('meeting_password', currentMeeting.meeting_data.password);
      }
    }
  }, [courseDetailsQuery.data]);

  return (
    <div css={styles.container}>
      <div css={styles.formWrapper} ref={ref}>
        <Controller
          name="meeting_name"
          control={meetingForm.control}
          rules={{
            required: 'Meeting name is required',
          }}
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
          rules={{
            required: 'Meeting summary is required',
          }}
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
          <Controller
            name="meeting_date"
            control={meetingForm.control}
            rules={{
              required: 'Meeting date is required',
            }}
            render={(controllerProps) => (
              <FormDateInput
                {...controllerProps}
                label={__('Meeting Date', 'tutor')}
                placeholder={__('Enter meeting date', 'tutor')}
                disabledBefore={new Date().toISOString()}
              />
            )}
          />

          <Controller
            name="meeting_time"
            control={meetingForm.control}
            rules={{
              required: 'Meeting time is required',
            }}
            render={(controllerProps) => <FormTimeInput {...controllerProps} placeholder={__('Start time', 'tutor')} />}
          />
          <div css={styles.meetingTimeWrapper}>
            <Controller
              name="meeting_duration"
              control={meetingForm.control}
              rules={{
                required: 'Meeting duration is required',
              }}
              render={(controllerProps) => (
                <FormInput {...controllerProps} placeholder={__('Duration', 'tutor')} type="number" />
              )}
            />
            <Controller
              name="meeting_duration_unit"
              control={meetingForm.control}
              rules={{
                required: 'Meeting duration unit is required',
              }}
              render={(controllerProps) => (
                <FormSelectInput
                  {...controllerProps}
                  options={[
                    { label: 'Minutes', value: 'min' },
                    { label: 'Hours', value: 'hr' },
                  ]}
                />
              )}
            />
          </div>
        </div>

        <Show when={type === 'google_meet'}>
          <Controller
            name="meeting_enrolledAsAttendee"
            control={meetingForm.control}
            render={(controllerProps) => (
              <FormCheckbox {...controllerProps} label={__('Add enrolled students as attendees', 'tutor')} />
            )}
          />
        </Show>

        <Show when={type === 'zoom'}>
          <Controller
            name="meeting_timezone"
            control={meetingForm.control}
            rules={{
              required: 'Time zone is required',
            }}
            render={(controllerProps) => (
              <FormSelectInput
                {...controllerProps}
                label={__('Time Zone', 'tutor')}
                placeholder="Select time zone"
                options={timeZonesOptions}
                isSearchable
              />
            )}
          />
          <Controller
            name="auto_recording"
            control={meetingForm.control}
            rules={{
              required: 'Auto recording is required',
            }}
            render={(controllerProps) => (
              <FormSelectInput
                {...controllerProps}
                label={__('Auto recording', 'tutor')}
                placeholder="Select auto recording option"
                options={[
                  { label: 'No Recordings', value: 'none' },
                  { label: 'Local', value: 'local' },
                  { label: 'Cloud', value: 'cloud' },
                ]}
              />
            )}
          />

          <Controller
            name="meeting_password"
            control={meetingForm.control}
            rules={{
              required: 'Meeting password is required',
            }}
            render={(controllerProps) => (
              <FormInput
                {...controllerProps}
                label={__('Meeting Password', 'tutor')}
                placeholder={__('Enter meeting password', 'tutor')}
                type="password"
                isPassword
              />
            )}
          />

          <Controller
            name="meeting_host"
            control={meetingForm.control}
            rules={{
              required: 'Meeting host is required',
            }}
            render={(controllerProps) => (
              <FormInput
                {...controllerProps}
                label={__('Meeting Host', 'tutor')}
                placeholder={__('Enter meeting host', 'tutor')}
                disabled
              />
            )}
          />
        </Show>
      </div>

      <div css={styles.buttonWrapper({ isScrolling })}>
        <Button variant="text" size="small" onClick={onCancel}>
          {__('Cancel', 'tutor')}
        </Button>
        <Button
          loading={saveZoomMeeting.isPending}
          variant="primary"
          size="small"
          onClick={meetingForm.handleSubmit(onSubmit)}
        >
          {currentMeetingId ? __('Update Meeting', 'tutor') : __('Create Meeting', 'tutor')}
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
    ${
      isScrolling &&
      css`
      box-shadow: ${shadow.scrollable};
    `
    }
  `,
};
