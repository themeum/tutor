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
import { useDeleteZoomMeetingMutation, type MeetingType } from '@CourseBuilderServices/course';
import { getCourseId } from '@CourseBuilderUtils/utils';
import { useRef, useState } from 'react';
import Popover from '@Molecules/Popover';
import MeetingForm from './MeetingForm';
import LoadingSpinner from '@Atoms/LoadingSpinner';

interface MeetingCardProps {
  type: MeetingType;
  ID: string;
  meetingTitle: string;
  meetingStartTime: string;
  meetingToken?: number;
  meetingPassword?: string;
  meetingLink: string;
}

const courseId = getCourseId();

const MeetingCard = ({
  type,
  ID,
  meetingTitle,
  meetingStartTime,
  meetingToken,
  meetingPassword,
  meetingLink,
}: MeetingCardProps) => {
  const [isOpen, setIsOpen] = useState(false);
  const deleteZoomMeetingMutation = useDeleteZoomMeetingMutation(String(courseId));
  const triggerRef = useRef<HTMLButtonElement>(null);

  const handleZoomMeetingDelete = async () => {
    await deleteZoomMeetingMutation.mutateAsync(ID);
  };

  const day = format(new Date(meetingStartTime), DateFormats.day);
  const month = format(new Date(meetingStartTime), DateFormats.month);
  const year = format(new Date(meetingStartTime), DateFormats.year);
  const [time, meridiem = ''] = format(new Date(meetingStartTime), DateFormats.hoursMinutes).split(' ');

  return (
    <>
      <div css={styles.card}>
        <div css={styles.cardTitle}>{meetingTitle}</div>

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

          <Show when={meetingToken}>
            <div css={styles.inlineContent}>
              {__('Meeting Token', 'tutor')}
              <div css={styles.hyphen} />
              <div>{meetingToken}</div>
            </div>
          </Show>

          <Show when={meetingPassword}>
            <div css={styles.inlineContent}>
              {__('Password', 'tutor')}
              <div css={styles.hyphen} />
              <div>{meetingPassword}</div>
            </div>
          </Show>

          <div css={styles.buttonWrapper}>
            <Button
              variant="secondary"
              size="small"
              type="button"
              onClick={() => {
                window.open(meetingLink, '_blank');
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
              <button
                type="button"
                css={styles.actionButton}
                data-visually-hidden
                onClick={() => {
                  if (meetingToken && type === 'zoom') {
                    handleZoomMeetingDelete();
                  } else {
                    alert('@TODO: Will implement later');
                  }
                }}
              >
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
      <Popover isOpen={isOpen} triggerRef={triggerRef} closePopover={() => setIsOpen(false)} maxWidth={'287px'}>
        <MeetingForm
          type={type}
          currentMeetingId={ID}
          onCancel={() => {
            setIsOpen(false);
          }}
        />
      </Popover>
    </>
  );
};

export default MeetingCard;

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
