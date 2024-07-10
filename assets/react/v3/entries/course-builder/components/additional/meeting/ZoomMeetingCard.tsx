import { css } from '@emotion/react';
import { format } from 'date-fns';
import { __ } from '@wordpress/i18n';

import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';

import { borderRadius, colorTokens, fontWeight, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';

import { DateFormats } from '@Config/constants';
import { styleUtils } from '@Utils/style-utils';
import { useDeleteZoomMeetingMutation, type ZoomMeeting } from '@CourseBuilderServices/course';
import { getCourseId } from '@CourseBuilderUtils/utils';
import { useRef, useState } from 'react';
import Popover from '@Molecules/Popover';
import LoadingSpinner from '@Atoms/LoadingSpinner';
import ZoomMeetingForm from './ZoomMeetingForm';

interface ZoomMeetingCardProps {
  data: ZoomMeeting;
  timezones: {
    [key: string]: string;
  };
  meetingHost: {
    [key: string]: string;
  };
  topicId?: string;
}

const courseId = getCourseId();

const ZoomMeetingCard = ({ data, meetingHost, timezones, topicId }: ZoomMeetingCardProps) => {
  const [isOpen, setIsOpen] = useState(false);
  const deleteZoomMeetingMutation = useDeleteZoomMeetingMutation(String(courseId));
  const triggerRef = useRef<HTMLButtonElement>(null);
  const { ID, meeting_data, post_title } = data;

  const handleZoomMeetingDelete = async () => {
    await deleteZoomMeetingMutation.mutateAsync(ID);
  };

  const day = format(new Date(meeting_data.start_time), DateFormats.day);
  const month = format(new Date(meeting_data.start_time), DateFormats.month);
  const year = format(new Date(meeting_data.start_time), DateFormats.year);
  const [time, meridiem = ''] = format(new Date(meeting_data.start_time), DateFormats.hoursMinutes).split(' ');

  return (
    <>
      <div css={styles.card}>
        <div css={styles.cardTitle}>{post_title}</div>

        <div css={styles.cardContent}>
          <span css={styles.inlineContent}>
            {__('Start time', 'tutor')}
            <div css={styles.hyphen} />
            <div css={styles.meetingDateTime} className="date-time">
              <span
                css={{
                  fontWeight: fontWeight.semiBold,
                }}
              >
                {`${day} `}
              </span>
              <span>{`${month} `}</span>
              <span
                css={{
                  fontWeight: fontWeight.semiBold,
                }}
              >
                {`${year}, `}
              </span>
              <span
                css={{
                  fontWeight: fontWeight.semiBold,
                }}
              >
                {`${time} `}
              </span>
              <span>{`${meridiem} `}</span>
            </div>
          </span>

          <Show when={meeting_data.id}>
            <div css={styles.inlineContent}>
              {__('Meeting Token', 'tutor')}
              <div css={styles.hyphen} />
              <div>{meeting_data.id}</div>
            </div>
          </Show>

          <Show when={meeting_data.password}>
            <div css={styles.inlineContent}>
              {__('Password', 'tutor')}
              <div css={styles.hyphen} />
              <div>{meeting_data.password}</div>
            </div>
          </Show>

          <div css={styles.buttonWrapper}>
            <Button
              variant="secondary"
              size="small"
              type="button"
              onClick={() => {
                window.open(meeting_data.start_url, '_blank');
              }}
            >
              {__('Start Meeting', 'tutor')}
            </Button>

            <div css={styles.actions}>
              <button
                ref={triggerRef}
                type="button"
                css={styles.actionButton}
                data-visually-hidden
                onClick={() => {
                  setIsOpen(true);
                }}
              >
                <SVGIcon name="edit" width={24} height={24} />
              </button>
              <button type="button" css={styles.actionButton} data-visually-hidden onClick={handleZoomMeetingDelete}>
                {deleteZoomMeetingMutation.isPending ? (
                  <LoadingSpinner size={24} />
                ) : (
                  <SVGIcon name="delete" width={24} height={24} />
                )}
              </button>
            </div>
          </div>
        </div>
      </div>
      <Popover isOpen={isOpen} triggerRef={triggerRef} closePopover={() => setIsOpen(false)} maxWidth={'306px'}>
        <ZoomMeetingForm
          data={data}
          meetingHost={meetingHost}
          topicId={topicId}
          onCancel={() => {
            setIsOpen(false);
          }}
        />
      </Popover>
    </>
  );
};

export default ZoomMeetingCard;

const styles = {
  card: css`
    ${styleUtils.display.flex('column')}
    padding: ${spacing[8]} ${spacing[12]} ${spacing[12]} ${spacing[12]};
    gap: ${spacing[8]};
    border-radius: ${borderRadius[6]};
    transition: background 0.3s ease;

    [data-visually-hidden] {
      opacity: 0;
      transition: opacity 0.3s ease-in-out;
    }

    &:hover {
      background-color: ${colorTokens.background.hover};
      [data-visually-hidden] {
        opacity: 1;
      }
      .date-time {
        background: none;
      }
    }
  `,
  cardTitle: css`
    ${typography.caption('medium')}
    color: ${colorTokens.text.title};
  `,
  cardContent: css`
    ${styleUtils.display.flex('column')}
    gap: ${spacing[8]};
  `,
  hyphen: css`
    width: 5px;
    height: 2px;
    background: ${colorTokens.stroke.default};
  `,
  inlineContent: css`
    ${typography.small('regular')}
    ${styleUtils.display.flex()}
    align-items: center;
    gap: ${spacing[6]};
  `,
  meetingDateTime: css`
    padding: ${spacing[4]} ${spacing[6]};
    border-radius: ${borderRadius[4]};
    background: ${colorTokens.background.status.processing};
    transition: background 0.3s ease-in-out;
  `,
  buttonWrapper: css`
    ${styleUtils.display.flex()};
    margin-top: ${spacing[8]};
    justify-content: space-between;
  `,
  actions: css`
    ${styleUtils.display.flex()};
    align-items: center;
    gap: ${spacing[8]};
  `,
  actionButton: css`
    ${styleUtils.resetButton};
    color: ${colorTokens.icon.default};
    display: flex;
    cursor: pointer;
  `,
};
