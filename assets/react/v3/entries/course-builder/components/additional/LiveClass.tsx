import { css } from '@emotion/react';
import { useQueryClient } from '@tanstack/react-query';
import { __ } from '@wordpress/i18n';
import { useRef, useState } from 'react';

import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';

import EmptyState from '@Molecules/EmptyState';
import Popover from '@Molecules/Popover';

import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import For from '@Controls/For';
import Show from '@Controls/Show';

import config, { tutorConfig } from '@Config/config';
import { Addons } from '@Config/constants';
import type { CourseDetailsResponse, GoogleMeet, MeetingType, ZoomMeeting } from '@CourseBuilderServices/course';
import { getCourseId, isAddonEnabled } from '@CourseBuilderUtils/utils';
import { AnimationType } from '@Hooks/useAnimation';
import { styleUtils } from '@Utils/style-utils';
import GoogleMeetMeetingCard from './meeting/GoogleMeetCard';
import GoogleMeetForm from './meeting/GoogleMeetForm';
import ZoomMeetingCard from './meeting/ZoomMeetingCard';
import ZoomMeetingForm from './meeting/ZoomMeetingForm';

import emptyStateImage2x from '@Images/empty-state-illustration-2x.webp';
import emptyStateImage from '@Images/empty-state-illustration.webp';

const isPro = !!tutorConfig.tutor_pro_url;
const isZoomAddonEnabled = isAddonEnabled(Addons.TUTOR_ZOOM_INTEGRATION);
const isGoogleMeetAddonEnabled = isAddonEnabled(Addons.TUTOR_GOOGLE_MEET_INTEGRATION);

const courseId = getCourseId();

const LiveClass = () => {
  const queryClient = useQueryClient();
  const courseDetails = queryClient.getQueryData(['CourseDetails', courseId]) as CourseDetailsResponse;

  const zoomMeetings = courseDetails?.zoom_meetings ?? ([] as ZoomMeeting[]);
  const zoomUsers = courseDetails?.zoom_users ?? ({} as { [key: string]: string });

  const googleMeetMeetings = courseDetails?.google_meet_meetings ?? ([] as GoogleMeet[]);

  const [showMeetingForm, setShowMeetingForm] = useState<MeetingType | null>(null);

  const zoomButtonRef = useRef<HTMLButtonElement>(null);
  const googleMeetButtonRef = useRef<HTMLButtonElement>(null);

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
            emptyStateImage={emptyStateImage}
            emptyStateImage2x={emptyStateImage2x}
            imageAltText={__('No live class addons found', 'tutor')}
            title={__('Make the learning more interactive and fun using Live class feature! ', 'tutor')}
            description={__(
              'when an unknown printer took a galley of type and scrambled it to make a type specimen book.',
              'tutor',
            )}
            actions={
              <Button
                size="small"
                icon={<SVGIcon name="crown" width={24} height={24} />}
                onClick={() => {
                  window.open(config.TUTOR_PRICING_PAGE, '_blank', 'noopener');
                }}
              >
                {__('Get Tutor LMS Pro', 'tutor')}
              </Button>
            }
          />
        }
      >
        <Show
          when={isZoomAddonEnabled || isGoogleMeetAddonEnabled}
          fallback={
            <EmptyState
              size="small"
              removeBorder={false}
              emptyStateImage={emptyStateImage}
              emptyStateImage2x={emptyStateImage2x}
              imageAltText={__('No live class addons found', 'tutor')}
              title={__('You can use this feature by activating Google meet Or Zoom from addons', 'tutor')}
              description={__(
                'when an unknown printer took a galley of type and scrambled it to make a type specimen book.',
                'tutor',
              )}
              actions={
                <Button
                  variant="secondary"
                  onClick={() => {
                    window.open(config.TUTOR_ADDONS_PAGE, '_blank', 'noopener');
                  }}
                >
                  {__('Go to addons', 'tutor')}
                </Button>
              }
            />
          }
        >
          <Show when={isZoomAddonEnabled}>
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
                    <ZoomMeetingCard data={meeting} meetingHost={zoomUsers} />
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

          <Show when={isGoogleMeetAddonEnabled}>
            <div
              css={styles.meetingsWrapper({
                hasMeeting: googleMeetMeetings.length > 0,
              })}
            >
              <For each={googleMeetMeetings}>
                {(meeting) => (
                  <div
                    key={meeting.ID}
                    css={styles.meeting({
                      hasMeeting: googleMeetMeetings.length > 0,
                    })}
                  >
                    <GoogleMeetMeetingCard data={meeting} />
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
          </Show>
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
        />
      </Popover>
      <Popover
        triggerRef={googleMeetButtonRef}
        isOpen={showMeetingForm === 'google_meet'}
        closePopover={() => setShowMeetingForm(null)}
        animationType={AnimationType.slideUp}
      >
        <GoogleMeetForm
          data={null}
          onCancel={() => {
            setShowMeetingForm(null);
          }}
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
    background-color: ${colorTokens.background.white};
    border-radius: ${borderRadius.card};

    ${
      hasMeeting &&
      css`
        border: 1px solid ${colorTokens.stroke.default};
      `
    }
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
