import { css } from '@emotion/react';
import { __, sprintf } from '@wordpress/i18n';
import { format } from 'date-fns';
import { useRef, useState } from 'react';

import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import ConfirmationPopover from '@Molecules/ConfirmationPopover';
import Popover from '@Molecules/Popover';

import { borderRadius, colorTokens, fontWeight, spacing } from '@Config/styles';
import { typography } from '@Config/typography';

import { DateFormats } from '@Config/constants';
import { type GoogleMeet, useDeleteGoogleMeetMutation } from '@CourseBuilderServices/course';
import { getCourseId } from '@CourseBuilderUtils/utils';
import { AnimationType } from '@Hooks/useAnimation';
import { styleUtils } from '@Utils/style-utils';
import { noop } from '@Utils/util';
import GoogleMeetForm from './GoogleMeetForm';

interface GoogleMeetMeetingCardProps {
  data: GoogleMeet;
  topicId?: string;
}

const courseId = getCourseId();

const GoogleMeetMeetingCard = ({ data, topicId }: GoogleMeetMeetingCardProps) => {
  const [isOpen, setIsOpen] = useState(false);
  const [isDeletePopoverOpen, setIsDeletePopoverOpen] = useState(false);
  const deleteGoogleMeetMeetingMutation = useDeleteGoogleMeetMutation(String(courseId), {
    'post-id': data.ID,
    'event-id': data.meeting_data.id,
  });
  const triggerRef = useRef<HTMLButtonElement>(null);
  const deleteRef = useRef<HTMLButtonElement>(null);
  const { meeting_data, post_title } = data;

  const handleGoogleMeetingDelete = async () => {
    const response = await deleteGoogleMeetMeetingMutation.mutateAsync();

    if (response.status_code === 200) {
      setIsDeletePopoverOpen(false);
    }
  };

  const day = format(new Date(meeting_data.start_datetime), DateFormats.day);
  const month = format(new Date(meeting_data.start_datetime), DateFormats.month);
  const year = format(new Date(meeting_data.start_datetime), DateFormats.year);
  const [time, meridiem = ''] = format(new Date(meeting_data.start_datetime), DateFormats.hoursMinutes).split(' ');

  return (
    <>
      <div
        css={styles.card({
          isPopoverOpen: isDeletePopoverOpen || isOpen,
        })}
      >
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

          <div css={styles.buttonWrapper}>
            <Button
              variant="secondary"
              size="small"
              type="button"
              onClick={() => {
                window.open(meeting_data.meet_link, '_blank', 'noopener');
              }}
            >
              {__('Start Meeting', 'tutor')}
            </Button>

            <div css={styles.actions}>
              <button
                ref={triggerRef}
                type="button"
                css={styleUtils.actionButton}
                data-visually-hidden
                onClick={() => setIsOpen(true)}
              >
                <SVGIcon name="edit" width={24} height={24} />
              </button>
              <button
                type="button"
                css={styleUtils.actionButton}
                data-visually-hidden
                onClick={() => {
                  setIsDeletePopoverOpen(true);
                }}
                ref={deleteRef}
              >
                <SVGIcon name="delete" width={24} height={24} />
              </button>
            </div>
          </div>
        </div>
      </div>
      <Popover isOpen={isOpen} triggerRef={triggerRef} closePopover={() => setIsOpen(false)} maxWidth={'306px'}>
        <GoogleMeetForm
          data={data}
          topicId={topicId}
          onCancel={() => {
            setIsOpen(false);
          }}
        />
      </Popover>
      <ConfirmationPopover
        isOpen={isDeletePopoverOpen}
        triggerRef={deleteRef}
        closePopover={noop}
        maxWidth="258px"
        title={sprintf(__('Delete "%s"', 'tutor'), post_title)}
        message={__('Are you sure you want to delete this meeting? This cannot be undone.', 'tutor')}
        animationType={AnimationType.slideUp}
        arrow="auto"
        hideArrow
        isLoading={deleteGoogleMeetMeetingMutation.isPending}
        confirmButton={{
          text: __('Delete', 'tutor'),
          variant: 'text',
          isDelete: true,
        }}
        cancelButton={{
          text: __('Cancel', 'tutor'),
          variant: 'text',
        }}
        onConfirmation={async () => {
          await handleGoogleMeetingDelete();
        }}
        onCancel={() => {
          setIsDeletePopoverOpen(false);
        }}
      />
    </>
  );
};

export default GoogleMeetMeetingCard;

const styles = {
  card: ({
    isPopoverOpen = false,
  }: {
    isPopoverOpen: boolean;
  }) => css`
    ${styleUtils.display.flex('column')}
    padding: ${spacing[8]} ${spacing[12]} ${spacing[12]} ${spacing[12]};
    gap: ${spacing[8]};
    border-radius: ${borderRadius[6]};
    transition: background 0.3s ease;
    [data-visually-hidden] {
      opacity: 0;
      transition: opacity 0.3s ease-in-out;
    }

    ${
      isPopoverOpen &&
      css`
        background-color: ${colorTokens.background.hover};
        [data-visually-hidden] {
          opacity: 1;
        }
        .date-time {
          background: none;
        }
      `
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
};
