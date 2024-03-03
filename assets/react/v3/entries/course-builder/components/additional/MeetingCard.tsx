import React from 'react';
import { css } from '@emotion/react';
import { format } from 'date-fns';

import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';

import { borderRadius, colorTokens, fontWeight, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';

import { styleUtils } from '@Utils/style-utils';
import { __ } from '@wordpress/i18n';

interface MeetingCardProps {
  meeting_title: string;
  meeting_date: string;
  meeting_start_time: string;
  meeting_token?: string;
  meeting_password?: string;
  meeting_link: string;
}

const MeetingCard = ({
  meeting_title,
  meeting_date,
  meeting_start_time,
  meeting_token,
  meeting_password,
  meeting_link,
}: MeetingCardProps) => {
  const dayOfMonths = format(new Date(meeting_date), 'dd');
  const month = format(new Date(meeting_date), 'MMM');
  const year = format(new Date(meeting_date), 'yyyy');
  const time = meeting_start_time.split(' ')[0];
  const ampm = meeting_start_time.split(' ')[1];

  return (
    <div css={styles.card}>
      <div css={styles.cardTitle}>{meeting_title}</div>

      <div css={styles.cardContent}>
        <span css={styles.inlineContent}>
          {__('Start time', 'tutor')}
          <div css={styles.hyphen} />
          <div css={styles.meetingDateTime} className="date-time">
            <span
              css={{
                fontWeight: fontWeight.medium,
              }}
            >
              {dayOfMonths}
            </span>{' '}
            <span>{month}</span>{' '}
            <span
              css={{
                fontWeight: fontWeight.medium,
              }}
            >
              {year}
            </span>
            ,{' '}
            <span
              css={{
                fontWeight: fontWeight.medium,
              }}
            >
              {time}
            </span>{' '}
            <span>{ampm}</span>
          </div>
        </span>

        <Show when={meeting_token}>
          <div css={styles.inlineContent}>
            {__('Meeting Token', 'tutor')}
            <div css={styles.hyphen} />
            <div>{meeting_token}</div>
          </div>
        </Show>

        <Show when={meeting_password}>
          <div css={styles.inlineContent}>
            {__('Password', 'tutor')}
            <div css={styles.hyphen} />
            <div>{meeting_password}</div>
          </div>
        </Show>

        <div css={styles.buttonWrapper}>
          <Button
            variant="outlined"
            size="small"
            type="button"
            onClick={() => {
              window.open(meeting_link, '_blank');
            }}
          >
            {__('Start Meeting', 'tutor')}
          </Button>

          <div css={styles.actions}>
            <button
              type="button"
              css={styles.actionButton}
              data-visually-hidden
              onClick={() => alert('@TODO: Will implememt later')}
            >
              <SVGIcon name="edit" width={24} height={24} />
            </button>
            <button
              type="button"
              css={styles.actionButton}
              data-visually-hidden
              onClick={() => alert('@TODO: Will implememt later')}
            >
              <SVGIcon name="delete" width={24} height={24} />
            </button>
          </div>
        </div>
      </div>
    </div>
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
