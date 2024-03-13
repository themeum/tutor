import React, { useRef, useState } from 'react';
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
import Popover from '@Molecules/Popover';
import { Portal, usePortalPopover } from '@Hooks/usePortalPopover';
import { element } from 'prop-types';

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

  const zoomButtonRef = useRef<HTMLButtonElement>(null);
  const googleMeetButtonRef = useRef<HTMLButtonElement>(null);
  const jitsiButtonRef = useRef<HTMLButtonElement>(null);

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
            imageAltText={__('No live class addons found', 'tutor')}
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
          <div
            css={styles.meetingsWrapper({
              hasMeeting: zoomMeetings.length > 0,
            })}
          >
            <For each={zoomMeetings}>
              {meeting => (
                <div
                  key={meeting.id}
                  css={styles.meeting({
                    hasMeeting: zoomMeetings.length > 0,
                  })}
                >
                  <MeetingCard
                    meetingTitle={meeting.meeting_title}
                    meetingDate={meeting.meeting_date}
                    meetingStartTime={meeting.meeting_start_time}
                    meetingLink={meeting.meeting_link}
                    meetingToken={meeting.meeting_token}
                    meetingPassword={meeting.meeting_password}
                  />
                </div>
              )}
            </For>
            <div
              css={styles.meetingsFooter({
                hasMeeting: zoomMeetings.length > 0,
              })}
            >
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
                ref={zoomButtonRef}
              >
                {__('Create a Zoom meeting', 'tutor')}
              </Button>
            </div>
          </div>

          <div
            css={styles.meetingsWrapper({
              hasMeeting: googleMeetMeetings.length > 0,
            })}
          >
            <For each={googleMeetMeetings}>
              {meeting => (
                <div
                  key={meeting.id}
                  css={styles.meeting({
                    hasMeeting: googleMeetMeetings.length > 0,
                  })}
                >
                  <MeetingCard
                    meetingTitle={meeting.meeting_title}
                    meetingDate={meeting.meeting_date}
                    meetingStartTime={meeting.meeting_start_time}
                    meetingLink={meeting.meeting_link}
                    meetingToken={meeting.meeting_token}
                    meetingPassword={meeting.meeting_password}
                  />
                </div>
              )}
            </For>
            <div
              css={styles.meetingsFooter({
                hasMeeting: googleMeetMeetings.length > 0,
              })}
            >
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
                ref={googleMeetButtonRef}
              >
                {__('Create a Google Meet', 'tutor')}
              </Button>
            </div>
          </div>

          <Button
            variant="secondary"
            icon={<SVGIcon name="jitsiColorize" width={24} height={24} />}
            buttonContentCss={css`
              justify-content: center;
            `}
            onClick={() => {
              alert('@TODO: Will be implemented in future');
            }}
            ref={jitsiButtonRef}
          >
            {__('Create a Jitsi meeting', 'tutor')}
          </Button>
        </Show>
      </Show>

      <Popover
        triggerRef={zoomButtonRef}
        isOpen={showMeetingForm === 'zoom'}
        closePopover={() => setShowMeetingForm(null)}
      >
        <MeetingForm
          type={showMeetingForm as MeetingType}
          setShowMeetingForm={setShowMeetingForm}
          setMeetings={setMeetings}
        />
      </Popover>
      <Popover
        triggerRef={googleMeetButtonRef}
        isOpen={showMeetingForm === 'google_meet'}
        closePopover={() => setShowMeetingForm(null)}
      >
        <MeetingForm
          type={showMeetingForm as MeetingType}
          setShowMeetingForm={setShowMeetingForm}
          setMeetings={setMeetings}
        />
      </Popover>
      <Popover
        triggerRef={jitsiButtonRef}
        isOpen={showMeetingForm === 'jitsi'}
        closePopover={() => setShowMeetingForm(null)}
      >
        <MeetingForm
          type={showMeetingForm as MeetingType}
          setShowMeetingForm={setShowMeetingForm}
          setMeetings={setMeetings}
        />
      </Popover>
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
  meetingsWrapper: ({ hasMeeting }: { hasMeeting: boolean }) => css`
    ${styleUtils.display.flex('column')}
    ${hasMeeting &&
    css`
      border: 1px solid ${colorTokens.stroke.default};
    `}
    border-radius: ${borderRadius.card};
  `,
  meeting: ({ hasMeeting }: { hasMeeting: boolean }) => css`
    padding: ${spacing[8]} ${spacing[8]} ${spacing[12]} ${spacing[8]};
    ${hasMeeting &&
    css`
      border-bottom: 1px solid ${colorTokens.stroke.divider};
    `}
  `,
  meetingsFooter: ({ hasMeeting }: { hasMeeting: boolean }) => css`
    width: 100%;
    ${hasMeeting &&
    css`
      padding: ${spacing[12]} ${spacing[8]};
    `}
  `,
};
