import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { format } from 'date-fns';
import { useEffect } from 'react';
import { Controller } from 'react-hook-form';

import Button from '@TutorShared/atoms/Button';
import { LoadingOverlay } from '@TutorShared/atoms/LoadingSpinner';

import FormDateInput from '@TutorShared/components/fields/FormDateInput';
import FormInput from '@TutorShared/components/fields/FormInput';
import FormTextareaInput from '@TutorShared/components/fields/FormTextareaInput';
import FormTimeInput from '@TutorShared/components/fields/FormTimeInput';

import { borderRadius, colorTokens, fontSize, shadow, spacing, zIndex } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import { useFormWithGlobalError } from '@TutorShared/hooks/useFormWithGlobalError';

import { type ZoomMeeting, type ZoomMeetingFormData, useSaveZoomMeetingMutation } from '@CourseBuilderServices/course';
import { useZoomMeetingDetailsQuery } from '@CourseBuilderServices/curriculum';
import { getCourseId } from '@CourseBuilderUtils/utils';
import FormSelectInput from '@TutorShared/components/fields/FormSelectInput';
import { tutorConfig } from '@TutorShared/config/config';
import { DateFormats } from '@TutorShared/config/constants';
import Show from '@TutorShared/controls/Show';
import { useIsScrolling } from '@TutorShared/hooks/useIsScrolling';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { type ID, isDefined } from '@TutorShared/utils/types';
import { invalidTimeRule } from '@TutorShared/utils/validation';

interface ZoomMeetingFormProps {
  onCancel: () => void;
  data: ZoomMeeting | null;
  meetingHost: {
    [key: string]: string;
  };
  topicId?: ID;
  meetingId?: ID;
}

const courseId = getCourseId();

