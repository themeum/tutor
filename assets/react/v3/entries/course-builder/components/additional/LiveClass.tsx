import React, { useState } from 'react';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';

import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';

import EmptyState from '@Molecules/EmptyState';

import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import For from '@Controls/For';

import MeetingForm, { MeetingType } from './MeetingForm';
import MeetingCard from './MeetingCard';
import { styleUtils } from '@Utils/style-utils';

export interface Meeting {
  id: number;
  type: MeetingType;
  meeting_title: string;
  meeting_date: string;
  meeting_start_time: string;
  meeting_link: string;
  meeting_token?: string;
  meeting_password?: string;
}

// @TODO: will come from app config api later.
const isPro = true;
const hasLiveAddons = true;

const LiveClass = () => {
  const [showMeetingForm, setShowMeetingForm] = useState<MeetingType | null>(null);
  // @TODO: will come from app config api later.
  const [meetings, setMeetings] = useState<Meeting[]>([]);

  const zoomMeetings = meetings.filter(meeting => meeting.type === 'zoom');
  const googleMeetMeetings = meetings.filter(meeting => meeting.type === 'google_meet');

  return (
    <div css={styles.liveClass}>
      <span css={styles.label}>
        {__('Live Class', 'tutor')}
        {!isPro && <SVGIcon name="crown" width={24} height={24} />}
      </span>
      <Show
        when={isPro}
        fallback={
          <EmptyState
            size="small"
            removeBorder={false}
            emptyStateImage="https://via.placeholder.com/360x360"
            emptyStateImage2x="https://via.placeholder.com/760x760"
            imageAltText="No live class addons found"
            title={__('Make the learning more interactive and fun using Live class feature! ', 'tutor')}
            description={__(
              'when an unknown printer took a galley of type and scrambled it to make a type specimen book.',
              'tutor'
            )}
            actions={
              <Button
                icon={<SVGIcon name="crown" width={24} height={24} />}
                onClick={() => {
                  alert('@TODO: Will be implemented in future');
                }}
              >
                {__('Get Tutor LMS Pro', 'tutor')}
              </Button>
            }
          />
        }
      >
        <Show
          when={hasLiveAddons}
          fallback={
            <EmptyState
              size="small"
              removeBorder={false}
              emptyStateImage="https://via.placeholder.com/360x360"
              emptyStateImage2x="https://via.placeholder.com/760x760"
              imageAltText="No live class addons found"
              title={__('You can use this feature by activating Google meet, Zoom or Jitsi from addons', 'tutor')}
              description={__(
                'when an unknown printer took a galley of type and scrambled it to make a type specimen book.',
                'tutor'
              )}
              actions={
                <Button
                  variant="secondary"
                  onClick={() => {
                    alert('@TODO: Will be implemented in future');
                  }}
                >
                  {__('Go to addons', 'tutor')}
                </Button>
              }
            />
          }
        >
          <Show
            when={showMeetingForm === 'zoom'}
            fallback={
              <Show
                when={zoomMeetings.length !== 0}
                fallback={
                  <Button
                    variant="secondary"
                    icon={<SVGIcon name="zoomColorize" width={24} height={24} />}
                    buttonContentCss={css`
                      justify-content: center;
                    `}
                    onClick={() => setShowMeetingForm('zoom')}
                  >
                    {__('Create a Zoom meeting', 'tutor')}
                  </Button>
                }
              >
                <div css={styles.meetingsWrapper}>
                  <For each={zoomMeetings}>
                    {meeting => (
                      <div key={meeting.id} css={styles.meeting}>
                        <MeetingCard
                          meeting_title={meeting.meeting_title}
                          meeting_date={meeting.meeting_date}
                          meeting_start_time={meeting.meeting_start_time}
                          meeting_link={meeting.meeting_link}
                          meeting_token={meeting.meeting_token}
                          meeting_password={meeting.meeting_password}
                        />
                      </div>
                    )}
                  </For>
                  <div css={styles.meetingsFooter}>
                    <Button
                      variant="secondary"
                      icon={<SVGIcon name="zoomColorize" width={24} height={24} />}
                      buttonContentCss={css`
                        justify-content: center;
                      `}
                      buttonCss={css`
                        width: 100%;
                      `}
                      onClick={() => setShowMeetingForm('zoom')}
                    >
                      {__('Create a Zoom meeting', 'tutor')}
                    </Button>
                  </div>
                </div>
              </Show>
            }
          >
            <MeetingForm type="zoom" setShowMeetingForm={setShowMeetingForm} setMeetings={setMeetings} />
          </Show>

          <Show
            when={showMeetingForm === 'google_meet'}
            fallback={
              <Show
                when={googleMeetMeetings.length !== 0}
                fallback={
                  <Button
                    variant="secondary"
                    icon={<SVGIcon name="googleMeetColorize" width={24} height={24} />}
                    buttonContentCss={css`
                      justify-content: center;
                    `}
                    onClick={() => setShowMeetingForm('google_meet')}
                  >
                    {__('Create a Google Meet', 'tutor')}
                  </Button>
                }
              >
                <div css={styles.meetingsWrapper}>
                  <For each={googleMeetMeetings}>
                    {meeting => (
                      <div key={meeting.id} css={styles.meeting}>
                        <MeetingCard
                          meeting_title={meeting.meeting_title}
                          meeting_date={meeting.meeting_date}
                          meeting_start_time={meeting.meeting_start_time}
                          meeting_link={meeting.meeting_link}
                          meeting_token={meeting.meeting_token}
                          meeting_password={meeting.meeting_password}
                        />
                      </div>
                    )}
                  </For>
                  <div css={styles.meetingsFooter}>
                    <Button
                      variant="secondary"
                      icon={<SVGIcon name="googleMeetColorize" width={24} height={24} />}
                      buttonContentCss={css`
                        justify-content: center;
                      `}
                      buttonCss={css`
                        width: 100%;
                      `}
                      onClick={() => setShowMeetingForm('google_meet')}
                    >
                      {__('Create a Google Meet', 'tutor')}
                    </Button>
                  </div>
                </div>
              </Show>
            }
          >
            <MeetingForm type="google_meet" setShowMeetingForm={setShowMeetingForm} setMeetings={setMeetings} />
          </Show>

          <Show
            when={showMeetingForm === 'jitsi'}
            fallback={
              <Button
                variant="secondary"
                icon={<SVGIcon name="jitsiColorize" width={24} height={24} />}
                buttonContentCss={css`
                  justify-content: center;
                `}
                onClick={() => {
                  alert('@TODO: Will be implemented in future');
                }}
              >
                {__('Create a Jitsi meeting', 'tutor')}
              </Button>
            }
          >
            <MeetingForm type="jitsi" setShowMeetingForm={setShowMeetingForm} setMeetings={setMeetings} />
          </Show>
        </Show>
      </Show>
    </div>
  );
};

export default LiveClass;

const styles = {
  label: css`
    ${styleUtils.display.inlineFlex()}
    align-items: center;
    gap: ${spacing[2]};
    ${typography.body()}
    color: ${colorTokens.text.title};
  `,
  liveClass: css`
    ${styleUtils.display.flex('column')}
    gap: ${spacing[8]};
  `,
  meetingsWrapper: css`
    ${styleUtils.display.flex('column')}
    border: 1px solid ${colorTokens.stroke.default};
    border-radius: ${borderRadius.card};
  `,
  meeting: css`
    padding: ${spacing[8]} ${spacing[8]} ${spacing[12]} ${spacing[8]};
    border-bottom: 1px solid ${colorTokens.stroke.divider};
  `,
  meetingsFooter: css`
    width: 100%;
    padding: ${spacing[12]} ${spacing[8]};
  `,
};
