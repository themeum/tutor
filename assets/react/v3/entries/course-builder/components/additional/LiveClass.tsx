import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useRef, useState } from 'react';

import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';

import EmptyState from '@Molecules/EmptyState';

import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import For from '@Controls/For';
import Show from '@Controls/Show';

import { AnimationType } from '@Hooks/useAnimation';
import Popover from '@Molecules/Popover';
import { styleUtils } from '@Utils/style-utils';
import type { MeetingType, ZoomMeeting } from '@CourseBuilderServices/course';
import { isAddonEnabled } from '@CourseBuilderUtils/utils';
import ZoomMeetingCard from './meeting/ZoomMeetingCard';
import { tutorConfig } from '@Config/config';
import ZoomMeetingForm from './meeting/ZoomMeetingForm';

interface LiveClassProps {
  zoomMeetings: ZoomMeeting[];
  zoomUsers: {
    [key: string]: string;
  };
  zoomTimezones: {
    [key: string]: string;
  };
}

const isPro = !!tutorConfig.tutor_pro_url;
const isZoomAddonEnabled = isAddonEnabled('Tutor Zoom Integration');

const LiveClass = ({ zoomMeetings, zoomUsers, zoomTimezones }: LiveClassProps) => {
  const [showMeetingForm, setShowMeetingForm] = useState<MeetingType | null>(null);

  const zoomButtonRef = useRef<HTMLButtonElement>(null);
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
          when={isPro && isZoomAddonEnabled}
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
              {(meeting) => (
                <div
                  key={meeting.ID}
                  css={styles.meeting({
                    hasMeeting: zoomMeetings.length > 0,
                  })}
                >
                  <ZoomMeetingCard data={meeting} meetingHost={zoomUsers} timezones={zoomTimezones} />
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
        </Show>
      </Show>

      <Popover
        triggerRef={zoomButtonRef}
        isOpen={showMeetingForm === 'zoom'}
        closePopover={() => setShowMeetingForm(null)}
        animationType={AnimationType.slideUp}
      >
        <ZoomMeetingForm
          data={null}
          meetingHost={zoomUsers}
          onCancel={() => {
            setShowMeetingForm(null);
          }}
          timezones={zoomTimezones}
        />
      </Popover>
      {/* @TODO: Will be implemented later */}
      {/* <Popover
        triggerRef={googleMeetButtonRef}
        isOpen={showMeetingForm === 'google_meet'}
        closePopover={() => setShowMeetingForm(null)}
        animationType={AnimationType.slideUp}
      >
        <MeetingForm
          type={showMeetingForm as MeetingType}
          onCancel={() => {
            setShowMeetingForm(null);
          }}
          currentMeetingId=""
        />
      </Popover> */}
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
    ${
      hasMeeting &&
      css`
      border: 1px solid ${colorTokens.stroke.default};
    `
    }
    border-radius: ${borderRadius.card};
  `,
  meeting: ({ hasMeeting }: { hasMeeting: boolean }) => css`
    padding: ${spacing[8]} ${spacing[8]} ${spacing[12]} ${spacing[8]};
    ${
      hasMeeting &&
      css`
      border-bottom: 1px solid ${colorTokens.stroke.divider};
    `
    }
  `,
  meetingsFooter: ({ hasMeeting }: { hasMeeting: boolean }) => css`
    width: 100%;
    ${
      hasMeeting &&
      css`
      padding: ${spacing[12]} ${spacing[8]};
    `
    }
  `,
};