const ZoomMeetingForm = ({ onCancel, data, meetingHost, topicId, meetingId }: ZoomMeetingFormProps) => {
  const { ref, isScrolling } = useIsScrolling({ defaultValue: true });
  const zoomMeetingDetailsQuery = useZoomMeetingDetailsQuery(meetingId ? meetingId : '', topicId ? topicId : '');

  const currentMeeting = data ?? zoomMeetingDetailsQuery.data;
  const meetingStartsAt = currentMeeting?.meeting_starts_at ?? currentMeeting?.meeting_data.start_time ?? '';

  const meetingForm = useFormWithGlobalError<ZoomMeetingFormData>({
    defaultValues: {
      meeting_name: currentMeeting?.post_title ?? '',
      meeting_summary: currentMeeting?.post_content ?? '',
      meeting_date: meetingStartsAt ? format(new Date(meetingStartsAt), DateFormats.yearMonthDay) : '',
      meeting_time: meetingStartsAt ? format(new Date(meetingStartsAt), DateFormats.hoursMinutes) : '',
      meeting_duration: currentMeeting?.meeting_data.duration ? String(currentMeeting?.meeting_data.duration) : '40',
      meeting_duration_unit: currentMeeting?.meeting_data.duration_unit ?? 'min',
      meeting_timezone: currentMeeting?.meeting_data.timezone ?? '',
      auto_recording: currentMeeting?.meeting_data.settings?.auto_recording ?? 'none',
      meeting_password: currentMeeting?.meeting_data.password ?? '',
      meeting_host: Object.keys(meetingHost).length === 1 ? Object.keys(meetingHost)[0] : '',
    },
    shouldFocusError: true,
    mode: 'onChange',
  });

  const saveZoomMeeting = useSaveZoomMeetingMutation();

  const timezones = tutorConfig.timezones;
  const timeZonesOptions = Object.keys(timezones).map((key) => ({
    label: timezones[key],
    value: key,
  }));

  const hostOptions = Object.keys(meetingHost).map((key) => ({
    label: meetingHost[key],
    value: key,
  }));

  const onSubmit = async (formData: ZoomMeetingFormData) => {
    if (!courseId) {
      return;
    }

    const response = await saveZoomMeeting.mutateAsync({
      ...(currentMeeting && { meeting_id: Number(currentMeeting.ID) }),
      ...(topicId && { topic_id: Number(topicId) }),
      course_id: courseId,
      meeting_title: formData.meeting_name,
      meeting_summary: formData.meeting_summary,
      meeting_date: formData.meeting_date,
      meeting_time: formData.meeting_time,
      meeting_duration: Number(formData.meeting_duration),
      meeting_duration_unit: formData.meeting_duration_unit,
      meeting_timezone: formData.meeting_timezone,
      auto_recording: formData.auto_recording,
      meeting_password: formData.meeting_password,
      click_form: topicId ? 'course_builder' : 'metabox',
      meeting_host: formData.meeting_host,
    });

    if (response.data) {
      onCancel();
      meetingForm.reset();
    }
  };

  useEffect(() => {
    if (isDefined(currentMeeting)) {
      meetingForm.reset({
        meeting_name: currentMeeting.post_title,
        meeting_summary: currentMeeting.post_content,
        meeting_date: meetingStartsAt ? format(new Date(meetingStartsAt), DateFormats.yearMonthDay) : '',
        meeting_time: meetingStartsAt ? format(new Date(meetingStartsAt), DateFormats.hoursMinutes) : '',
        meeting_duration: String(currentMeeting.meeting_data.duration),
        meeting_duration_unit: currentMeeting.meeting_data.duration_unit,
        meeting_timezone: currentMeeting.meeting_data.timezone,
        auto_recording: currentMeeting.meeting_data.settings?.auto_recording ?? 'none',
        meeting_password: currentMeeting.meeting_data.password,
        meeting_host: currentMeeting.meeting_data.host_id,
      });
    }

    const timeoutId = setTimeout(() => {
      meetingForm.setFocus('meeting_name');
    }, 250);

    return () => {
      clearTimeout(timeoutId);
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [currentMeeting]);

  return (
    <div css={styles.container}>
      <div css={styles.formWrapper} ref={ref}>
        <Show when={!zoomMeetingDetailsQuery.isLoading} fallback={<LoadingOverlay />}>
          <Controller
            name="meeting_name"
            control={meetingForm.control}
            rules={{
              required: __('Name is required', 'tutor'),
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
              required: __('Summary is required', 'tutor'),
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
                required: __('Date is required', 'tutor'),
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
                required: __('Time is required', 'tutor'),
                validate: invalidTimeRule,
              }}
              render={(controllerProps) => (
                <FormTimeInput {...controllerProps} placeholder={__('Start time', 'tutor')} />
              )}
            />
            <div css={styles.meetingTimeWrapper}>
              <Controller
                name="meeting_duration"
                control={meetingForm.control}
                rules={{
                  required: __('Duration is required', 'tutor'),
                }}
                render={(controllerProps) => (
                  <FormInput
                    {...controllerProps}
                    label={__('Meeting Duration', 'tutor')}
                    placeholder={__('Duration', 'tutor')}
                    type="number"
                    selectOnFocus
                  />
                )}
              />
              <Controller
                name="meeting_duration_unit"
                control={meetingForm.control}
                rules={{
                  required: __('Duration unit is required', 'tutor'),
                }}
                render={(controllerProps) => (
                  <FormSelectInput
                    {...controllerProps}
                    label={<span>&nbsp;</span>}
                    options={[
                      { label: __('Minutes', 'tutor'), value: 'min' },
                      { label: __('Hours', 'tutor'), value: 'hr' },
                    ]}
                  />
                )}
              />
            </div>
          </div>

          <Controller
            name="meeting_timezone"
            control={meetingForm.control}
            rules={{
              required: __('Timezone is required', 'tutor'),
            }}
            render={(controllerProps) => (
              <FormSelectInput
                {...controllerProps}
                label={__('Timezone', 'tutor')}
                placeholder={__('Select timezone', 'tutor')}
                options={timeZonesOptions}
                selectOnFocus
                isSearchable
              />
            )}
          />
          <Controller
            name="auto_recording"
            control={meetingForm.control}
            rules={{
              required: __('Auto recording is required', 'tutor'),
            }}
            render={(controllerProps) => (
              <FormSelectInput
                {...controllerProps}
                label={__('Auto Recording', 'tutor')}
                placeholder={__('Select auto recording option', 'tutor')}
                options={[
                  { label: __('No recordings', 'tutor'), value: 'none' },
                  { label: __('Local', 'tutor'), value: 'local' },
                  { label: __('Cloud', 'tutor'), value: 'cloud' },
                ]}
              />
            )}
          />

          <Controller
            name="meeting_password"
            control={meetingForm.control}
            rules={{
              required: __('Password is required', 'tutor'),
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
              required: __('Meeting host is required', 'tutor'),
            }}
            render={(controllerProps) => (
              <FormSelectInput
                {...controllerProps}
                label={__('Meeting Host', 'tutor')}
                placeholder={__('Enter meeting host', 'tutor')}
                options={hostOptions}
                disabled={isDefined(currentMeeting)}
                selectOnFocus
                isSearchable
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
          data-cy="save-zoom-meeting"
          loading={saveZoomMeeting.isPending}
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

export default ZoomMeetingForm;

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
  meetingDateTimeWrapper: css`
    ${styleUtils.display.flex('column')}
    gap: ${spacing[6]};
  `,
  meetingTimeWrapper: css`
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

    ${isScrolling &&
    css`
      box-shadow: ${shadow.scrollable};
    `}
  `,
};
